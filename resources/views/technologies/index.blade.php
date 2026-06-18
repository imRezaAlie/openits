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
                    <h4 class="mb-0">Technologies</h4>
                    <small class="text-muted">Define and manage the tech stack catalog</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-primary btn-sm">Manage System Stacks</a>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#technologyModal" data-action="create">
                        Add Technology
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($technologiesByCategory as $category => $technologies)
                <div class="col-xl-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ \App\Support\TechnologyCategories::label($category) }}</h5>
                            <span class="badge badge-light">{{ $technologies->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Technology</th>
                                            <th class="text-end">Systems</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($technologies as $technology)
                                            <tr class="tech-row" style="cursor: pointer;" onclick="window.location='{{ route('technologies.show', $technology) }}'">
                                                <td>
                                                    <a href="{{ route('technologies.show', $technology) }}" class="text-body fw-semibold text-decoration-none" onclick="event.stopPropagation()">
                                                        @if($technology->icon)
                                                            <i class="{{ $technology->icon }} text-primary me-1"></i>
                                                        @endif
                                                        {{ $technology->name }}
                                                    </a>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('technologies.show', $technology) }}" class="badge badge-primary text-decoration-none" onclick="event.stopPropagation()">
                                                        {{ $technology->systems_count }}
                                                    </a>
                                                </td>
                                                <td class="text-end" onclick="event.stopPropagation()">
                                                    <button type="button"
                                                            class="btn btn-sm btn-light edit-technology"
                                                            data-name="{{ $technology->name }}"
                                                            data-category="{{ $technology->category }}"
                                                            data-icon="{{ $technology->icon }}"
                                                            data-update-url="{{ route('technologies.update', $technology) }}">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('technologies.destroy', $technology) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ $technology->name }} from catalog?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        No technologies in catalog yet.
                        <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#technologyModal" data-action="create">Add your first technology</button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="technologyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="technologyModalTitle">Add Technology</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="technologyForm" method="POST" action="{{ route('technologies.store') }}">
                @csrf
                <div id="technologyMethodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="tech-name" class="form-control" required placeholder="e.g. Rust, Traefik, Elasticsearch" value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category" id="tech-category" class="form-control" required>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ \App\Support\TechnologyCategories::label($category) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" name="icon" id="tech-icon" class="form-control" placeholder="e.g. fa-brands fa-rust" value="{{ old('icon') }}">
                        <small class="text-muted">Optional. Example: <code>fa-brands fa-docker</code></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
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
        $('[data-action="create"]').on('click', function() {
            $('#technologyModalTitle').text('Add Technology');
            $('#technologyForm').attr('action', '{{ route("technologies.store") }}');
            $('#technologyMethodField').html('');
            $('#technologyForm')[0].reset();
        });

        $('.edit-technology').on('click', function() {
            const el = $(this);
            $('#technologyModalTitle').text('Edit Technology');
            $('#technologyForm').attr('action', el.data('update-url'));
            $('#technologyMethodField').html('<input type="hidden" name="_method" value="PUT">');
            $('#tech-name').val(el.data('name'));
            $('#tech-category').val(el.data('category'));
            $('#tech-icon').val(el.data('icon') || '');
            new bootstrap.Modal(document.getElementById('technologyModal')).show();
        });

        @if($errors->any())
            new bootstrap.Modal(document.getElementById('technologyModal')).show();
        @endif
    </script>
@endpush
