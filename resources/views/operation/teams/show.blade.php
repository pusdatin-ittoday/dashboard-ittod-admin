<x-admin.layout
    title="Detail Tim: {{ $team->team_name }}"
    subtitle="ID Tim: {{ $team->id }}"
>
    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('operation.teams.index') }}" class="inline-flex w-fit items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Kembali ke Verifikasi Transaksi
        </a>

        <span class="inline-flex rounded-md bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">
            {{ $team->event->title ?? $team->competition_id }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-1 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950">Bukti Pembayaran Pendaftaran</h2>
                        <p class="mt-1 text-sm text-gray-600">Validasi media bukti transfer pendaftaran tim.</p>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Media ID: {{ $team->payment_proof_id ?? 'Belum Diunggah' }}
                    </span>
                </div>

                <div class="flex min-h-56 items-center justify-center bg-gray-50 px-6 py-8">
                    @if($team->paymentProof)
                        @if($team->paymentProof->type === 'image' || in_array(pathinfo($team->paymentProof->url, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                            <div class="max-w-md overflow-hidden rounded-lg border border-gray-200 bg-white p-2 shadow-sm">
                                <img src="{{ $team->paymentProof->url }}" alt="Bukti Pembayaran" class="mx-auto max-h-80 rounded-md object-contain">
                                <div class="mt-3 flex justify-end">
                                    <a href="{{ $team->paymentProof->url }}" target="_blank" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        Buka Penuh
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="flex w-full max-w-md items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-red-50 text-xs font-bold text-red-600">
                                    PDF
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-950">{{ $team->paymentProof->name }}</p>
                                    <p class="text-xs text-gray-500">Berkas Dokumen PDF</p>
                                </div>
                                <a href="{{ $team->paymentProof->url }}" target="_blank" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    Buka
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-gray-300 text-gray-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-gray-500">Bukti transfer belum diunggah</p>
                        </div>
                    @endif
                </div>
            </section>

            <section>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-950">Berkas Identitas Anggota</h2>
                    <p class="mt-1 text-sm text-gray-600">Periksa kartu identitas dan catatan revisi setiap anggota.</p>
                </div>

                <div class="space-y-4">
                    @foreach($team->members as $member)
                        <article class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="flex flex-col gap-2 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    @if($member->role === 'leader')
                                        <span class="rounded-md bg-amber-100 px-2 py-1 text-xs font-semibold uppercase text-amber-800">Ketua</span>
                                    @else
                                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold uppercase text-gray-600">Anggota</span>
                                    @endif
                                    <h3 class="text-base font-semibold text-gray-950">{{ $member->user->full_name }}</h3>
                                </div>
                                <span class="text-sm text-gray-500">{{ $member->user->email }}</span>
                            </div>

                            <div class="grid grid-cols-1 gap-6 px-6 py-5 md:grid-cols-2">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                                    <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Kartu Identitas / KTM</p>
                                    @if($member->kartu)
                                        @if($member->kartu->type === 'image' || in_array(pathinfo($member->kartu->url, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                                            <img src="{{ $member->kartu->url }}" alt="KTM {{ $member->user->full_name }}" class="max-h-48 rounded-md border border-gray-200 bg-white object-contain p-1">
                                            <a href="{{ $member->kartu->url }}" target="_blank" class="mt-3 inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-white">
                                                Lihat Gambar
                                            </a>
                                        @else
                                            <div class="flex items-center gap-3 rounded-md border border-gray-200 bg-white p-3">
                                                <span class="rounded bg-red-50 px-2 py-1 text-xs font-bold text-red-600">PDF</span>
                                                <p class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-800">{{ $member->kartu->name }}</p>
                                                <a href="{{ $member->kartu->url }}" target="_blank" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Buka</a>
                                            </div>
                                        @endif
                                    @else
                                        <p class="py-8 text-center text-sm text-gray-500">KTM belum diunggah</p>
                                    @endif
                                </div>

                                <div class="flex flex-col justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status Berkas Anggota</p>
                                        @if(empty($member->verification_error))
                                            <span class="mt-2 inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                Berkas Valid
                                            </span>
                                        @else
                                            <span class="mt-2 inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                                Ada Kesalahan
                                            </span>
                                            <div class="mt-3 rounded-md border border-rose-100 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                                {{ $member->verification_error }}
                                            </div>
                                        @endif
                                    </div>

                                    <form action="{{ route('operation.teams.verifyMember', ['teamId' => $team->id, 'userId' => $member->user_id]) }}" method="POST" class="mt-5 border-t border-gray-200 pt-5">
                                        @csrf
                                        <label for="member-error-{{ $member->user_id }}" class="block text-sm font-semibold text-gray-700">Catatan Kesalahan</label>
                                        <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                                            <input
                                                id="member-error-{{ $member->user_id }}"
                                                type="text"
                                                name="verification_error"
                                                value="{{ $member->verification_error }}"
                                                placeholder="Kosongkan jika berkas valid"
                                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                            >
                                            <button type="submit" class="rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                                Simpan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Informasi Tim</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Nama Tim</dt>
                        <dd class="text-right font-semibold text-gray-950">{{ $team->team_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Kode Lomba</dt>
                        <dd class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs font-semibold text-gray-700">{{ $team->team_code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Cabang Lomba</dt>
                        <dd class="text-right font-semibold text-gray-950">{{ $team->event->title ?? $team->competition_id }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Kapasitas</dt>
                        <dd class="font-semibold text-gray-950">{{ $team->members->count() }} / {{ $team->max_member }} Anggota</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Status Penguncian</dt>
                        <dd>
                            @php
                                $isTeamVerified = (bool) $team->is_verified;
                                $hasTeamErr = !empty($team->verification_error);
                                $hasMemErr = $team->members->contains(fn($memberItem) => !empty($memberItem->verification_error));
                                $isUnderReview = !$isTeamVerified && !$hasTeamErr && !$hasMemErr;
                            @endphp
                            @if($isTeamVerified || $isUnderReview)
                                <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Terkunci</span>
                            @else
                                <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Terbuka Revisi</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                x-data="{
                    verified: '{{ $team->is_verified ? '1' : '0' }}',
                    rejectionReason: @js($team->verification_error ?? ''),
                    rejectionError: '',
                    submitDecision(event) {
                        if (this.verified === '0' && ! this.rejectionReason.trim()) {
                            event.preventDefault();
                            this.rejectionError = 'Alasan penolakan wajib diisi saat menolak verifikasi.';
                            this.$nextTick(() => this.$refs.rejectionReason?.focus());
                        }
                    },
                }"
            >
                <h2 class="text-lg font-semibold text-gray-950">Keputusan Verifikasi</h2>
                <form action="{{ route('operation.teams.verify', $team->id) }}" method="POST" class="mt-5 space-y-5" x-on:submit="submitDecision($event)">
                    @csrf

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status Validasi Berkas Pendaftaran</p>
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <label class="rounded-lg border border-gray-200 px-3 py-3 text-center hover:bg-gray-50">
                                <input type="radio" name="is_verified" value="1" x-model="verified" class="text-emerald-600 focus:ring-emerald-500">
                                <span class="mt-2 block text-sm font-semibold text-emerald-700">Setujui</span>
                            </label>
                            <label class="rounded-lg border border-gray-200 px-3 py-3 text-center hover:bg-gray-50">
                                <input type="radio" name="is_verified" value="0" x-model="verified" class="text-rose-600 focus:ring-rose-500">
                                <span class="mt-2 block text-sm font-semibold text-rose-700">Tolak</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="verified === '0'">
                        <label for="verification_error" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Penolakan / Catatan Kesalahan</label>
                        <textarea
                            id="verification_error"
                            name="verification_error"
                            x-ref="rejectionReason"
                            x-model="rejectionReason"
                            x-on:input="rejectionError = ''"
                            placeholder="Sebutkan kesalahan pada data atau berkas tim..."
                            x-bind:class="rejectionError ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500'"
                            class="mt-2 h-28 w-full resize-none rounded-md text-sm shadow-sm"
                        ></textarea>
                        <p x-show="rejectionError" x-text="rejectionError" class="mt-2 text-sm font-semibold text-rose-700"></p>
                    </div>

                    <button type="submit" class="w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">
                        Simpan Keputusan
                    </button>
                </form>
            </section>
        </aside>
    </div>
</x-admin.layout>
