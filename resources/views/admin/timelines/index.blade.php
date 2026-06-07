<x-admin.layout
    title="Pengaturan Timeline Kompetisi"
    subtitle="Kelola fase pendaftaran, submission, penjurian, dan final khusus event lomba."
>
    @if ($canManageTimelines)
        <div class="mb-6 flex justify-end">
            <button
                type="button"
                x-data
                x-on:click="$dispatch('open-modal', 'create-competition')"
                class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
            >
                Tambah Kompetisi
            </button>
        </div>
    @endif

    @if ($canManageTimelines && $errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Timeline belum bisa disimpan.</p>
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
                    <h2 class="text-xl font-semibold text-gray-950">Direktori Timeline Kompetisi</h2>
                    <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">
                        Competition Records
                    </span>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Kompetisi, agenda, tim, dan status publikasi</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $events->count() }} records detected</p>
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
                    placeholder="Search kompetisi, status, tim, atau jumlah agenda..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Kompetisi</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Agenda</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Tim</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Selengkapnya</th>
                        @if ($canManageTimelines)
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($events as $event)
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($event->title . ' ' . ($event->is_active ? 'aktif' : 'nonaktif') . ' ' . $event->teams_count . ' tim ' . $event->timelines_count . ' agenda') }}"
                            class="align-top hover:bg-gray-50"
                        >
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $event->title }}</p>
                                <p class="mt-1 text-xs text-gray-500">ID: {{ $event->id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-[11px] font-bold uppercase text-indigo-700">
                                    {{ $event->timelines_count }} agenda
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-700">{{ $event->teams_count }} tim</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded border px-2 py-1 text-[11px] font-bold uppercase {{ $event->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-gray-200 bg-gray-50 text-gray-500' }}">
                                    {{ $event->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.timelines.agenda', $event) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    Lihat Detail
                                </a>
                            </td>
                            @if ($canManageTimelines)
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        <button
                                            type="button"
                                            x-data
                                            x-on:click="$dispatch('open-modal', 'edit-competition-{{ $event->id }}')"
                                            class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100"
                                        >
                                            Edit Kompetisi
                                        </button>
                                        @if ($event->teams_count > 0)
                                            <form method="POST" action="{{ route('admin.competitions.status', $event) }}" onsubmit="return confirm('{{ $event->is_active ? 'Nonaktifkan' : 'Aktifkan kembali' }} kompetisi ini?')">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="rounded-md border px-3 py-2 text-sm font-semibold {{ $event->is_active ? 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                                                >
                                                    {{ $event->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.competitions.destroy', $event) }}" onsubmit="return confirm('Hapus kompetisi ini? Timeline kompetisi juga akan terhapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                                                >
                                                    Hapus Kompetisi
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageTimelines ? 6 : 5 }}" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada event kompetisi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canManageTimelines)
    <x-modal name="create-competition" maxWidth="2xl" focusable>
        <form method="POST" action="{{ route('admin.competitions.store') }}" class="p-6">
            @csrf

            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-950">Tambah Kompetisi</h3>
                <p class="mt-1 text-sm text-gray-600">Buat cabang lomba baru untuk IT Today.</p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Nama Kompetisi</span>
                    <input name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Biaya Pendaftaran</span>
                    <x-admin.currency-input name="price" :value="old('price', 0)" required />
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-semibold text-gray-700">Deskripsi</span>
                    <textarea name="description" required rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-semibold text-gray-700">URL Guide Book</span>
                    <input type="url" name="guide_book_url" value="{{ old('guide_book_url') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Contact Person 1</span>
                    <input name="contact_person1" value="{{ old('contact_person1') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Contact Person 2</span>
                    <input name="contact_person2" value="{{ old('contact_person2') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-competition')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Kompetisi</button>
            </div>
        </form>
    </x-modal>

    @foreach ($events as $event)
        <x-modal name="edit-competition-{{ $event->id }}" maxWidth="2xl" focusable>
            <form method="POST" action="{{ route('admin.competitions.update', $event) }}" class="p-6">
                @csrf
                @method('PATCH')

                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-950">Edit Kompetisi</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ $event->title }}</p>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Nama Kompetisi</span>
                        <input name="title" value="{{ old('title', $event->title) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Biaya Pendaftaran</span>
                        <x-admin.currency-input name="price" :value="old('price', $event->price)" required />
                    </label>

                    <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active)) class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-gray-700">Kompetisi aktif</span>
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-semibold text-gray-700">Deskripsi</span>
                        <textarea name="description" required rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $event->description) }}</textarea>
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-semibold text-gray-700">URL Guide Book</span>
                        <input type="url" name="guide_book_url" value="{{ old('guide_book_url', $event->guide_book_url) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Contact Person 1</span>
                        <input name="contact_person1" value="{{ old('contact_person1', $event->contact_person1) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Contact Person 2</span>
                        <input name="contact_person2" value="{{ old('contact_person2', $event->contact_person2) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-competition-{{ $event->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>
    @endforeach

    @endif
</x-admin.layout>
