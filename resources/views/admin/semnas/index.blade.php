<x-admin.layout
    title="Verifikasi Seminar Nasional"
    subtitle="Monitoring pendaftaran peserta Semnas, verifikasi bukti follow IG, dan pengelola link WhatsApp Group."
>
    <div
        x-data="{ 
            lightboxOpen: false, 
            lightboxImg: '', 
            lightboxTitle: '',
            lightboxIsPdf: false,
            kuesionerModalOpen: false,
            kuesionerTargetName: '',
            kuesionerKenalSentral: false,
            kuesionerSumberSentral: '',
            kuesionerKenalAcer: false,
            kuesionerKenalNvidia: false,
            kuesionerKenalMicrosoft: false,
            waModalOpen: false,
            waEventId: '{{ $semnasEvent?->id }}',
            waCurrentLink: '{{ $semnasEvent?->whatsapp_group_link }}',
            rejectModalOpen: false,
            rejectUserId: '',
            rejectEventId: '',
            rejectTargetName: '',
            rejectReason: '',
            isExporting: false,
            async exportToSheets() {
                this.isExporting = true;
                const filter = '{{ request('event_id') }}';
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
        }"
        x-init="
            $watch('lightboxOpen', v => { if (v) { document.body.classList.add('overflow-y-hidden'); } else { document.body.classList.remove('overflow-y-hidden'); } });
            $watch('kuesionerModalOpen', v => { if (v) { document.body.classList.add('overflow-y-hidden'); } else { document.body.classList.remove('overflow-y-hidden'); } });
            $watch('waModalOpen', v => { if (v) { document.body.classList.add('overflow-y-hidden'); } else { document.body.classList.remove('overflow-y-hidden'); } });
            $watch('rejectModalOpen', v => { if (v) { document.body.classList.add('overflow-y-hidden'); } else { document.body.classList.remove('overflow-y-hidden'); } });
        "
        x-on:open-lightbox.window="lightboxOpen = true; lightboxImg = $event.detail.img; lightboxTitle = $event.detail.title; lightboxIsPdf = $event.detail.isPdf || false"
        class="flex flex-col gap-6"
    >
        <!-- Top Banner / WhatsApp Link Card -->
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shrink-0">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-950">Link Group WhatsApp Semnas</h3>
                    <p class="text-xs text-gray-600 mt-0.5">Tautan yang otomatis tampil pada dashboard peserta yang telah di-ACC (Terdaftar).</p>
                    <div class="mt-1 flex items-center gap-2">
                        @if(!empty($semnasEvent?->whatsapp_group_link))
                            <a href="{{ $semnasEvent->whatsapp_group_link }}" target="_blank" class="text-xs font-mono text-emerald-700 font-semibold underline truncate max-w-md hover:text-emerald-900">
                                {{ $semnasEvent->whatsapp_group_link }}
                            </a>
                        @else
                            <span class="text-xs text-amber-700 font-medium italic">Belum diatur (Klik tombol disamping untuk mengatur)</span>
                        @endif
                    </div>
                </div>
            </div>

            <button
                type="button"
                @click="waModalOpen = true"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-emerald-500 transition-colors shrink-0 cursor-pointer"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Link Group WA
            </button>
        </div>

        <!-- Top Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-3">
            <x-admin.stat-card label="Pending Verifikasi" :value="$pendingCount" tone="amber" />
            <x-admin.stat-card label="Terdaftar (Accepted)" :value="$acceptedCount" tone="emerald" />
            <x-admin.stat-card label="Ditolak (Rejected)" :value="$rejectedCount" tone="rose" />
        </div>

        <!-- Main Table Section -->
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-950">Daftar Pendaftar Seminar Nasional</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola verifikasi kuesioner dan bukti follow Instagram narasumber.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('admin.semnas-participants.index') }}" class="flex flex-wrap items-center gap-2">
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
                                    placeholder="Cari nama/email/instansi..."
                                    class="block w-full sm:w-56 pl-9 rounded-md border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500"
                                >
                            </div>

                            @if($semnasEvents->count() > 1)
                                <div>
                                    <select name="event_id" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Semua Event Semnas</option>
                                        @foreach($semnasEvents as $e)
                                            <option value="{{ $e->id }}" @selected(request('event_id') === $e->id)>{{ $e->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- Filter Status -->
                            <div>
                                <select name="status" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500 font-medium">
                                    <option value="all" @selected($filterStatus === 'all')>Semua Status</option>
                                    <option value="pending" @selected($filterStatus === 'pending')>Pending</option>
                                    <option value="accepted" @selected($filterStatus === 'accepted')>Accepted</option>
                                    <option value="rejected" @selected($filterStatus === 'rejected')>Rejected</option>
                                </select>
                            </div>
                        </form>

                        <!-- Export Buttons -->
                        <div class="flex items-center gap-2">
                            <a 
                                href="{{ route('export.semnas-participants', ['event_id' => request('event_id')]) }}"
                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-bold uppercase text-white shadow-xs hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export CSV
                            </a>

                            @if(in_array(auth()->user()->role, ['superadmin', 'admin_biasa']))
                                <button 
                                    type="button"
                                    @click="exportToSheets()" 
                                    :disabled="isExporting"
                                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3.5 py-2 text-xs font-bold uppercase text-white shadow-xs hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Peserta / Identitas</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Kuesioner Semnas</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Bukti Follow IG</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Status</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($participants as $participant)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <!-- Peserta / Identitas -->
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
                                        Terdaftar: {{ $participant->date_added_formatted }}
                                    </p>
                                </td>

                                <!-- Hasil Kuesioner -->
                                <td class="px-6 py-4">
                                    <button
                                        type="button"
                                        @click="
                                            kuesionerModalOpen = true;
                                            kuesionerTargetName = {{ \Illuminate\Support\Js::from($participant->full_name) }};
                                            kuesionerKenalSentral = {{ $participant->kenal_sentral_komputer ? 'true' : 'false' }};
                                            kuesionerSumberSentral = {{ \Illuminate\Support\Js::from($participant->sumber_kenal_sentral ?? '-') }};
                                            kuesionerKenalAcer = {{ $participant->kenal_acer ? 'true' : 'false' }};
                                            kuesionerKenalNvidia = {{ $participant->kenal_nvidia ? 'true' : 'false' }};
                                            kuesionerKenalMicrosoft = {{ $participant->kenal_microsoft ? 'true' : 'false' }};
                                        "
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors cursor-pointer"
                                    >
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Hasil Kuesioner
                                    </button>
                                </td>

                                <!-- Bukti Follow IG -->
                                <td class="px-6 py-4">
                                    @if($participant->ig_proof_url)
                                        @if($participant->is_pdf)
                                            <div class="flex items-center gap-2">
                                                <a
                                                    href="{{ $participant->ig_proof_url }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer"
                                                >
                                                    <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    Lihat Dokumen PDF
                                                </a>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-3">
                                                <img
                                                    src="{{ $participant->ig_proof_url }}"
                                                    alt="Bukti IG {{ $participant->full_name }}"
                                                    class="h-12 w-12 rounded-lg border border-gray-200 object-cover p-0.5 shadow-xs cursor-pointer hover:opacity-85 transition-opacity"
                                                    @click="$dispatch('open-lightbox', { img: '{{ $participant->ig_proof_url }}', title: {{ \Illuminate\Support\Js::from('Bukti Follow IG - ' . $participant->full_name) }}, isPdf: false })"
                                                >
                                                <button
                                                    type="button"
                                                    @click="$dispatch('open-lightbox', { img: '{{ $participant->ig_proof_url }}', title: {{ \Illuminate\Support\Js::from('Bukti Follow IG - ' . $participant->full_name) }}, isPdf: false })"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded border border-indigo-200 cursor-pointer transition-colors"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat Foto
                                                </button>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum upload bukti</span>
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
                                            <form method="POST" action="{{ route('admin.semnas-participants.verify') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                                                <input type="hidden" name="event_id" value="{{ $participant->event_id }}">
                                                <button
                                                    type="submit"
                                                    name="action"
                                                    value="accept"
                                                    class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-500 shadow-xs transition-colors cursor-pointer"
                                                    title="ACC Pendaftaran"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Terima
                                                </button>
                                            </form>
                                        @endif

                                        @if($participant->payment_verification !== 'rejected')
                                            <button
                                                type="button"
                                                @click="
                                                    rejectModalOpen = true;
                                                    rejectUserId = '{{ $participant->user_id }}';
                                                    rejectEventId = '{{ $participant->event_id }}';
                                                    rejectTargetName = {{ \Illuminate\Support\Js::from($participant->full_name) }};
                                                    rejectReason = '';
                                                "
                                                class="inline-flex items-center gap-1 rounded-md bg-rose-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-rose-500 shadow-xs transition-colors cursor-pointer"
                                                title="Tolak Pendaftaran"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Tolak
                                            </button>
                                        @endif

                                        <!-- Button Hapus Peserta -->
                                        <form
                                            method="POST"
                                            action="{{ route('admin.semnas-participants.destroy') }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pendaftaran Semnas {{ addslashes($participant->full_name) }}?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                                            <input type="hidden" name="event_id" value="{{ $participant->event_id }}">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white p-1.5 text-xs font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 hover:border-red-300 shadow-xs transition-colors cursor-pointer"
                                                title="Hapus Data"
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
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="font-medium">Belum ada data pendaftar Seminar Nasional yang sesuai filter.</p>
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

        <!-- Modal Popup Hasil Kuesioner Semnas -->
        <div
            x-show="kuesionerModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="kuesionerModalOpen = false"
        >
            <div
                x-show="kuesionerModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity"
                @click="kuesionerModalOpen = false"
            ></div>

            <div
                x-show="kuesionerModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transform transition-all z-10 my-auto"
            >
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 bg-indigo-50/70">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-950">Hasil Kuesioner Semnas</h3>
                            <p class="text-xs text-indigo-700 font-medium" x-text="kuesionerTargetName"></p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="kuesionerModalOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-colors cursor-pointer"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <!-- Q1 -->
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-1">
                        <p class="text-xs font-semibold text-gray-700">1. Apakah sebelumnya sudah mengenal Sentral Komputer?</p>
                        <div class="pt-0.5">
                            <template x-if="kuesionerKenalSentral">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✓ Ya, Sudah Mengenal
                                </span>
                            </template>
                            <template x-if="!kuesionerKenalSentral">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                    ✗ Belum Mengenal
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-1">
                        <p class="text-xs font-semibold text-gray-700">2. Darimanakah Anda mengenal Sentral Komputer?</p>
                        <p class="text-xs font-bold text-gray-900 bg-white p-2 rounded border border-gray-200 italic" x-text="kuesionerSumberSentral || '-'"></p>
                    </div>

                    <!-- Q3 -->
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-1">
                        <p class="text-xs font-semibold text-gray-700">3. Apakah sebelumnya sudah mengenal Acer?</p>
                        <div class="pt-0.5">
                            <template x-if="kuesionerKenalAcer">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✓ Ya, Sudah Mengenal
                                </span>
                            </template>
                            <template x-if="!kuesionerKenalAcer">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                    ✗ Belum Mengenal
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-1">
                        <p class="text-xs font-semibold text-gray-700">4. Apakah sebelumnya sudah mengenal NVIDIA?</p>
                        <div class="pt-0.5">
                            <template x-if="kuesionerKenalNvidia">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✓ Ya, Sudah Mengenal
                                </span>
                            </template>
                            <template x-if="!kuesionerKenalNvidia">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                    ✗ Belum Mengenal
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Q5 -->
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-1">
                        <p class="text-xs font-semibold text-gray-700">5. Apakah sebelumnya sudah mengenal Microsoft?</p>
                        <div class="pt-0.5">
                            <template x-if="kuesionerKenalMicrosoft">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✓ Ya, Sudah Mengenal
                                </span>
                            </template>
                            <template x-if="!kuesionerKenalMicrosoft">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                    ✗ Belum Mengenal
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button
                        type="button"
                        @click="kuesionerModalOpen = false"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 shadow-xs cursor-pointer transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Edit Link Group WhatsApp -->
        <div
            x-show="waModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="waModalOpen = false"
        >
            <div
                x-show="waModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity"
                @click="waModalOpen = false"
            ></div>

            <div
                x-show="waModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-md bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transform transition-all z-10 my-auto"
            >
                <form method="POST" action="{{ route('admin.semnas-participants.whatsapp-link') }}">
                    @csrf
                    <input type="hidden" name="event_id" :value="waEventId">

                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 bg-emerald-50">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-950">Edit Link Group WhatsApp</h3>
                                <p class="text-xs text-emerald-700 font-medium">Seminar Nasional</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="waModalOpen = false"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-colors cursor-pointer"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                URL Link Group WhatsApp <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="url"
                                name="whatsapp_group_link"
                                x-model="waCurrentLink"
                                required
                                placeholder="https://chat.whatsapp.com/..."
                                class="w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500"
                            >
                            <p class="mt-1 text-[11px] text-gray-500">Tautan ini akan langsung muncul di dashboard peserta yang statusnya telah di-ACC.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2.5">
                        <button
                            type="button"
                            @click="waModalOpen = false"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 shadow-xs cursor-pointer transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 shadow-xs cursor-pointer transition-colors"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lightbox Modal Preview Bukti Follow IG -->
        <div
            x-show="lightboxOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="lightboxOpen = false"
        >
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
                            Bukti Follow IG Narasumber
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
                        Buka File di Tab Baru
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

        <!-- Modal Tolak Pendaftaran Semnas -->
        <div
            x-show="rejectModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            @keydown.escape.window="rejectModalOpen = false"
        >
            <div
                x-show="rejectModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity"
                @click="rejectModalOpen = false"
            ></div>

            <div
                x-show="rejectModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-md bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transform transition-all z-10 my-auto"
            >
                <form method="POST" action="{{ route('admin.semnas-participants.verify') }}">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="user_id" :value="rejectUserId">
                    <input type="hidden" name="event_id" :value="rejectEventId">

                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 bg-rose-50">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-950">Tolak Pendaftaran Semnas</h3>
                                <p class="text-xs text-rose-700 truncate max-w-xs" x-text="rejectTargetName"></p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="rejectModalOpen = false"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-colors cursor-pointer"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Catatan / Alasan Penolakan (Opsional)
                            </label>
                            <textarea
                                name="verification_error"
                                x-model="rejectReason"
                                rows="3"
                                maxlength="191"
                                placeholder="Contoh: Bukti follow IG buram / akun IG tidak ditemukan..."
                                class="w-full rounded-md border-gray-300 text-xs shadow-xs focus:border-rose-500 focus:ring-rose-500"
                            ></textarea>
                            <p class="mt-1 text-[11px] text-gray-500">Catatan ini akan tersimpan dan dapat dibaca oleh peserta / admin.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2.5">
                        <button
                            type="button"
                            @click="rejectModalOpen = false"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 shadow-xs cursor-pointer transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500 shadow-xs cursor-pointer transition-colors"
                        >
                            Konfirmasi Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin.layout>
