<?php

namespace Database\Seeders;

use App\Models\Api;
use App\Models\ApiVersion;
use App\Models\Domain;
use App\Models\RestDetail;
use App\Models\SoapDetail;
use App\Models\System;
use App\Models\Technology;
use App\Models\TpsMetric;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ApiDocumentationSeeder extends Seeder
{
    public function run(): void
    {
        $salesforce = Vendor::firstOrCreate(['name' => 'Salesforce']);
        $sap = Vendor::firstOrCreate(['name' => 'SAP']);
        $stripe = Vendor::firstOrCreate(['name' => 'Stripe']);

        $enterpriseDomain = Domain::where('slug', 'enterprise')->firstOrFail();

        $crm = System::updateOrCreate(
            ['name' => 'CRM System'],
            [
                'vendor_id' => $salesforce->id,
                'domain_id' => $enterpriseDomain->id,
                'description' => 'Customer relationship management platform',
                'system_type' => 'CRM',
                'icon' => 'fa-users',
            ]
        );

        $erp = System::updateOrCreate(
            ['name' => 'ERP System'],
            [
                'vendor_id' => $sap->id,
                'domain_id' => $enterpriseDomain->id,
                'description' => 'Enterprise resource planning',
                'system_type' => 'ERP',
                'icon' => 'fa-building',
            ]
        );

        $finance = System::updateOrCreate(
            ['name' => 'Finance System'],
            [
                'vendor_id' => $sap->id,
                'domain_id' => $enterpriseDomain->id,
                'description' => 'Finance and billing systems',
                'system_type' => 'Finance',
            ]
        );

        $payment = System::updateOrCreate(
            ['name' => 'Payment Gateway'],
            [
                'vendor_id' => $stripe->id,
                'domain_id' => $enterpriseDomain->id,
                'description' => 'Payment processing service',
                'system_type' => 'Payment Gateway',
                'icon' => 'fa-credit-card',
                'parent_system_id' => $finance->id,
            ]
        );

        $this->seedApi('Get Customer',
            ['type' => 'rest', 'description' => 'Retrieve customer details by ID', 'owner_system_id' => $crm->id],
            [
                'version' => '1.0.0',
                'endpoint_url' => 'https://api.example.com/v1/customers/{id}',
                'request_format' => 'JSON',
                'response_format' => 'JSON',
                'authentication_type' => 'Bearer',
                'status' => 'deprecated',
                'is_default' => false,
            ],
            ['http_method' => 'GET'],
            null,
            [$crm->id, $erp->id],
            1250
        );
        $this->seedApi('Get Customer',
            ['type' => 'rest', 'description' => 'Retrieve customer details by ID', 'owner_system_id' => $crm->id],
            [
                'version' => '2.0.0',
                'endpoint_url' => 'https://api.example.com/v2/customers/{id}',
                'request_format' => 'JSON',
                'response_format' => 'JSON',
                'authentication_type' => 'Bearer',
            ],
            ['http_method' => 'GET']
        );

        $this->seedApi('Sync Contacts',
            ['type' => 'soap', 'description' => 'Synchronize contact records with ERP', 'owner_system_id' => $crm->id],
            [
                'endpoint_url' => 'https://crm.example.com/soap/contacts',
                'request_format' => 'XML',
                'response_format' => 'XML',
            ],
            null,
            ['method_name' => 'SyncContacts', 'wsdl_url' => 'https://crm.example.com/wsdl'],
            [$crm->id, $erp->id],
            430
        );

        $this->seedApi('Process Order',
            ['type' => 'soap', 'description' => 'Process a new order via payment gateway', 'owner_system_id' => $erp->id],
            [
                'endpoint_url' => 'https://erp.example.com/soap/orders',
                'request_format' => 'XML',
                'response_format' => 'XML',
            ],
            null,
            ['method_name' => 'ProcessOrder'],
            [$erp->id, $payment->id, $crm->id],
            850
        );

        $this->seedApi('Get Inventory',
            ['type' => 'rest', 'description' => 'Get current inventory levels', 'owner_system_id' => $erp->id],
            [
                'endpoint_url' => 'https://erp.example.com/api/inventory',
                'request_format' => 'JSON',
                'response_format' => 'JSON',
                'authentication_type' => 'API Key',
            ],
            ['http_method' => 'GET'],
            null,
            [$erp->id],
            620
        );

        $this->seedApi('Charge Card',
            ['type' => 'rest', 'description' => 'Charge a credit card', 'owner_system_id' => $payment->id],
            [
                'version' => '2.0.0',
                'endpoint_url' => 'https://pay.example.com/v2/charges',
                'request_format' => 'JSON',
                'response_format' => 'JSON',
                'authentication_type' => 'Bearer',
            ],
            ['http_method' => 'POST'],
            null,
            [$payment->id, $erp->id],
            2000
        );
        $this->seedApi('Charge Card',
            ['type' => 'rest', 'description' => 'Charge a credit card', 'owner_system_id' => $payment->id],
            [
                'version' => '1.0.0',
                'endpoint_url' => 'https://pay.example.com/v1/charges',
                'request_format' => 'JSON',
                'response_format' => 'JSON',
                'authentication_type' => 'Bearer',
                'status' => 'deprecated',
                'is_default' => false,
            ],
            ['http_method' => 'POST']
        );

        $graphUser = $this->seedApi('GraphQL User Query',
            ['type' => 'graphql', 'description' => 'Fetch user profile via GraphQL', 'owner_system_id' => $crm->id],
            [
                'endpoint_url' => 'https://crm.example.com/graphql',
                'request_format' => 'JSON',
                'response_format' => 'JSON',
                'authentication_type' => 'Bearer',
                'protocol_details' => [
                    'operation_type' => 'query',
                    'schema_url' => 'https://crm.example.com/graphql/schema',
                    'operation_name' => 'GetUser',
                    'query' => 'query GetUser($id: ID!) { user(id: $id) { id name email } }',
                ],
            ],
            null,
            null,
            [$crm->id, $erp->id]
        );

        $inventoryRpc = $this->seedApi('Inventory gRPC Stream',
            ['type' => 'grpc', 'description' => 'Stream inventory updates over gRPC', 'owner_system_id' => $erp->id],
            [
                'endpoint_url' => 'grpc://erp.example.com:50051',
                'protocol_details' => [
                    'service_name' => 'inventory.InventoryService',
                    'method_name' => 'StreamLevels',
                    'proto_url' => 'https://erp.example.com/proto/inventory.proto',
                    'rpc_type' => 'server_streaming',
                ],
            ],
            null,
            null,
            [$erp->id]
        );

        $liveOrders = $this->seedApi('Live Order Feed',
            ['type' => 'websocket', 'description' => 'Real-time order status over WebSocket', 'owner_system_id' => $erp->id],
            [
                'endpoint_url' => 'wss://erp.example.com/ws/orders',
                'request_format' => 'JSON',
                'protocol_details' => [
                    'subprotocol' => 'json',
                    'message_format' => 'JSON',
                ],
            ],
            null,
            null,
            [$erp->id, $crm->id]
        );

        $metricsStream = $this->seedApi('Metrics SSE Stream',
            ['type' => 'sse', 'description' => 'Server-sent events for system metrics', 'owner_system_id' => $erp->id],
            [
                'endpoint_url' => 'https://erp.example.com/events/metrics',
                'protocol_details' => [
                    'event_types' => 'metric,heartbeat',
                    'retry_interval' => 3000,
                ],
            ],
            null,
            null,
            [$erp->id]
        );

        $orderSocket = $this->seedApi('Order Socket.IO Hub',
            ['type' => 'socketio', 'description' => 'Socket.IO hub for order notifications', 'owner_system_id' => $crm->id],
            [
                'endpoint_url' => 'https://crm.example.com/socket.io',
                'protocol_details' => [
                    'namespace' => '/orders',
                    'transport' => 'websocket',
                    'events_emit' => ['order:subscribe', 'order:ack'],
                    'events_listen' => ['order:created', 'order:updated'],
                ],
            ],
            null,
            null,
            [$crm->id, $payment->id]
        );

        $this->seedNonApiIntegrations($crm, $erp, $finance, $payment);
        $this->seedSystemTechnologies($crm, $erp, $finance, $payment);
    }

    private function seedNonApiIntegrations(System $crm, System $erp, System $finance, System $payment): void
    {
        $this->seedApi('ERP Daily Export (SFTP)',
            ['type' => 'sftp', 'description' => 'Nightly CSV export to finance partner — no HTTP API', 'owner_system_id' => $erp->id],
            [
                'endpoint_url' => 'sftp.finance-partner.internal',
                'authentication_type' => 'SSH Key',
                'protocol_details' => [
                    'port' => 22,
                    'remote_path' => '/inbound/erp',
                    'direction' => 'push',
                    'auth_method' => 'ssh_key',
                    'file_pattern' => 'orders_*.csv',
                ],
            ],
            null,
            null,
            [$erp->id, $finance->id]
        );

        $this->seedApi('Compliance Archive (FTPS)',
            ['type' => 'ftps', 'description' => 'Regulatory document archive via FTPS', 'owner_system_id' => $finance->id],
            [
                'endpoint_url' => 'ftps.archive.internal',
                'authentication_type' => 'Username/Password',
                'protocol_details' => [
                    'port' => 990,
                    'remote_path' => '/archive/compliance',
                    'direction' => 'push',
                    'passive_mode' => true,
                    'file_pattern' => '*.pdf',
                ],
            ],
            null,
            null,
            [$finance->id]
        );

        $this->seedApi('ERP Infrastructure Monitoring',
            ['type' => 'zabbix', 'description' => 'Zabbix agents and SNMP traps for ERP hosts', 'owner_system_id' => $erp->id],
            [
                'endpoint_url' => 'https://zabbix.ops.internal',
                'protocol_details' => [
                    'agent_type' => 'agent',
                    'host_group' => 'Production/ERP',
                    'template' => 'Template App HTTP Service',
                    'monitored_host' => 'erp-app-01.internal',
                    'trigger_severity' => 'high, disaster',
                ],
            ],
            null,
            null,
            [$erp->id]
        );

        $this->seedApi('Security Event Feed (SIEM)',
            ['type' => 'siem', 'description' => 'CEF/syslog forwarding to corporate SIEM', 'owner_system_id' => $crm->id],
            [
                'endpoint_url' => 'siem-collector.security.internal',
                'authentication_type' => 'Syslog',
                'protocol_details' => [
                    'platform' => 'QRadar',
                    'log_format' => 'cef',
                    'ingestion_method' => 'syslog',
                    'source_index' => 'crm_security',
                    'port' => 514,
                ],
            ],
            null,
            null,
            [$crm->id, $payment->id]
        );

        $this->seedApi('Payment Gateway Logs (Splunk)',
            ['type' => 'splunk', 'description' => 'Splunk HEC ingestion for payment audit logs', 'owner_system_id' => $payment->id],
            [
                'endpoint_url' => 'https://splunk-hec.ops.internal',
                'authentication_type' => 'HEC Token',
                'protocol_details' => [
                    'ingestion_type' => 'hec',
                    'index' => 'security',
                    'sourcetype' => '_json',
                    'source' => 'payment-gateway',
                    'hec_port' => 8088,
                ],
            ],
            null,
            null,
            [$payment->id, $finance->id]
        );
    }

    private function seedSystemTechnologies(System $crm, System $erp, System $finance, System $payment): void
    {
        $crm->technologies()->sync($this->techSync([
            ['PHP', 'programming_language', '8.3'],
            ['JavaScript', 'programming_language', 'ES2022'],
            ['Laravel', 'framework', '11'],
            ['Docker', 'container', '24'],
            ['nginx', 'web_server', '1.25'],
            ['PostgreSQL', 'database', '16'],
            ['Redis', 'database', '7'],
        ]));

        $erp->technologies()->sync($this->techSync([
            ['Java', 'programming_language', '17'],
            ['Spring Boot', 'framework', '3.2'],
            ['Docker', 'container', '24'],
            ['Kubernetes', 'orchestration', '1.29'],
            ['MySQL', 'database', '8'],
            ['Kafka', 'messaging', '3.6'],
        ]));

        $finance->technologies()->sync($this->techSync([
            ['Java', 'programming_language', '17'],
            ['Docker', 'container', '24'],
            ['PostgreSQL', 'database', '16'],
        ]));

        $payment->technologies()->sync($this->techSync([
            ['Ruby', 'programming_language', '3.2'],
            ['Docker', 'container', '24'],
            ['Kubernetes', 'orchestration', '1.29'],
            ['nginx', 'web_server', '1.25'],
            ['Redis', 'database', '7'],
            ['AWS', 'cloud', null],
        ]));
    }

    /** @param list<array{0: string, 1: string, 2: ?string}> $pairs */
    private function techSync(array $pairs): array
    {
        $sync = [];

        foreach ($pairs as [$name, $category, $version]) {
            $id = Technology::where('name', $name)->where('category', $category)->value('id');
            if ($id) {
                $sync[$id] = ['version' => $version];
            }
        }

        return $sync;
    }

    /**
     * @param  array<string, mixed>  $apiAttributes
     * @param  array<string, mixed>  $versionAttributes
     * @param  array<string, mixed>|null  $restDetail
     * @param  array<string, mixed>|null  $soapDetail
     * @param  array<int>|null  $systemIds
     */
    private function seedApi(
        string $name,
        array $apiAttributes,
        array $versionAttributes = [],
        ?array $restDetail = null,
        ?array $soapDetail = null,
        ?array $systemIds = null,
        ?float $tps = null,
    ): Api {
        $api = Api::updateOrCreate(['name' => $name], $apiAttributes);

        $versionLabel = $versionAttributes['version'] ?? '1.0.0';
        $isDefault = $versionAttributes['is_default'] ?? true;

        if ($isDefault) {
            $api->versions()->update(['is_default' => false]);
        }

        $version = ApiVersion::updateOrCreate(
            ['api_id' => $api->id, 'version' => $versionLabel],
            array_merge([
                'endpoint_url' => null,
                'status' => 'active',
                'is_default' => $isDefault,
            ], $versionAttributes, ['version' => $versionLabel])
        );

        if ($restDetail) {
            RestDetail::updateOrCreate(['api_version_id' => $version->id], $restDetail);
        }

        if ($soapDetail) {
            SoapDetail::updateOrCreate(['api_version_id' => $version->id], $soapDetail);
        }

        if ($systemIds !== null) {
            $api->systems()->sync($systemIds);
        }

        if ($tps !== null) {
            TpsMetric::updateOrCreate(
                ['api_id' => $api->id, 'recorded_at' => now()->startOfDay()],
                ['tps_value' => $tps]
            );
        }

        return $api;
    }
}
