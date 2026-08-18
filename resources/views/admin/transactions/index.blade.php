<x-admin.layout
    title="Verifikasi Transaksi"
    subtitle="Validasi bukti transfer tim kompetisi dan catat alasan penolakan dengan modal interaktif."
>
    @php
        $pendingCount = $teams->filter(fn ($team) => $team->is_verified === 'pending')->count();
        $acceptedCount = $teams->filter(fn ($team) => $team->is_verified === 'approved')->count();
        $rejectedCount = $teams->filter(fn ($team) => $team->is_verified === 'rejected')->count();
    @endphp

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <x-admin.stat-card label="Pending" :value="$pendingCount" tone="amber" />
        <x-admin.stat-card label="Accepted" :value="$acceptedCount" tone="emerald" />
        <x-admin.stat-card label="Rejected" :value="$rejectedCount" tone="rose" />
    </div>

    <section
        x-data="{
            rejectOpen: false,
            selectedTeam: null,
            rejectAction: '',
            rejectReason: '',
            openReject(teamName, action) {
                this.selectedTeam = teamName;
                this.rejectAction = action;
                this.rejectReason = '';
                this.rejectOpen = true;
                this.$nextTick(() => this.$refs.rejectReason?.focus());
            },
            closeReject() {
                this.rejectOpen = false;
                this.selectedTeam = null;
                this.rejectAction = '';
                this.rejectReason = '';
            },
        }"
        x-on:keydown.escape.window="rejectOpen && closeReject()"
        class="rounded-lg border border-gray-200 bg-white shadow-sm"
    >
        <div class="border-b border-gray-200 px-6 py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Antrean Tim</h2>
                </div>
                <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex flex-col sm:flex-row gap-3">
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
                            placeholder="Cari transaksi..."
                            class="block w-full sm:w-auto pl-10 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="sr-only">Filter Kompetisi</label>
                        <select name="competition_id" onchange="this.form.submit()" class="block w-full sm:w-auto rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Kompetisi</option>
                            @foreach($competitions as $competition)
                                <option value="{{ $competition->id }}" @selected($filterCompetition == $competition->id)>{{ $competition->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sr-only">Filter Status</label>
                        <select name="status" onchange="this.form.submit()" class="block w-full sm:w-auto rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="default" @selected($filterStatus === 'default')>Pending & Rejected</option>
                            <option value="all" @selected($filterStatus === 'all')>Semua Status</option>
                            <option value="pending" @selected($filterStatus === 'pending')>Pending</option>
                            <option value="accepted" @selected($filterStatus === 'accepted')>Accepted</option>
                            <option value="rejected" @selected($filterStatus === 'rejected')>Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="hidden">Submit</button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pendaftaran</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Kompetisi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Bukti Transfer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Waktu Pendaftaran</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Waktu Submit Bukti</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($teams as $team)
                        @php
                            $status = $team->is_verified === 'approved' ? 'accepted' : ($team->is_verified === 'rejected' ? 'rejected' : 'pending');
                            $isIndividual = $team->event?->participation_type === 'individual';
                            $primaryMember = $team->members->firstWhere('role', 'leader') ?? $team->members->first();
                            $displayName = $isIndividual
                                ? ($primaryMember?->user?->full_name ?? 'Peserta')
                                : $team->team_name;
                            $individualCode = Str::upper(Str::substr(str_replace('-', '', $team->id), 0, 8));
                        @endphp

                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $displayName }}</p>
                                @if($isIndividual)
                                    <p class="text-sm text-gray-600">Individu · ID: {{ $individualCode }}</p>
                                @else
                                <p class="text-sm text-gray-600">{{ $team->team_code }} · {{ $team->members->count() }} anggota</p>
                                @endif
                                @if ($team->verification_error)
                                    <p class="mt-2 max-w-md text-sm text-rose-700">Alasan: {{ $team->verification_error }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $team->event?->title ?? 'Event tidak ditemukan' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($team->paymentProof)
                                    <div class="space-y-1">
                                        <p class="font-medium text-gray-950">{{ $team->paymentProof->name }}</p>
                                        @if ($team->payment_proof_url)
                                            <a href="{{ $team->payment_proof_url }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                                Lihat Gambar
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">Belum ada bukti transfer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $team->created_at?->format('d M Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $team->paymentProof?->created_at?->format('d M Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <x-admin.status-badge :status="$status" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    @if($team->is_verified !== 'approved')
                                        <form method="POST" action="{{ route('admin.transactions.accept', $team) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                                Accept
                                            </button>
                                        </form>

                                        <button
                                            type="button"
                                            class="rounded-md bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-500"
                                            x-on:click="openReject(@js($displayName), @js(route('admin.transactions.reject', $team)))"
                                        >
                                            Reject
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada tim yang perlu diverifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teams->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                {{ $teams->links() }}
            </div>
        @endif

        <div
            x-cloak
            x-show="rejectOpen"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
            aria-labelledby="reject-modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div x-show="rejectOpen" x-transition.opacity class="fixed inset-0 bg-gray-950/60" x-on:click="closeReject()"></div>

            <div
                x-show="rejectOpen"
                x-transition
                class="relative w-full max-w-lg rounded-lg bg-white shadow-xl"
            >
                <form method="POST" x-bind:action="rejectAction">
                    @csrf
                    @method('PATCH')

                    <div class="border-b border-gray-200 px-6 py-5">
                        <h3 id="reject-modal-title" class="text-lg font-semibold text-gray-950">Tolak Transaksi</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Isi alasan penolakan untuk <span class="font-semibold text-gray-950" x-text="selectedTeam"></span>.
                        </p>
                    </div>

                    <div class="px-6 py-5">
                        <label for="verification_error" class="text-sm font-semibold text-gray-800">Alasan penolakan</label>
                        <textarea
                            id="verification_error"
                            name="verification_error"
                            rows="5"
                            x-ref="rejectReason"
                            x-model="rejectReason"
                            required
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500"
                            placeholder="Contoh: nominal transfer tidak sesuai atau bukti transfer tidak terbaca."
                        ></textarea>
                        @error('verification_error')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 bg-gray-50 px-6 py-4">
                        <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-white" x-on:click="closeReject()">
                            Batal
                        </button>
                        <button type="submit" class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-admin.layout>
