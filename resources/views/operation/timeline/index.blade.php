<x-admin.layout
    title="Lini Masa Kegiatan"
    subtitle="Kelola lini masa resmi kegiatan non-kompetisi IT Today."
>
    <div class="mb-6 flex flex-wrap justify-end gap-3">
        <a href="{{ route('timeline.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
            Tambah Lini Masa
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <section x-data="{ search: '' }" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-950">Direktori Agenda Kegiatan</h2>
                    <span class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-bold uppercase text-indigo-700">
                        Timeline Records
                    </span>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Agenda non-kompetisi, kegiatan, dan tanggal pelaksanaan</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $timelines->count() }} records detected</p>
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
                    placeholder="Search agenda, kegiatan, atau tanggal..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Agenda</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($timelines as $timeline)
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($timeline->title . ' ' . ($timeline->event->title ?? $timeline->event_id) . ' ' . ($timeline->event?->type ?? '') . ' ' . ($timeline->date?->format('d M Y H:i') ?? '')) }}"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $timeline->title }}</p>
                                <p class="mt-1 text-xs text-gray-500">ID: {{ $timeline->id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-[11px] font-bold uppercase text-indigo-700">
                                    {{ $timeline->event->title ?? $timeline->event_id }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                {{ $timeline->date ? $timeline->date->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('timeline.edit', $timeline->id) }}" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                                        Edit
                                    </a>
                                    <form action="{{ route('timeline.destroy', $timeline->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada lini masa kegiatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</x-admin.layout>
