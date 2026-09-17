<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Mendaftarkan alias middleware untuk tugas Orang ke-3
        $middleware->alias([
            /**
             * REQ-09: Middleware untuk mengunci data peserta (Data Freezing Logic)
             * Menjamin data tidak bisa diubah jika is_verified = true
             */
            'data_frozen' => \App\Http\Middleware\EnsureDataNotFrozen::class,
        ]);

        $trustedProxies = env('TRUSTED_PROXIES');
        if ($trustedProxies) {
            $middleware->trustProxies(at: $trustedProxies === '*' ? '*' : explode(',', $trustedProxies));
        }

        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'redirect' => route('login'),
                ], 419);
            }

            if ($request->isMethod('get')) {
                return redirect()->guest(route('login'))
                    ->with('status', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }

            return redirect()->route('login')
                ->with('status', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
    })->create();