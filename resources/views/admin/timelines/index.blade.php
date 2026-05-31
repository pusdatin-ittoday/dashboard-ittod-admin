<x-admin.layout
    title="Pengaturan Timeline Kompetisi"
    subtitle="Kerangka untuk menyusun fase pendaftaran, submission, penjurian, dan final."
>
    <div class="mb-6 grid gap-4 md:grid-cols-2">
        <x-admin.stat-card label="Total Timeline" :value="$timelineCount" />
        <x-admin.stat-card label="Event Kompetisi" :value="$events->count()" tone="emerald" />
    </div>

    <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-gray-950">Timeline per Kompetisi</h2>
            <p class="mt-1 text-sm text-gray-600">Form create/update dapat dipasang di setiap event saat endpoint timeline sudah final.</p>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse ($events as $event)
                <div class="px-6 py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-950">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-600">{{ $event->timelines->count() }} item timeline</p>
                        </div>
                        <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Tambah Item
                        </button>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @forelse ($event->timelines as $timeline)
                            <div class="rounded-lg border border-gray-200 px-4 py-3">
                                <p class="font-semibold text-gray-950">{{ $timeline->title }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ $timeline->date?->format('d M Y H:i') }}</p>
                            </div>
                        @empty
                            <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-600">
                                Timeline belum disusun.
                            </p>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-sm text-gray-600">Belum ada event kompetisi.</p>
            @endforelse
        </div>
    </section>
</x-admin.layout>
