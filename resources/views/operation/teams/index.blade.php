<x-admin.layout
    title="Verifikasi Transaksi"
    subtitle="Periksa bukti transfer, kelengkapan data, dan status verifikasi tim."
>
    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <section x-data="{ search: '' }" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-950">Direktori Transaksi Tim</h2>
                    <span class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-bold uppercase text-indigo-700">
                        Verification Records
                    </span>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Validasi pembayaran, data anggota, dan status transaksi</p>
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
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Nama Tim / Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Cabang Lomba</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Ketua & Anggota</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($teams as $team)
                        @php
                            $isTeamVerified = (bool) $team->is_verified;
                            $hasTeamErr = !empty($team->verification_error);
                            $hasMemErr = $team->members->contains(fn($m) => !empty($m->verification_error));

                            if ($isTeamVerified) {
                                $statusLabel = 'Terverifikasi';
                                $statusClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                            } elseif ($hasTeamErr || $hasMemErr) {
                                $statusLabel = 'Butuh Revisi';
                                $statusClass = 'border-rose-200 bg-rose-50 text-rose-700';
                            } else {
                                $statusLabel = 'Sedang Diperiksa';
                                $statusClass = 'border-amber-200 bg-amber-50 text-amber-700';
                            }
                        @endphp
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($team->team_name . ' ' . $team->team_code . ' ' . ($team->event->title ?? $team->competition_id) . ' ' . $team->members->map(fn ($member) => ($member->user->full_name ?? 'Peserta') . ' ' . $member->role)->join(' ') . ' ' . $statusLabel) }}"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $team->team_name }}</p>
                                <p class="mt-1 text-xs text-gray-500">Kode: {{ $team->team_code }}</p>
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
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $member->role === 'leader' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $member->role === 'leader' ? 'Ketua' : 'Anggota' }}
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
                                <a href="{{ route('operation.teams.show', $team->id) }}" class="inline-flex items-center justify-center rounded-md bg-gray-950 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                    Periksa Berkas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada transaksi tim.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin.layout>
