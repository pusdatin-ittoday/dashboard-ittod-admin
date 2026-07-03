<x-admin.layout
    title="Dashboard Operasional"
    subtitle="Ringkasan cepat antrean verifikasi, event aktif, dan pekerjaan admin."
>
    @if(in_array($userRole, ['superadmin', 'admin_biasa']) && $globalStats)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Statistik Global</h2>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-admin.stat-card label="Total Event" :value="$globalStats['events']" />
                <x-admin.stat-card label="Total Tim" :value="$globalStats['teams']" />
                <x-admin.stat-card label="Menunggu Verifikasi" :value="$globalStats['pendingTransactions']" tone="amber" />
                <x-admin.stat-card label="Ditolak" :value="$globalStats['rejectedTransactions']" tone="rose" />
            </div>
        </div>
    @endif

    @if(in_array($userRole, ['superadmin', 'panitia_lomba']) && $competitions)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                {{ $userRole === 'superadmin' ? 'Summary Kompetisi' : 'Kompetisi yang Dikelola' }}
            </h2>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($competitions as $competition)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 line-clamp-1" title="{{ $competition->title }}">
                            {{ $competition->title }}
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Total Tim:</span>
                                <span class="font-bold text-gray-900">{{ $competition->total_teams }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Terverifikasi:</span>
                                <span class="font-bold text-emerald-600">{{ $competition->verified_teams }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Menunggu:</span>
                                <span class="font-bold text-amber-500">{{ $competition->pending_teams }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Ditolak:</span>
                                <span class="font-bold text-rose-600">{{ $competition->rejected_teams }}</span>
                            </div>
                            @if($competition->requires_submission)
                                <div class="pt-3 mt-3 border-t border-gray-100 flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Tim Submit:</span>
                                    <span class="font-bold text-blue-600">{{ $competition->submitted_teams }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if($competitions->isEmpty())
                <p class="text-gray-500 italic mt-2">Belum ada kompetisi yang dikelola.</p>
            @endif
        </div>
    @endif

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-950">Akses Cepat (Quick Access)</h2>
            <p class="text-sm text-gray-600">Menu pintasan untuk menuju ke halaman kerja utama Anda.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @if(in_array($userRole, ['superadmin', 'panitia_lomba']))
                <a href="{{ route('operation.teams.index') }}" class="group block rounded-lg border border-gray-200 p-5 hover:border-indigo-500 hover:bg-indigo-50 transition-colors">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="rounded-md bg-indigo-100 p-2 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Berkas & Peserta</h3>
                    </div>
                    <p class="text-sm text-gray-600">Verifikasi kelengkapan berkas, data peserta, dan pantau status tim yang mendaftar.</p>
                </a>

                <a href="{{ route('admin.timelines.index') }}" class="group block rounded-lg border border-gray-200 p-5 hover:border-emerald-500 hover:bg-emerald-50 transition-colors">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="rounded-md bg-emerald-100 p-2 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">{{ $userRole === 'superadmin' ? 'Kelola Event dan Kompetisi' : 'Kelola Kompetisi' }}</h3>
                    </div>
                    <p class="text-sm text-gray-600">Atur deskripsi lomba, tautan guidebook, serta timeline untuk setiap event.</p>
                </a>
            @endif

            @if(in_array($userRole, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                <a href="{{ route('admin.announcements.index') }}" class="group block rounded-lg border border-gray-200 p-5 hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="rounded-md bg-blue-100 p-2 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Pengumuman</h3>
                    </div>
                    <p class="text-sm text-gray-600">Buat dan kelola informasi pengumuman yang akan disiarkan kepada peserta.</p>
                </a>
            @endif
            
            @if(in_array($userRole, ['superadmin', 'admin_biasa']))
                <a href="{{ route('admin.transactions.index') }}" class="group block rounded-lg border border-gray-200 p-5 hover:border-amber-500 hover:bg-amber-50 transition-colors">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="rounded-md bg-amber-100 p-2 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Transaksi</h3>
                    </div>
                    <p class="text-sm text-gray-600">Validasi bukti transfer pembayaran peserta dan perbarui status verifikasi tim.</p>
                </a>
            @endif
        </div>
    </section>
</x-admin.layout>
