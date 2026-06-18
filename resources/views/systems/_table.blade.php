<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover systems-table mb-0">
                <thead>
                    <tr>
                        <th>System</th>
                        <th>Domain</th>
                        <th>Vendor</th>
                        <th>Type</th>
                        <th class="text-center">APIs</th>
                        <th class="text-center">Processes</th>
                        <th class="text-center">Tech</th>
                        <th class="text-center">Servers</th>
                        <th class="text-center">Docs</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($systems as $system)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="system-icon-sm">
                                        <i class="{{ $system->icon && Str::startsWith($system->icon, 'fa') ? $system->icon : 'fa-solid fa-server' }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <a href="{{ route('integrations.system', $system) }}" class="fw-semibold text-body text-decoration-none d-block text-truncate">{{ $system->name }}</a>
                                        @if($system->parent)
                                            <small class="text-muted">↳ {{ $system->parent->name }}</small>
                                        @elseif($system->children_count > 0)
                                            <small class="text-muted">{{ $system->children_count }} sub-system(s)</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($system->domain)
                                    <a href="{{ route('systems.index', ['domain_id' => $system->domain_id]) }}"
                                       class="badge text-decoration-none"
                                       style="background: {{ $system->domain->color ?? '#64748b' }}; color: #fff;">
                                        {{ $system->domain->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($system->vendor)
                                    <a href="{{ route('systems.index', ['vendor_id' => $system->vendor_id]) }}" class="badge badge-info text-decoration-none">{{ $system->vendor->name }}</a>
                                @else
                                    <span class="badge badge-warning">Unassigned</span>
                                @endif
                            </td>
                            <td>{{ $system->system_type ?? '—' }}</td>
                            <td class="text-center">
                                @if($system->owned_apis_count)
                                    <a href="{{ route('apis.index', ['system_id' => $system->id]) }}" class="badge badge-primary">{{ $system->owned_apis_count }}</a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($system->bpmns_count)
                                    <a href="{{ route('systems.processes', $system) }}" class="badge badge-info">{{ $system->bpmns_count }}</a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($system->technologies_count)
                                    <a href="{{ route('systems.technologies', $system) }}" class="badge badge-light">{{ $system->technologies_count }}</a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($system->servers_count)
                                    <a href="{{ route('systems.servers', $system) }}" class="badge badge-secondary">{{ $system->servers_count }}</a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($system->documents_count)
                                    <a href="{{ route('systems.documents', $system) }}" class="badge badge-secondary">{{ $system->documents_count }}</a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('integrations.system', $system) }}" class="btn btn-primary" title="Integrations"><i class="fa-solid fa-diagram-project"></i></a>
                                    <a href="{{ route('systems.processes', $system) }}" class="btn btn-outline-info" title="Processes"><i class="fa-solid fa-sitemap"></i></a>
                                    <a href="{{ route('systems.technologies', $system) }}" class="btn btn-outline-secondary" title="Tech Stack"><i class="fa-solid fa-layer-group"></i></a>
                                    <a href="{{ route('systems.servers', $system) }}" class="btn btn-outline-secondary" title="Servers"><i class="fa-solid fa-server"></i></a>
                                    <a href="{{ route('systems.documents', $system) }}" class="btn btn-outline-secondary" title="Documents"><i class="fa-solid fa-file-lines"></i></a>
                                    <button type="button" class="btn btn-outline-primary edit-system" title="Edit"
                                            data-vendor="{{ $system->vendor_id }}"
                                            data-domain="{{ $system->domain_id }}"
                                            data-name="{{ $system->name }}"
                                            data-description="{{ $system->description }}"
                                            data-system-type="{{ $system->system_type }}"
                                            data-icon="{{ $system->icon }}"
                                            data-parent="{{ $system->parent_system_id }}"
                                            data-update-url="{{ route('systems.update', $system) }}">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
