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

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-950">Prioritas Hari Ini</h2>
                <p class="text-sm text-gray-600">Gunakan modul di sidebar untuk masuk ke antrean kerja masing-masing.</p>
            </div>
            <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                Buka Verifikasi
            </a>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="font-semibold text-gray-950">Keuangan</p>
                <p class="mt-2 text-sm text-gray-600">Validasi bukti transfer tim kompetisi dan catat alasan penolakan bila ada.</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="font-semibold text-gray-950">Administrasi Peserta</p>
                <p class="mt-2 text-sm text-gray-600">Pantau kelengkapan berkas, peserta event, dan dokumen pendukung.</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="font-semibold text-gray-950">Timeline</p>
                <p class="mt-2 text-sm text-gray-600">Pastikan jadwal kompetisi tersusun dan siap dipublikasikan.</p>
            </div>
        </div>
    </section>
</x-admin.layout>
