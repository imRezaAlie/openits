@if(!empty($items ?? $breadcrumbs ?? []))
    @php($crumbs = $items ?? $breadcrumbs ?? [])
    @if(count($crumbs) > 1)
        <nav aria-label="breadcrumb" class="openits-breadcrumb">
            <ol class="breadcrumb mb-0">
                @foreach($crumbs as $crumb)
                    @if($loop->last || empty($crumb['url']))
                        <li class="breadcrumb-item active" @if($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif
@endif
