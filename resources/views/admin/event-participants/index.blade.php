<x-admin.layout
    title="Peserta Kegiatan & Bootcamp"
    subtitle="Monitoring pendaftar, validasi bukti transfer, dan pengelolaan peserta event non-kompetisi."
>
    <div
        x-data="{ 
            lightboxOpen: false, 
            lightboxImg: '', 
            lightboxTitle: '', 
            addModalOpen: false,
            userSearch: '',
            selectedUserId: '',
            selectedUserName: ''
        }"
        x-init="$watch('lightboxOpen', v => { if (v) { document.body.classList.add('overflow-y-hidden'); } else { document.body.classList.remove('overflow-y-hidden'); } })"
        x-on:open-lightbox.window="lightboxOpen = true; lightboxImg = $event.detail.img; lightboxTitle = $event.detail.title"
        class="flex flex-col gap-6"
    >
        <!-- Top Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-3">
            <x-admin.stat-card label="Pending Verifikasi" :value="$pendingCount" tone="amber" />
            <x-admin.stat-card label="Terverifikasi (Accepted)" :value="$acceptedCount" tone="emerald" />
            <x-admin.stat-card label="Ditolak (Rejected)" :value="$rejectedCount" tone="rose" />
        </div>

        <!-- Main Card Section -->
        <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-950">Daftar Peserta Kegiatan</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola verifikasi pembayaran dan partisipasi event.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Button Tambah Peserta Manual -->
                        <button
                            type="button"
                            @click="addModalOpen = true"
                            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors cursor-pointer"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Peserta Manual
                        </button>

                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('admin.event-participants.index') }}" class="flex flex-wrap items-center gap-2">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                                    </svg>
                                </span>
                                <input
                                    type="search"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Cari nama/email/institusi..."
                                    class="block w-full sm:w-56 pl-9 rounded-md border-gray-300 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500"
                                >
                            </div>

                            <div>
                                <select name="event_id" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Event</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" @selected(request('event_id') === $event->id)>{{ $event->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <select name="status" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="all" @selected($filterStatus === 'all')>Semua Status</option>
                                    <option value="default" @selected($filterStatus === 'default')>Pending & Rejected</option>
                                    <option value="pending" @selected($filterStatus === 'pending')>Pending</option>
                                    <option value="accepted" @selected($filterStatus === 'accepted')>Accepted</option>
                                    <option value="rejected" @selected($filterStatus === 'rejected')>Rejected</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Peserta</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Kategori</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Kegiatan</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Bukti Pembayaran</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Status</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($participants as $participant)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <!-- Peserta info -->
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-950">{{ $participant->full_name }}</p>
                                    <p class="text-xs text-gray-600">{{ $participant->email }}</p>
                                    @if($participant->phone_number)
                                        <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $participant->phone_number }}</p>
                                    @endif
                                    @if($participant->nama_sekolah)
                                        <p class="text-[11px] text-gray-500 mt-0.5 italic truncate max-w-xs">{{ $participant->nama_sekolah }}</p>
                                    @endif
                                    <p class="text-[10px] text-gray-400 mt-1">
                                        Terdaftar: {{ \Carbon\Carbon::parse($participant->date_added)->format('d M Y H:i') }}
                                    </p>
                                </td>

                                <!-- Kategori Badge -->
                                <td class="px-6 py-4">
                                    @if($participant->category === 'MineToday')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-extrabold text-amber-800 border border-amber-300 shadow-xs">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Peserta MineToday
                                        </span>
                                    @elseif($participant->category === 'Mahasiswa IPB')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-extrabold text-blue-700 border border-blue-200 shadow-xs">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                            Mahasiswa IPB (Gratis)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700 border border-gray-200 shadow-xs">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                            Umum
                                        </span>
                                    @endif
                                </td>

                                <!-- Event Title -->
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                    {{ $participant->event_title }}
                                </td>

                                <!-- Bukti Pembayaran -->
                                <td class="px-6 py-4 text-sm">
                                    @if($participant->payment_proof_url)
                                        <div class="flex items-center gap-3">
                                            <img
                                                src="{{ $participant->payment_proof_url }}"
                                                alt="Bukti Transfer {{ $participant->full_name }}"
                                                class="h-12 w-12 rounded-lg border border-gray-200 object-cover p-0.5 shadow-xs cursor-pointer hover:opacity-85 transition-opacity"
                                                @click="$dispatch('open-lightbox', { img: '{{ $participant->payment_proof_url }}', title: {{ \Illuminate\Support\Js::from('Bukti Pembayaran - ' . $participant->full_name . ' (' . $participant->event_title . ')') }} })"
                                            >
                                            <div class="flex flex-col gap-1">
                                                <button
                                                    type="button"
                                                    @click="$dispatch('open-lightbox', { img: '{{ $participant->payment_proof_url }}', title: {{ \Illuminate\Support\Js::from('Bukti Pembayaran - ' . $participant->full_name . ' (' . $participant->event_title . ')') }} })"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded border border-indigo-200 cursor-pointer transition-colors"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat Foto
                                                </button>
                                                <span class="text-[10px] text-gray-400 font-mono">Klik untuk perbesar</span>
                                            </div>
                                        </div>
                                    @else
                                        @if($participant->category === 'Mahasiswa IPB')
                                            <span class="inline-flex rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                                                Gratis (Jalur IPB)
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada bukti bayar</span>
                                        @endif
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="px-6 py-4">
                                    <x-admin.status-badge :status="$participant->payment_verification" />
                                </td>

                                <!-- Actions Column -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($participant->payment_verification !== 'accepted')
                                            <form method="POST" action="{{ route('admin.event-participants.verify') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                                                <input type="hidden" name="event_id" value="{{ $participant->event_id }}">
                                                <button
                                                    type="submit"
                                                    name="action"
                                                    value="accept"
                                                    class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-500 shadow-xs transition-colors cursor-pointer"
                                                    title="Terima Pembayaran"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Terima
                                                </button>
                                            </form>
                                        @endif

                                        @if($participant->payment_verification !== 'rejected')
                                            <form method="POST" action="{{ route('admin.event-participants.verify') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                                                <input type="hidden" name="event_id" value="{{ $participant->event_id }}">
                                                <button
                                                    type="submit"
                                                    name="action"
                                                    value="reject"
                                                    class="inline-flex items-center gap-1 rounded-md bg-rose-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-rose-500 shadow-xs transition-colors cursor-pointer"
                                                    title="Tolak Pembayaran"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Button Hapus Peserta -->
                                        <form
                                            method="POST"
                                            action="{{ route('admin.event-participants.destroy') }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus peserta {{ addslashes($participant->full_name) }} dari kegiatan ini?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                                            <input type="hidden" name="event_id" value="{{ $participant->event_id }}">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white p-1.5 text-xs font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 hover:border-red-300 shadow-xs transition-colors cursor-pointer"
                                                title="Hapus Peserta dari Kegiatan"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="font-medium">Belum ada peserta yang terdaftar sesuai filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($participants->hasPages())
                <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                    {{ $participants->links() }}
                </div>
            @endif
        </section>

        <!-- Lightbox Modal Preview Bukti Pembayaran -->
        <div
            x-show="lightboxOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="lightboxOpen = false"
        >
            <!-- Backdrop -->
            <div
                x-show="lightboxOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/80 backdrop-blur-xs transition-opacity"
                @click="lightboxOpen = false"
            ></div>

            <!-- Modal Content -->
            <div
                x-show="lightboxOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transform transition-all z-10 my-auto"
            >
                <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4 bg-white shrink-0">
                    <div>
                        <span class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-extrabold uppercase text-indigo-700">
                            Bukti Pembayaran
                        </span>
                        <h3 class="mt-1 text-base font-bold text-gray-950 truncate max-w-xl" x-text="lightboxTitle"></h3>
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

                <div class="p-6 bg-gray-50/60 flex items-center justify-center overflow-auto max-h-[70vh]">
                    <img :src="lightboxImg" alt="Document Preview" class="max-h-[65vh] w-auto max-w-full rounded-lg border border-gray-200 bg-white p-2 object-contain shadow-md">
                </div>

                <div class="px-6 py-4 bg-white border-t border-gray-200 flex justify-between items-center shrink-0">
                    <a
                        :href="lightboxImg"
                        target="_blank"
                        download
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-xs cursor-pointer"
                    >
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Buka Gambar di Tab Baru
                    </a>
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

        <!-- Modal Tambah Peserta Manual -->
        <div
            x-show="addModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="addModalOpen = false"
        >
            <!-- Backdrop -->
            <div
                x-show="addModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity"
                @click="addModalOpen = false"
            ></div>

            <!-- Modal Panel -->
            <div
                x-show="addModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transform transition-all z-10 my-auto"
            >
                <form method="POST" action="{{ route('admin.event-participants.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-950">Tambah Peserta Manual</h3>
                                <p class="text-xs text-gray-500">Daftarkan akun peserta langsung ke kegiatan/bootcamp.</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="addModalOpen = false"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-colors cursor-pointer"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <!-- Select User -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Pilih Akun User <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="userSearch"
                                placeholder="Ketik untuk filter nama atau email user..."
                                class="w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500 mb-1.5"
                            >
                            <select
                                name="user_id"
                                required
                                class="w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500"
                                size="6"
                            >
                                @foreach($allUsers as $u)
                                    <option
                                        value="{{ $u->id }}"
                                        x-show="!userSearch || '{{ strtolower(addslashes($u->full_name . ' ' . $u->email . ' ' . ($u->nama_sekolah ?? ''))) }}'.includes(userSearch.toLowerCase())"
                                    >
                                        {{ $u->full_name }} ({{ $u->email }}) @if($u->nama_sekolah) - {{ $u->nama_sekolah }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-gray-500">Pilih satu akun user dari daftar di atas.</p>
                        </div>

                        <!-- Select Event -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Pilih Kegiatan / Event <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="event_id"
                                required
                                class="w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" @selected(str_contains(strtolower($event->title), 'bootcamp'))>
                                        {{ $event->title }} ({{ $event->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Verification Status -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Status Verifikasi Awal <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="payment_verification"
                                required
                                class="w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="accepted" selected>Accepted (Langsung Terverifikasi & Lunas)</option>
                                <option value="pending">Pending (Menunggu Verifikasi)</option>
                                <option value="rejected">Rejected (Ditolak)</option>
                            </select>
                        </div>

                        <!-- Upload Bukti Transfer (Opsional) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Upload Bukti Pembayaran (Opsional)
                            </label>
                            <input
                                type="file"
                                name="payment_proof_file"
                                accept="image/*"
                                class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                            >
                            <p class="mt-1 text-[11px] text-gray-400">JPG, PNG, atau WebP (Maks 3MB).</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2.5">
                        <button
                            type="button"
                            @click="addModalOpen = false"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 shadow-xs cursor-pointer transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 shadow-xs cursor-pointer transition-colors"
                        >
                            Simpan & Daftarkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin.layout>
