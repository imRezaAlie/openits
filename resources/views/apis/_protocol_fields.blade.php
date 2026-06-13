@php
    $pd = isset($version) ? ($version->protocol_details ?? []) : [];
@endphp

<div id="graphql-fields" class="type-fields" style="{{ $type === 'graphql' ? '' : 'display:none' }}">
    <hr><h5>GraphQL Details</h5>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Operation Type</label>
            <select name="graphql_operation_type" class="form-control">
                @foreach(['query', 'mutation', 'subscription'] as $op)
                    <option value="{{ $op }}" @selected(old('graphql_operation_type', $pd['operation_type'] ?? 'query') === $op)>{{ ucfirst($op) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Schema URL</label>
            <input type="url" name="graphql_schema_url" class="form-control" value="{{ old('graphql_schema_url', $pd['schema_url'] ?? '') }}" placeholder="https://api.example.com/graphql">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Operation Name</label>
            <input type="text" name="graphql_operation_name" class="form-control" value="{{ old('graphql_operation_name', $pd['operation_name'] ?? '') }}">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Query / Mutation Document</label>
        <textarea name="graphql_query" class="form-control font-monospace" rows="6" placeholder="query GetUser($id: ID!) { user(id: $id) { name } }">{{ old('graphql_query', $pd['query'] ?? '') }}</textarea>
    </div>
</div>

<div id="grpc-fields" class="type-fields" style="{{ $type === 'grpc' ? '' : 'display:none' }}">
    <hr><h5>gRPC Details</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Service Name</label>
            <input type="text" name="grpc_service_name" class="form-control" value="{{ old('grpc_service_name', $pd['service_name'] ?? '') }}" placeholder="com.example.UserService">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Method Name</label>
            <input type="text" name="grpc_method_name" class="form-control" value="{{ old('grpc_method_name', $pd['method_name'] ?? '') }}" placeholder="GetUser">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Proto / Descriptor URL</label>
            <input type="url" name="grpc_proto_url" class="form-control" value="{{ old('grpc_proto_url', $pd['proto_url'] ?? '') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">RPC Type</label>
            <select name="grpc_rpc_type" class="form-control">
                @foreach(['unary', 'server_streaming', 'client_streaming', 'bidirectional'] as $rpc)
                    <option value="{{ $rpc }}" @selected(old('grpc_rpc_type', $pd['rpc_type'] ?? 'unary') === $rpc)>{{ str_replace('_', ' ', ucfirst($rpc)) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div id="websocket-fields" class="type-fields" style="{{ $type === 'websocket' ? '' : 'display:none' }}">
    <hr><h5>WebSocket Details</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Subprotocol</label>
            <input type="text" name="websocket_subprotocol" class="form-control" value="{{ old('websocket_subprotocol', $pd['subprotocol'] ?? '') }}" placeholder="e.g. json, wamp">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Message Format</label>
            <input type="text" name="websocket_message_format" class="form-control" value="{{ old('websocket_message_format', $pd['message_format'] ?? 'JSON') }}">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Handshake Headers (JSON)</label>
        <textarea name="websocket_handshake_headers" class="form-control font-monospace" rows="3" placeholder='{"Authorization":"Bearer ..."}'>{{ old('websocket_handshake_headers', isset($pd['handshake_headers']) ? json_encode($pd['handshake_headers'], JSON_PRETTY_PRINT) : '') }}</textarea>
    </div>
</div>

<div id="sse-fields" class="type-fields" style="{{ $type === 'sse' ? '' : 'display:none' }}">
    <hr><h5>SSE (Server-Sent Events) Details</h5>
    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Event Types</label>
            <input type="text" name="sse_event_types" class="form-control" value="{{ old('sse_event_types', $pd['event_types'] ?? '') }}" placeholder="message, heartbeat, order-updated">
            <small class="text-muted">Comma-separated event names</small>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Retry Interval (ms)</label>
            <input type="number" name="sse_retry_interval" class="form-control" min="0" value="{{ old('sse_retry_interval', $pd['retry_interval'] ?? '') }}">
        </div>
    </div>
</div>

<div id="socketio-fields" class="type-fields" style="{{ $type === 'socketio' ? '' : 'display:none' }}">
    <hr><h5>Socket.IO Details</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Namespace</label>
            <input type="text" name="socketio_namespace" class="form-control" value="{{ old('socketio_namespace', $pd['namespace'] ?? '/') }}" placeholder="/orders">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Transport</label>
            <select name="socketio_transport" class="form-control">
                <option value="websocket" @selected(old('socketio_transport', $pd['transport'] ?? 'websocket') === 'websocket')>WebSocket</option>
                <option value="polling" @selected(old('socketio_transport', $pd['transport'] ?? '') === 'polling')>Long Polling</option>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Emit Events (JSON array)</label>
        <textarea name="socketio_events_emit" class="form-control font-monospace" rows="3" placeholder='["order:created","order:updated"]'>{{ old('socketio_events_emit', isset($pd['events_emit']) ? json_encode($pd['events_emit'], JSON_PRETTY_PRINT) : '') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Listen Events (JSON array)</label>
        <textarea name="socketio_events_listen" class="form-control font-monospace" rows="3" placeholder='["order:status","notification"]'>{{ old('socketio_events_listen', isset($pd['events_listen']) ? json_encode($pd['events_listen'], JSON_PRETTY_PRINT) : '') }}</textarea>
    </div>
</div>
