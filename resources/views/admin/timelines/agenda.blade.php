<x-admin.layout
    title="Agenda {{ $event->title }}"
    subtitle="Kelola daftar agenda khusus untuk kompetisi {{ $event->title }}."
>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.timelines.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Kembali
        </a>

        @if ($canManageTimelines)
            <button
                type="button"
                x-data
                x-on:click="$dispatch('open-modal', 'create-agenda')"
                class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
            >
                Tambah Agenda
            </button>
        @endif
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
                                <p class="mt-1 text-xs text-gray-500">ID: {{ $agenda->id }}</p>
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
                                        <form method="POST" action="{{ route('admin.timelines.destroy', $agenda) }}" onsubmit="return confirm('Hapus agenda ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
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
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" x-on:click="$dispatch('close-modal', 'edit-agenda-{{ $agenda->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Perubahan</button>
                    </div>
                </form>
            </x-modal>
        @endforeach
    @endif
</x-admin.layout>
