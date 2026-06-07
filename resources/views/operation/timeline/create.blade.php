<x-admin.layout
    title="Tambah Lini Masa Baru"
    subtitle="Definisikan tanggal penting untuk kegiatan non-kompetisi."
>
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Lini masa belum bisa disimpan.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-5">
            <h2 class="text-xl font-semibold text-gray-950">Form Lini Masa</h2>
            <p class="mt-1 text-sm text-gray-600">Isi rincian kegiatan dan tanggal pelaksanaan agenda.</p>
        </div>

        <form action="{{ route('timeline.store') }}" method="POST" class="space-y-5 px-6 py-5">
            @csrf

            <div>
                <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <label for="event_id" class="block text-sm font-semibold text-gray-700">Pilih Kegiatan</label>
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('open-modal', 'create-event')"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Tambah Kegiatan
                    </button>
                </div>
                <select name="event_id" id="event_id" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="" disabled selected>-- Pilih Kegiatan --</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ old('event_id') === $event->id ? 'selected' : '' }}>
                            {{ $event->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="block">
                <span class="text-sm font-semibold text-gray-700">Nama Agenda Kegiatan</span>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    placeholder="Contoh: Pembukaan acara"
                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
            </label>

            <label class="block">
                <span class="text-sm font-semibold text-gray-700">Tanggal & Waktu Pelaksanaan</span>
                <input
                    type="datetime-local"
                    name="date"
                    value="{{ old('date') }}"
                    required
                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
            </label>

            <div class="flex justify-end gap-3 border-t border-gray-200 pt-5">
                <a href="{{ route('timeline.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                    Simpan Agenda
                </button>
            </div>
        </form>
    </section>

    <x-modal name="create-event" maxWidth="2xl" focusable>
        <form method="POST" action="{{ route('operation.events.store') }}" class="p-6">
            @csrf

            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-950">Tambah Kegiatan</h3>
                <p class="mt-1 text-sm text-gray-600">Setelah tersimpan, kegiatan baru akan muncul di pilihan kegiatan.</p>
            </div>

            <div class="mt-5">
                @include('operation.timeline.partials.event-form')
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-event')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                    Simpan Kegiatan
                </button>
            </div>
        </form>
    </x-modal>
</x-admin.layout>
