@extends('master')

@push('head-src')
    <link href="{{ asset('css/tech-radar.css') }}" rel="stylesheet">
    <script src="{{ asset('js/d3.v7.min.js') }}"></script>
@endpush

@section('body')
<div class="content-body tech-radar-page">
    <div class="container-fluid tech-radar-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Technology Radar</h4>
                    <small class="text-muted">Adopt · Trial · Assess · Hold — across your technology landscape</small>
                </div>
                <a href="{{ route('technologies.index') }}" class="btn btn-outline-secondary btn-sm">Manage Technologies</a>
            </div>
        </div>

        <div class="row align-items-start g-4">
            <div class="col-xl-7">
                <div class="card tech-radar-chart-card">
                    <div class="card-body p-2">
                        <div id="tech-radar-container" class="tech-radar-canvas-wrap">
                            <div class="tech-radar-zoom-controls btn-group btn-group-sm" role="group" aria-label="Radar zoom">
                                <button type="button" class="btn btn-light btn-sm" id="radar-zoom-in" title="Zoom in">+</button>
                                <button type="button" class="btn btn-light btn-sm" id="radar-zoom-out" title="Zoom out">−</button>
                                <button type="button" class="btn btn-light btn-sm" id="radar-zoom-reset" title="Reset zoom">⟲</button>
                            </div>
                            <svg id="tech-radar-svg"></svg>
                        </div>
                        <p class="tech-radar-hint text-muted small text-center mb-1">Scroll to zoom · drag to pan · click a blip to edit</p>
                        <div class="tech-radar-legend d-flex flex-wrap gap-3 justify-content-center mt-2 small">
                            @foreach(\App\Support\TechRadarRings::LABELS as $ring => $label)
                                <span><span class="legend-ring ring-{{ $ring }}"></span> {{ $label }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card" id="radar-position-panel">
                    <div class="card-header"><h6 class="mb-0">Position Technology</h6></div>
                    <div class="card-body">
                        <p id="radar-selected-label" class="small text-primary mb-2 d-none"></p>
                        <form method="POST" id="radar-update-form" action="">
                            @csrf
                            @method('PUT')
                            <div class="mb-2">
                                <label class="form-label small">Technology</label>
                                <select id="radar-tech-select" class="form-control form-control-sm">
                                    <option value="">Select…</option>
                                    @foreach($chartData['blips'] as $blip)
                                        <option value="{{ $blip['id'] }}" data-ring="{{ $blip['ring'] }}" data-notes="{{ $blip['notes'] ?? '' }}">{{ $blip['name'] }} ({{ $blip['category_label'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Ring</label>
                                <select name="ring" id="radar-ring" class="form-control form-control-sm">
                                    @foreach($rings as $ring)
                                        <option value="{{ $ring }}">{{ \App\Support\TechRadarRings::label($ring) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Notes</label>
                                <textarea name="notes" id="radar-notes" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Update Position</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tech-radar-usage-row">
            <div class="col-12">
                <div class="card tech-radar-usage-card">
                    <div class="card-header"><h6 class="mb-0">Usage Report</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive tech-radar-usage-table">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Technology</th><th>Category</th><th>Ring</th><th>Systems</th></tr></thead>
                                <tbody>
                                    @foreach($usageReport as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['category'] }}</td>
                                            <td>{{ $row['ring'] }}</td>
                                            <td>{{ $row['systems_count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script>
        window.techRadarData = @json($chartData);
        window.techRadarUpdateUrl = '{{ url('/c4/tech-radar') }}';
    </script>
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script src="{{ asset('js/tech-radar.js') }}"></script>
@endpush
