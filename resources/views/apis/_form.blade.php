@php
    use App\Support\ApiTypes;
    $isEdit = isset($api);
    $version = $activeVersion ?? ($isEdit ? $api->defaultVersion : null);
    $defaultOwner = $defaultOwnerSystemId ?? null;
    $existingOwner = $isEdit ? ($api->owner_system_id ?? $api->systems->first()?->id) : null;
    $ownerSystemId = old('owner_system_id', $existingOwner ?? $defaultOwner);
    $selectedAdditionalSystems = old('system_ids', $isEdit
        ? $api->systems->pluck('id')->reject(fn ($id) => (int) $id === (int) $ownerSystemId)->values()->toArray()
        : []);
    $defaultVendor = $defaultVendorId ?? null;
    $selectedVendorId = old('vendor_id', $isEdit ? ($api->ownerSystem?->vendor_id) : $defaultVendor);
    $type = old('type', $isEdit ? $api->type : 'rest');
    $vendors = $vendors ?? collect();
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">API Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $isEdit ? $api->name : '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" id="api-type" class="form-control" required>
            <optgroup label="{{ ApiTypes::GROUP_API }}">
                @foreach(ApiTypes::API_PROTOCOLS as $apiType)
                    <option value="{{ $apiType }}" @selected($type === $apiType)>{{ ApiTypes::label($apiType) }}</option>
                @endforeach
            </optgroup>
            <optgroup label="{{ ApiTypes::GROUP_NON_API }}">
                @foreach(ApiTypes::NON_API_INTEGRATIONS as $apiType)
                    <option value="{{ $apiType }}" @selected($type === $apiType)>{{ ApiTypes::label($apiType) }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>
</div>

<div class="card bg-light mb-3">
    <div class="card-body py-3">
        <h6 class="mb-3">Vendor &amp; System Assignment</h6>
        <p class="text-muted small mb-3">Each API belongs to one <strong>owner system</strong> (under a vendor) and can integrate with other systems across vendors.</p>
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <label class="form-label">Vendor</label>
                @if($vendors->isEmpty())
                    <div class="alert alert-warning mb-0 py-2 small">
                        No vendors yet.
                        <a href="{{ route('supplier.index') }}" class="alert-link">Add a vendor</a>
                        first.
                    </div>
                @else
                    <select id="vendor-filter" class="form-control">
                        <option value="">All vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected((int) $selectedVendorId === $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Filter owner system list by vendor</small>
                @endif
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <label class="form-label">Owner System</label>
                @if($systems->isEmpty())
                    <div class="alert alert-warning mb-0 py-2 small">
                        No systems yet.
                        <a href="{{ route('systems.index') }}" class="alert-link">Create a system</a>
                        first.
                    </div>
                @else
                    <select name="owner_system_id" id="owner-system-id" class="form-control">
                        <option value="">— Select owner system —</option>
                        @foreach($systems as $system)
                            <option value="{{ $system->id }}"
                                    data-vendor-id="{{ $system->vendor_id ?? '' }}"
                                    @selected((int) $ownerSystemId === $system->id)>
                                {{ $system->name }}@if($system->vendor) ({{ $system->vendor->name }})@endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">System that owns this API</small>
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label">Integrated Systems</label>
                @if($systems->isEmpty())
                    <select name="system_ids[]" class="form-control" multiple disabled size="4"></select>
                @else
                    <select name="system_ids[]" id="additional-system-ids" class="form-control" multiple size="4">
                        @foreach($systems as $system)
                            <option value="{{ $system->id }}" @selected(in_array($system->id, $selectedAdditionalSystems))>
                                {{ $system->name }}@if($system->vendor) — {{ $system->vendor->name }}@endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Other systems this API connects to (any vendor)</small>
                @endif
            </div>
        </div>
    </div>
</div>

@if($isEdit && isset($activeVersion))
    <input type="hidden" name="version" value="{{ $activeVersion->id }}">
    <div class="alert alert-light border mb-3 py-2">
        Editing version <strong>{{ $activeVersion->version }}</strong>
        @if($activeVersion->is_default)
            <span class="badge badge-primary ms-1">Default</span>
        @endif
        <span class="badge badge-{{ $activeVersion->status_badge_class }} ms-1">{{ ucfirst($activeVersion->status) }}</span>
        @if($api->versions->count() > 1)
            <span class="ms-2 small">
                Switch:
                @foreach($api->versions as $v)
                    @if((int) $v->id !== (int) $activeVersion->id)
                        <a href="{{ route('apis.edit', ['api' => $api, 'version' => $v->id]) }}" class="badge badge-light text-decoration-none">{{ $v->version }}</a>
                    @endif
                @endforeach
            </span>
        @endif
    </div>
@else
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Initial Version</label>
            <input type="text" name="version" class="form-control" value="{{ old('version', '1.0.0') }}" placeholder="e.g. 1.0.0, v2">
            <small class="text-muted">First version label for this API</small>
        </div>
    </div>
@endif

<div class="mb-3">
    <label class="form-label" id="endpoint-label">Endpoint URL</label>
    <input type="text" name="endpoint_url" id="endpoint-url" class="form-control" value="{{ old('endpoint_url', $version?->endpoint_url ?? '') }}" placeholder="https://api.example.com/v1 or host:port">
    <small class="text-muted" id="endpoint-hint">Full URL for API protocols; host or connection string for file transfer / monitoring integrations.</small>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $isEdit ? $api->description : '') }}</textarea>
</div>

<div id="api-format-fields" class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Request Format</label>
        <input type="text" name="request_format" class="form-control" value="{{ old('request_format', $version?->request_format ?? 'JSON') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Response Format</label>
        <input type="text" name="response_format" class="form-control" value="{{ old('response_format', $version?->response_format ?? 'JSON') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Authentication Type</label>
        <input type="text" name="authentication_type" class="form-control" value="{{ old('authentication_type', $version?->authentication_type ?? '') }}" placeholder="e.g. Bearer, Basic, SSH Key, HEC Token">
    </div>
</div>

<div id="rest-fields" class="type-fields" style="{{ $type === 'rest' ? '' : 'display:none' }}">
    <hr><h5>REST Details</h5>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">HTTP Method</label>
            <select name="http_method" class="form-control">
                @foreach(['GET','POST','PUT','PATCH','DELETE'] as $method)
                    <option value="{{ $method }}" @selected(old('http_method', ($version?->restDetail) ? $version->restDetail->http_method : 'GET') === $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Request Parameters (JSON)</label>
        <textarea name="request_parameters" class="form-control font-monospace" rows="4" placeholder='[{"name":"id","in":"path","required":true}]'>{{ old('request_parameters', ($version?->restDetail) ? json_encode($version->restDetail->request_parameters, JSON_PRETTY_PRINT) : '') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Response Schema (JSON)</label>
        <textarea name="response_schema" class="form-control font-monospace" rows="4">{{ old('response_schema', ($version?->restDetail) ? json_encode($version->restDetail->response_schema, JSON_PRETTY_PRINT) : '') }}</textarea>
    </div>
</div>

<div id="soap-fields" class="type-fields" style="{{ $type === 'soap' ? '' : 'display:none' }}">
    <hr><h5>SOAP Details</h5>
    <div class="mb-3">
        <label class="form-label">WSDL URL</label>
        <input type="url" name="wsdl_url" class="form-control" value="{{ old('wsdl_url', ($version?->soapDetail) ? $version->soapDetail->wsdl_url : '') }}">
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Namespace</label>
            <input type="text" name="namespace" class="form-control" value="{{ old('namespace', ($version?->soapDetail) ? $version->soapDetail->namespace : '') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Method Name</label>
            <input type="text" name="method_name" class="form-control" value="{{ old('method_name', ($version?->soapDetail) ? $version->soapDetail->method_name : '') }}">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">SOAP Action</label>
        <input type="text" name="soap_action" class="form-control" value="{{ old('soap_action', ($version?->soapDetail) ? $version->soapDetail->soap_action : '') }}">
    </div>
</div>

@include('apis._protocol_fields', ['type' => $type, 'version' => $version])
@include('apis._connection_fields', ['type' => $type, 'version' => $version])
