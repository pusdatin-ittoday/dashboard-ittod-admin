<x-admin.layout
    title="Edit Lini Masa"
    subtitle="Sunting informasi jadwal penting kegiatan non-kompetisi."
>
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Lini masa belum bisa diperbarui.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-5">
            <h2 class="text-xl font-semibold text-gray-950">Form Sunting Lini Masa</h2>
            <p class="mt-1 text-sm text-gray-600">Ubah rincian agenda kegiatan di bawah ini.</p>
        </div>

        <form action="{{ route('timeline.update', $timeline->id) }}" method="POST" class="space-y-5 px-6 py-5">
            @csrf
            @method('PUT')

            <label class="block">
                <span class="text-sm font-semibold text-gray-700">Pilih Kegiatan</span>
                <select name="event_id" id="event_id" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ (old('event_id', $timeline->event_id) === $event->id) ? 'selected' : '' }}>
                            {{ $event->title }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-semibold text-gray-700">Nama Agenda Kegiatan</span>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title', $timeline->title) }}"
                    required
                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
            </label>

            <label class="block">
                <span class="text-sm font-semibold text-gray-700">Tanggal & Waktu Pelaksanaan</span>
                <input
                    type="datetime-local"
                    name="date"
                    value="{{ old('date', $timeline->date ? $timeline->date->format('Y-m-d\TH:i') : '') }}"
                    required
                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
            </label>

            <div class="flex justify-end gap-3 border-t border-gray-200 pt-5">
                <a href="{{ route('timeline.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                    Perbarui Agenda
                </button>
            </div>
        </form>
    </section>
</x-admin.layout>
