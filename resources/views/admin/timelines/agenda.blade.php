<x-admin.layout
    title="Agenda {{ $event->title }}"
    subtitle="Kelola daftar agenda khusus untuk kompetisi {{ $event->title }}."
>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.timelines.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Kembali
        </a>

        @if ($canManageTimelines)
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-modal', 'edit-registration-deadline-{{ $event->id }}')"
                    class="inline-flex items-center justify-center rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                >
                    Atur Batas Pendaftaran
                </button>
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-modal', 'create-agenda')"
                    class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                >
                    Tambah Agenda
                </button>
            </div>
        @endif
    </div>

    @php
        $regTimeline = $event->timelines->firstWhere('is_registration', true);
    @endphp
    <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-gray-900">Status Pendaftaran Event</h3>
                    @if($event->is_active)
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Pendaftaran Aktif</span>
                    @else
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Pendaftaran Ditutup</span>
                    @endif
                </div>
                @if($regTimeline)
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-600">
                        <span>Timeline Acuan: <strong class="text-gray-900">{{ $regTimeline->title }}</strong></span>
                        <span>•</span>
                        <span>Mulai: <strong class="text-gray-900">{{ $regTimeline->date?->format('d M Y, H:i') }}</strong></span>
                        <span>•</span>
                        <span>Deadline: <strong class="text-gray-900">{{ $regTimeline->end_date ? $regTimeline->end_date->format('d M Y, H:i') : ($regTimeline->date ? $regTimeline->date->format('d M Y, H:i') : '-') }}</strong></span>
                        @php
                            $deadline = $regTimeline->end_date ?? $regTimeline->date;
                            $isExpired = $deadline && now()->greaterThan($deadline);
                        @endphp
                        @if($isExpired)
                            <span class="rounded bg-red-50 border border-red-200 px-2 py-0.5 text-[11px] font-bold text-red-700">Tutup Otomatis (Deadline Terlewati)</span>
                        @else
                            <span class="rounded bg-blue-50 border border-blue-200 px-2 py-0.5 text-[11px] font-bold text-blue-700">Auto-close Aktif</span>
                        @endif
                    </div>
                @else
                    <p class="mt-1 text-xs text-gray-500">Belum ada timeline yang dijadikan acuan auto-close pendaftaran. Pendaftaran saat ini dikontrol secara manual.</p>
                @endif
            </div>
            @if($canManageTimelines)
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-modal', 'edit-registration-deadline-{{ $event->id }}')"
                    class="inline-flex items-center justify-center rounded-md border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100 shrink-0"
                >
                    Atur Timeline Pendaftaran
                </button>
            @endif
        </div>
    </div>

    @if ($canManageTimelines && $errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Agenda belum bisa disimpan.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section x-data="{ search: '' }" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-950">Direktori Agenda {{ $event->title }}</h2>
                    <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">
                        Agenda Records
                    </span>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">{{ $event->teams_count }} tim terdaftar</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $event->timelines->count() }} records detected</p>
        </div>

        @if(isset($globalTimelines) && $globalTimelines->isNotEmpty())
        <div class="border-b border-gray-200 bg-blue-50/50 px-6 py-4">
            <h3 class="mb-3 text-sm font-bold text-blue-900 flex items-center">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Agenda Global Kompetisi (Read-Only)
            </h3>
            <div class="overflow-x-auto rounded border border-blue-100 bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-600">Agenda</th>
                            <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-600">Waktu Mulai</th>
                            <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-600">Waktu Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($globalTimelines as $globalAgenda)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="text-sm font-semibold text-gray-950">{{ $globalAgenda->title }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-700">{{ \Carbon\Carbon::parse($globalAgenda->start_date)->translatedFormat('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-700">{{ \Carbon\Carbon::parse($globalAgenda->end_date)->translatedFormat('d M Y, H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs italic text-gray-500">*Agenda di atas berlaku untuk semua kompetisi dan hanya dapat diubah oleh Superadmin.</p>
        </div>
        @endif

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
                    placeholder="Search agenda atau tanggal..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Agenda</th>
                        @if ($event->type === 'competition')
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Waktu Mulai</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Waktu Selesai</th>
                        @else
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Tanggal & Waktu</th>
                        @endif
                        @if ($canManageTimelines)
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($event->timelines as $agenda)
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($agenda->title . ' ' . ($agenda->date?->format('d M Y H:i') ?? '') . ($event->type === 'competition' ? ' ' . ($agenda->end_date?->format('d M Y H:i') ?? '') : '')) }}"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $agenda->title }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-gray-500">ID: {{ $agenda->id }}</p>
                                    @if($agenda->is_registration)
                                        <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800">Timeline Pendaftaran</span>
                                    @endif
                                    @if($agenda->is_submission)
                                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Timeline Submisi</span>
                                    @endif
                                </div>
                            </td>
                            @if ($event->type === 'competition')
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">{{ $agenda->date?->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">{{ $agenda->end_date?->format('d M Y H:i') }}</td>
                            @else
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700">{{ $agenda->date?->format('d M Y H:i') }}</td>
                            @endif
                            @if ($canManageTimelines)
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            x-data
                                            x-on:click="$dispatch('open-modal', 'edit-agenda-{{ $agenda->id }}')"
                                            class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            x-data
                                            x-on:click="$dispatch('open-modal', 'confirm-delete-{{ $agenda->id }}')"
                                            class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageTimelines ? 3 : 2 }}" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada agenda spesifik/tambahan untuk kompetisi ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canManageTimelines)
        <x-modal name="create-agenda" maxWidth="lg" focusable>
            <form method="POST" action="{{ route('admin.timelines.store') }}" class="p-6">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event->id }}">

                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-950">Tambah Agenda Spesifik</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ $event->title }}</p>
                </div>

                <div class="mt-5 space-y-4">
                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Nama Agenda</span>
                        <input
                            name="title"
                            value="{{ old('title') }}"
                            required
                            placeholder="Contoh: Open Registration HackToday"
                            class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </label>

                    @if ($event->type === 'competition')
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Waktu Mulai</span>
                            <input
                                type="datetime-local"
                                name="date"
                                value="{{ old('date') }}"
                                required
                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Waktu Selesai</span>
                            <input
                                type="datetime-local"
                                name="end_date"
                                value="{{ old('end_date') }}"
                                required
                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                        </label>
                        <div class="mt-3 flex items-start gap-2">
                            <input type="checkbox" name="is_submission" id="is_submission_create" value="1" class="mt-1 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <label for="is_submission_create" class="text-sm text-gray-700 leading-tight">Jadikan sebagai Timeline Pengumpulan / Revisi Submisi</label>
                        </div>
                    @else
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Tanggal & Waktu</span>
                            <input
                                type="datetime-local"
                                name="date"
                                value="{{ old('date') }}"
                                required
                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                        </label>
                    @endif

                    <div class="mt-3 flex items-start gap-2">
                        <input type="checkbox" name="is_registration" id="is_registration_create" value="1" class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_registration_create" class="text-sm text-gray-700 leading-tight">Jadikan sebagai Timeline Pendaftaran (Auto-close pendaftaran saat deadline berakhir)</label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close-modal', 'create-agenda')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Agenda</button>
                </div>
            </form>
        </x-modal>

        @foreach ($event->timelines as $agenda)
            <x-modal name="edit-agenda-{{ $agenda->id }}" maxWidth="lg" focusable>
                <form method="POST" action="{{ route('admin.timelines.update', $agenda) }}" class="p-6">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="event_id" value="{{ $event->id }}">

                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-gray-950">Edit Agenda Spesifik</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $event->title }}</p>
                    </div>

                    <div class="mt-5 space-y-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Nama Agenda</span>
                            <input
                                name="title"
                                value="{{ old('title', $agenda->title) }}"
                                required
                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                        </label>

                        @if ($event->type === 'competition')
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Waktu Mulai</span>
                                <input
                                    type="datetime-local"
                                    name="date"
                                    value="{{ old('date', $agenda->date?->format('Y-m-d\TH:i')) }}"
                                    required
                                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Waktu Selesai</span>
                                <input
                                    type="datetime-local"
                                    name="end_date"
                                    value="{{ old('end_date', $agenda->end_date?->format('Y-m-d\TH:i')) }}"
                                    required
                                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                            </label>
                            <div class="mt-3 flex items-start gap-2">
                                <input type="checkbox" name="is_submission" id="is_submission_edit_{{ $agenda->id }}" value="1" {{ $agenda->is_submission ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <label for="is_submission_edit_{{ $agenda->id }}" class="text-sm text-gray-700 leading-tight">Jadikan sebagai Timeline Pengumpulan / Revisi Submisi</label>
                            </div>
                        @else
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Tanggal & Waktu</span>
                                <input
                                    type="datetime-local"
                                    name="date"
                                    value="{{ old('date', $agenda->date?->format('Y-m-d\TH:i')) }}"
                                    required
                                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >
                            </label>
                        @endif

                        <div class="mt-3 flex items-start gap-2">
                            <input type="checkbox" name="is_registration" id="is_registration_edit_{{ $agenda->id }}" value="1" {{ $agenda->is_registration ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="is_registration_edit_{{ $agenda->id }}" class="text-sm text-gray-700 leading-tight">Jadikan sebagai Timeline Pendaftaran (Auto-close pendaftaran saat deadline berakhir)</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" x-on:click="$dispatch('close-modal', 'edit-agenda-{{ $agenda->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Perubahan</button>
                    </div>
                </form>
            </x-modal>

            <x-modal name="confirm-delete-{{ $agenda->id }}" maxWidth="md" focusable>
                <form method="POST" action="{{ route('admin.timelines.destroy', $agenda) }}" class="p-6 text-center">
                    @csrf
                    @method('DELETE')
                    
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2">
                        Hapus Agenda <span class="text-red-600">{{ $agenda->title }}</span>?
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">
                        Apakah Anda yakin ingin menghapus agenda ini?<br>Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="flex justify-center gap-3">
                        <button type="button" x-on:click="$dispatch('close-modal', 'confirm-delete-{{ $agenda->id }}')" class="rounded-md border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="rounded-md bg-red-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-red-700 shadow-sm transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </form>
            </x-modal>
        @endforeach

        <x-modal name="edit-registration-deadline-{{ $event->id }}" maxWidth="lg" focusable>
            <form method="POST" action="{{ route('admin.timelines.registration-deadline', $event) }}" class="p-6">
                @csrf
                @method('PATCH')
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-950">Atur Timeline Penutupan Pendaftaran</h3>
                    <p class="mt-1 text-sm text-gray-600">Pilih agenda timeline yang akan digunakan sebagai acuan auto-close pendaftaran untuk {{ $event->title }}.</p>
                </div>
                
                <div class="mt-5 space-y-4">
                    @php
                        $activeRegTimeline = $event->timelines->where('is_registration', true)->first();
                    @endphp
                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Pilih Timeline Acuan</span>
                        <select
                            name="timeline_id"
                            class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white"
                        >
                            <option value="">-- Tidak ada (Kontrol Manual) --</option>
                            @foreach($event->timelines as $timeline)
                                <option value="{{ $timeline->id }}" {{ $activeRegTimeline?->id === $timeline->id ? 'selected' : '' }}>
                                    {{ $timeline->title }} ({{ $timeline->date?->format('d M Y H:i') }} s.d. {{ $timeline->end_date ? $timeline->end_date->format('d M Y H:i') : 'Selesai' }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <div class="rounded-md bg-blue-50 p-3 text-xs text-blue-700 border border-blue-200">
                        <p class="font-semibold mb-1">Cara Kerja Auto Tutup Pendaftaran:</p>
                        <ul class="list-disc pl-4 space-y-0.5 text-blue-600">
                            <li>Batas waktu dihitung dari <strong>Waktu Selesai (Deadline)</strong> agenda terpilih.</li>
                            <li>Ketika waktu tersebut telah terlewati, status pendaftaran event akan otomatis dinonaktifkan.</li>
                            <li>Jika memilih "Tidak ada (Kontrol Manual)", pendaftaran event dibuka/tutup secara manual melalui tombol aktifkan/nonaktifkan.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-registration-deadline-{{ $event->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Pengaturan</button>
                </div>
            </form>
        </x-modal>
    @endif
</x-admin.layout>
