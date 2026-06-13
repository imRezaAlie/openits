@php
    $spec = $protocolSpec ?? [];
    $typeLabel = \App\Support\ApiTypes::label($spec['type'] ?? $api->type);
@endphp

<div class="swagger-ui-wrap soap-spec-ui protocol-spec-ui">
    <div class="soap-spec-topbar">
        <div class="soap-spec-topbar-inner">
            <span class="soap-spec-logo badge badge-{{ $api->type_badge_class }}">{{ $typeLabel }}</span>
            <span class="soap-spec-title">{{ $spec['title'] ?? $api->name }}</span>
        </div>
    </div>
    <div class="soap-spec-info">
        <h2 class="soap-spec-info-title">{{ $spec['title'] ?? $api->name }}</h2>
        @if(!empty($spec['description']))
            <p class="soap-spec-info-desc">{{ $spec['description'] }}</p>
        @endif
        <div class="soap-spec-info-meta">
            @if(!empty($spec['endpoint']))
                <span><strong>Endpoint:</strong> <a href="{{ $spec['endpoint'] }}" target="_blank" rel="noopener">{{ $spec['endpoint'] }}</a></span>
            @elseif(!empty($spec['connection']))
                <span><strong>Connection:</strong> <code>{{ $spec['connection'] }}</code></span>
            @endif
            @if($api->authentication_type)
                <span class="ms-3"><strong>Auth:</strong> {{ $api->authentication_type }}</span>
            @endif
        </div>
    </div>

    <div class="soap-opblock soap-opblock-post is-open">
        <div class="soap-opblock-summary">
            <span class="soap-opblock-method soap-method badge badge-{{ $api->type_badge_class }}">{{ $typeLabel }}</span>
            <span class="soap-opblock-path">{{ $api->name }}</span>
        </div>
        <div class="soap-opblock-body">
            <div class="soap-section">
                <h4>{{ !empty($spec['integration_kind']) ? 'Connection Configuration' : 'Protocol Configuration' }}</h4>
                <table class="soap-params-table">
                    <tbody>
                        @foreach($spec as $key => $value)
                            @if(!in_array($key, ['type', 'title', 'description', 'endpoint', 'connection', 'authentication_type', 'integration_kind'], true) && $value !== null && $value !== '')
                                <tr>
                                    <td>{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                                    <td>
                                        @if(is_array($value))
                                            <pre class="soap-code-block mb-0"><code>{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                        @elseif(in_array($key, ['query'], true))
                                            <pre class="soap-code-block mb-0"><code>{{ $value }}</code></pre>
                                        @else
                                            <code>{{ $value }}</code>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
