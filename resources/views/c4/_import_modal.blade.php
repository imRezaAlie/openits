<div class="modal fade" id="c4ImportModal" tabindex="-1" aria-labelledby="c4ImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="c4ImportModalLabel">Import C4 Model</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="c4ImportForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Import Format</label>
                        <select name="import_type" id="c4-import-type" class="form-control form-control-sm">
                            @foreach(\App\Support\C4ImportTypes::ALL as $importType)
                                <option value="{{ $importType }}">{{ \App\Support\C4ImportTypes::label($importType) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="c4-base-url-field">
                        <label class="form-label">Base URL <span class="text-muted">(OpenAPI only, optional)</span></label>
                        <input type="url" name="base_url" class="form-control form-control-sm" placeholder="https://api.example.com/v1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File</label>
                        <input type="file" name="file" id="c4-import-file" class="form-control form-control-sm" required>
                        <small class="text-muted" id="c4-import-hint">Accepted: .json, .yaml, .yml</small>
                    </div>
                    <div id="c4-import-progress-wrap" class="d-none mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="c4-import-status-text">Queued…</span>
                            <span id="c4-import-percent">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div id="c4-import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>
                    </div>
                    <div id="c4-import-result" class="alert d-none mb-0"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="button" id="c4ImportSubmitBtn" class="btn btn-primary">Import</button>
            </div>
        </div>
    </div>
</div>
