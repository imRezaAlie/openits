@extends('master')

@push('head-src')
    <style>
        .mds-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0d9488 100%);
            border: none;
            color: #fff;
        }
        .mds-hero h4, .mds-hero p, .mds-hero small { color: #fff; }
        .layer-card {
            border-radius: 14px;
            border: 1px solid #eee;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .layer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }
        .layer-accent { height: 4px; border-radius: 14px 14px 0 0; }
        .mds-flow {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 12px;
        }
        .mds-flow-step {
            text-align: center;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            min-width: 120px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .mds-flow-arrow { color: #94a3b8; font-size: 1.25rem; }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Modern Data Stack</h4>
                    <small class="text-muted">Medallion architecture bridging platform-specific dictionaries to a canonical data model</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('data-dictionary.entities.index') }}" class="btn btn-outline-primary btn-sm">Canonical Dictionary</a>
                    <a href="{{ route('platform-schemas.index') }}" class="btn btn-outline-primary btn-sm">Platform Schemas</a>
                    <a href="{{ route('field-mappings.index') }}" class="btn btn-outline-primary btn-sm">Field Mappings</a>
                    <a href="{{ route('data-stack.export') }}" class="btn btn-primary btn-sm" target="_blank">Export JSON</a>
                </div>
            </div>
        </div>

        <div class="card mds-hero mb-4">
            <div class="card-body">
                <h4 class="mb-2">Unified schema governance across platforms</h4>
                <p class="mb-3 opacity-90">Each system (CRM, ERP, Payment Gateway) maintains its own native data dictionary. The MDS layer maps those platform fields to a shared canonical model for cross-system integration.</p>
                <div class="mds-flow">
                    @foreach($layers as $i => $layer)
                        @if($i > 0)<span class="mds-flow-arrow">→</span>@endif
                        <div class="mds-flow-step bg-white text-dark border">
                            <span class="badge badge-{{ $layer['badge'] }} mb-1">{{ $layer['label'] }}</span>
                            <div class="text-muted small">{{ $layer['count'] }} schemas</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-2 col-sm-4 mb-3 mb-xl-0">
                <div class="card mb-0 h-100"><div class="card-body">
                    <p class="text-muted small mb-1">Canonical entities</p>
                    <h3 class="mb-0">{{ number_format($stats['canonical_entities']) }}</h3>
                </div></div>
            </div>
            <div class="col-xl-2 col-sm-4 mb-3 mb-xl-0">
                <div class="card mb-0 h-100"><div class="card-body">
                    <p class="text-muted small mb-1">Attributes</p>
                    <h3 class="mb-0">{{ number_format($stats['canonical_attributes']) }}</h3>
                </div></div>
            </div>
            <div class="col-xl-2 col-sm-4 mb-3 mb-xl-0">
                <div class="card mb-0 h-100"><div class="card-body">
                    <p class="text-muted small mb-1">Platform schemas</p>
                    <h3 class="mb-0">{{ number_format($stats['platform_schemas']) }}</h3>
                </div></div>
            </div>
            <div class="col-xl-2 col-sm-4 mb-3 mb-xl-0">
                <div class="card mb-0 h-100"><div class="card-body">
                    <p class="text-muted small mb-1">Platform fields</p>
                    <h3 class="mb-0">{{ number_format($stats['platform_fields']) }}</h3>
                </div></div>
            </div>
            <div class="col-xl-2 col-sm-4 mb-3 mb-xl-0">
                <div class="card mb-0 h-100"><div class="card-body">
                    <p class="text-muted small mb-1">Field mappings</p>
                    <h3 class="mb-0">{{ number_format($stats['field_mappings']) }}</h3>
                </div></div>
            </div>
            <div class="col-xl-2 col-sm-4">
                <div class="card mb-0 h-100"><div class="card-body">
                    <p class="text-muted small mb-1">Fields mapped</p>
                    <h3 class="mb-0">{{ $stats['mapped_fields_pct'] }}%</h3>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Canonical Entities (Gold)</h5>
                        <a href="{{ route('data-dictionary.entities.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Entity</th><th>Attributes</th></tr></thead>
                                <tbody>
                                    @forelse($entities as $entity)
                                        <tr>
                                            <td><a href="{{ route('data-dictionary.entities.show', $entity) }}">{{ $entity->name }}</a></td>
                                            <td>{{ $entity->attributes_count }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-muted text-center py-4">No canonical entities yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Platform Schemas</h5>
                        <a href="{{ route('platform-schemas.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Schema</th><th>System</th><th>Layer</th><th>Fields</th></tr></thead>
                                <tbody>
                                    @forelse($schemas as $schema)
                                        <tr>
                                            <td><a href="{{ route('platform-schemas.show', $schema) }}">{{ $schema->name }}</a></td>
                                            <td>{{ $schema->system?->name }}</td>
                                            <td><span class="badge badge-{{ \App\Support\DataLayers::badgeClass($schema->data_layer) }}">{{ \App\Support\DataLayers::label($schema->data_layer) }}</span></td>
                                            <td>{{ $schema->fields_count }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-muted text-center py-4">No platform schemas yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($systemsWithSchemas->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Systems with Data Dictionaries</h5></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($systemsWithSchemas as $system)
                        <a href="{{ route('platform-schemas.index', ['system_id' => $system->id]) }}" class="badge badge-light border px-3 py-2">
                            {{ $system->name }} ({{ $system->platform_schemas_count }} schemas)
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Field Mappings</h5>
                <a href="{{ route('field-mappings.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Platform field</th>
                                <th>System</th>
                                <th>→</th>
                                <th>Canonical attribute</th>
                                <th>Entity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMappings as $mapping)
                                <tr>
                                    <td><code>{{ $mapping->native_name }}</code></td>
                                    <td>{{ $mapping->system_name }}</td>
                                    <td class="text-muted">→</td>
                                    <td><code>{{ $mapping->attribute_name }}</code></td>
                                    <td>{{ $mapping->entity_name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-4">No field mappings defined yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
