@props([
    'label',
    'value',
    'tone' => 'gray',
])

@php
    $tones = [
        'gray' => 'border-gray-200 bg-white text-gray-950',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-950',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-950',
    ];
@endphp

<div class="rounded-lg border {{ $tones[$tone] ?? $tones['gray'] }} p-5 shadow-sm">
    <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold">{{ $value }}</p>
</div>
