<?php

namespace App\Services;

use App\Jobs\SendVerificationEmailJob;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    public function verifyTransaction($teamId, $action, $reason = null)
    {
        // DB::transaction menjaga agar database aman kalau tiba-tiba ada error di tengah jalan
        return DB::transaction(function () use ($teamId, $action, $reason) {
            
            // Mencari data tim berdasarkan ID. 
            // (Sesuaikan nama '\App\Models\Team' dengan nama Model yang dibuat Orang 1)
            $team = \App\Models\Team::findOrFail($teamId);

            if ($action === 'verify') {
                $team->is_verified = 1; 
                $team->verification_error = null;
            } elseif ($action === 'reject') {
                $team->is_verified = 2; 
                $team->verification_error = $reason; 
            }

            $team->save();

            SendVerificationEmailJob::dispatch($team);

            return $team;
        });
    }
}