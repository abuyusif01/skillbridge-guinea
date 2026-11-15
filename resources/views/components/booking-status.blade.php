@props(['status'])

@php
    $class = match ($status->value) {
        'pending' => 'badge-amber',
        'accepted' => 'badge-blue',
        'completed' => 'badge-green',
        'declined' => 'badge-red',
        default => '',
    };
@endphp

<span class="badge {{ $class }}">{{ $status->label() }}</span>
