<x-admin.layout
    title="Pengaturan Pengumuman"
    subtitle="Kelola pengumuman untuk seluruh peserta event IT Today."
>
    <div class="mb-6 flex justify-end">
        <button
            type="button"
            x-data
            x-on:click="$dispatch('open-modal', 'create-announcement')"
            class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
        >
            Tambah Pengumuman
        </button>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Pengumuman belum bisa disimpan.</p>
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
                    <h2 class="text-xl font-semibold text-gray-950">Direktori Pengumuman</h2>
                </div>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Manajemen konten pengumuman peserta</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $announcements->count() }} records detected</p>
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
                    placeholder="Search judul, event..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Pengumuman</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Penulis</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($announcements as $announcement)
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($announcement->title . ' ' . $announcement->event?->title) }}"
                            class="align-top hover:bg-gray-50 {{ $announcement->is_pinned ? 'bg-amber-50/40 hover:bg-amber-50' : '' }}"
                        >
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded border border-indigo-100 bg-indigo-50 px-2 py-1 text-[11px] font-bold uppercase text-indigo-700">
                                    {{ $announcement->event?->title ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if ($announcement->is_pinned)
                                        <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200 uppercase">
                                            Pinned
                                        </span>
                                    @endif
                                    <p class="font-semibold text-gray-950">{{ $announcement->title }}</p>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $announcement->description }}</p>
                                <p class="mt-2 text-xs text-gray-400">{{ $announcement->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                {{ $announcement->author?->full_name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end gap-2">
                                    <form method="POST" action="{{ route('admin.announcements.pin', $announcement) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="rounded-md border {{ $announcement->is_pinned ? 'border-amber-300 bg-amber-100 text-amber-800 hover:bg-amber-200' : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100' }} px-3 py-1.5 text-xs font-semibold"
                                        >
                                            {{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}
                                        </button>
                                    </form>
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'edit-announcement-{{ $announcement->id }}')"
                                        class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100"
                                    >
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada pengumuman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-modal name="create-announcement" maxWidth="2xl" focusable>
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="p-6">
            @csrf

            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-950">Tambah Pengumuman</h3>
                <p class="mt-1 text-sm text-gray-600">Buat pengumuman baru untuk event kompetisi.</p>
            </div>

            <div class="mt-5 grid gap-4">
                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Event</span>
                    <select name="event_id" {{ auth()->user()->role === 'panitia_lomba' ? 'required' : '' }} class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @if(auth()->user()->role !== 'panitia_lomba')
                            <option value="">Umum (Seluruh Peserta)</option>
                        @else
                            <option value="" disabled selected>Pilih Event</option>
                        @endif
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Judul Pengumuman</span>
                    <input name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-3">
                    <input type="hidden" name="is_pinned" value="0">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned')) class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-semibold text-gray-700">Sematkan Pengumuman (Pin)</span>
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Isi Pengumuman</span>
                    <textarea name="description" required rows="6" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-announcement')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Pengumuman</button>
            </div>
        </form>
    </x-modal>

    @foreach ($announcements as $announcement)
        <x-modal name="edit-announcement-{{ $announcement->id }}" maxWidth="2xl" focusable>
            <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="p-6">
                @csrf
                @method('PATCH')

                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-950">Edit Pengumuman</h3>
                </div>

                <div class="mt-5 grid gap-4">
                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Event</span>
                        <select name="event_id" {{ auth()->user()->role === 'panitia_lomba' ? 'required' : '' }} class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @if(auth()->user()->role !== 'panitia_lomba')
                                <option value="">Umum (Seluruh Peserta)</option>
                            @else
                                <option value="" disabled @selected(empty($announcement->event_id))>Pilih Event</option>
                            @endif
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}" @selected(old('event_id', $announcement->event_id) == $event->id)>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Judul Pengumuman</span>
                        <input name="title" value="{{ old('title', $announcement->title) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </label>

                    <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-3">
                        <input type="hidden" name="is_pinned" value="0">
                        <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned)) class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-gray-700">Sematkan Pengumuman (Pin)</span>
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700">Isi Pengumuman</span>
                        <textarea name="description" required rows="6" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $announcement->description) }}</textarea>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-announcement-{{ $announcement->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-admin.layout>
