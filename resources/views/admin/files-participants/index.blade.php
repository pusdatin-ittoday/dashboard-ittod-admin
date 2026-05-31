<x-admin.layout
    title="Kelola Berkas & Peserta"
    subtitle="Kerangka monitoring kelengkapan peserta, tim, dan dokumen pendukung acara."
>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-950">Rekap Peserta per Event</h2>
                <p class="mt-1 text-sm text-gray-600">Gunakan data ini sebagai dasar filter detail peserta dan kelengkapan berkas.</p>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse ($events as $event)
                    <div class="grid gap-4 px-6 py-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                        <div>
                            <p class="font-semibold text-gray-950">{{ $event->title }}</p>
                            <p class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $event->type)) }}</p>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <span class="rounded-md bg-gray-100 px-3 py-2 font-semibold text-gray-700">{{ $event->teams_count }} tim</span>
                            <span class="rounded-md bg-gray-100 px-3 py-2 font-semibold text-gray-700">{{ $event->participants_count }} peserta</span>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-gray-600">Belum ada event.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-950">Berkas Terbaru</h2>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse ($recentFiles as $file)
                    <div class="px-6 py-4">
                        <p class="font-semibold text-gray-950">{{ $file->name }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $file->grouping }} · {{ $file->type }}</p>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-gray-600">Belum ada berkas terbaru.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-admin.layout>
