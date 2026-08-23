<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold leading-tight text-gray-800">
                    Submissions: {{ $singleEvent->title }}
                </h2>
                <div class="mt-1 flex items-center gap-2 text-sm text-gray-600">
                    <a href="{{ route('admin.timelines.index') }}" class="hover:text-blue-600 hover:underline">Timelines</a>
                    <span>/</span>
                    <span>Submissions</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <a href="{{ route('export.submissions', $singleEvent->id) }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-bold uppercase text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Export CSV
                </a>
                
                <button 
                    type="button"
                    x-data="{ 
                        isExporting: false, 
                        async exportToSheets() {
                            this.isExporting = true;
                            try {
                                const response = await fetch('{{ route('export.submissions.sheets', $singleEvent->id) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                });
                                const data = await response.json();
                                if (data.success) {
                                    const newWindow = window.open(data.url, '_blank');
                                    if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                                        alert('Ekspor berhasil! Namun tab baru terblokir oleh browser. Silakan buka manual: ' + data.url);
                                    }
                                } else {
                                    alert('Gagal mengekspor: ' + (data.message || 'Terjadi kesalahan.'));
                                }
                            } catch (error) {
                                console.error(error);
                                alert('Terjadi kesalahan jaringan.');
                            } finally {
                                this.isExporting = false;
                            }
                        }
                    }"
                    @click="exportToSheets()" 
                    :disabled="isExporting"
                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-bold uppercase text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <template x-if="isExporting">
                        <span>Exporting...</span>
                    </template>
                    <template x-if="!isExporting">
                        <span>Export Sheets</span>
                    </template>
                </button>

                @if ($canManageTimelines)
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'edit-deadline-{{ $singleEvent->id }}')" class="inline-flex items-center justify-center rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-all duration-150 shadow-sm">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Atur Deadline
                    </button>
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'edit-panitia_lomba-submission-{{ $singleEvent->id }}')" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all duration-150 shadow-sm">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Kelola Format
                    </button>
                @endif
            </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $submissionTimeline = $singleEvent->timelines->where('is_submission', true)->first();
            @endphp
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-2">Status Submisi</h3>
                    @if($submissionTimeline)
                        <div class="flex flex-col sm:flex-row sm:items-center text-sm gap-2">
                            <div>
                                <span class="font-semibold text-gray-700 mr-2">Dibuka mulai:</span>
                                <span class="text-gray-900">{{ $submissionTimeline->date->format('d M Y, H:i') }}</span>
                            </div>
                            <span class="hidden sm:inline font-semibold text-gray-400">|</span>
                            <div>
                                <span class="font-semibold text-gray-700 mr-2">Deadline:</span>
                                <span class="text-gray-900">{{ $submissionTimeline->end_date ? $submissionTimeline->end_date->format('d M Y, H:i') : 'Tidak ditentukan' }}</span>
                            </div>
                        </div>
                        @if(now()->between($submissionTimeline->date, $submissionTimeline->end_date ?? now()->addYears(100)))
                            <div class="mt-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                Submisi Terbuka
                            </div>
                        @else
                            <div class="mt-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Submisi Ditutup
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">Belum ada deadline submisi yang diatur. Peserta tidak dapat mengumpulkan karya saat ini.</p>
                        <div class="mt-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Submisi Terkunci
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    @if ($singleEvent->submissions->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">Belum ada submission</h3>
                            <p class="mt-1 text-sm text-gray-500">Belum ada tim yang mengumpulkan karya untuk kompetisi ini.</p>
                        </div>
                    @else
                        @php
                            $allKeys = [];
                            if (!empty($singleEvent->submission_fields)) {
                                foreach ($singleEvent->submission_fields as $field) {
                                    $allKeys[$field['label']] = $field['label'];
                                }
                            }
                            foreach ($singleEvent->submissions as $submission) {
                                $subObj = is_string($submission->submission_object) ? json_decode($submission->submission_object, true) : $submission->submission_object;
                                if (is_array($subObj)) {
                                    foreach (array_keys($subObj) as $key) {
                                        $allKeys[$key] = \Illuminate\Support\Str::title(str_replace('_', ' ', $key));
                                    }
                                }
                            }
                        @endphp
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap">Tim</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap">Waktu</th>
                                        @foreach ($allKeys as $key => $label)
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap">{{ $label }}</th>
                                        @endforeach
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($singleEvent->submissions as $submission)
                                        @php
                                            $subObj = is_string($submission->submission_object) ? json_decode($submission->submission_object, true) : $submission->submission_object;
                                            if (!is_array($subObj)) $subObj = [];
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $submission->team->team_name ?? 'Tim Tidak Diketahui' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $submission->created_at->format('d M Y, H:i') }}
                                            </td>
                                            @foreach ($allKeys as $key => $label)
                                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                                    @if (isset($subObj[$key]))
                                                        @if (filter_var($subObj[$key], FILTER_VALIDATE_URL))
                                                            <a href="{{ $subObj[$key] }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center">
                                                                Buka Link
                                                                <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                            </a>
                                                        @else
                                                            <span title="{{ $subObj[$key] }}">{{ Str::limit($subObj[$key], 50) }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400 italic">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'confirm-delete-{{ $submission->team_id }}')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md border border-red-200 transition-colors">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modals --}}
    @foreach ($singleEvent->submissions as $submission)
        <x-modal name="confirm-delete-{{ $submission->team_id }}" maxWidth="md" focusable>
            <form action="{{ route('admin.competitions.submissions.destroy', ['event' => $singleEvent->id, 'team_id' => $submission->team_id]) }}" method="POST" class="p-6 text-center">
                @csrf
                @method('DELETE')
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">
                    Hapus Submisi Tim <span class="text-red-600">{{ $submission->team->team_name ?? 'Tidak Diketahui' }}</span>?
                </h2>
                <p class="text-sm text-gray-500 mb-6">
                    Apakah Anda yakin ingin menghapus data submisi ini?<br>Tindakan ini tidak dapat dibatalkan dan file/link yang telah dikirimkan akan terhapus dari sistem.
                </p>
                <div class="flex justify-center gap-3">
                    <button type="button" x-on:click="$dispatch('close-modal', 'confirm-delete-{{ $submission->team_id }}')" class="rounded-md border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="rounded-md bg-red-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-red-700 shadow-sm transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <x-modal name="edit-panitia_lomba-submission-{{ $singleEvent->id }}" maxWidth="2xl" focusable>
        <form method="POST" action="{{ route('admin.competitions.panitia_lomba-details', $singleEvent) }}" class="p-6">
            @csrf
            @method('PATCH')
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-950">Format Submission Karya</h3>
                <p class="mt-1 text-sm text-gray-600">Tentukan kolom/isian apa saja yang harus dikumpulkan oleh peserta.</p>
            </div>
            
            <div class="mt-4 bg-blue-50 border border-blue-200 text-blue-800 text-sm p-4 rounded-md">
                <strong>Catatan:</strong> Mengubah format tidak akan menghapus data submission yang sudah ada. Kolom tabel akan otomatis menyesuaikan dengan data yang pernah dikirimkan peserta.
            </div>

            <div class="mt-5" x-data="{
                fields: ({{ json_encode($singleEvent->submission_fields ?? []) }} || []).map(f => ({ label: f.label || '', type: f.type || 'url' })),
                addField() {
                    this.fields.push({ label: '', type: 'url' });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                }
            }">
                <input type="hidden" name="submission_fields" x-bind:value="JSON.stringify(fields)">
                
                <div class="space-y-4">
                    <template x-for="(field, index) in fields" :key="index">
                        <div class="flex items-start gap-4 p-4 border border-gray-200 rounded-md bg-gray-50 relative">
                            <div class="flex-1 grid grid-cols-2 gap-4">
                                <label class="block">
                                    <span class="text-sm font-semibold text-gray-700">Label (Nama Isian) <span class="text-red-500">*</span></span>
                                    <input type="text" x-model="field.label" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Misal: Link GDrive / Proposal PDF">
                                </label>
                                <label class="block">
                                    <span class="text-sm font-semibold text-gray-700">Tipe Input <span class="text-red-500">*</span></span>
                                    <select x-model="field.type" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white">
                                        <option value="url">Link / URL (Drive, Youtube, web, dll)</option>
                                        <option value="file">Upload File (PDF, JPG, PNG - Max 20MB)</option>
                                    </select>
                                </label>
                            </div>
                            <button type="button" @click="removeField(index)" class="mt-6 text-red-600 hover:text-red-800 text-sm font-bold bg-white px-3 py-2 rounded border border-red-200 hover:bg-red-50" title="Hapus field">
                                Hapus
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="addField()" class="inline-flex items-center gap-2 rounded-md border border-dashed border-gray-400 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Field
                    </button>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-panitia_lomba-submission-{{ $singleEvent->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Simpan Format</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-deadline-{{ $singleEvent->id }}" maxWidth="lg" focusable>
        <form method="POST" action="{{ route('admin.competitions.submission-deadline', $singleEvent) }}" class="p-6">
            @csrf
            @method('PATCH')
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-950">Atur Deadline Submisi & Revisi</h3>
                <p class="mt-1 text-sm text-gray-600">Pilih timeline yang akan digunakan sebagai batas waktu pengumpulan karya.</p>
            </div>
            
            <div class="mt-5 space-y-4">
                @php
                    $activeTimeline = $singleEvent->timelines->where('is_submission', true)->first();
                @endphp
                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Pilih Timeline <span class="text-red-500">*</span></span>
                    <select
                        name="timeline_id"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white"
                    >
                        <option value="">-- Tidak ada (Tutup Submisi) --</option>
                        @foreach($singleEvent->timelines as $timeline)
                            <option value="{{ $timeline->id }}" {{ $activeTimeline?->id === $timeline->id ? 'selected' : '' }}>
                                {{ $timeline->title }} ({{ $timeline->date->format('d M Y') }} - {{ $timeline->end_date ? $timeline->end_date->format('d M Y') : 'Selesai' }})
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-deadline-{{ $singleEvent->id }}')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Deadline</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
