@php
    $isStaging = app()->environment('staging') 
        || (bool) env('IS_STAGING', false) 
        || str_contains(config('app.url', ''), 'staging')
        || str_contains(request()->getHost(), 'staging');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ ($isStaging ? '[STAGING] ' : '') . config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <x-staging-banner />
        <div class="min-h-screen bg-white">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <x-confirm-danger-modal />

        @if($isStaging)
            <div class="fixed bottom-4 right-4 z-50 pointer-events-none select-none">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-600/90 text-white text-xs font-black shadow-xl backdrop-blur-sm border border-amber-300/60 uppercase tracking-widest">
                    <span class="h-2 w-2 rounded-full bg-amber-200 animate-ping"></span>
                    <span>STAGING</span>
                </div>
            </div>
        @endif
    </body>
</html>
