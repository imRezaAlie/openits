@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="mb-0">{{ $adr->exists ? 'Edit ADR' : 'New ADR' }}</h4>
            </div>
        </div>

        <form method="POST" action="{{ $adr->exists ? route('c4.adrs.update', $adr) : route('c4.adrs.store') }}">
            @csrf
            @if($adr->exists) @method('PUT') @endif

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $adr->title) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected(old('status', $adr->status) === $status)>{{ \App\Support\AdrStatuses::label($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">System</label>
                            <select name="system_id" id="adr-system" class="form-control" onchange="window.location='{{ route('c4.adrs.create') }}?system_id='+this.value">
                                <option value="">— None —</option>
                                @foreach($systems as $system)
                                    <option value="{{ $system->id }}" @selected(old('system_id', $selectedSystemId) == $system->id)>{{ $system->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Decided at</label>
                            <input type="date" name="decided_at" class="form-control" value="{{ old('decided_at', $adr->decided_at?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Context</label>
                            <textarea name="context" class="form-control" rows="4">{{ old('context', $adr->context) }}</textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Decision</label>
                            <textarea name="decision" class="form-control" rows="4">{{ old('decision', $adr->decision) }}</textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Consequences</label>
                            <textarea name="consequences" class="form-control" rows="4">{{ old('consequences', $adr->consequences) }}</textarea>
                        </div>
                        @if(count($c4Elements))
                            <div class="col-12 mb-3">
                                <label class="form-label">Linked C4 Elements</label>
                                <div class="row">
                                    @foreach($c4Elements as $el)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="linked_elements[{{ $loop->index }}][element_id]" value="{{ $el['id'] }}" id="el-{{ $el['id'] }}"
                                                    @checked(in_array($el['id'], $linkedElementIds ?? []))>
                                                <input type="hidden" name="linked_elements[{{ $loop->index }}][element_type]" value="{{ $el['type'] }}">
                                                <label class="form-check-label" for="el-{{ $el['id'] }}">{{ $el['name'] }} <span class="text-muted">({{ $el['type'] }})</span></label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save ADR</button>
                    <a href="{{ route('c4.adrs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
@endpush
