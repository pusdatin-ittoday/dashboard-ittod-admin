@props([
    'status' => 'pending',
])

@php
    $labels = [
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'pending' => 'Pending',
    ];

    $classes = [
        'accepted' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
        'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
    ];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $classes[$status] ?? $classes['pending'] }}">
    {{ $labels[$status] ?? ucfirst($status) }}
</span>
