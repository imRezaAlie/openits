@php
    $pd = isset($version) ? ($version->protocol_details ?? []) : [];
@endphp

<div id="ftps-fields" class="type-fields" style="{{ $type === 'ftps' ? '' : 'display:none' }}">
    <hr><h5>FTPS Details</h5>
    <p class="text-muted small">File transfer integration — no REST/SOAP API required.</p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Port</label>
            <input type="number" name="ftps_port" class="form-control" value="{{ old('ftps_port', $pd['port'] ?? 990) }}" min="1" max="65535">
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label">Remote Path</label>
            <input type="text" name="ftps_remote_path" class="form-control" value="{{ old('ftps_remote_path', $pd['remote_path'] ?? '') }}" placeholder="/exports/inbound">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Direction</label>
            <select name="ftps_direction" class="form-control">
                @foreach(['push' => 'Push (upload)', 'pull' => 'Pull (download)', 'bidirectional' => 'Bidirectional'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('ftps_direction', $pd['direction'] ?? 'push') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Passive Mode</label>
            <select name="ftps_passive_mode" class="form-control">
                <option value="1" @selected(old('ftps_passive_mode', ($pd['passive_mode'] ?? true) ? '1' : '0') === '1')>Yes</option>
                <option value="0" @selected(old('ftps_passive_mode', ($pd['passive_mode'] ?? true) ? '1' : '0') === '0')>No</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">File Pattern</label>
            <input type="text" name="ftps_file_pattern" class="form-control" value="{{ old('ftps_file_pattern', $pd['file_pattern'] ?? '') }}" placeholder="*.csv, orders_*.xml">
        </div>
    </div>
</div>

<div id="sftp-fields" class="type-fields" style="{{ $type === 'sftp' ? '' : 'display:none' }}">
    <hr><h5>SFTP Details</h5>
    <p class="text-muted small">Secure file transfer — SSH-based, not an HTTP API.</p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Port</label>
            <input type="number" name="sftp_port" class="form-control" value="{{ old('sftp_port', $pd['port'] ?? 22) }}" min="1" max="65535">
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label">Remote Path</label>
            <input type="text" name="sftp_remote_path" class="form-control" value="{{ old('sftp_remote_path', $pd['remote_path'] ?? '') }}" placeholder="/data/outbound">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Direction</label>
            <select name="sftp_direction" class="form-control">
                @foreach(['push' => 'Push (upload)', 'pull' => 'Pull (download)', 'bidirectional' => 'Bidirectional'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('sftp_direction', $pd['direction'] ?? 'pull') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Auth Method</label>
            <select name="sftp_auth_method" class="form-control">
                @foreach(['password', 'ssh_key', 'both'] as $auth)
                    <option value="{{ $auth }}" @selected(old('sftp_auth_method', $pd['auth_method'] ?? 'ssh_key') === $auth)>{{ ucfirst(str_replace('_', ' ', $auth)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">File Pattern</label>
            <input type="text" name="sftp_file_pattern" class="form-control" value="{{ old('sftp_file_pattern', $pd['file_pattern'] ?? '') }}" placeholder="*.json">
        </div>
    </div>
</div>

<div id="zabbix-fields" class="type-fields" style="{{ $type === 'zabbix' ? '' : 'display:none' }}">
    <hr><h5>Zabbix Monitoring</h5>
    <p class="text-muted small">Infrastructure monitoring — agents, traps, and templates, not a business API.</p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Agent Type</label>
            <select name="zabbix_agent_type" class="form-control">
                @foreach(['agent', 'snmp', 'ipmi', 'jmx', 'trap'] as $agent)
                    <option value="{{ $agent }}" @selected(old('zabbix_agent_type', $pd['agent_type'] ?? 'agent') === $agent)>{{ strtoupper($agent) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Host Group</label>
            <input type="text" name="zabbix_host_group" class="form-control" value="{{ old('zabbix_host_group', $pd['host_group'] ?? '') }}" placeholder="Production / ERP">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Template</label>
            <input type="text" name="zabbix_template" class="form-control" value="{{ old('zabbix_template', $pd['template'] ?? '') }}" placeholder="Template App HTTP Service">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Monitored Host</label>
            <input type="text" name="zabbix_monitored_host" class="form-control" value="{{ old('zabbix_monitored_host', $pd['monitored_host'] ?? '') }}" placeholder="erp-app-01.internal">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Trigger Severity</label>
            <input type="text" name="zabbix_trigger_severity" class="form-control" value="{{ old('zabbix_trigger_severity', $pd['trigger_severity'] ?? '') }}" placeholder="warning, average, high, disaster">
        </div>
    </div>
</div>

<div id="siem-fields" class="type-fields" style="{{ $type === 'siem' ? '' : 'display:none' }}">
    <hr><h5>SIEM Integration</h5>
    <p class="text-muted small">Security event forwarding — syslog, CEF, or log collectors.</p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">SIEM Platform</label>
            <input type="text" name="siem_platform" class="form-control" value="{{ old('siem_platform', $pd['platform'] ?? '') }}" placeholder="QRadar, Sentinel, Elastic SIEM">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Log Format</label>
            <select name="siem_log_format" class="form-control">
                @foreach(['syslog', 'cef', 'leef', 'json', 'raw'] as $fmt)
                    <option value="{{ $fmt }}" @selected(old('siem_log_format', $pd['log_format'] ?? 'cef') === $fmt)>{{ strtoupper($fmt) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Ingestion Method</label>
            <select name="siem_ingestion_method" class="form-control">
                @foreach(['syslog', 'tcp', 'udp', 'file', 'agent'] as $method)
                    <option value="{{ $method }}" @selected(old('siem_ingestion_method', $pd['ingestion_method'] ?? 'syslog') === $method)>{{ ucfirst($method) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Source / Index</label>
            <input type="text" name="siem_source_index" class="form-control" value="{{ old('siem_source_index', $pd['source_index'] ?? '') }}" placeholder="security_events">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Port</label>
            <input type="number" name="siem_port" class="form-control" value="{{ old('siem_port', $pd['port'] ?? 514) }}" min="1" max="65535">
        </div>
    </div>
</div>

<div id="splunk-fields" class="type-fields" style="{{ $type === 'splunk' ? '' : 'display:none' }}">
    <hr><h5>Splunk Integration</h5>
    <p class="text-muted small">Log aggregation via HEC, forwarder, or indexer — not a REST business API.</p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Ingestion Type</label>
            <select name="splunk_ingestion_type" class="form-control">
                @foreach(['hec', 'forwarder', 'syslog', 's2s'] as $ing)
                    <option value="{{ $ing }}" @selected(old('splunk_ingestion_type', $pd['ingestion_type'] ?? 'hec') === $ing)>{{ strtoupper($ing) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Index</label>
            <input type="text" name="splunk_index" class="form-control" value="{{ old('splunk_index', $pd['index'] ?? '') }}" placeholder="main, security, ops">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Sourcetype</label>
            <input type="text" name="splunk_sourcetype" class="form-control" value="{{ old('splunk_sourcetype', $pd['sourcetype'] ?? '') }}" placeholder="_json, access_combined">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Source</label>
            <input type="text" name="splunk_source" class="form-control" value="{{ old('splunk_source', $pd['source'] ?? '') }}" placeholder="erp-payment-gateway">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">HEC Port</label>
            <input type="number" name="splunk_hec_port" class="form-control" value="{{ old('splunk_hec_port', $pd['hec_port'] ?? 8088) }}" min="1" max="65535">
        </div>
    </div>
</div>
