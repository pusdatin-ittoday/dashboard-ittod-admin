<x-admin.layout
    title="Daftar Pengguna"
    subtitle="Admin dapat melihat daftar seluruh peserta umum (users)."
>
<div x-data="{ search: '' }">
    <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-950">Peserta Umum</h2>
                <span class="rounded border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-bold uppercase text-indigo-700">
                    Public Users
                </span>
            </div>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">View registered participants</p>
        </div>

        <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                <label class="sr-only">Filter Event</label>
                <select name="event_id" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Event</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected($filterEventId === $event->id)>{{ $event->title }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('export.users.global', ['event_id' => $filterEventId]) }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-bold uppercase text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Export CSV
            </a>
        </div>
    </div>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 text-xs font-bold text-gray-700">ID</span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gray-700">User Directory</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $users->total() }} users detected</p>
        </div>

        <div class="border-b border-gray-200 px-4 py-4">
            <label class="relative block">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"></path>
                    </svg>
                </span>
                <input
                    type="search"
                    x-model="search"
                    placeholder="Pencarian dilakukan pada halaman ini saja..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Email / Telp</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Instansi</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Status Registrasi</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Verifikasi Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($users as $userIdentity)
                        @php $user = $userIdentity->user; @endphp
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower(($user?->full_name ?? $userIdentity->email) . ' ' . $userIdentity->email . ' ' . ($user?->phone_number ?? '') . ' ' . ($user?->nama_sekolah ?? '')) }}"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-gray-950">{{ $user?->full_name ?? $userIdentity->email }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm text-gray-700">{{ $userIdentity->email }}</p>
                                @if ($user?->phone_number)
                                    <p class="text-xs text-gray-500 mt-1">{{ $user->phone_number }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{ $user?->nama_sekolah ?: '-' }}
                            </td>
                            <td class="px-4 py-4">
                                @if ($user?->is_registration_complete)
                                    <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                        Lengkap
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                                        Belum Lengkap
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase {{ $userIdentity->is_verified ? 'text-blue-700 bg-blue-50 border border-blue-200' : 'text-gray-500 bg-gray-50 border border-gray-200' }} px-2 py-1 rounded">
                                    {{ $userIdentity->is_verified ? 'Terverifikasi' : 'Belum' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-600">Belum ada akun peserta umum.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                {{ $users->links() }}
            </div>
        @endif
    </section>
</div>
</x-admin.layout>
