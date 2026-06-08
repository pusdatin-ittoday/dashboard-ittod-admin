<x-admin.layout
    title="Kelola Berkas & Peserta"
    subtitle="Direktori monitoring kelengkapan peserta, tim, dan dokumen pendukung acara."
>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('export.teams.global') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition-all duration-150">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Unduh Semua Tim
            </a>
            <a href="{{ route('export.participants.global') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm transition-all duration-150">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Unduh Semua Peserta Seminar
            </a>
            <a href="{{ route('admin.files.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all duration-150">
                Lihat Berkas Terbaru
            </a>
        </div>

        <section x-data="{ search: '' }" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-950">Direktori Event & Peserta</h2>
                        <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">
                            Participant Records
                        </span>
                    </div>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Monitoring event, tim, peserta, dan status kelengkapan</p>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $events->count() }} records detected</p>
            </div>

            <div class="border-b border-gray-200 px-6 py-4">
                <label class="relative block">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                        </svg>
                    </span>
                    <input
                        type="search"
                        x-model="search"
                        placeholder="Search event, tipe, jumlah tim, atau peserta..."
                        class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </label>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Teams</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Participants</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($events as $event)
                            @php
                                $eventType = ucfirst(str_replace('_', ' ', $event->type));
                                $isEmpty = $event->teams_count === 0 && $event->participants_count === 0;
                            @endphp
                            <tr
                                x-show="$el.dataset.search.includes(search.toLowerCase())"
                                data-search="{{ Str::lower($event->title . ' ' . $eventType . ' ' . $event->teams_count . ' tim ' . $event->participants_count . ' peserta') }}"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-950">{{ $event->title }}</p>
                                    <p class="mt-1 text-xs text-gray-500">ID: {{ $event->id }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-[11px] font-bold uppercase text-indigo-700">
                                        {{ $eventType }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">{{ $event->teams_count }} tim</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">{{ $event->participants_count }} peserta</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $isEmpty ? 'bg-gray-100 text-gray-600' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $isEmpty ? 'Belum ada data' : 'Data tersedia' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada event.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</x-admin.layout>
