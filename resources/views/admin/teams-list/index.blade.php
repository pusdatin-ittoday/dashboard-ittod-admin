<x-admin.layout
    title="List Tim & Monitoring Peserta"
    subtitle="Direktori pemantauan seluruh tim kompetisi dan event IT Today (Read-Only)."
>
    <div 
        x-data="{ 
            lightboxOpen: false, 
            lightboxImg: '', 
            lightboxTitle: '',
            isExportingSheets: false,
            exportCsv() {
                const form = document.getElementById('filter-form');
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                window.location.href = '{{ route('export.teams-list.csv') }}?' + params.toString();
            },
            async exportToSheets() {
                this.isExportingSheets = true;
                const form = document.getElementById('filter-form');
                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());

                try {
                    const response = await fetch('{{ route('export.teams-list.sheets') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await response.json();
                    if (data.success) {
                        const newWindow = window.open(data.url, '_blank');
                        if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                            alert('Ekspor berhasil! Namun tab baru terblokir oleh browser. Silakan buka: ' + data.url);
                        }
                    } else {
                        alert('Gagal mengekspor: ' + (data.message || 'Terjadi kesalahan.'));
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.isExportingSheets = false;
                }
            }
        }" 
        x-init="$watch('lightboxOpen', v => { if (v) { document.body.classList.add('overflow-y-hidden'); } else { document.body.classList.remove('overflow-y-hidden'); } })" 
        x-on:open-lightbox.window="lightboxOpen = true; lightboxImg = $event.detail.img; lightboxTitle = $event.detail.title" 
        class="flex flex-col gap-6"
    >
        <!-- Summary Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Tim</span>
                    <span class="rounded-full bg-indigo-50 p-2 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ $stats['total_teams'] }}</p>
                <p class="mt-1 text-xs text-gray-500">Tim terdaftar saat ini</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Berkas Terverifikasi</span>
                    <span class="rounded-full bg-blue-50 p-2 text-blue-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-black text-blue-700">{{ $stats['verified_berkas'] }}</p>
                <p class="mt-1 text-xs text-gray-500">Berkas lolos validasi</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Pembayaran Lunas</span>
                    <span class="rounded-full bg-emerald-50 p-2 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['verified_pembayaran'] }}</p>
                <p class="mt-1 text-xs text-gray-500">Transaksi disetujui</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-700">Total Anggota</span>
                    <span class="rounded-full bg-purple-50 p-2 text-purple-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-black text-purple-700">{{ $stats['total_members'] }}</p>
                <p class="mt-1 text-xs text-gray-500">Total individu peserta</p>
            </div>
        </div>

        <!-- Filter & Search Bar Section -->
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <form id="filter-form" method="GET" action="{{ route('admin.teams-list.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                <!-- Search -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">
                        Cari Tim / Peserta
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                            </svg>
                        </span>
                        <input
                            type="search"
                            name="search"
                            value="{{ $searchQuery }}"
                            placeholder="Nama tim, kode, ketua, email, sekolah..."
                            onsearch="this.form.submit()"
                            class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <!-- Event Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">
                        Event / Lomba
                    </label>
                    <select
                        name="event_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <optgroup label="Global">
                            <option value="" @selected(empty($selectedEventId))>Semua Tim Kompetisi</option>
                            <option value="all_events" @selected($selectedEventId === 'all_events')>Semua Event (Non-Kompetisi)</option>
                        </optgroup>

                        @php
                            $compEvents = $events->where('type', 'competition');
                            $nonCompEvents = $events->where('type', 'non_competition');
                        @endphp

                        @if($compEvents->isNotEmpty())
                            <optgroup label="Kompetisi / Lomba">
                                @foreach($compEvents as $ev)
                                    <option value="{{ $ev->id }}" @selected($selectedEventId === $ev->id)>
                                        {{ $ev->title }} ({{ $ev->participation_type === 'individual' ? 'Individu' : 'Tim' }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if($nonCompEvents->isNotEmpty())
                            <optgroup label="Kegiatan / Event">
                                @foreach($nonCompEvents as $ev)
                                    <option value="{{ $ev->id }}" @selected($selectedEventId === $ev->id)>
                                        {{ $ev->title }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <!-- Status Berkas Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">
                        Status Berkas
                    </label>
                    <select
                        name="status_berkas"
                        onchange="this.form.submit()"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Berkas</option>
                        <option value="verified" {{ $selectedStatusBerkas === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="pending" {{ $selectedStatusBerkas === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ $selectedStatusBerkas === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Status Pembayaran Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">
                        Status Pembayaran
                    </label>
                    <select
                        name="status_pembayaran"
                        onchange="this.form.submit()"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Pembayaran</option>
                        <option value="verified" {{ $selectedStatusPembayaran === 'verified' ? 'selected' : '' }}>Bayar Lunas</option>
                        <option value="unverified" {{ $selectedStatusPembayaran === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi (Ada Bukti)</option>
                        <option value="unpaid" {{ $selectedStatusPembayaran === 'unpaid' ? 'selected' : '' }}>Belum Bayar (Tanpa Bukti)</option>
                        <option value="rejected" {{ $selectedStatusPembayaran === 'rejected' ? 'selected' : '' }}>Bayar Ditolak</option>
                    </select>
                </div>

                <!-- Batch / Date Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">
                        Periode / Batch
                    </label>
                    <select
                        name="batch"
                        onchange="this.form.submit()"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Periode</option>
                        <option value="batch_1" {{ $selectedBatch === 'batch_1' ? 'selected' : '' }}>Batch 1 (17 Jul - 31 Jul)</option>
                        <option value="batch_2" {{ $selectedBatch === 'batch_2' ? 'selected' : '' }}>Batch 2 (31 Jul - 11 Agu)</option>
                        <option value="today" {{ $selectedBatch === 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="this_week" {{ $selectedBatch === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="this_month" {{ $selectedBatch === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    </select>
                </div>

                <!-- Action & Export Buttons -->
                <div class="flex flex-wrap items-center gap-2 sm:col-span-2 lg:col-span-6 justify-between mt-2 pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                        @if($selectedEventId || $selectedStatusBerkas || $selectedStatusPembayaran || $selectedBatch || $searchQuery)
                            <a
                                href="{{ route('admin.teams-list.index') }}"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-xs transition-colors"
                            >
                                <svg class="mr-1.5 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filter
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="exportCsv()"
                            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-white shadow-xs hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors cursor-pointer"
                        >
                            <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export CSV
                        </button>

                        <button
                            type="button"
                            @click="exportToSheets()"
                            :disabled="isExportingSheets"
                            class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-white shadow-xs hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer"
                        >
                            <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <template x-if="isExportingSheets">
                                <span>Exporting...</span>
                            </template>
                            <template x-if="!isExportingSheets">
                                <span>Export Google Sheets</span>
                            </template>
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Read-Only Teams Table Section -->
        <section id="teams-table" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Daftar Tim Terdaftar</h3>
                <span class="text-xs font-semibold text-gray-500">
                    Menampilkan {{ $teams->firstItem() ?? 0 }} - {{ $teams->lastItem() ?? 0 }} dari {{ $teams->total() }} tim
                </span>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600">Nama Tim & Kode</th>
                            <th class="px-3 py-3 text-xs font-bold uppercase text-gray-600 whitespace-nowrap">Event / Lomba</th>
                            <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600">Ketua & Instansi</th>
                            <th class="px-3 py-3 text-xs font-bold uppercase text-gray-600 whitespace-nowrap">Anggota</th>
                            <th class="px-3 py-3 text-xs font-bold uppercase text-gray-600 whitespace-nowrap">Status Berkas</th>
                            <th class="px-3 py-3 text-xs font-bold uppercase text-gray-600 whitespace-nowrap">Status Pembayaran</th>
                            <th class="px-3.5 py-3 text-xs font-bold uppercase text-gray-600 whitespace-nowrap">Waktu Daftar</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase text-gray-600 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @include('admin.teams-list._team_rows', ['teams' => $teams])
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-center">
                {{ $teams->links('components.admin.pagination') }}
            </div>
        </section>

        <!-- Admin Themed Image Preview Lightbox Modal with Smooth Transition -->
        <div
            x-show="lightboxOpen"
            x-cloak
            x-on:keydown.escape.window="lightboxOpen = false"
            class="fixed inset-0 z-[99999] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
            style="display: none;"
        >
            <!-- Dark Backdrop Fade Transition -->
            <div
                x-show="lightboxOpen"
                x-on:click="lightboxOpen = false"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-all"
            ></div>

            <!-- Modal Card Scale & Fade Transition -->
            <div
                x-show="lightboxOpen"
                @click.outside="lightboxOpen = false"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transform transition-all z-10 my-auto"
            >
                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4 bg-white shrink-0">
                    <div>
                        <span class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-extrabold uppercase text-indigo-700">
                            Preview Dokumen
                        </span>
                        <h3 class="mt-1 text-lg font-bold text-gray-950 truncate" x-text="lightboxTitle"></h3>
                    </div>
                    <button
                        type="button"
                        @click="lightboxOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors cursor-pointer"
                        title="Tutup (Esc)"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body Image View -->
                <div class="p-6 bg-gray-50/60 flex items-center justify-center overflow-auto max-h-[70vh]">
                    <img :src="lightboxImg" alt="Document Preview" class="max-h-[65vh] w-auto max-w-full rounded-lg border border-gray-200 bg-white p-2 object-contain shadow-md">
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-white border-t border-gray-200 flex justify-end shrink-0">
                    <button
                        type="button"
                        @click="lightboxOpen = false"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-xs cursor-pointer transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Detail Preview Read-Only Container -->
    <div id="teams-modals-container">
        @include('admin.teams-list._team_modals', ['teams' => $teams])
    </div>
</x-admin.layout>
