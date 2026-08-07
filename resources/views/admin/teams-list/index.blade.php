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
                        <option value="verified" {{ $selectedStatusBerkas === 'verified' ? 'selected' : '' }}>✓ Terverifikasi</option>
                        <option value="pending" {{ $selectedStatusBerkas === 'pending' ? 'selected' : '' }}>⏱ Pending</option>
                        <option value="rejected" {{ $selectedStatusBerkas === 'rejected' ? 'selected' : '' }}>✕ Ditolak</option>
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
                        <option value="verified" {{ $selectedStatusPembayaran === 'verified' ? 'selected' : '' }}>✓ Lunas / Approved</option>
                        <option value="pending" {{ $selectedStatusPembayaran === 'pending' ? 'selected' : '' }}>⏱ Pending</option>
                        <option value="rejected" {{ $selectedStatusPembayaran === 'rejected' ? 'selected' : '' }}>✕ Ditolak</option>
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
                        class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-indigo-700 transition-colors"
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

        <!-- Read-Only Teams Table Section -->
        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Daftar Tim Terdaftar</h3>
                <span class="text-xs font-semibold text-gray-500">
                    Menampilkan {{ $teams->firstItem() ?? 0 }}-{{ $teams->lastItem() ?? 0 }} dari {{ $teams->total() }} tim
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
                        @forelse ($teams as $team)
                            @php
                                $leader = $team->members->first(fn($m) => $m->role === 'leader')?->user ?? $team->members->first()?->user;
                            @endphp
                            <tr class="align-top hover:bg-gray-50">
                                <!-- Nama Tim & Kode -->
                                <td class="px-4 py-3.5">
                                    <p class="font-extrabold text-gray-900 text-xs sm:text-sm">
                                        {{ $team->team_name }}
                                    </p>
                                    @if($team->team_code)
                                        <p class="text-[11px] font-mono text-indigo-600 font-bold mt-0.5">
                                            Code: {{ $team->team_code }}
                                        </p>
                                    @endif
                                </td>

                                <!-- Event / Kompetisi -->
                                <td class="px-3 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700">
                                        {{ $team->event?->title ?? 'Kompetisi' }}
                                    </span>
                                </td>

                                <!-- Ketua & Sekolah -->
                                <td class="px-4 py-3.5">
                                    <p class="font-bold text-gray-900 text-xs">
                                        👑 {{ $leader?->full_name ?? 'Unknown Leader' }}
                                    </p>
                                    <p class="text-[11px] text-gray-500 font-mono mt-0.5">
                                        {{ $leader?->email ?? '-' }}
                                    </p>
                                    @if($leader?->nama_sekolah)
                                        <p class="text-[11px] text-gray-600 mt-0.5">
                                            🏫 {{ $leader->nama_sekolah }}
                                        </p>
                                    @endif
                                </td>

                                <!-- Jumlah Anggota -->
                                <td class="px-3 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                        👥 {{ $team->members->count() }} Orang
                                    </span>
                                </td>

                                <!-- Status Berkas -->
                                <td class="px-3 py-3.5 whitespace-nowrap">
                                    @if(in_array($team->is_document_verified, ['verified', 'approved', '1', 1], true))
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-bold text-blue-800">
                                            ✓ Berkas Lolos
                                        </span>
                                    @elseif(in_array($team->is_document_verified, ['rejected', '0'], true))
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-800">
                                            ✕ Berkas Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                                            ⏱ Berkas Pending
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Pembayaran -->
                                <td class="px-3 py-3.5 whitespace-nowrap">
                                    @if(in_array($team->is_verified, ['approved', 'verified', '1', 1], true))
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">
                                            ✓ Bayar Lunas
                                        </span>
                                    @elseif(in_array($team->is_verified, ['rejected', '0'], true))
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-800">
                                            ✕ Bayar Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                                            ⏱ Bayar Pending
                                        </span>
                                    @endif
                                </td>

                                <!-- Waktu Daftar -->
                                <td class="px-3.5 py-3.5 text-xs font-medium text-gray-600 whitespace-nowrap">
                                    @if($team->created_at?->isBefore('2026-08-01'))
                                        <span class="inline-flex items-center rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-extrabold text-purple-800 border border-purple-200 mb-1">
                                            Batch 1 (17-31 Jul)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-extrabold text-indigo-800 border border-indigo-200 mb-1">
                                            Batch 2 (31 Jul - 11 Agu)
                                        </span>
                                    @endif
                                    <div>{{ $team->created_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                    <div class="text-gray-400 font-mono text-[11px] mt-0.5">{{ $team->created_at?->format('H:i') ?? '' }} WIB</div>
                                </td>

                                <!-- Aksi Read-Only Detail -->
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'view-team-{{ $team->id }}')"
                                        class="rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 cursor-pointer"
                                    >
                                        Detail Preview
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                    Tidak ada data tim yang sesuai dengan kriteria pencarian/filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Laravel Pagination Links -->
            @if($teams->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $teams->links() }}
                </div>
            @endif
        </section>

        <!-- Fullscreen Image Lightbox Modal -->
        <div
            x-show="lightboxOpen"
            x-cloak
            x-on:keydown.escape.window="lightboxOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-md"
            style="display: none;"
        >
            <button
                type="button"
                @click="lightboxOpen = false"
                class="absolute top-4 right-4 z-50 rounded-full bg-white/20 p-2.5 text-white hover:bg-white/40 focus:outline-none transition-colors cursor-pointer"
                title="Tutup Preview (Esc)"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div @click.outside="lightboxOpen = false" class="relative max-w-5xl max-h-[92vh] flex flex-col items-center justify-center overflow-hidden rounded-xl bg-black/60 p-3 border border-white/20 shadow-2xl">
                <img :src="lightboxImg" alt="Enlarged Document" class="max-h-[82vh] w-auto max-w-full rounded-lg object-contain shadow-2xl">
                <div class="mt-3 text-center text-xs font-bold text-white/90 flex items-center gap-3">
                    <span x-text="lightboxTitle" class="bg-indigo-900/80 text-indigo-200 px-2.5 py-0.5 rounded border border-indigo-500/40"></span>
                    <span>&bull;</span>
                    <a :href="lightboxImg" target="_blank" class="text-indigo-300 underline hover:text-white transition-colors">
                        Buka Dokumen Asli di Tab Baru ↗
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Detail Preview Read-Only -->
    @foreach ($teams as $team)
        <x-modal name="view-team-{{ $team->id }}" maxWidth="3xl" focusable>
            <div class="p-6">
                <div class="flex items-start justify-between border-b border-gray-200 pb-4">
                    <div>
                        <span class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-extrabold uppercase text-indigo-700">
                            {{ $team->event?->title ?? 'Kompetisi' }}
                        </span>
                        <h3 class="mt-2 text-xl font-bold text-gray-950">{{ $team->team_name }}</h3>
                        @if($team->team_code)
                            <p class="text-xs font-mono text-indigo-600 font-bold mt-0.5">Kode Tim: {{ $team->team_code }}</p>
                        @endif
                    </div>
                    <div class="text-right flex flex-col items-end gap-1">
                        <!-- Status Badges -->
                        <div class="flex items-center gap-1.5">
                            @if(in_array($team->is_document_verified, ['verified', 'approved', '1', 1], true))
                                <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800">✓ Berkas Lolos</span>
                            @else
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">⏱ Berkas Pending</span>
                            @endif

                            @if(in_array($team->is_verified, ['approved', 'verified', '1', 1], true))
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">✓ Bayar Lunas</span>
                            @else
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">⏱ Bayar Pending</span>
                            @endif
                        </div>
                        <p class="text-[11px] font-mono text-gray-500 mt-1">
                            Terdaftar: {{ $team->created_at?->translatedFormat('d F Y, H:i') ?? '-' }} WIB
                        </p>
                    </div>
                </div>

                <!-- Members Roster List -->
                <div class="mt-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Daftar Anggota Tim ({{ $team->members->count() }} Orang)</h4>
                    <div class="space-y-3">
                        @foreach($team->members as $mem)
                            @php $u = $mem->user; @endphp
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div class="flex items-start gap-3">
                                    <div class="h-8 w-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($u?->full_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-gray-900 text-sm">{{ $u?->full_name ?? 'Nama Unknown' }}</span>
                                            @if($mem->role === 'leader')
                                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-200">
                                                    Ketua Tim
                                                </span>
                                            @else
                                                <span class="bg-gray-200 text-gray-700 text-[10px] font-semibold px-2 py-0.5 rounded">
                                                    Anggota
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-gray-500 font-mono mt-0.5">{{ $u?->email ?? '-' }}</p>
                                        @if($u?->phone_number)
                                            <p class="text-gray-500 font-mono">WA/Telp: {{ $u->phone_number }}</p>
                                        @endif
                                        @if($u?->nama_sekolah)
                                            <p class="text-gray-600 mt-1">Instansi: <strong>{{ $u->nama_sekolah }}</strong></p>
                                        @endif
                                    </div>
                                </div>

                                @if($u?->ktm_key)
                                    <div class="shrink-0">
                                        <button
                                            type="button"
                                            @click="$dispatch('open-lightbox', { img: '{{ env('R2_PUBLIC', 'https://cdn.ittoday.web.id') . '/' . $u->ktm_key }}', title: 'KTM / Kartu Identitas - {{ addslashes($u?->full_name ?? '') }}' })"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded border border-indigo-200 shadow-sm cursor-pointer transition-colors"
                                        >
                                            <span>🔍 Preview KTM / Kartu</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex justify-end border-t border-gray-200 pt-4">
                    <button type="button" x-on:click="$dispatch('close-modal', 'view-team-{{ $team->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 cursor-pointer">Tutup</button>
                </div>
            </div>
        </x-modal>
    @endforeach
</x-admin.layout>
