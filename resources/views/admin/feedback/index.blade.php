<x-admin.layout
    title="Kritik, Saran & Feedback Peserta"
    subtitle="Pantau dan tinjau masukan, kendala teknis, serta saran dari peserta IT Today."
>
    <section x-data="{ search: '', activeTab: 'all', lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }" x-on:open-lightbox.window="lightboxOpen = true; lightboxImg = $event.detail.img; lightboxTitle = $event.detail.title" class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-950">Kotak Masukan Peserta</h2>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Direktori umpan balik dan bukti screenshot pendukung
                </p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ $feedbacks->count() }} masukan terdeteksi
            </p>
        </div>

        <div class="border-b border-gray-200 px-6 py-4 flex flex-col sm:flex-row gap-3 justify-between items-center">
            <label class="relative block w-full sm:w-80">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                    </svg>
                </span>
                <input
                    type="search"
                    x-model="search"
                    placeholder="Search subjek, nama, email, isi..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Pengirim</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Subjek & Isi Feedback</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Media / Screenshot</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-600">Waktu Kirim</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($feedbacks as $item)
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower($item->user?->full_name . ' ' . $item->user?->email . ' ' . $item->subject . ' ' . $item->content) }}"
                            class="align-top hover:bg-gray-50"
                        >
                            <!-- Pengirim -->
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900 text-sm">
                                    {{ $item->user?->full_name ?? 'Peserta Unknown' }}
                                </p>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">
                                    {{ $item->user?->email ?? $item->user?->identity?->email ?? '-' }}
                                </p>
                                @if($item->user?->phone_number)
                                    <p class="text-[11px] text-gray-400 font-mono">
                                        WA: {{ $item->user->phone_number }}
                                    </p>
                                @endif
                            </td>

                            <!-- Subjek & Isi Feedback (Truncated with '...') -->
                            <td class="px-6 py-4 max-w-xs sm:max-w-md">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex rounded border border-purple-200 bg-purple-50 px-2 py-0.5 text-[10px] font-extrabold uppercase text-purple-700">
                                        {{ $item->subject }}
                                    </span>
                                    @if($item->status === 'reviewed')
                                        <span class="inline-flex rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800">
                                            Ditinjau
                                        </span>
                                    @elseif($item->status === 'resolved')
                                        <span class="inline-flex rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                            Baru / Pending
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-gray-700 leading-relaxed font-normal">
                                    {{ Str::limit($item->content, 110, '...') }}
                                </p>
                                @if(strlen($item->content) > 110)
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'view-feedback-{{ $item->id }}')"
                                        class="mt-1.5 inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer"
                                    >
                                        Baca Selengkapnya &rarr;
                                    </button>
                                @endif
                            </td>

                            <!-- Media / Screenshot Attachments -->
                            <td class="px-6 py-4">
                                @if(!empty($item->media_urls) && is_array($item->media_urls))
                                    <div class="flex flex-wrap gap-1.5 items-center">
                                        @foreach(array_slice($item->media_urls, 0, 3) as $imgUrl)
                                            <button
                                                type="button"
                                                @click="lightboxOpen = true; lightboxImg = '{{ $imgUrl }}'; lightboxTitle = '{{ addslashes($item->subject) }}'"
                                                class="block h-12 w-12 rounded border border-gray-300 bg-gray-100 overflow-hidden shadow-sm hover:opacity-80 transition-opacity cursor-pointer text-left"
                                            >
                                                <img src="{{ $imgUrl }}" alt="Attachment" class="h-full w-full object-cover">
                                            </button>
                                        @endforeach
                                        @if(count($item->media_urls) > 3)
                                            <button
                                                type="button"
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'view-feedback-{{ $item->id }}')"
                                                class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded border border-gray-200 hover:bg-gray-200 cursor-pointer"
                                            >
                                                +{{ count($item->media_urls) - 3 }} foto
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tanpa Lampiran</span>
                                @endif
                            </td>

                            <!-- Tanggal dan Jam Kirim -->
                            <td class="px-6 py-4 text-xs font-medium text-gray-600 whitespace-nowrap">
                                <div>{{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                <div class="text-gray-400 font-mono mt-0.5">{{ $item->created_at?->format('H:i') ?? '' }} WIB</div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end gap-2">
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'view-feedback-{{ $item->id }}')"
                                        class="rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 cursor-pointer"
                                    >
                                        Detail Preview
                                    </button>

                                    @if(auth()->user()?->role === 'superadmin')
                                        <form method="POST" action="{{ route('admin.feedback.destroy', $item) }}" onsubmit="return confirm('Hapus pesan feedback ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 cursor-pointer"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                Belum ada masukan atau feedback dari peserta.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Fullscreen Image Lightbox Modal -->
        <div
            x-show="lightboxOpen"
            x-cloak
            x-on:keydown.escape.window="lightboxOpen = false"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/90 p-4 backdrop-blur-md"
            style="display: none;"
        >
            <!-- Close Button -->
            <button
                type="button"
                @click="lightboxOpen = false"
                class="absolute top-4 right-4 z-[99999] rounded-full bg-white/20 p-2.5 text-white hover:bg-white/40 focus:outline-none transition-colors cursor-pointer"
                title="Tutup Preview (Esc)"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Modal Content Image -->
            <div @click.outside="lightboxOpen = false" class="relative max-w-5xl max-h-[92vh] flex flex-col items-center justify-center overflow-hidden rounded-xl bg-black/60 p-3 border border-white/20 shadow-2xl">
                <img :src="lightboxImg" alt="Enlarged Screenshot" class="max-h-[82vh] w-auto max-w-full rounded-lg object-contain shadow-2xl">
                <div class="mt-3 text-center text-xs font-bold text-white/90 flex items-center gap-3">
                    <span x-text="lightboxTitle" class="bg-purple-900/80 text-purple-200 px-2.5 py-0.5 rounded border border-purple-500/40"></span>
                    <span>&bull;</span>
                    <a :href="lightboxImg" target="_blank" class="text-indigo-300 underline hover:text-white transition-colors">
                        Buka Foto Asli di Tab Baru ↗
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Modals Preview Full Content & Screenshots -->
    @foreach ($feedbacks as $item)
        <x-modal name="view-feedback-{{ $item->id }}" maxWidth="3xl" focusable>
            <div class="p-6">
                <div class="flex items-start justify-between border-b border-gray-200 pb-4">
                    <div>
                        <span class="inline-flex rounded border border-purple-200 bg-purple-50 px-2 py-0.5 text-xs font-extrabold uppercase text-purple-700">
                            {{ $item->subject }}
                        </span>
                        <h3 class="mt-2 text-lg font-bold text-gray-950">Detail Feedback Peserta</h3>
                    </div>
                    <p class="text-xs font-mono text-gray-500">
                        {{ $item->created_at?->translatedFormat('d F Y, H:i') ?? '-' }} WIB
                    </p>
                </div>

                <!-- Info Pengirim -->
                <div class="mt-4 rounded-lg bg-gray-50 p-4 border border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="font-bold text-gray-500 uppercase tracking-wide">Nama Peserta:</span>
                        <p class="text-sm font-extrabold text-gray-900 mt-0.5">{{ $item->user?->full_name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <span class="font-bold text-gray-500 uppercase tracking-wide">Email:</span>
                        <p class="text-sm font-semibold text-gray-900 mt-0.5 font-mono">{{ $item->user?->email ?? '-' }}</p>
                    </div>
                </div>

                <!-- Full Content Text -->
                <div class="mt-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Isi Feedback & Saran Lengkap:</h4>
                    <div class="mt-2 rounded-md border border-gray-200 bg-white p-4 text-sm font-normal text-gray-800 leading-relaxed whitespace-pre-wrap max-h-60 overflow-y-auto">
                        {{ $item->content }}
                    </div>
                </div>

                <!-- Full Screenshots Lightbox Gallery -->
                @if(!empty($item->media_urls) && is_array($item->media_urls))
                    <div x-data class="mt-5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Lampiran Gambar / Screenshot ({{ count($item->media_urls) }}):</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($item->media_urls as $img)
                                <button
                                    type="button"
                                    @click="$dispatch('open-lightbox', { img: '{{ $img }}', title: '{{ addslashes($item->subject) }}' })"
                                    class="group relative aspect-video overflow-hidden rounded-lg border-2 border-gray-300 bg-black/5 hover:border-indigo-600 transition-all text-left cursor-pointer"
                                >
                                    <img src="{{ $img }}" alt="Screenshot" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity gap-1">
                                        <span>🔍 Memperbesar Gambar</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button" x-on:click="$dispatch('close-modal', 'view-feedback-{{ $item->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 cursor-pointer">Tutup</button>
                </div>
            </div>
        </x-modal>
    @endforeach
</x-admin.layout>
