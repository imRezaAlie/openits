<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import API Specification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Import Type</label>
                        <select name="import_type" id="import-type" class="form-control">
                            <option value="openapi">OpenAPI / Swagger (JSON or YAML)</option>
                            <option value="wsdl">WSDL (SOAP)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="base-url-field">
                        <label class="form-label">Base URL (optional override)</label>
                        <input type="url" name="base_url" class="form-control" placeholder="https://api.example.com/v1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File</label>
                        <div id="import-dropzone" class="border border-2 border-dashed rounded p-4 text-center bg-light">
                            <p class="mb-2">Drag & drop your file here, or click to browse</p>
                            <input type="file" name="file" id="import-file" class="form-control" accept=".json,.yaml,.yml,.wsdl,.xml" required>
                            <small class="text-muted d-block mt-2" id="import-file-hint">Accepted: .json, .yaml, .yml</small>
                        </div>
                    </div>
                    <div id="import-progress" class="progress d-none mb-3" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                    <div id="import-result" class="alert d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                <button type="button" id="importSubmitBtn" class="btn btn-primary">Import</button>
            </div>
        </div>
    </div>
</div>
