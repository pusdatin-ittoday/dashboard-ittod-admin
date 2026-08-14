@forelse ($teams as $team)
    @php
        $leader = $team->members->first(fn($m) => $m->role === 'leader')?->user ?? $team->members->first()?->user;
    @endphp
    <tr class="align-top hover:bg-gray-50 team-row">
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
                {{ $leader?->full_name ?? 'Unknown Leader' }}
            </p>
            <p class="text-[11px] text-gray-500 font-mono mt-0.5">
                {{ $leader?->email ?? '-' }}
            </p>
            @if($leader?->nama_sekolah)
                <p class="text-[11px] text-gray-600 mt-0.5">
                    {{ $leader->nama_sekolah }}
                </p>
            @endif
        </td>

        <!-- Jumlah Anggota -->
        <td class="px-3 py-3.5 whitespace-nowrap">
            <span class="inline-flex items-center text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                {{ $team->members->count() }} Orang
            </span>
        </td>

        <!-- Status Berkas -->
        <td class="px-3 py-3.5 whitespace-nowrap">
            @if(in_array($team->is_document_verified, ['verified', 'approved', '1', 1], true))
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-bold text-blue-800">
                    Berkas Lolos
                </span>
            @elseif(in_array($team->is_document_verified, ['rejected', '0'], true))
                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-bold text-red-800">
                    Berkas Ditolak
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                    Berkas Pending
                </span>
            @endif
        </td>

        <!-- Status Pembayaran -->
        <td class="px-3 py-3.5 whitespace-nowrap">
            @if(in_array($team->is_verified, ['approved', 'verified', '1', 1], true))
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                    Bayar Lunas
                </span>
            @elseif(in_array($team->is_verified, ['rejected', '0'], true))
                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-bold text-red-800 border border-red-200">
                    Bayar Ditolak
                </span>
            @elseif(!empty($team->payment_proof_id))
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 border border-amber-200">
                    Belum Diverifikasi
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-semibold text-gray-600 border border-gray-200">
                    Belum Bayar
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
    @if(request()->get('page', 1) == 1)
        <tr>
            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                Tidak ada data tim yang sesuai dengan kriteria pencarian/filter.
            </td>
        </tr>
    @endif
@endforelse
