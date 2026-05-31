@props([
    'active' => false,
    'href' => '#',
])

<a
    href="{{ $href }}"
    @class([
        'block rounded-lg px-4 py-3 text-sm font-semibold transition',
        'bg-gray-950 text-white shadow-sm' => $active,
        'text-gray-700 hover:bg-white hover:text-gray-950 hover:shadow-sm' => ! $active,
    ])
>
    {{ $slot }}
</a>
