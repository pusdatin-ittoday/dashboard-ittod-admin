<div class="grid gap-4 sm:grid-cols-2">
    <label class="block sm:col-span-2">
        <span class="text-sm font-semibold text-gray-700">Nama Kegiatan</span>
        <input
            name="title"
            value="{{ old('title') }}"
            required
            placeholder="Contoh: Seminar Nasional IT Today"
            class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
    </label>

    <label class="block sm:col-span-2">
        <span class="text-sm font-semibold text-gray-700">Deskripsi</span>
        <textarea
            name="description"
            rows="3"
            required
            placeholder="Ringkasan singkat event atau kegiatan."
            class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('description') }}</textarea>
    </label>

    <label class="block sm:col-span-2">
        <span class="text-sm font-semibold text-gray-700">URL Guide Book</span>
        <input
            type="url"
            name="guide_book_url"
            value="{{ old('guide_book_url') }}"
            required
            placeholder="https://example.com/guide-book"
            class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
    </label>

    <label class="block sm:col-span-2">
        <span class="text-sm font-semibold text-gray-700">Biaya Pendaftaran</span>
        <x-admin.currency-input name="price" :value="old('price', 0)" class="rounded-xl focus:border-indigo-500 focus:ring-indigo-500" />
    </label>

    <label class="block">
        <span class="text-sm font-semibold text-gray-700">Contact Person 1</span>
        <input
            name="contact_person1"
            value="{{ old('contact_person1') }}"
            required
            placeholder="Nama / nomor kontak"
            class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
    </label>

    <label class="block">
        <span class="text-sm font-semibold text-gray-700">Contact Person 2</span>
        <input
            name="contact_person2"
            value="{{ old('contact_person2') }}"
            placeholder="Opsional"
            class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
    </label>
</div>
