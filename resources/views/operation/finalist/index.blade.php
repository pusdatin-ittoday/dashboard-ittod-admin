<x-admin.layout
    title="Finalist & Juara"
    subtitle="Kelola status finalis dan peringkat juara untuk setiap kompetisi."
>
    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('operation.finalist.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[240px]">
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Cari Nama Tim</label>
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
                    placeholder="Cari nama tim..."
                    class="block w-full pl-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>
        </div>
        <div class="min-w-[200px]">
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Cabang Kompetisi</label>
            <select name="event_id" onchange="this.form.submit()"
                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Kompetisi</option>
                @foreach($events as $event)
                    <option value="{{ $event->id }}" {{ $selectedEventId === $event->id ? 'selected' : '' }}>
                        {{ $event->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[180px]">
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Status</label>
            <select name="status" onchange="this.form.submit()"
                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Tim</option>
                <option value="winner" {{ $selectedStatus === 'winner' ? 'selected' : '' }}>🏆 Juara</option>
                <option value="finalist" {{ $selectedStatus === 'finalist' ? 'selected' : '' }}>⭐ Finalis (bukan juara)</option>
                <option value="none" {{ $selectedStatus === 'none' ? 'selected' : '' }}>Belum Ditandai</option>
            </select>
        </div>
        <button type="submit"
            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
            Cari
        </button>
        @if(request('search') || $selectedEventId || $selectedStatus)
            <a href="{{ route('operation.finalist.index') }}"
               class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                Reset
            </a>
        @endif
    </form>

    {{-- Jadwal Pengumuman Landing Page Card (Serentak Semua Lomba) --}}
    @php
        $now = now();
        $finalistTl = $globalFinalistTimelineId 
            ? (\App\Models\CompetitionTimeline::find($globalFinalistTimelineId) ?? \App\Models\EventTimeline::find($globalFinalistTimelineId))
            : null;
        $winnerTl = $globalWinnerTimelineId 
            ? (\App\Models\CompetitionTimeline::find($globalWinnerTimelineId) ?? \App\Models\EventTimeline::find($globalWinnerTimelineId))
            : null;

        $finalistDate = $finalistTl?->start_date ?? $finalistTl?->date;
        $winnerDate   = $winnerTl?->start_date ?? $winnerTl?->date;

        $isFinalistRevealed = $finalistDate ? $now->greaterThanOrEqualTo($finalistDate) : false;
        $isWinnerRevealed   = $winnerDate ? $now->greaterThanOrEqualTo($winnerDate) : false;
    @endphp

    <div class="mb-6 rounded-xl border border-indigo-200 bg-gradient-to-r from-indigo-50/70 via-white to-amber-50/70 p-5 shadow-sm"
         x-data="{ openScheduleModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl">📢</span>
                    <h3 class="text-base font-bold text-gray-900">Jadwal Pengumuman di Landing Page</h3>
                    <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-800">Serentak Semua Kompetisi</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Atur kapan daftar finalis dan juara akan ditampilkan ke landing page. Pengaturan ini berlaku serentak untuk seluruh cabang kompetisi IT Today 2026.
                </p>
            </div>
            <button type="button" @click="openScheduleModal = true"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Atur Timeline Pengumuman
            </button>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Finalis Schedule Box --}}
            <div class="rounded-lg border border-indigo-100 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-indigo-700">⭐ Pengumuman Finalis</span>
                    @if(!$finalistTl)
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-600">
                            Belum Diatur (Default)
                        </span>
                    @elseif($isFinalistRevealed)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Sudah Tayang di Landing
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span> Belum Tayang
                        </span>
                    @endif
                </div>
                <div class="mt-2 text-sm font-semibold text-gray-800">
                    {{ $finalistTl?->title ?? 'Mengikuti kata kunci timeline' }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                    @if($finalistTl)
                        Waktu tayang: {{ $finalistDate->translatedFormat('d F Y, H:i') }} WIB
                    @else
                        Akan otomatis tayang jika ada timeline dengan judul mengandung "finalis" yang waktunya sudah tiba.
                    @endif
                </div>
            </div>

            {{-- Juara Schedule Box --}}
            <div class="rounded-lg border border-amber-100 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-amber-700">🏆 Pengumuman Juara (Pemenang)</span>
                    @if(!$winnerTl)
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-600">
                            Belum Diatur (Default)
                        </span>
                    @elseif($isWinnerRevealed)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Sudah Tayang di Landing
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span> Belum Tayang
                        </span>
                    @endif
                </div>
                <div class="mt-2 text-sm font-semibold text-gray-800">
                    {{ $winnerTl?->title ?? 'Mengikuti kata kunci timeline' }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                    @if($winnerTl)
                        Waktu tayang: {{ $winnerDate->translatedFormat('d F Y, H:i') }} WIB
                    @else
                        Akan otomatis tayang jika ada timeline dengan judul mengandung "juara" yang waktunya sudah tiba.
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal Atur Jadwal Pengumuman --}}
        <div x-show="openScheduleModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div x-show="openScheduleModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="openScheduleModal = false"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div x-show="openScheduleModal" x-transition
                    class="inline-block transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form action="{{ route('operation.finalist.schedule') }}" method="POST">
                        @csrf
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                            <h3 class="text-base font-bold text-gray-900">Atur Jadwal Pengumuman Landing Page</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Berlaku serentak untuk seluruh cabang kompetisi IT Today 2026.</p>
                        </div>

                        <div class="p-6 space-y-5">
                            {{-- Pilih Timeline Finalis --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                    ⭐ Timeline Pengumuman Finalis (Semua Lomba)
                                </label>
                                <select name="finalist_timeline_id" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Belum Diatur (Gunakan Default Kata Kunci) --</option>
                                    @if($globalTimelines->isNotEmpty())
                                        <optgroup label="Timeline Global Kompetisi">
                                            @foreach($globalTimelines as $gtl)
                                                <option value="{{ $gtl->id }}" {{ $globalFinalistTimelineId === $gtl->id ? 'selected' : '' }}>
                                                    {{ $gtl->title }} ({{ $gtl->start_date ? $gtl->start_date->format('d M Y, H:i') : '-' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @foreach($events as $ev)
                                        @if($ev->timelines->isNotEmpty())
                                            <optgroup label="Timeline {{ $ev->title }}">
                                                @foreach($ev->timelines as $tl)
                                                    <option value="{{ $tl->id }}" {{ $globalFinalistTimelineId === $tl->id ? 'selected' : '' }}>
                                                        {{ $tl->title }} ({{ $tl->date ? $tl->date->format('d M Y, H:i') : '-' }})
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-gray-500">Daftar finalis seluruh lomba akan mulai ditampilkan di landing page begitu tanggal timeline ini tiba.</p>
                            </div>

                            {{-- Pilih Timeline Juara --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                    🏆 Timeline Pengumuman Juara (Semua Lomba)
                                </label>
                                <select name="winner_timeline_id" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Belum Diatur (Gunakan Default Kata Kunci) --</option>
                                    @if($globalTimelines->isNotEmpty())
                                        <optgroup label="Timeline Global Kompetisi">
                                            @foreach($globalTimelines as $gtl)
                                                <option value="{{ $gtl->id }}" {{ $globalWinnerTimelineId === $gtl->id ? 'selected' : '' }}>
                                                    {{ $gtl->title }} ({{ $gtl->start_date ? $gtl->start_date->format('d M Y, H:i') : '-' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @foreach($events as $ev)
                                        @if($ev->timelines->isNotEmpty())
                                            <optgroup label="Timeline {{ $ev->title }}">
                                                @foreach($ev->timelines as $tl)
                                                    <option value="{{ $tl->id }}" {{ $globalWinnerTimelineId === $tl->id ? 'selected' : '' }}>
                                                        {{ $tl->title }} ({{ $tl->date ? $tl->date->format('d M Y, H:i') : '-' }})
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-gray-500">Podium juara seluruh lomba akan mulai ditampilkan di landing page begitu tanggal timeline ini tiba.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-3">
                            <button type="button" @click="openScheduleModal = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm hover:bg-indigo-700">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    @php
        $totalFinalist = $teams->getCollection()->where('is_finalist', true)->whereNull('rank')->count();
        $totalWinner   = $teams->getCollection()->whereNotNull('rank')->where('is_finalist', true)->count();
        $totalNone     = $teams->getCollection()->where('is_finalist', false)->count();
    @endphp
    <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase text-amber-600">Juara</p>
            <p class="text-2xl font-bold text-amber-800">{{ $teams->getCollection()->where('is_finalist', true)->whereNotNull('rank')->count() }}</p>
        </div>
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase text-indigo-600">Finalis</p>
            <p class="text-2xl font-bold text-indigo-800">{{ $teams->getCollection()->where('is_finalist', true)->whereNull('rank')->count() }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase text-gray-500">Belum Ditandai</p>
            <p class="text-2xl font-bold text-gray-700">{{ $teams->getCollection()->where('is_finalist', false)->count() }}</p>
        </div>
    </div>

    {{-- Table --}}
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Tim / Peserta</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Cabang Kompetisi</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Ketua / Anggota</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Status Finalis</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($teams as $team)
                        @php
                            $isIndividual = $team->event?->participation_type === 'individual';
                            $primaryMember = $team->members->firstWhere('role', 'leader') ?? $team->members->first();
                            $displayName = $isIndividual
                                ? ($primaryMember?->user?->full_name ?? 'Peserta')
                                : $team->team_name;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $displayName }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    {{ $isIndividual ? 'Individu' : 'Kode: ' . $team->team_code }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-[11px] font-bold uppercase text-indigo-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
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
                                            <span>{{ $member->user->full_name ?? '-' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($team->rank)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                        🏆 Juara {{ $team->rank }}
                                    </span>
                                @elseif($team->is_finalist)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">
                                        ⭐ Finalis
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-400">
                                        Belum ditandai
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div x-data="{ open: false, status: '{{ $team->rank ? 'juara'.$team->rank : ($team->is_finalist ? 'finalis' : 'bukan') }}' }">
                                    <button @click="open = true" type="button"
                                        class="inline-flex items-center justify-center whitespace-nowrap rounded border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold uppercase text-amber-700 transition hover:border-amber-300 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                        Set Finalis / Juara
                                    </button>

                                    {{-- Modal --}}
                                    <div x-show="open" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true">
                                        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                                            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="open = false"></div>
                                            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                                            <div x-show="open" x-transition
                                                class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                                                <div class="bg-white px-6 pb-4 pt-5">
                                                    <div class="flex items-start gap-4">
                                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-100">
                                                            <span class="text-xl">🏆</span>
                                                        </div>
                                                        <div class="flex-1">
                                                            <h3 class="text-base font-semibold text-gray-900">Set Finalis: {{ $displayName }}</h3>
                                                            <p class="mt-1 text-sm text-gray-500">Tandai tim ini sebagai finalis. Jika juara, isi peringkatnya.</p>
                                                                <form action="{{ route('operation.teams.finalist', $team->id) }}" method="POST" class="mt-4">
                                                                    @csrf
                                                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                                        {{-- Cabut Status (Bukan Finalis) --}}
                                                                        <label class="cursor-pointer rounded-lg border px-3 py-3 text-center transition-colors hover:bg-red-50" 
                                                                            x-bind:class="status === 'bukan' ? 'border-red-400 bg-red-50 text-red-800' : 'border-gray-200 text-gray-500'">
                                                                            <input type="radio" value="bukan" x-model="status" class="sr-only">
                                                                            <span class="block text-sm font-semibold">❌ Cabut Status (Hapus)</span>
                                                                        </label>

                                                                        {{-- Finalis --}}
                                                                        <label class="cursor-pointer rounded-lg border px-3 py-3 text-center transition-colors hover:bg-blue-50" 
                                                                            x-bind:class="status === 'finalis' ? 'border-blue-400 bg-blue-50 text-blue-800' : 'border-gray-200 text-gray-500'">
                                                                            <input type="radio" value="finalis" x-model="status" class="sr-only">
                                                                            <span class="block text-sm font-semibold">⭐ Finalis (Saja)</span>
                                                                        </label>
                                                                    </div>

                                                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mt-5 mb-2">Tetapkan Juara</p>
                                                                    <div class="grid grid-cols-3 gap-2">
                                                                        {{-- Juara 1 --}}
                                                                        <label class="cursor-pointer rounded-lg border px-2 py-3 text-center transition-colors hover:bg-amber-50" 
                                                                            x-bind:class="status === 'juara1' ? 'border-amber-400 bg-amber-50 text-amber-800' : 'border-gray-200 text-gray-500'">
                                                                            <input type="radio" value="juara1" x-model="status" class="sr-only">
                                                                            <span class="block text-sm font-bold">🥇 Juara 1</span>
                                                                        </label>

                                                                        {{-- Juara 2 --}}
                                                                        <label class="cursor-pointer rounded-lg border px-2 py-3 text-center transition-colors hover:bg-slate-100" 
                                                                            x-bind:class="status === 'juara2' ? 'border-slate-400 bg-slate-100 text-slate-800' : 'border-gray-200 text-gray-500'">
                                                                            <input type="radio" value="juara2" x-model="status" class="sr-only">
                                                                            <span class="block text-sm font-bold">🥈 Juara 2</span>
                                                                        </label>

                                                                        {{-- Juara 3 --}}
                                                                        <label class="cursor-pointer rounded-lg border px-2 py-3 text-center transition-colors hover:bg-orange-50" 
                                                                            x-bind:class="status === 'juara3' ? 'border-orange-400 bg-orange-50 text-orange-800' : 'border-gray-200 text-gray-500'">
                                                                            <input type="radio" value="juara3" x-model="status" class="sr-only">
                                                                            <span class="block text-sm font-bold">🥉 Juara 3</span>
                                                                        </label>
                                                                    </div>

                                                                    {{-- Hidden Inputs --}}
                                                                    <input type="hidden" name="is_finalist" x-bind:value="status === 'bukan' ? 0 : 1">
                                                                    <input type="hidden" name="rank" x-bind:value="status.startsWith('juara') ? status.replace('juara', '') : ''">
                                                                {{-- Actions --}}
                                                                <div class="flex flex-row-reverse gap-3 pt-2">
                                                                    <button type="submit"
                                                                        class="inline-flex justify-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">
                                                                        Simpan
                                                                    </button>
                                                                    <button type="button" @click="open = false"
                                                                        class="inline-flex justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                                        Batal
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                Tidak ada tim kompetisi ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($teams->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $teams->links() }}
            </div>
        @endif
    </section>
</x-admin.layout>
