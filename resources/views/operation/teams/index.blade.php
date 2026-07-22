@php
    $isOnlyIndividual = auth()->user()->role === 'panitia_lomba' && auth()->user()->events->every(fn($e) => $e->participation_type === 'individual');
    $title = $isOnlyIndividual ? 'Verifikasi Berkas Peserta' : 'Verifikasi Berkas Tim';
@endphp
<x-admin.layout
    :title="$title"
    subtitle="Periksa kelengkapan data, kartu identitas, dan dokumen persyaratan lomba."
>
    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div x-data="{
        search: '',
        isExporting: false,
        eventTypes: {
            @foreach($events as $e)
                '{{ $e->id }}': '{{ $e->type }}',
            @endforeach
        },
        exportCsv() {
            const filter = '{{ $filterEventId }}' || 'all_teams';
            if (filter === 'all_teams') {
                window.location.href = '{{ route('export.teams.global') }}';
            } else if (filter === 'all_participants') {
                window.location.href = '{{ route('export.participants.global') }}';
            } else {
                const type = this.eventTypes[filter];
                if (type === 'competition') {
                    window.location.href = '{{ route('export.teams') }}?event_id=' + filter;
                } else {
                    window.location.href = '{{ route('export.participants') }}?event_id=' + filter;
                }
            }
        },
        async exportToSheets() {
            this.isExporting = true;
            const filter = '{{ $filterEventId }}' || 'all_teams';
            
            let exportType = 'event';
            if (filter === 'all_teams') {
                exportType = 'teams_global';
            } else if (filter === 'all_participants') {
                exportType = 'participants_global';
            }
            
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
                        event_id: filter
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
        <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-950">{{ $title }}</h2>
                    <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">
                        Participant Files
                    </span>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Periksa kelengkapan data dan dokumen persyaratan lomba</p>
            </div>

            <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
                <form method="GET" action="{{ route('operation.teams.index') }}" class="flex items-center gap-2">
                    <label class="sr-only">Filter Event</label>
                    <select name="event_id" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @if(in_array(auth()->user()->role, ['superadmin', 'admin_biasa']))
                            <option value="all_teams" @selected($filterEventId === 'all_teams' || !$filterEventId)>Semua Tim (Global)</option>
                            <option value="all_participants" @selected($filterEventId === 'all_participants')>Semua Peserta Seminar (Global)</option>
                        @else
                            <option value="" @selected(!$filterEventId)>Pilih Event</option>
                        @endif
                        
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @selected($filterEventId === $event->id)>
                                {{ $event->type === 'competition' ? 'Lomba' : 'Seminar' }}: {{ $event->title }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <button 
                    @click="exportCsv()" 
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-bold uppercase text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Export CSV
                </button>

                @if(in_array(auth()->user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                <button 
                    @click="exportToSheets()" 
                    :disabled="isExporting"
                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-bold uppercase text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
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

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    @php
                        $isOnlyIndividual = auth()->user()->role === 'panitia_lomba' && auth()->user()->events->every(fn($e) => $e->participation_type === 'individual');
                    @endphp
                    <h2 class="text-xl font-semibold text-gray-950">Direktori Berkas {{ $isOnlyIndividual ? 'Peserta' : 'Tim' }}</h2>
                    <span class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-bold uppercase text-indigo-700">
                        Document Records
                    </span>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Validasi data anggota dan kelengkapan dokumen persyaratan lomba</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $teams->count() }} records detected</p>
        </div>

        <div class="border-b border-gray-200 px-6 py-4">
            <label class="relative block">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                    </svg>
                </span>
                <input
                    type="search"
                    x-model="search"
                    placeholder="Search transaksi by tim, kode, lomba, anggota, atau status..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Pendaftaran</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Cabang Lomba</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Ketua & Anggota / Peserta</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($teams as $team)
                        @php
                            $isTeamVerified = $team->is_document_verified === 'approved';
                            $isIndividual = $team->event?->participation_type === 'individual';
                            $primaryMember = $team->members->firstWhere('role', 'leader') ?? $team->members->first();
                            $displayName = $isIndividual
                                ? ($primaryMember?->user?->full_name ?? 'Peserta')
                                : $team->team_name;
                            $individualCode = Str::upper(Str::substr(str_replace('-', '', $team->id), 0, 8));
                            $hasTeamErr = !empty($team->verification_error);
                            $hasMemErr = $team->members->contains(fn($m) => !empty($m->verification_error));

                            if ($isTeamVerified) {
                                $statusLabel = 'Terverifikasi';
                                $statusClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                            } elseif ($hasTeamErr || $hasMemErr) {
                                $statusLabel = 'Ditolak (Revisi)';
                                $statusClass = 'border-rose-200 bg-rose-50 text-rose-700';
                            } else {
                                $statusLabel = 'Sedang Diperiksa';
                                $statusClass = 'border-amber-200 bg-amber-50 text-amber-700';
                            }
                        @endphp
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($displayName . ' ' . ($isIndividual ? '' : $team->team_code) . ' ' . ($team->event->title ?? $team->competition_id) . ' ' . $team->members->map(fn ($member) => ($member->user->full_name ?? 'Peserta') . ' ' . $member->role)->join(' ') . ' ' . $statusLabel) }}"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $displayName }}</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $isIndividual ? 'Individu · ID: ' . $individualCode : 'Kode: ' . $team->team_code }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-[11px] font-bold uppercase text-indigo-700">
                                    {{ $team->event->title ?? $team->competition_id }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @foreach($team->members as $member)
                                        <div class="flex items-center gap-2 text-sm text-gray-700">
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $isIndividual ? 'bg-indigo-100 text-indigo-800' : ($member->role === 'leader' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500') }}">
                                                {{ $isIndividual ? 'Peserta' : ($member->role === 'leader' ? 'Ketua' : 'Anggota') }}
                                            </span>
                                            <span>{{ $member->user->full_name ?? 'Peserta' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded border px-2 py-1 text-[11px] font-bold uppercase {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('operation.teams.show', $team->id) }}" class="inline-flex items-center justify-center rounded border border-green-200 bg-green-50 px-2 py-1 text-[11px] font-bold uppercase text-green-700 transition hover:border-green-300 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        Periksa Berkas
                                    </a>
                                    @if(auth()->user()->role === 'superadmin')
                                         <button
                                             type="button"
                                             x-data
                                             x-on:click="$dispatch('confirm-danger', {
                                                 title: 'Hapus Tim Permanen',
                                                 message: 'Apakah Anda yakin ingin menghapus tim {{ addslashes($displayName) }} beserta berkas dan seluruh anggotanya? Data yang dihapus tidak dapat dikembalikan.',
                                                 action: '{{ route('operation.teams.destroy', $team->id) }}',
                                                 method: 'DELETE',
                                                 confirmText: 'Ya, Hapus Tim'
                                             })"
                                             class="inline-flex items-center justify-center rounded border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-bold uppercase text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                                         >
                                             Hapus
                                         </button>
                                     @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada tim terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
</x-admin.layout>
