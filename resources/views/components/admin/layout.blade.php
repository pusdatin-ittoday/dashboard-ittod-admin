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

    <div class="bg-gray-50">
        <div class="mx-auto flex max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:px-8">
            <aside class="hidden w-64 shrink-0 lg:block">
                <div class="sticky top-8 space-y-2">
                    <x-admin.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        Overview
                    </x-admin.sidebar-link>
                    <x-admin.sidebar-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff.*')">
                        Manajemen Staff
                    </x-admin.sidebar-link>
                    <x-admin.sidebar-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')">
                        Verifikasi Transaksi
                    </x-admin.sidebar-link>
                    <x-admin.sidebar-link :href="route('admin.files-participants.index')" :active="request()->routeIs('admin.files-participants.*')">
                        Berkas & Peserta
                    </x-admin.sidebar-link>
                    <x-admin.sidebar-link :href="route('admin.timelines.index')" :active="request()->routeIs('admin.timelines.*')">
                        Timeline Kompetisi
                    </x-admin.sidebar-link>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <div class="mb-6 grid gap-2 sm:hidden">
                    <x-admin.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        Overview
                    </x-admin.sidebar-link>
                    <x-admin.sidebar-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')">
                        Verifikasi Transaksi
                    </x-admin.sidebar-link>
                    <x-admin.sidebar-link :href="route('admin.files-participants.index')" :active="request()->routeIs('admin.files-participants.*')">
                        Berkas & Peserta
                    </x-admin.sidebar-link>
                    <x-admin.sidebar-link :href="route('admin.timelines.index')" :active="request()->routeIs('admin.timelines.*')">
                        Timeline
                    </x-admin.sidebar-link>
                </div>

                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>
