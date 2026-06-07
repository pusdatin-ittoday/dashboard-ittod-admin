<x-admin.layout
    title="Berkas Terbaru"
    subtitle="Dashboard audit dokumen terbaru dari peserta dan submission event."
>
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.files-participants.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Lihat Direktori Peserta
        </a>
    </div>

    <section x-data="{ search: '' }" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-950">Direktori Berkas</h2>
                    <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">
                        File Records
                    </span>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Monitoring dokumen peserta dan submission event</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $recentFiles->count() }} files detected</p>
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
                    placeholder="Search nama berkas, grouping, tipe, uploader, atau tanggal..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">File</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Grouping</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Uploader</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Uploaded At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($recentFiles as $file)
                        @php
                            $uploadedAt = $file->created_at?->format('d M Y H:i') ?? 'Tanggal belum tersedia';
                        @endphp
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($file->name . ' ' . $file->grouping . ' ' . $file->type . ' ' . ($file->uploader?->full_name ?? $file->uploader?->email ?? '') . ' ' . $uploadedAt) }}"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $file->name }}</p>
                                <p class="mt-1 text-xs text-gray-500">ID: {{ $file->id }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $file->grouping }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-[11px] font-bold uppercase text-indigo-700">
                                    {{ $file->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $file->uploader?->full_name ?? $file->uploader?->email ?? 'Tidak diketahui' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $uploadedAt }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada berkas terbaru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin.layout>
