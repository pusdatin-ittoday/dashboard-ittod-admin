<?php

namespace App\Http\Controllers;

use App\Services\VerificationService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $verificationService;

    // Menghubungkan VerificationService ke Controller ini
    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * UC-02: Verifikasi Pembayaran (Approve / Reject)
     */
    public function verify(Request $request, $teamId)
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin_keuangan', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Hanya Admin Keuangan dan Superadmin yang dapat mengakses fitur ini.'
            ], 403);
        }

        // Validasi input kiriman dari Frontend (Orang 4)
        $request->validate([
            'action' => 'required|in:verify,reject',
            // Kalau action-nya reject, admin wajib mengisi alasan error-nya
            'verification_error' => 'required_if:action,reject|string|nullable',
        ]);

        try {
            // Menyuruh VerificationService untuk memproses logikanya
            $team = $this->verificationService->verifyTransaction(
                $teamId,
                $request->action,
                $request->verification_error
            );

            // Sesuai Activity Diagram: Beri respon sukses ke Frontend
            return response()->json([
                'success' => true,
                'message' => 'Verifikasi pembayaran berhasil diproses.',
                'data' => $team
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * UC-03: Lihat Rekap Transaksi Dana (Fungsi Recap)
     */
    public function getRecap()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin_keuangan', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Hanya Admin Keuangan dan Superadmin yang dapat mengakses fitur ini.'
            ], 403);
        }

        try {
            // Menghitung total dana dari tim yang sudah diverifikasi (is_verified = 1)
            // Di sini kita join ke tabel 'event' melalui 'competition_id' untuk mengambil data harganya
            // (Asumsi: Di tabel 'event' milik Orang 1 ada kolom bernama 'price' atau 'fee')
            $totalDana = \App\Models\Team::where('is_verified', 1)
                ->join('event', 'team.competition_id', '=', 'event.id')
                ->sum('event.price'); // <-- Ganti 'price' kalau Orang 1 pakai nama lain (misal: 'registration_fee')

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data rekap akumulasi dana.',
                'total_accumulated_funds' => $totalDana
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil rekap dana: ' . $e->getMessage()
            ], 500);
        }
    }
}