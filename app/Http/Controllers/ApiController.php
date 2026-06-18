<?php

namespace App\Http\Controllers;

use App\Events\ApiDocumentationUpdated;
use App\Models\Api;
use App\Models\ApiVersion;
use App\Models\System;
use App\Models\Vendor;
use App\Services\OpenApiImporter;
use App\Services\OpenApiSpecBuilder;
use App\Services\ProtocolSpecBuilder;
use App\Services\SoapSpecBuilder;
use App\Services\TpsService;
use App\Services\WsdlImporter;
use App\Support\ApiTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiController extends Controller
{
    public function __construct(
        private OpenApiImporter $openApiImporter,
        private WsdlImporter $wsdlImporter,
        private TpsService $tpsService,
        private OpenApiSpecBuilder $openApiSpecBuilder,
        private SoapSpecBuilder $soapSpecBuilder,
        private ProtocolSpecBuilder $protocolSpecBuilder,
    ) {}

    public function index(Request $request): View
    {
        $query = Api::with(['systems.vendor', 'ownerSystem.vendor', 'latestTps', 'defaultVersion', 'versions']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($vendorId = $request->integer('vendor_id')) {
            $query->forVendor($vendorId);
        }

        if ($systemId = $request->get('system_id')) {
            $query->where(function ($q) use ($systemId) {
                $q->where('owner_system_id', $systemId)
                    ->orWhereHas('systems', fn ($sq) => $sq->where('systems.id', $systemId));
            });
        }

        $apis = $query->orderBy('name')->get();
        $systems = System::with('vendor')->orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('apis.index', compact('apis', 'systems', 'vendors'));
    }

    public function create(Request $request): View
    {
        $systems = System::with('vendor')->orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $defaultOwnerSystemId = $request->integer('system_id') ?: old('owner_system_id');
        $defaultVendorId = $request->integer('vendor_id') ?: old('vendor_id');

        return view('apis.create', compact('systems', 'vendors', 'defaultOwnerSystemId', 'defaultVendorId'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate($this->validationRules());

        $api = Api::create(collect($validated)->only([
            'name', 'type', 'description', 'owner_system_id',
        ])->toArray());

        $versionLabel = $validated['version'] ?? '1.0.0';
        $version = $api->versions()->create([
            'version' => $versionLabel,
            'endpoint_url' => $validated['endpoint_url'] ?? null,
            'description' => $validated['version_description'] ?? null,
            'request_format' => $validated['request_format'] ?? null,
            'response_format' => $validated['response_format'] ?? null,
            'authentication_type' => $validated['authentication_type'] ?? null,
            'status' => 'active',
            'is_default' => true,
        ]);

        $this->syncTypeDetails($api, $version, $validated['type'], $validated);
        $this->syncApiSystems($api, $validated['owner_system_id'] ?? null, $validated['system_ids'] ?? null);

        ApiDocumentationUpdated::dispatch($api->load('ownerSystem'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'API created successfully.', 'api_id' => $api->id]);
        }

        return redirect()->route('apis.show', $api)->with('success', 'API created successfully.');
    }

    public function show(Request $request, Api $api): View
    {
        $api->load([
            'systems.vendor',
            'ownerSystem.vendor',
            'versions.restDetail',
            'versions.soapDetail',
            'tpsMetrics' => fn ($q) => $q->orderByDesc('recorded_at')->limit(50),
        ]);

        $activeVersion = $api->resolveVersion($request->integer('version') ?: null);
        $systems = System::with('vendor')->orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $chartData = $this->tpsService->getChartData($api);

        $openApiSpec = $api->isRest() ? $this->openApiSpecBuilder->build($api, $activeVersion) : null;
        $soapSpec = $api->isSoap() ? $this->soapSpecBuilder->build($api, $activeVersion) : null;
        $protocolSpec = $api->hasProtocolSpec() ? $this->protocolSpecBuilder->build($api, $activeVersion) : null;

        return view('apis.show', compact(
            'api', 'activeVersion', 'systems', 'vendors', 'chartData', 'openApiSpec', 'soapSpec', 'protocolSpec'
        ));
    }

    public function spec(Request $request, Api $api): JsonResponse
    {
        $api->load(['versions.restDetail', 'versions.soapDetail']);
        $activeVersion = $api->resolveVersion($request->integer('version') ?: null);

        if ($api->isRest()) {
            return response()->json($this->openApiSpecBuilder->build($api, $activeVersion));
        }

        if ($api->isSoap()) {
            return response()->json($this->soapSpecBuilder->build($api, $activeVersion));
        }

        return response()->json($this->protocolSpecBuilder->build($api, $activeVersion));
    }

    public function edit(Request $request, Api $api): View
    {
        $api->load(['versions.restDetail', 'versions.soapDetail', 'systems', 'ownerSystem.vendor', 'versions']);
        $activeVersion = $api->resolveVersion($request->integer('version') ?: null);
        $systems = System::with('vendor')->orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('apis.edit', compact('api', 'activeVersion', 'systems', 'vendors'));
    }

    public function update(Request $request, Api $api): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        $api->update(collect($validated)->only([
            'name', 'type', 'description', 'owner_system_id',
        ])->toArray());

        $activeVersion = $api->resolveVersion($request->integer('version') ?: null);

        $activeVersion->update(collect($validated)->only([
            'endpoint_url', 'request_format', 'response_format', 'authentication_type',
        ])->toArray());

        $this->syncTypeDetails($api, $activeVersion, $validated['type'], $validated);
        $this->syncApiSystems($api, $validated['owner_system_id'] ?? null, $validated['system_ids'] ?? null);

        ApiDocumentationUpdated::dispatch($api->load('ownerSystem'));

        return redirect()->route('apis.show', $api)->with('success', 'API updated successfully.');
    }

    public function destroy(Api $api): RedirectResponse
    {
        $api->delete();

        return redirect()->route('apis.index')->with('success', 'API deleted successfully.');
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'import_type' => 'required|in:openapi,wsdl',
            'file' => 'required|file|max:10240',
            'base_url' => 'nullable|string|max:2048',
        ]);

        try {
            if ($request->import_type === 'openapi') {
                $created = $this->openApiImporter->import($request->file('file'), $request->base_url);
            } else {
                $created = $this->wsdlImporter->import($request->file('file'));
            }

            return response()->json([
                'success' => true,
                'message' => count($created).' API(s) imported successfully.',
                'count' => count($created),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function addTps(Request $request, Api $api): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'tps_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'recorded_at' => 'nullable|date',
        ]);

        $this->tpsService->record(
            $api,
            (float) $validated['tps_value'],
            $validated['notes'] ?? null,
            isset($validated['recorded_at']) ? new \DateTime($validated['recorded_at']) : null
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'TPS recorded successfully.']);
        }

        return redirect()->route('apis.show', $api)->with('success', 'TPS recorded successfully.');
    }

    public function getSystems(Api $api): JsonResponse
    {
        return response()->json($api->systems);
    }

    public function attachSystem(Request $request, Api $api): JsonResponse
    {
        $request->validate(['system_id' => 'required|exists:systems,id']);
        $api->systems()->syncWithoutDetaching([$request->system_id]);

        return response()->json(['success' => true, 'message' => 'System linked successfully.']);
    }

    public function detachSystem(Api $api, System $system): JsonResponse
    {
        $api->systems()->detach($system->id);

        return response()->json(['success' => true, 'message' => 'System unlinked successfully.']);
    }

    /** @return array<string, mixed> */
    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => ApiTypes::validationRule(),
            'version' => 'nullable|string|max:50',
            'version_description' => 'nullable|string',
            'endpoint_url' => 'nullable|string|max:2048',
            'description' => 'nullable|string',
            'request_format' => 'nullable|string|max:50',
            'response_format' => 'nullable|string|max:50',
            'authentication_type' => 'nullable|string|max:100',
            'owner_system_id' => 'nullable|exists:systems,id',
            'system_ids' => 'nullable|array',
            'system_ids.*' => 'exists:systems,id',
            'http_method' => 'required_if:type,rest|nullable|string|max:10',
            'request_parameters' => 'nullable|string',
            'response_schema' => 'nullable|string',
            'wsdl_url' => 'nullable|string|max:2048',
            'namespace' => 'nullable|string|max:512',
            'soap_action' => 'nullable|string|max:512',
            'method_name' => 'nullable|string|max:255',
            'graphql_operation_type' => 'nullable|in:query,mutation,subscription',
            'graphql_schema_url' => 'nullable|string|max:2048',
            'graphql_operation_name' => 'nullable|string|max:255',
            'graphql_query' => 'nullable|string',
            'grpc_service_name' => 'nullable|string|max:255',
            'grpc_method_name' => 'nullable|string|max:255',
            'grpc_proto_url' => 'nullable|string|max:2048',
            'grpc_rpc_type' => 'nullable|in:unary,server_streaming,client_streaming,bidirectional',
            'websocket_subprotocol' => 'nullable|string|max:255',
            'websocket_message_format' => 'nullable|string|max:50',
            'websocket_handshake_headers' => 'nullable|string',
            'sse_event_types' => 'nullable|string|max:512',
            'sse_retry_interval' => 'nullable|integer|min:0',
            'socketio_namespace' => 'nullable|string|max:255',
            'socketio_transport' => 'nullable|in:websocket,polling',
            'socketio_events_emit' => 'nullable|string',
            'socketio_events_listen' => 'nullable|string',
            'ftps_port' => 'nullable|integer|min:1|max:65535',
            'ftps_remote_path' => 'nullable|string|max:512',
            'ftps_direction' => 'nullable|in:push,pull,bidirectional',
            'ftps_passive_mode' => 'nullable|in:0,1',
            'ftps_file_pattern' => 'nullable|string|max:255',
            'sftp_port' => 'nullable|integer|min:1|max:65535',
            'sftp_remote_path' => 'nullable|string|max:512',
            'sftp_direction' => 'nullable|in:push,pull,bidirectional',
            'sftp_auth_method' => 'nullable|in:password,ssh_key,both',
            'sftp_file_pattern' => 'nullable|string|max:255',
            'zabbix_agent_type' => 'nullable|in:agent,snmp,ipmi,jmx,trap',
            'zabbix_host_group' => 'nullable|string|max:255',
            'zabbix_template' => 'nullable|string|max:255',
            'zabbix_monitored_host' => 'nullable|string|max:255',
            'zabbix_trigger_severity' => 'nullable|string|max:255',
            'siem_platform' => 'nullable|string|max:255',
            'siem_log_format' => 'nullable|in:syslog,cef,leef,json,raw',
            'siem_ingestion_method' => 'nullable|in:syslog,tcp,udp,file,agent',
            'siem_source_index' => 'nullable|string|max:255',
            'siem_port' => 'nullable|integer|min:1|max:65535',
            'splunk_ingestion_type' => 'nullable|in:hec,forwarder,syslog,s2s',
            'splunk_index' => 'nullable|string|max:255',
            'splunk_sourcetype' => 'nullable|string|max:255',
            'splunk_source' => 'nullable|string|max:255',
            'splunk_hec_port' => 'nullable|integer|min:1|max:65535',
        ];
    }

    private function syncTypeDetails(Api $api, ApiVersion $version, string $type, array $validated): void
    {
        if ($type === ApiTypes::REST) {
            $version->soapDetail?->delete();
            $version->update(['protocol_details' => null]);
            $version->restDetail()->updateOrCreate(
                ['api_version_id' => $version->id],
                [
                    'http_method' => $validated['http_method'] ?? 'GET',
                    'request_parameters' => $this->parseJsonField($validated['request_parameters'] ?? null),
                    'response_schema' => $this->parseJsonField($validated['response_schema'] ?? null),
                    'openapi_spec' => null,
                ]
            );
            $version->load('restDetail');
            $this->openApiSpecBuilder->buildAndPersist($api, $version);

            return;
        }

        if ($type === ApiTypes::SOAP) {
            $version->restDetail?->delete();
            $version->update(['protocol_details' => null]);
            $version->soapDetail()->updateOrCreate(
                ['api_version_id' => $version->id],
                [
                    'wsdl_url' => $validated['wsdl_url'] ?? null,
                    'namespace' => $validated['namespace'] ?? null,
                    'soap_action' => $validated['soap_action'] ?? null,
                    'method_name' => $validated['method_name'] ?? null,
                ]
            );
            $version->load('soapDetail');
            $version->soapDetail->update(['operation_spec' => $this->soapSpecBuilder->buildFromFields($api, $version)]);

            return;
        }

        $version->restDetail?->delete();
        $version->soapDetail?->delete();
        $version->update(['protocol_details' => $this->buildProtocolDetails($type, $validated)]);
    }

    /** @return array<string, mixed> */
    private function buildProtocolDetails(string $type, array $validated): array
    {
        $details = match ($type) {
            ApiTypes::GRAPHQL => [
                'operation_type' => $validated['graphql_operation_type'] ?? 'query',
                'schema_url' => $validated['graphql_schema_url'] ?? null,
                'operation_name' => $validated['graphql_operation_name'] ?? null,
                'query' => $validated['graphql_query'] ?? null,
            ],
            ApiTypes::GRPC => [
                'service_name' => $validated['grpc_service_name'] ?? null,
                'method_name' => $validated['grpc_method_name'] ?? null,
                'proto_url' => $validated['grpc_proto_url'] ?? null,
                'rpc_type' => $validated['grpc_rpc_type'] ?? 'unary',
            ],
            ApiTypes::WEBSOCKET => [
                'subprotocol' => $validated['websocket_subprotocol'] ?? null,
                'message_format' => $validated['websocket_message_format'] ?? null,
                'handshake_headers' => $this->parseJsonField($validated['websocket_handshake_headers'] ?? null),
            ],
            ApiTypes::SSE => [
                'event_types' => $validated['sse_event_types'] ?? null,
                'retry_interval' => isset($validated['sse_retry_interval']) ? (int) $validated['sse_retry_interval'] : null,
            ],
            ApiTypes::SOCKETIO => [
                'namespace' => $validated['socketio_namespace'] ?? '/',
                'transport' => $validated['socketio_transport'] ?? 'websocket',
                'events_emit' => $this->parseJsonField($validated['socketio_events_emit'] ?? null),
                'events_listen' => $this->parseJsonField($validated['socketio_events_listen'] ?? null),
            ],
            ApiTypes::FTPS => [
                'port' => isset($validated['ftps_port']) ? (int) $validated['ftps_port'] : 990,
                'remote_path' => $validated['ftps_remote_path'] ?? null,
                'direction' => $validated['ftps_direction'] ?? 'push',
                'passive_mode' => ($validated['ftps_passive_mode'] ?? '1') === '1',
                'file_pattern' => $validated['ftps_file_pattern'] ?? null,
            ],
            ApiTypes::SFTP => [
                'port' => isset($validated['sftp_port']) ? (int) $validated['sftp_port'] : 22,
                'remote_path' => $validated['sftp_remote_path'] ?? null,
                'direction' => $validated['sftp_direction'] ?? 'pull',
                'auth_method' => $validated['sftp_auth_method'] ?? 'ssh_key',
                'file_pattern' => $validated['sftp_file_pattern'] ?? null,
            ],
            ApiTypes::ZABBIX => [
                'agent_type' => $validated['zabbix_agent_type'] ?? 'agent',
                'host_group' => $validated['zabbix_host_group'] ?? null,
                'template' => $validated['zabbix_template'] ?? null,
                'monitored_host' => $validated['zabbix_monitored_host'] ?? null,
                'trigger_severity' => $validated['zabbix_trigger_severity'] ?? null,
            ],
            ApiTypes::SIEM => [
                'platform' => $validated['siem_platform'] ?? null,
                'log_format' => $validated['siem_log_format'] ?? 'cef',
                'ingestion_method' => $validated['siem_ingestion_method'] ?? 'syslog',
                'source_index' => $validated['siem_source_index'] ?? null,
                'port' => isset($validated['siem_port']) ? (int) $validated['siem_port'] : 514,
            ],
            ApiTypes::SPLUNK => [
                'ingestion_type' => $validated['splunk_ingestion_type'] ?? 'hec',
                'index' => $validated['splunk_index'] ?? null,
                'sourcetype' => $validated['splunk_sourcetype'] ?? null,
                'source' => $validated['splunk_source'] ?? null,
                'hec_port' => isset($validated['splunk_hec_port']) ? (int) $validated['splunk_hec_port'] : 8088,
            ],
            default => [],
        };

        return array_filter($details, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    private function syncApiSystems(Api $api, ?int $ownerSystemId, ?array $systemIds): void
    {
        $ids = array_values(array_filter(array_unique(array_merge(
            $ownerSystemId ? [$ownerSystemId] : [],
            $systemIds ?? []
        ))));

        $api->systems()->sync($ids);
    }

    private function parseJsonField(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $value];
    }
}
