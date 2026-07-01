<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDataNotFrozen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // REQ-09: Pengunci Data (Data Freezing Logic) untuk role user (peserta)
        if ($user && $user->role === 'user') {
            $profile = $user->user;
            if ($profile) {
                $team = $profile->teams()->first();
                if ($team) {
                    $isVerified = $team->is_verified === 'approved';
                    $hasTeamError = !empty($team->verification_error);
                    $hasMemberError = $team->members()->whereNotNull('verification_error')->where('verification_error', '!=', '')->exists();

                    // Sedang diperiksa jika belum verifikasi (is_verified = false) dan belum ada catatan error
                    $isUnderReview = !$isVerified && !$hasTeamError && !$hasMemberError;

                    if ($isVerified || $isUnderReview) {
                        abort(403, 'Akses Ditolak: Data Anda telah dikunci karena sedang diperiksa atau telah terverifikasi.');
                    }
                }
            }
        }

        return $next($request);
    }
}