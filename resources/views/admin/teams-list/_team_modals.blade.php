@foreach ($teams as $team)
    <x-modal name="view-team-{{ $team->id }}" maxWidth="3xl" focusable>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between border-b border-gray-200 pb-4 gap-3">
                <div>
                    <span class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-xs font-extrabold uppercase text-indigo-700">
                        {{ $team->event?->title ?? 'Kompetisi' }}
                    </span>
                    <h3 class="mt-2 text-xl font-bold text-gray-950">{{ $team->team_name }}</h3>
                    @if($team->team_code)
                        <p class="text-xs font-mono text-indigo-600 font-bold mt-0.5">Kode Tim: {{ $team->team_code }}</p>
                    @endif
                </div>
                <div class="text-left sm:text-right flex flex-col items-start sm:items-end gap-1.5">
                    <!-- Status Badges -->
                    <div class="flex items-center gap-1.5">
                        @if(in_array($team->is_document_verified, ['verified', 'approved', '1', 1], true))
                            <span class="rounded bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-800">Berkas Lolos</span>
                        @else
                            <span class="rounded bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800">Berkas Pending</span>
                        @endif

                        @if(in_array($team->is_verified, ['approved', 'verified', '1', 1], true))
                            <span class="rounded bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200">Bayar Lunas</span>
                        @elseif(in_array($team->is_verified, ['rejected', '0'], true))
                            <span class="rounded bg-red-100 px-2.5 py-0.5 text-[10px] font-bold text-red-800 border border-red-200">Bayar Ditolak</span>
                        @elseif(!empty($team->payment_proof_id))
                            <span class="rounded bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200">Belum Diverifikasi</span>
                        @else
                            <span class="rounded bg-gray-100 px-2.5 py-0.5 text-[10px] font-semibold text-gray-600 border border-gray-200">Belum Bayar</span>
                        @endif
                    </div>

                    <p class="text-[11px] font-mono text-gray-500">
                        Terdaftar: {{ $team->created_at?->translatedFormat('d F Y, H:i') ?? '-' }} WIB
                    </p>

                    <!-- Preview Bukti Pembayaran Button -->
                    <div class="mt-1">
                        @if($team->paymentProof?->url)
                            <button
                                type="button"
                                @click="$dispatch('open-lightbox', { img: '{{ $team->paymentProof->url }}', title: 'Bukti Pembayaran - {{ addslashes($team->team_name) }}' })"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded border border-emerald-200 shadow-xs cursor-pointer transition-colors"
                            >
                                Preview Bukti Pembayaran
                            </button>
                        @else
                            <button
                                type="button"
                                disabled
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-400 bg-gray-100 px-3 py-1.5 rounded border border-gray-200 cursor-not-allowed opacity-60"
                                title="Tim belum mengunggah bukti pembayaran"
                            >
                                Bukti Pembayaran Belum Ada
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Members Roster List -->
            <div class="mt-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Daftar Anggota Tim ({{ $team->members->count() }} Orang)</h4>
                <div class="space-y-3">
                    @foreach($team->members as $mem)
                        @php $u = $mem->user; @endphp
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 grid grid-cols-1 md:grid-cols-12 gap-3 items-center text-xs">
                            <!-- Left Column: Member Info (6 cols) -->
                            <div class="md:col-span-6 flex items-start gap-3">
                                <div class="h-8 w-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shrink-0">
                                    {{ strtoupper(substr($u?->full_name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-extrabold text-gray-900 text-sm">{{ $u?->full_name ?? 'Nama Unknown' }}</span>
                                        @if($mem->role === 'leader')
                                            <span class="bg-purple-600 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded shadow-xs tracking-wide">
                                                Ketua Tim
                                            </span>
                                        @else
                                            <span class="bg-gray-200 text-gray-700 text-[10px] font-semibold px-2 py-0.5 rounded">
                                                Anggota
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-gray-500 font-mono mt-1">{{ $u?->email ?? '-' }}</p>
                                    @if($u?->phone_number)
                                        <p class="text-gray-500 font-mono">WA/Telp: {{ $u->phone_number }}</p>
                                    @endif
                                    @if($u?->nama_sekolah)
                                        <p class="text-gray-600 mt-0.5">Instansi: <strong>{{ $u->nama_sekolah }}</strong></p>
                                    @endif
                                    @if(!empty($mem->verification_error))
                                        <p class="text-[11px] text-rose-700 font-medium mt-1 bg-rose-50 border border-rose-200 rounded px-2 py-1">
                                            <strong>Catatan Revisi:</strong> {{ $mem->verification_error }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Middle Column: Centered Member Document Status Badge (3 cols) -->
                            <div class="md:col-span-3 flex items-center justify-center text-center">
                                <!-- Status Berkas Member -->
                                @if(!empty($mem->verification_error))
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2.5 py-1 rounded border border-rose-200 shadow-2xs whitespace-nowrap">
                                        Berkas Ditolak
                                    </span>
                                @elseif($mem->is_verified)
                                    <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2.5 py-1 rounded border border-blue-200 shadow-2xs whitespace-nowrap">
                                        Berkas Valid
                                    </span>
                                @else
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded border border-amber-200 shadow-2xs whitespace-nowrap">
                                        Berkas Pending
                                    </span>
                                @endif
                            </div>

                            <!-- Right Column: KTM Preview Button (3 cols) -->
                            <div class="md:col-span-3 flex justify-start md:justify-end">
                                @if($u?->ktm_key)
                                    <button
                                        type="button"
                                        @click="$dispatch('open-lightbox', { img: '{{ env('R2_PUBLIC', 'https://cdn.ittoday.web.id') . '/' . $u->ktm_key }}', title: 'KTM / Kartu Identitas - {{ addslashes($u?->full_name ?? '') }}' })"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded border border-indigo-200 shadow-sm cursor-pointer transition-colors whitespace-nowrap"
                                    >
                                        <span>Preview KTM / Kartu</span>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 italic">KTM Belum Ada</span>
                                @endif
                            </div>
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
