@php
    $roleLabels = [
        'superadmin' => 'Super Admin',
        'admin_keuangan' => 'Admin Keuangan',
        'panitia' => 'Admin Panitia',
    ];

    $roleBadgeClasses = [
        'superadmin' => 'border-orange-200 bg-orange-50 text-orange-700',
        'admin_keuangan' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'panitia' => 'border-sky-200 bg-sky-50 text-sky-700',
    ];

    $accessScopes = [
        'superadmin' => 'Semua event',
        'admin_keuangan' => 'Verifikasi Transaksi',
    ];
@endphp

<x-admin.layout
    title="Manajemen Akun Staff"
    subtitle="Superadmin dapat mengelola akun staff, role admin lain hanya dapat melihat."
>
<div x-data="{ search: '', createRole: @js(old('role', 'superadmin')), ...staffEditor() }">
    <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-950">Manajemen Admin</h2>
                <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">
                    Superadmin Restricted
                </span>
            </div>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-gray-700">Manage superadmin, admin keuangan, and admin panitia</p>
        </div>

        @if ($canManageStaff)
            <button
                type="button"
                x-data
                x-on:click="$dispatch('open-modal', 'create-staff')"
                class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-bold uppercase text-white shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
            >
                Add Admin
            </button>
        @endif
    </div>

    @if ($canManageStaff && $errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Data belum bisa disimpan.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 text-xs font-bold text-gray-700">ID</span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gray-700">Admin Directory</p>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $staffAccounts->count() }} admins detected</p>
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
                    placeholder="Search admin by name, email, access level, or event..."
                    class="w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Access Level</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-600">Status</th>
                        @if ($canManageStaff)
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase text-gray-600">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($staffAccounts as $staff)
                        <tr
                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ Str::lower(($staff->user?->full_name ?? $staff->email) . ' ' . $staff->email . ' ' . ($roleLabels[$staff->role] ?? $staff->role) . ' ' . ($accessScopes[$staff->role] ?? $staff->events->pluck('title')->join(' ')) . ' ' . ($staff->is_verified ? 'aktif' : 'nonaktif')) }}"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-gray-950">{{ $staff->user?->full_name ?? $staff->email }}</p>
                                    @if (auth()->id() === $staff->id)
                                        <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700">Your Account</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $staff->email }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded border px-2 py-1 text-[11px] font-bold uppercase {{ $roleBadgeClasses[$staff->role] ?? 'border-gray-200 bg-gray-50 text-gray-700' }}">
                                    {{ $roleLabels[$staff->role] ?? str_replace('_', ' ', $staff->role) }}
                                </span>
                            </td>
                            <td class="max-w-xs px-4 py-4 text-sm text-gray-700">
                                {{ $accessScopes[$staff->role] ?? ($staff->events->pluck('title')->join(', ') ?: 'Belum ditugaskan') }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-2 text-sm font-bold uppercase {{ $staff->is_verified ? 'text-emerald-700' : 'text-gray-500' }}">
                                    {{ $staff->is_verified ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            @if ($canManageStaff)
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            x-on:click="fetchStaff(@js(route('admin.staff.show', $staff)))"
                                            x-bind:disabled="isFetchingStaff"
                                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                        >
                                            <span x-text="isFetchingStaff ? 'Loading' : 'Edit'">Edit</span>
                                        </button>

                                        <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}" onsubmit="return confirm('Hapus akun staff ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                @disabled(auth()->id() === $staff->id)
                                                class="rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 disabled:hover:bg-white"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageStaff ? 6 : 5 }}" class="px-4 py-10 text-center text-sm text-gray-600">Belum ada akun staff.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canManageStaff)
    <x-modal name="create-staff" maxWidth="2xl" focusable>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="p-6">
            @csrf

            <div class="flex items-start justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950">Tambah Admin</h3>
                    <p class="mt-1 text-sm text-gray-600">Buat akun staff baru dan tentukan aksesnya.</p>
                </div>
                <button type="button" x-on:click="$dispatch('close-modal', 'create-staff')" class="rounded-md px-2 py-1 text-sm font-semibold text-gray-500 hover:bg-gray-100">Tutup</button>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Nama</span>
                    <input name="full_name" value="{{ old('full_name') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Password</span>
                    <input type="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Konfirmasi Password</span>
                    <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Role</span>
                    <select name="role" x-model="createRole" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach ($roleLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-3">
                    <input type="hidden" name="is_verified" value="0">
                    <input type="checkbox" name="is_verified" value="1" checked class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-semibold text-gray-700">Aktif dan bisa login</span>
                </label>
            </div>

            <template x-if="createRole === 'panitia'">
                <div class="mt-5">
                    <p class="text-sm font-semibold text-gray-700">Kompetisi yang dikelola</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @forelse ($events as $event)
                            <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>{{ $event->title }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada kompetisi.</p>
                        @endforelse
                    </div>
                </div>
            </template>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-staff')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit-staff" maxWidth="2xl" focusable>
        <form method="POST" x-bind:action="form.action" class="p-6">
            @csrf
            @method('PATCH')

            <div class="flex items-start justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950">Edit Admin</h3>
                    <p class="mt-1 text-sm text-gray-600" x-text="form.email"></p>
                </div>
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-staff')" class="rounded-md px-2 py-1 text-sm font-semibold text-gray-500 hover:bg-gray-100">Tutup</button>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Nama</span>
                    <input name="full_name" x-model="form.full_name" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Email</span>
                    <input type="email" name="email" x-model="form.email" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Password Baru</span>
                    <input type="password" name="password" x-model="form.password" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Konfirmasi Password Baru</span>
                    <input type="password" name="password_confirmation" x-model="form.password_confirmation" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </label>

                <label class="block">
                    <span class="text-sm font-semibold text-gray-700">Role</span>
                    <select name="role" x-model="form.role" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach ($roleLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-3">
                    <input type="hidden" name="is_verified" value="0">
                    <input type="checkbox" name="is_verified" value="1" x-model="form.is_verified" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-semibold text-gray-700">Aktif dan bisa login</span>
                </label>
            </div>

            <template x-if="form.role === 'panitia'">
                <div class="mt-5">
                    <p class="text-sm font-semibold text-gray-700">Kompetisi yang dikelola</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @forelse ($events as $event)
                            <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="event_ids[]"
                                    value="{{ $event->id }}"
                                    x-model="form.event_ids"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                >
                                <span>{{ $event->title }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada kompetisi.</p>
                        @endforelse
                    </div>
                </div>
            </template>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-staff')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Perubahan</button>
            </div>
        </form>
    </x-modal>
    @endif
</div>

<script>
    window.staffEditor = function () {
        return {
            isFetchingStaff: false,
            form: {
                action: '',
                full_name: '',
                email: '',
                password: '',
                password_confirmation: '',
                role: 'panitia',
                is_verified: true,
                event_ids: [],
            },
            async fetchStaff(url) {
                this.isFetchingStaff = true;

                try {
                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    if (! response.ok) {
                        throw new Error('Gagal mengambil data staff.');
                    }

                    const staff = await response.json();

                    this.form = {
                        action: staff.update_url,
                        full_name: staff.full_name,
                        email: staff.email,
                        password: '',
                        password_confirmation: '',
                        role: staff.role,
                        is_verified: staff.is_verified,
                        event_ids: staff.event_ids.map(String),
                    };

                    this.$dispatch('open-modal', 'edit-staff');
                } catch (error) {
                    alert(error.message);
                } finally {
                    this.isFetchingStaff = false;
                }
            },
        };
    };
</script>
</x-admin.layout>
