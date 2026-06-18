@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .tech-category-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #737B8B;
            margin-bottom: 0.75rem;
        }
        .tech-item {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            transition: border-color 0.2s, background-color 0.2s;
        }
        .tech-item.selected {
            border-color: var(--primary);
            background-color: rgba(79, 70, 229, 0.04);
        }
        .tech-item label {
            cursor: pointer;
            margin-bottom: 0;
            width: 100%;
        }
        .tech-version {
            max-width: 120px;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">{{ $system->name }} — Tech Stack</h4>
                    <small class="text-muted">Programming languages, Docker, Kubernetes, nginx, and more</small>
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
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-secondary btn-sm">Back to Systems</a>
                    <a href="{{ route('technologies.index') }}" class="btn btn-outline-secondary btn-sm">Manage Catalog</a>
                    <a href="{{ route('systems.processes', $system) }}" class="btn btn-outline-info btn-sm">Processes</a>
                    <a href="{{ route('systems.servers', $system) }}" class="btn btn-outline-secondary btn-sm">Servers</a>
                    <a href="{{ route('integrations.system', $system) }}" class="btn btn-outline-primary btn-sm">Integrations</a>
                </div>
            </div>
        </div>

        @if($assigned->isNotEmpty())
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body py-3">
                            <small class="text-muted d-block mb-2">Current stack ({{ $assigned->count() }})</small>
                            @foreach($assigned as $tech)
                                <span class="badge badge-primary me-1 mb-1">
                                    @if($tech->icon)
                                        <i class="{{ $tech->icon }} me-1"></i>
                                    @endif
                                    {{ $tech->name }}@if($tech->pivot->version) {{ $tech->pivot->version }}@endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('systems.technologies.sync', $system) }}">
            @csrf
            <div class="row">
                @forelse($technologiesByCategory as $category => $technologies)
                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">{{ \App\Support\TechnologyCategories::label($category) }}</h5>
                            </div>
                            <div class="card-body">
                                @foreach($technologies as $index => $technology)
                                    @php
                                        $isAssigned = $assigned->has($technology->id);
                                        $version = $isAssigned ? $assigned[$technology->id]->pivot->version : '';
                                    @endphp
                                    <div class="tech-item {{ $isAssigned ? 'selected' : '' }}" data-tech-id="{{ $technology->id }}">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <label class="d-flex align-items-center flex-grow-1">
                                                <input type="checkbox"
                                                       class="form-check-input me-2 tech-checkbox"
                                                       name="technologies[{{ $category }}_{{ $index }}][id]"
                                                       value="{{ $technology->id }}"
                                                       @checked($isAssigned)>
                                                <span>
                                                    @if($technology->icon)
                                                        <i class="{{ $technology->icon }} text-primary me-1"></i>
                                                    @endif
                                                    {{ $technology->name }}
                                                </span>
                                            </label>
                                            <input type="text"
                                                   class="form-control form-control-sm tech-version"
                                                   name="technologies[{{ $category }}_{{ $index }}][version]"
                                                   value="{{ $version }}"
                                                   placeholder="Version"
                                                   @disabled(! $isAssigned)>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">No technologies in catalog. Run the TechnologySeeder first.</div>
                    </div>
                @endforelse
            </div>

            @if($technologiesByCategory->isNotEmpty())
                <div class="row mb-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save Tech Stack</button>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script>
        document.querySelectorAll('.tech-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const item = this.closest('.tech-item');
                const versionInput = item.querySelector('.tech-version');

                if (this.checked) {
                    item.classList.add('selected');
                    versionInput.disabled = false;
                } else {
                    item.classList.remove('selected');
                    versionInput.disabled = true;
                    versionInput.value = '';
                }
            });
        });
    </script>
@endpush
