<x-admin.layout
    title="Dashboard Operasional"
    subtitle="Ringkasan cepat antrean verifikasi, event aktif, dan pekerjaan panitia."
>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Total Event" :value="$stats['events']" />
        <x-admin.stat-card label="Total Tim" :value="$stats['teams']" />
        <x-admin.stat-card label="Menunggu Verifikasi" :value="$stats['pendingTransactions']" tone="amber" />
        <x-admin.stat-card label="Ditolak" :value="$stats['rejectedTransactions']" tone="rose" />
    </div>


    <section x-data="{ search: '' }" class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-950">Prioritas Hari Ini</h2>
                <p class="text-sm text-gray-600">Gunakan menu navigasi untuk masuk ke antrean kerja masing-masing.</p>
            </div>
            <a href="{{ route('operation.teams.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                Buka Verifikasi
            </a>
        </div>

        <div class="mt-5">
            <label class="relative block">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                    </svg>
                </span>
                <input
                    type="search"
                    x-model="search"
                    placeholder="Search modul dashboard..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div
                x-show="$el.dataset.search.includes(search.toLowerCase())"
                data-search="keuangan validasi bukti transfer transaksi verifikasi penolakan"
                class="rounded-lg border border-gray-200 p-4"
            >
                <p class="font-semibold text-gray-950">Keuangan</p>
                <p class="mt-2 text-sm text-gray-600">Validasi bukti transfer tim kompetisi dan catat alasan penolakan bila ada.</p>
            </div>
            <div
                x-show="$el.dataset.search.includes(search.toLowerCase())"
                data-search="administrasi peserta berkas event dokumen pendukung kelengkapan"
                class="rounded-lg border border-gray-200 p-4"
            >
                <p class="font-semibold text-gray-950">Administrasi Peserta</p>
                <p class="mt-2 text-sm text-gray-600">Pantau kelengkapan berkas, peserta event, dan dokumen pendukung.</p>
            </div>
            <div
                x-show="$el.dataset.search.includes(search.toLowerCase())"
                data-search="timeline kompetisi jadwal lomba publikasi"
                class="rounded-lg border border-gray-200 p-4"
            >
                <p class="font-semibold text-gray-950">Timeline</p>
                <p class="mt-2 text-sm text-gray-600">Pastikan jadwal kompetisi tersusun dan siap dipublikasikan.</p>
            </div>
        </div>
    </section>
</x-admin.layout>
