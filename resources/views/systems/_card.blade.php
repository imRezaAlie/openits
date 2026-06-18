<div class="col-xl-4 col-lg-6 mb-4">
    <div class="card system-card mb-0">
        <div class="system-card-header">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="d-flex gap-3 min-w-0">
                    <div class="system-icon">
                        <i class="{{ $system->icon && Str::startsWith($system->icon, 'fa') ? $system->icon : 'fa-solid fa-server' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <h5 class="mb-1 text-truncate">{{ $system->name }}</h5>
                        <div class="d-flex flex-wrap gap-1">
                            @if($system->domain)
                                <a href="{{ route('systems.index', ['domain_id' => $system->domain_id]) }}"
                                   class="badge text-decoration-none"
                                   style="background: {{ $system->domain->color ?? '#64748b' }}; color: #fff;">
                                    {{ $system->domain->name }}
                                </a>
                            @endif
                            @if($system->vendor)
                                <a href="{{ route('integrations.tree', ['vendor_id' => $system->vendor_id]) }}" class="badge badge-info text-decoration-none">{{ $system->vendor->name }}</a>
                            @else
                                <span class="badge badge-warning">No vendor</span>
                            @endif
                            @if($system->system_type)
                                <span class="badge badge-light">{{ $system->system_type }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="dropdown flex-shrink-0">
                    <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown" aria-label="Actions">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item edit-system" href="#"
                           data-vendor="{{ $system->vendor_id }}"
                           data-domain="{{ $system->domain_id }}"
                           data-name="{{ $system->name }}"
                           data-description="{{ $system->description }}"
                           data-system-type="{{ $system->system_type }}"
                           data-icon="{{ $system->icon }}"
                           data-parent="{{ $system->parent_system_id }}"
                           data-update-url="{{ route('systems.update', $system) }}">
                            <i class="fa-solid fa-pen me-2 text-muted"></i> Edit
                        </a>
                        <a class="dropdown-item" href="{{ route('integrations.system', $system) }}">
                            <i class="fa-solid fa-diagram-project me-2 text-muted"></i> Integrations
                        </a>
                        <a class="dropdown-item" href="{{ route('systems.servers', $system) }}">
                            <i class="fa-solid fa-server me-2 text-muted"></i> Servers
                        </a>
                        <a class="dropdown-item" href="{{ route('systems.documents', $system) }}">
                            <i class="fa-solid fa-file-lines me-2 text-muted"></i> Documents
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('systems.destroy', $system) }}" method="POST" onsubmit="return confirm('Delete this system?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fa-solid fa-trash me-2"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <p class="text-muted small mb-2">{{ Str::limit($system->description, 100) ?: 'No description provided.' }}</p>
            <div class="system-metrics">
                <div class="system-metric">
                    <strong>{{ $system->owned_apis_count }}</strong>
                    <span>APIs</span>
                </div>
                <div class="system-metric">
                    <strong>{{ $system->bpmns_count }}</strong>
                    <span>Processes</span>
                </div>
                <div class="system-metric">
                    <strong>{{ $system->technologies_count }}</strong>
                    <span>Tech</span>
                </div>
                <div class="system-metric">
                    <strong>{{ $system->servers_count }}</strong>
                    <span>Servers</span>
                </div>
                <div class="system-metric">
                    <strong>{{ $system->documents_count }}</strong>
                    <span>Docs</span>
                </div>
            </div>
            <div class="system-actions">
                <a href="{{ route('integrations.system', $system) }}" class="btn btn-primary btn-sm">Integrations</a>
                <a href="{{ route('systems.processes', $system) }}" class="btn btn-outline-info btn-sm">Processes</a>
                <a href="{{ route('systems.technologies', $system) }}" class="btn btn-outline-secondary btn-sm">Tech Stack</a>
                <a href="{{ route('systems.servers', $system) }}" class="btn btn-outline-secondary btn-sm">Servers</a>
                <a href="{{ route('systems.documents', $system) }}" class="btn btn-outline-secondary btn-sm">Documents</a>
                <a href="{{ route('apis.create', ['system_id' => $system->id, 'vendor_id' => $system->vendor_id]) }}" class="btn btn-outline-primary btn-sm">Add API</a>
            </div>
        </div>
    </div>
</div>
