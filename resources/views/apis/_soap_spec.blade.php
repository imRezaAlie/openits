@php $spec = $soapSpec ?? []; @endphp

<div class="swagger-ui-wrap soap-spec-ui">
    <div class="soap-spec-topbar">
        <div class="soap-spec-topbar-inner">
            <span class="soap-spec-logo">SOAP</span>
            <span class="soap-spec-title">{{ $spec['title'] ?? $api->name }}</span>
        </div>
    </div>

    <div class="soap-spec-info">
        <h2 class="soap-spec-info-title">{{ $spec['title'] ?? $api->name }}</h2>
        @if(!empty($spec['description']))
            <p class="soap-spec-info-desc">{{ $spec['description'] }}</p>
        @endif
        <div class="soap-spec-info-meta">
            @if(!empty($spec['wsdl_url']))
                <span><strong>WSDL:</strong> <a href="{{ $spec['wsdl_url'] }}" target="_blank">{{ $spec['wsdl_url'] }}</a></span>
            @endif
            @if(!empty($spec['authentication_type']))
                <span><strong>Auth:</strong> {{ $spec['authentication_type'] }}</span>
            @endif
        </div>
    </div>

    <div class="soap-opblock soap-opblock-post is-open">
        <div class="soap-opblock-summary">
            <span class="soap-opblock-method soap-method">SOAP</span>
            <span class="soap-opblock-path">{{ $spec['method_name'] ?? $api->name }}</span>
            @if(!empty($spec['endpoint']))
                <span class="soap-opblock-endpoint">{{ $spec['endpoint'] }}</span>
            @endif
        </div>

        <div class="soap-opblock-body">
            @if(!empty($spec['description']))
                <div class="soap-section">
                    <h4>Description</h4>
                    <p>{{ $spec['description'] }}</p>
                </div>
            @endif

            <div class="soap-section">
                <h4>Operation Details</h4>
                <table class="soap-params-table">
                    <thead>
                        <tr><th>Name</th><th>Value</th></tr>
                    </thead>
                    <tbody>
                        @if(!empty($spec['namespace']))
                            <tr><td>Namespace</td><td><code>{{ $spec['namespace'] }}</code></td></tr>
                        @endif
                        @if(!empty($spec['soap_action']))
                            <tr><td>SOAP Action</td><td><code>{{ $spec['soap_action'] }}</code></td></tr>
                        @endif
                        @if(!empty($spec['endpoint']))
                            <tr><td>Endpoint URL</td><td><code>{{ $spec['endpoint'] }}</code></td></tr>
                        @endif
                        <tr><td>Request Format</td><td>{{ $spec['request_format'] ?? 'XML' }}</td></tr>
                        <tr><td>Response Format</td><td>{{ $spec['response_format'] ?? 'XML' }}</td></tr>
                    </tbody>
                </table>
            </div>

            @if(!empty($spec['parameters']))
                <div class="soap-section">
                    <h4>Parameters</h4>
                    <table class="soap-params-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>In</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($spec['parameters'] as $param)
                                <tr>
                                    <td><code>{{ $param['name'] }}</code></td>
                                    <td>{{ $param['in'] ?? 'body' }}</td>
                                    <td>{{ $param['type'] ?? 'string' }}</td>
                                    <td>{{ !empty($param['required']) ? 'Yes' : 'No' }}</td>
                                    <td>{{ $param['description'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="soap-section">
                <h4>Request Body <small class="text-muted">application/xml</small></h4>
                <div class="soap-try-body">
                    <pre class="soap-code-block"><code>{{ $spec['request_example'] ?? '' }}</code></pre>
                </div>
            </div>

            <div class="soap-section">
                <h4>Responses</h4>
                <div class="soap-response-block">
                    <div class="soap-response-header">
                        <span class="soap-response-code">200</span>
                        <span class="soap-response-desc">Successful SOAP response</span>
                        <span class="soap-response-type">application/xml</span>
                    </div>
                    <pre class="soap-code-block"><code>{{ $spec['response_example'] ?? '' }}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
