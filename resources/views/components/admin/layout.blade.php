@props([
    'title',
    'subtitle' => null,
])

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Admin Portal IT Today</p>
            <h1 class="text-2xl font-semibold text-gray-950">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-sm text-gray-600">{{ $subtitle }}</p>
            @endif
        </div>
    </x-slot>

    <div class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</x-app-layout>
