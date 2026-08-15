<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center me-4 sm:me-6">
                    <a href="{{ route('admin.dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 lg:space-x-2 xl:space-x-3 sm:-my-px sm:flex items-center">
                    <!-- Dashboard -->
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('admin.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- List Peserta -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Peserta') }}
                        </x-nav-link>
                    @endif

                    <!-- Peserta Kegiatan / Event -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                        <x-nav-link :href="route('admin.event-participants.index')" :active="request()->routeIs('admin.event-participants.*')">
                            {{ __('Peserta Event') }}
                        </x-nav-link>
                    @endif

                    <!-- List Tim (Read-Only Overview) -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                        <x-nav-link :href="route('admin.teams-list.index')" :active="request()->routeIs('admin.teams-list.*')">
                            {{ __('List Tim') }}
                        </x-nav-link>
                    @endif

                    <!-- Event & Lomba -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'panitia_lomba', 'admin_biasa']))
                        <x-nav-link :href="route('admin.timelines.index')" :active="request()->routeIs('admin.timelines.*')">
                            {{ __('Event & Lomba') }}
                        </x-nav-link>
                    @endif

                    <!-- Berkas & Tim -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'panitia_lomba']))
                        <x-nav-link :href="route('operation.teams.index')" :active="request()->routeIs('operation.teams.*') || request()->routeIs('admin.files-participants.*')">
                            {{ __('Berkas & Tim') }}
                        </x-nav-link>
                    @endif

                    <!-- Verifikasi Pembayaran -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa']))
                        <x-nav-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')">
                            {{ __('Pembayaran') }}
                        </x-nav-link>
                    @endif

                    <!-- Pengumuman -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                        <x-nav-link :href="route('admin.announcements.index')" :active="request()->routeIs('admin.announcements.*')">
                            {{ __('Pengumuman') }}
                        </x-nav-link>
                    @endif

                    <!-- Feedback Peserta -->
                    @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                        <x-nav-link :href="route('admin.feedback.index')" :active="request()->routeIs('admin.feedback.*')">
                            {{ __('Feedback') }}
                        </x-nav-link>
                    @endif

                    <!-- Staff: Only Superadmin -->
                    @if(Auth::check() && Auth::user()->role === 'superadmin')
                        <x-nav-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff.*')">
                            {{ __('Staff') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-4">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <span class="whitespace-nowrap">{{ Auth::user()?->user?->full_name ?? Auth::user()?->email }}</span>

                            <div class="ms-1.5">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Role Badge -->
                        <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-500 font-semibold uppercase tracking-wider">
                            Role: {{ Auth::user()?->role }}
                        </div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('admin.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('List Peserta') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.event-participants.index')" :active="request()->routeIs('admin.event-participants.*')">
                    {{ __('Peserta Event') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.teams-list.index')" :active="request()->routeIs('admin.teams-list.*')">
                    {{ __('List Tim') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'panitia_lomba', 'admin_biasa']))
                <x-responsive-nav-link :href="route('admin.timelines.index')" :active="request()->routeIs('admin.timelines.*')">
                    {{ __('Event & Lomba') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'panitia_lomba']))
                <x-responsive-nav-link :href="route('operation.teams.index')" :active="request()->routeIs('operation.teams.*') || request()->routeIs('admin.files-participants.*')">
                    {{ __('Berkas & Tim') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa']))
                <x-responsive-nav-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')">
                    {{ __('Verifikasi Pembayaran') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::check() && in_array(Auth::user()->role, ['superadmin', 'admin_biasa', 'panitia_lomba']))
                <x-responsive-nav-link :href="route('admin.announcements.index')" :active="request()->routeIs('admin.announcements.*')">
                    {{ __('Pengumuman') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.feedback.index')" :active="request()->routeIs('admin.feedback.*')">
                    {{ __('Feedback') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::check() && Auth::user()->role === 'superadmin')
                <x-responsive-nav-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff.*')">
                    {{ __('Manajemen Staff') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()?->user?->full_name ?? Auth::user()?->email }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()?->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
