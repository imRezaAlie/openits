@php
    $badgeClass = match ($status) {
        'active' => 'badge-success',
        'development' => 'badge-primary',
        'retired' => 'badge-secondary',
        'review' => 'badge-warning',
        'clarification' => 'badge-light text-dark',
        default => 'badge-light text-dark',
    };
@endphp
<span class="badge status-badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
