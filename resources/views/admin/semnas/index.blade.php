<x-admin.layout
    title="Peserta Seminar Nasional (Semnas)"
    subtitle="Data pendaftaran peserta Seminar Nasional beserta respon pertanyaan mitra dan verifikasi kartu institusi / follow Instagram."
>
<div x-data="{
    search: '',
    isExporting: false,
    async exportToSheets() {
        this.isExporting = true;
        const filter = '{{ $filterEventId }}';
        const exportType = filter ? 'semnas_participants_event' : 'semnas_participants_global';
        
        try {
            const response = await fetch('{{ route('export.recap.sheets') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    export_type: exportType,
                    event_id: filter || null
                })
            });
            const data = await response.json();
            if (data.success) {
                const newWindow = window.open(data.url, '_blank');
                if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                    alert('Ekspor berhasil! Namun tab baru terblokir oleh browser. Silakan buka manual: ' + data.url);
                }
            } else {
                alert('Gagal mengekspor: ' + (data.message || 'Terjadi kesalahan.'));
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan jaringan.');
        } finally {
            this.isExporting = false;
        }
    }
}">
    <!-- Header & Action Bar -->
    <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-950">Peserta Semnas</h2>
                <span class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-bold uppercase text-indigo-700">
                    Seminar Nasional
                </span>
                <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">
                    {{ $participants->total() }} Peserta
                </span>
            </div>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                Pendaftaran & survei kemitraan Acer, NVIDIA, Microsoft, dan Sentral Komputer
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
            <form method="GET" action="{{ route('admin.semnas.index') }}" class="flex items-center gap-2">
                <label class="sr-only">Filter Event</label>
                <select name="event_id" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Event Semnas</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected($filterEventId === $event->id)>
                            {{ $event->title }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a 
                href="{{ route('export.semnas-participants', ['event_id' => $filterEventId]) }}"
                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-bold uppercase text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>

            @if(in_array(auth()->user()->role, ['superadmin', 'admin_biasa']))
                <button 
                    @click="exportToSheets()" 
                    :disabled="isExporting"
                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-bold uppercase text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <template x-if="isExporting">
                        <span>Exporting...</span>
                    </template>
                    <template x-if="!isExporting">
                        <span>Export Google Sheets</span>
                    </template>
                </button>
            @endif
        </div>
    </div>

    <!-- Table Section -->
    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Daftar Peserta Seminar Nasional</h3>
                <p class="text-xs text-gray-500 mt-0.5">Menampilkan seluruh data pendaftar seminar nasional beserta bukti berkas</p>
            </div>
            <div class="w-full sm:w-72">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        x-model="search"
                        type="text" 
                        placeholder="Cari nama, institusi, IG..." 
                        class="w-full rounded-md border-gray-300 pl-9 text-xs focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-gray-400"
                    >
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center w-12">No</th>
                        <th scope="col" class="px-4 py-3.5">Peserta</th>
                        <th scope="col" class="px-4 py-3.5">NIM / Kartu Institusi</th>
                        <th scope="col" class="px-4 py-3.5">Instagram & Bukti Follow</th>
                        <th scope="col" class="px-4 py-3.5">Respon Pertanyaan Mitra</th>
                        <th scope="col" class="px-4 py-3.5">Event & Waktu Daftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($participants as $p)
                        @php
                            $searchData = Str::lower($p->full_name . ' ' . ($p->nama_sekolah ?? '') . ' ' . ($p->id_instagram ?? '') . ' ' . ($p->event_title ?? ''));
                        @endphp
                        <tr 
                            x-show="!search || '{{ addslashes($searchData) }}'.includes(search.toLowerCase())"
                            class="hover:bg-gray-50 transition"
                        >
                            <!-- No -->
                            <td class="px-4 py-4 text-center text-xs font-medium text-gray-500">
                                {{ $loop->iteration + ($participants->currentPage() - 1) * $participants->perPage() }}
                            </td>

                            <!-- Peserta -->
                            <td class="px-4 py-4">
                                <p class="font-semibold text-gray-950">{{ $p->full_name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>{{ $p->nama_sekolah ?: '-' }}</span>
                                </p>
                            </td>

                            <!-- NIM / Kartu Institusi -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($p->ktm_url)
                                    <a 
                                        href="{{ $p->ktm_url }}" 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 hover:border-indigo-300 transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Lihat Kartu
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak dilampirkan</span>
                                @endif
                            </td>

                            <!-- Instagram & Bukti Follow -->
                            <td class="px-4 py-4">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-700">
                                        <span class="font-medium text-pink-600">IG:</span>
                                        <span class="font-semibold">{{ $p->id_instagram ? '@' . ltrim($p->id_instagram, '@') : '-' }}</span>
                                    </div>
                                    @if($p->ig_follow_url)
                                        <a 
                                            href="{{ $p->ig_follow_url }}" 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-100 transition"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Bukti Follow IG
                                        </a>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">Tanpa bukti</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Respon Pertanyaan Mitra -->
                            <td class="px-4 py-4">
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                                    <div>
                                        <span class="text-gray-500">Sentral:</span>
                                        <span class="font-bold {{ $p->kenal_sentral_komputer ? 'text-emerald-700' : 'text-gray-500' }}">
                                            {{ $p->kenal_sentral_komputer ? 'Ya' : 'Tidak' }}
                                        </span>
                                        @if($p->kenal_sentral_komputer && $p->sumber_kenal_sentral)
                                            <span class="text-[10px] text-gray-500 block truncate max-w-[140px]" title="{{ $p->sumber_kenal_sentral }}">
                                                ({{ $p->sumber_kenal_sentral }})
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Acer:</span>
                                        <span class="font-bold {{ $p->kenal_acer ? 'text-emerald-700' : 'text-gray-500' }}">
                                            {{ $p->kenal_acer ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">NVIDIA:</span>
                                        <span class="font-bold {{ $p->kenal_nvidia ? 'text-emerald-700' : 'text-gray-500' }}">
                                            {{ $p->kenal_nvidia ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Microsoft:</span>
                                        <span class="font-bold {{ $p->kenal_microsoft ? 'text-emerald-700' : 'text-gray-500' }}">
                                            {{ $p->kenal_microsoft ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Event & Waktu Daftar -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 rounded border border-indigo-100 bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700">
                                    {{ $p->event_title }}
                                </span>
                                <p class="text-[11px] text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($p->date_added)->translatedFormat('d M Y, H:i') }}
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="font-semibold text-gray-600">Belum ada data peserta seminar nasional.</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Peserta yang mendaftar seminar nasional akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($participants->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                {{ $participants->links() }}
            </div>
        @endif
    </section>
</div>
</x-admin.layout>
