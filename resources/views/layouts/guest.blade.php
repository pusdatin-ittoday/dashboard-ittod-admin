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
    <body class="font-sans text-gray-900 antialiased">
        <x-staging-banner />
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/" class="flex flex-col items-center gap-2">
                    <x-application-logo class="h-24 w-auto object-contain" />
                    @if($isStaging)
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-black bg-amber-500 text-white shadow uppercase tracking-wider">
                            <span class="h-1.5 w-1.5 rounded-full bg-white animate-ping"></span>
                            STAGING ENVIRONMENT
                        </span>
                    @endif
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
