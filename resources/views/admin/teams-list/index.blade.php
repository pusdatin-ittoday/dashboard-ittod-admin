<x-admin.layout
    title="List Tim & Monitoring Peserta"
    subtitle="Direktori pemantauan seluruh tim kompetisi dan event IT Today (Read-Only)."
>
    <div x-data="{ lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }" x-on:open-lightbox.window="lightboxOpen = true; lightboxImg = $event.detail.img; lightboxTitle = $event.detail.title" class="flex flex-col gap-6">
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
            <form method="GET" action="{{ route('admin.teams-list.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
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
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Event</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" {{ $selectedEventId === $ev->id ? 'selected' : '' }}>
                                {{ $ev->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Berkas Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">
                        Status Berkas
                    </label>
                    <select
                        name="status_berkas"
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
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Pembayaran</option>
                        <option value="verified" {{ $selectedStatusPembayaran === 'verified' ? 'selected' : '' }}>Lunas / Approved</option>
                        <option value="pending" {{ $selectedStatusPembayaran === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ $selectedStatusPembayaran === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Batch / Date Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">
                        Periode / Batch
                    </label>
                    <select
                        name="batch"
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

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-6 justify-end mt-1">
                    <button
                        type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-indigo-700 transition-colors cursor-pointer"
                    >
                        Terapkan Filter
                    </button>
                    @if($selectedEventId || $selectedStatusBerkas || $selectedStatusPembayaran || $selectedBatch || $searchQuery)
                        <a
                            href="{{ route('admin.teams-list.index') }}"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- Read-Only Teams Table Section with Automatic Scroll Lazy Loading -->
        <section
            x-data="{
                nextPage: {{ $teams->currentPage() + 1 }},
                hasMore: {{ $teams->hasMorePages() ? 'true' : 'false' }},
                loading: false,
                showingTo: {{ $teams->lastItem() ?? 0 }},
                total: {{ $teams->total() }},

                async loadMore() {
                    if (this.loading || !this.hasMore) return;
                    this.loading = true;

                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('page', this.nextPage);
                    urlParams.set('ajax', '1');

                    try {
                        const response = await fetch(`${window.location.pathname}?${urlParams.toString()}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();

                        if (data.rows_html) {
                            document.getElementById('teams-table-body').insertAdjacentHTML('beforeend', data.rows_html);
                        }
                        if (data.modals_html) {
                            document.getElementById('teams-modals-container').insertAdjacentHTML('beforeend', data.modals_html);
                        }

                        this.hasMore = data.has_more;
                        this.nextPage = data.next_page;
                        this.showingTo = data.showing_to;
                    } catch (e) {
                        console.error('Error loading more teams:', e);
                    } finally {
                        this.loading = false;
                    }
                }
            }"
            x-init="
                const observer = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting && hasMore && !loading) {
                        loadMore();
                    }
                }, { rootMargin: '300px' });
                if ($refs.scrollTrigger) {
                    observer.observe($refs.scrollTrigger);
                }
            "
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
        >
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Daftar Tim Terdaftar</h3>
                <span class="text-xs font-semibold text-gray-500">
                    Menampilkan <span x-text="showingTo"></span> dari <span x-text="total"></span> tim
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
                    <tbody id="teams-table-body" class="divide-y divide-gray-200 bg-white">
                        @include('admin.teams-list._team_rows', ['teams' => $teams])
                    </tbody>
                </table>
            </div>

            <!-- Auto Scroll Sentinel & Loading Indicator -->
            <div x-ref="scrollTrigger" class="h-4"></div>

            <div x-show="loading" class="px-6 py-4 border-t border-gray-200 bg-indigo-50/60 text-center text-xs font-bold text-indigo-700 flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memuat data tim berikutnya otomatis...</span>
            </div>

            <div x-show="!hasMore && total > 0" class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-center text-xs text-gray-500 font-semibold">
                Semua data tim telah ditampilkan (<span x-text="total"></span> tim)
            </div>
        </section>

        <!-- Admin Themed Image Preview Lightbox Modal -->
        <div
            x-show="lightboxOpen"
            x-cloak
            x-on:keydown.escape.window="lightboxOpen = false"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/80 p-4 sm:p-6 backdrop-blur-sm"
            style="display: none;"
        >
            <div
                @click.outside="lightboxOpen = false"
                class="relative w-full max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transform transition-all"
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
