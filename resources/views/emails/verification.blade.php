<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Verifikasi Pembayaran - {{ $team->team_name }}</title>
    <style>
        body {
            font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            border: 1px solid #e5e7eb;
        }
        .header {
            padding: 32px;
            text-align: center;
            color: #ffffff;
        }
        .header.verified {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .header.rejected {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 32px;
            color: #374151;
            line-height: 1.6;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 16px;
        }
        .details-box {
            background-color: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .details-row:last-child {
            margin-bottom: 0;
        }
        .details-label {
            font-weight: 600;
            color: #6b7280;
        }
        .details-value {
            font-weight: 700;
            color: #1f2937;
        }
        .note-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
            font-size: 14px;
        }
        .note-title {
            font-weight: 700;
            margin-bottom: 4px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
        }
        .footer a {
            color: #6b7280;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        @if ($team->is_verified === 1)
            <!-- Approved Header -->
            <div class="header verified">
                <h1>Pembayaran Berhasil Diverifikasi</h1>
            </div>
            <div class="content">
                <p>Halo Ketua Tim,</p>
                <p>Kami ingin menginformasikan bahwa pembayaran pendaftaran tim Anda telah berhasil diverifikasi oleh Admin Keuangan IT Today. Selamat! Tim Anda sekarang resmi terdaftar.</p>
                
                <div class="details-box">
                    <div class="details-row">
                        <span class="details-label">Nama Tim:</span>
                        <span class="details-value">{{ $team->team_name }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Kode Tim:</span>
                        <span class="details-value"><code>{{ $team->team_code }}</code></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Cabang Lomba:</span>
                        <span class="details-value">{{ $team->event->title }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Status:</span>
                        <span class="details-value" style="color: #10b981;">Terverifikasi</span>
                    </div>
                </div>

                <p>Silakan masuk ke dashboard IT Today untuk mengunggah berkas kompetisi dan memantau timeline kegiatan terbaru.</p>
            </div>
        @else
            <!-- Rejected Header -->
            <div class="header rejected">
                <h1>Pembayaran Ditolak</h1>
            </div>
            <div class="content">
                <p>Halo Ketua Tim,</p>
                <p>Mohon maaf, bukti transfer pembayaran pendaftaran yang Anda unggah untuk tim Anda tidak dapat diverifikasi oleh Admin Keuangan kami.</p>
                
                <div class="details-box">
                    <div class="details-row">
                        <span class="details-label">Nama Tim:</span>
                        <span class="details-value">{{ $team->team_name }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Cabang Lomba:</span>
                        <span class="details-value">{{ $team->event->title }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Status:</span>
                        <span class="details-value" style="color: #ef4444;">Ditolak</span>
                    </div>
                </div>

                <div class="note-box">
                    <div class="note-title">Catatan dari Admin:</div>
                    <div>{{ $team->verification_error ?? 'Bukti transfer tidak sesuai atau tidak valid.' }}</div>
                </div>

                <p>Silakan masuk kembali ke dashboard IT Today Anda untuk mengunggah ulang bukti transfer pembayaran yang valid agar kami dapat segera memproses ulang pendaftaran tim Anda.</p>
            </div>
        @endif

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem IT Today 2026. Jangan membalas email ini.</p>
            <p>&copy; 2026 IT Today. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
