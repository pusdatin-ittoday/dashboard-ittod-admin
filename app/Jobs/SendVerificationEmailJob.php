<?php

namespace App\Jobs;

use App\Mail\VerificationStatusMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $team;

    // Menangkap data tim dari Service
    public function __construct($team)
    {
        $this->team = $team;
    }

    public function handle()
    {
        // 1. Ambil data member yang rolenya 'leader' dari tim ini
        $leaderMember = $this->team->members()->where('role', 'leader')->first();
        
        // 2. Ambil data user/identitas dari member tersebut
        $leaderUser = $leaderMember ? $leaderMember->user : null;
        
        // 3. Ambil emailnya
        $email = $leaderUser ? $leaderUser->email : null;

        // 4. Jalankan pengiriman email jika emailnya ketemu
        if ($email) {
            Mail::to($email)->send(new VerificationStatusMail($this->team));
        } else {
            // Log error opsional jika tiba-tiba email gaada di database
            \Illuminate\Support\Facades\Log::warning("Email ketua untuk tim ID {$this->team->id} tidak ditemukan.");
        }
    }
}