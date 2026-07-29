<x-admin.layout
    title="Verifikasi Bukti Bayar"
    subtitle="Validasi bukti pembayaran peserta event non-kompetisi."
>
    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <x-admin.stat-card label="Pending" :value="$pendingCount" tone="amber" />
        <x-admin.stat-card label="Accepted" :value="$acceptedCount" tone="emerald" />
        <x-admin.stat-card label="Rejected" :value="$rejectedCount" tone="rose" />
    </div>

    <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Antrean Peserta</h2>
                </div>
                <form method="GET" action="{{ route('admin.event-participants.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                            </svg>
                        </span>
                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama/email/no. telp..."
                            class="block w-full sm:w-auto pl-10 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="sr-only">Filter Event</label>
                        <select name="event_id" onchange="this.form.submit()" class="block w-full sm:w-auto rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected(request('event_id') === $event->id)>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sr-only">Filter Status</label>
                        <select name="status" onchange="this.form.submit()" class="block w-full sm:w-auto rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="default" @selected($filterStatus === 'default')>Pending & Rejected</option>
                            <option value="all" @selected($filterStatus === 'all')>Semua Status</option>
                            <option value="pending" @selected($filterStatus === 'pending')>Pending</option>
                            <option value="accepted" @selected($filterStatus === 'accepted')>Accepted</option>
                            <option value="rejected" @selected($filterStatus === 'rejected')>Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="hidden">Submit</button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peserta</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Bukti Transfer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($participants as $participant)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-950">{{ $participant->full_name }}</p>
                                <p class="text-sm text-gray-600">{{ $participant->email }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($participant->date_added)->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $participant->event_title }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($participant->payment_proof)
                                    <div class="space-y-1">
                                        <p class="font-medium text-gray-950">File Terunggah</p>
                                        <a href="{{ Storage::url($participant->payment_proof) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                            Lihat Gambar
                                        </a>
                                    </div>
                                @else
                                    <span class="text-gray-500">Belum ada bukti transfer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-admin.status-badge :status="$participant->payment_verification" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    @if($participant->payment_verification !== 'accepted')
                                        <form method="POST" action="{{ route('admin.event-participants.verify') }}">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                                            <input type="hidden" name="event_id" value="{{ $participant->event_id }}">
                                            <button type="submit" name="action" value="accept" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                                Accept
                                            </button>
                                        </form>
                                    @endif

                                    @if($participant->payment_verification !== 'rejected')
                                        <form method="POST" action="{{ route('admin.event-participants.verify') }}">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                                            <input type="hidden" name="event_id" value="{{ $participant->event_id }}">
                                            <button type="submit" name="action" value="reject" class="rounded-md bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                                                Reject
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-600">Belum ada data peserta yang perlu diverifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($participants->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                {{ $participants->links() }}
            </div>
        @endif
    </section>
</x-admin.layout>
