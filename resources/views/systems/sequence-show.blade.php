<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bpmn->name }} — Sequence Diagram</title>
    <link href="{{ asset('landing/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sequence-designer.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="seq-app">
    <div class="seq-toolbar">
        <div>
            <h1>{{ $bpmn->name }}</h1>
            <small style="opacity:0.75">
                Sequence diagram
                @if($bpmn->system)
                    · {{ $bpmn->system->name }}
                @endif
            </small>
        </div>
        <div class="seq-toolbar-actions">
            <input type="text" id="process-name" class="form-control form-control-sm" value="{{ $bpmn->name }}" style="width:200px">
            <button type="button" id="btn-save" class="seq-btn seq-btn-success">Save Changes</button>
            <button type="button" id="btn-export-svg" class="seq-btn seq-btn-outline">Export SVG</button>
            <button type="button" id="btn-export-png" class="seq-btn seq-btn-outline">Export PNG</button>
            @if($bpmn->system)
                <a href="{{ route('systems.processes', $bpmn->system) }}" class="seq-btn seq-btn-secondary">Back</a>
            @endif
        </div>
    </div>

    <div class="seq-main">
        <div class="seq-panel">
            <div class="seq-panel-header">
                <span>Designer</span>
                <div class="seq-tab-group">
                    <button type="button" class="seq-tab active" data-tab="designer">Visual</button>
                    <button type="button" class="seq-tab" data-tab="source">Source</button>
                </div>
            </div>
            <div class="seq-panel-body">
                <div id="designer-view">
                    <div class="seq-section">
                        <div class="seq-section-title">Participants (lifelines)</div>
                        <div id="participants-list"></div>
                        <button type="button" id="add-participant" class="seq-add-row">+ Add Participant</button>
                    </div>
                    <div class="seq-section">
                        <div class="seq-section-title">Messages</div>
                        <div id="messages-list"></div>
                        <button type="button" id="add-message" class="seq-add-row">+ Add Message</button>
                    </div>
                </div>
                <div id="source-view" style="display:none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Mermaid sequenceDiagram syntax</small>
                        <button type="button" id="btn-sync-to-designer" class="seq-btn seq-btn-primary btn-sm">Apply to Visual</button>
                    </div>
                    <textarea id="source-editor" class="seq-source-editor" rows="18" spellcheck="false"></textarea>
                </div>
            </div>
        </div>

        <div class="seq-preview">
            <div class="seq-preview-header">
                <span>Live Preview</span>
                <button type="button" class="seq-tab active" data-tab="preview">Preview</button>
            </div>
            <div class="seq-preview-body">
                <div id="preview-error" class="seq-error"></div>
                <div id="mermaid-preview"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>
    window.SequenceDesignerConfig = {
        mode: 'edit',
        csrfToken: @json(csrf_token()),
        processName: @json($bpmn->name),
        initialSource: @json($bpmn->xml),
        updateUrl: @json(route('systems.update.bpmn', $bpmn)),
        backUrl: @json($bpmn->system ? route('systems.processes', $bpmn->system) : route('processes.index')),
    };
</script>
<script src="{{ asset('js/sequence-designer.js') }}"></script>
</body>
</html>
