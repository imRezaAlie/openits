@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0 py-0">
                            <li class="breadcrumb-item"><a href="{{ route('systems.index') }}">Systems</a></li>
                            @if($system->vendor)
                                <li class="breadcrumb-item">
                                    <a href="{{ route('systems.index', ['vendor_id' => $system->vendor_id]) }}">{{ $system->vendor->name }}</a>
                                </li>
                            @endif
                            <li class="breadcrumb-item active">{{ $system->name }} — Documents</li>
                        </ol>
                    </nav>
                    <h4 class="mb-0">{{ $system->name }} — Supporting Documents</h4>
                    <small class="text-muted">Manuals, specifications, runbooks, and other reference files</small>
                    <div class="mt-1">
                        @if($system->vendor)
                            <span class="badge badge-info">{{ $system->vendor->name }}</span>
                        @endif
                        @if($system->system_type)
                            <span class="badge badge-light">{{ $system->system_type }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-sm">All Documents</a>
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-secondary btn-sm">Back to Systems</a>
                    <a href="{{ route('systems.servers', $system) }}" class="btn btn-outline-secondary btn-sm">Servers</a>
                    <a href="{{ route('systems.technologies', $system) }}" class="btn btn-outline-secondary btn-sm">Tech Stack</a>
                    <a href="{{ route('systems.processes', $system) }}" class="btn btn-outline-info btn-sm">Processes</a>
                    <a href="{{ route('integrations.system', $system) }}" class="btn btn-outline-primary btn-sm">Integrations</a>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal" id="addDocumentBtn">
                        Add Document
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Document Library</h5>
                        <span class="badge badge-primary">{{ $system->documents->count() }} document(s)</span>
                    </div>
                    <div class="card-body p-0">
                        @if($system->documents->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No supporting documents uploaded for this system yet.</p>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal">Add first document</button>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Version</th>
                                            <th>Attachment</th>
                                            <th>Uploaded</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($system->documents as $document)
                                            <tr>
                                                <td class="fw-semibold">{{ $document->name }}</td>
                                                <td>
                                                    @if($document->version)
                                                        <span class="badge badge-light">{{ $document->version }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('systems.documents.download', [$system, $document]) }}" class="text-decoration-none">
                                                        <i class="fa-solid fa-paperclip me-1"></i>{{ $document->attachment_original_name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $document->created_at->format('Y-m-d H:i') }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('systems.documents.download', [$system, $document]) }}" class="btn btn-outline-primary" title="Download">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                        <button type="button"
                                                                class="btn btn-outline-secondary edit-document"
                                                                title="Edit"
                                                                data-id="{{ $document->id }}"
                                                                data-name="{{ $document->name }}"
                                                                data-version="{{ $document->version }}"
                                                                data-attachment="{{ $document->attachment_original_name }}">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <form action="{{ route('systems.documents.destroy', [$system, $document]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="documentForm" method="POST" action="{{ route('systems.documents.store', $system) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="documentFormMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Add Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="document_name" class="form-control" required placeholder="e.g. Operations Runbook">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Version</label>
                        <input type="text" name="version" id="document_version" class="form-control" placeholder="e.g. 1.0, v2.3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Attachment <span class="text-danger" id="attachmentRequired">*</span></label>
                        <input type="file" name="attachment" id="document_attachment" class="form-control">
                        <small class="text-muted" id="attachmentHint">Max 20 MB. PDF, Word, Excel, images, and other common formats.</small>
                        <small class="text-muted d-none" id="currentAttachmentHint"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="documentSubmitBtn">Save Document</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script>
        const storeUrl = @json(route('systems.documents.store', $system));
        const updateUrlTemplate = @json(route('systems.documents.update', [$system, '__DOCUMENT__']));
        const modal = document.getElementById('documentModal');
        const form = document.getElementById('documentForm');
        const methodInput = document.getElementById('documentFormMethod');
        const modalTitle = document.getElementById('documentModalLabel');
        const attachmentInput = document.getElementById('document_attachment');
        const attachmentRequired = document.getElementById('attachmentRequired');
        const currentAttachmentHint = document.getElementById('currentAttachmentHint');

        function resetDocumentForm() {
            form.action = storeUrl;
            methodInput.value = 'POST';
            modalTitle.textContent = 'Add Document';
            form.reset();
            attachmentInput.required = true;
            attachmentRequired.classList.remove('d-none');
            currentAttachmentHint.classList.add('d-none');
            currentAttachmentHint.textContent = '';
        }

        document.getElementById('addDocumentBtn')?.addEventListener('click', resetDocumentForm);
        modal?.addEventListener('hidden.bs.modal', resetDocumentForm);

        document.querySelectorAll('.edit-document').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                form.action = updateUrlTemplate.replace('__DOCUMENT__', id);
                methodInput.value = 'PUT';
                modalTitle.textContent = 'Edit Document';

                document.getElementById('document_name').value = this.dataset.name || '';
                document.getElementById('document_version').value = this.dataset.version || '';
                attachmentInput.value = '';
                attachmentInput.required = false;
                attachmentRequired.classList.add('d-none');
                currentAttachmentHint.textContent = 'Current file: ' + (this.dataset.attachment || '—') + '. Leave empty to keep it.';
                currentAttachmentHint.classList.remove('d-none');

                bootstrap.Modal.getOrCreateInstance(modal).show();
            });
        });
    </script>
@endpush
