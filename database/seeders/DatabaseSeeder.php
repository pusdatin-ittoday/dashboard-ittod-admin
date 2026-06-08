<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserIdentity;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Media;
use App\Models\CompetitionSubmission;
use App\Models\EventTimeline;
use App\Models\EventAnnouncement;
use App\Models\EventParticipant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Event::create([
            'id' => 'HackToday',
            'title' => 'HackToday',
            'description' => 'Kompetisi Capture the Flag (CTF) tingkat nasional untuk menguji kemampuan analisis dan eksploitasi keamanan siber.',
            'guide_book_url' => 'https://ittoday.web.id/guidebook-hacktoday.pdf',
            'type' => 'competition',
            'price' => 150000,
            'contact_person1' => 'wa.me/628123456780',
            'contact_person2' => 'wa.me/628123456781',
            'max_noncompetition_participant' => null,
        ]);

        Event::create([
            'id' => 'UXToday',
            'title' => 'UX Today',
            'description' => 'Kompetisi perancangan antarmuka pengguna (UI/UX) tingkat nasional untuk menghadirkan solusi digital kreatif.',
            'guide_book_url' => 'https://ittoday.web.id/guidebook-uxtoday.pdf',
            'type' => 'competition',
            'price' => 100000,
            'contact_person1' => 'wa.me/628123456782',
            'contact_person2' => 'wa.me/628123456783',
            'max_noncompetition_participant' => null,
        ]);

        Event::create([
            'id' => 'ITBrains',
            'title' => 'IT-Brains',
            'description' => 'Kompetisi analisis bisnis IT dan pemecahan masalah (IT Case Study) berskala nasional.',
            'guide_book_url' => 'https://ittoday.web.id/guidebook-itbrains.pdf',
            'type' => 'competition',
            'price' => 120000,
            'contact_person1' => 'wa.me/628123456784',
            'contact_person2' => 'wa.me/628123456785',
            'max_noncompetition_participant' => null,
        ]);

        Event::create([
            'id' => 'GameToday',
            'title' => 'GameToday',
            'description' => 'Kompetisi perancangan dan pengembangan game tingkat nasional untuk memamerkan kreativitas dan gameplay inovatif.',
            'guide_book_url' => 'https://ittoday.web.id/guidebook-gametoday.pdf',
            'type' => 'competition',
            'price' => 130000,
            'contact_person1' => 'wa.me/628123456786',
            'contact_person2' => 'wa.me/628123456787',
            'max_noncompetition_participant' => null,
        ]);

        Event::create([
            'id' => 'Seminar',
            'title' => 'Seminar Nasional IT Today',
            'description' => 'Seminar teknologi nasional yang menghadirkan tokoh dan ahli IT terkemuka untuk membahas tren teknologi terbaru.',
            'guide_book_url' => 'https://ittoday.web.id/guidebook-seminar.pdf',
            'type' => 'non_competition',
            'price' => 50000,
            'contact_person1' => 'wa.me/628123456788',
            'contact_person2' => null,
            'max_noncompetition_participant' => 500,
        ]);

        Event::create([
            'id' => 'Exhibition',
            'title' => 'IT Exhibition',
            'description' => 'Pameran teknologi karya mahasiswa dan startup.',
            'guide_book_url' => 'https://ittoday.web.id/guidebook-exhibition.pdf',
            'type' => 'non_competition',
            'price' => 0,
            'contact_person1' => 'wa.me/628123456789',
            'contact_person2' => null,
            'max_noncompetition_participant' => 1000,
        ]);




        // ==========================================
        // TESTING ACCOUNTS
        // ==========================================

        // Akun Superadmin 1 (superadmin)
        $superadminUser = User::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'email' => 'superadmin@ittoday.id',
            'full_name' => 'Superadmin IT Today',
            'is_registration_complete' => 1,
        ]);

        UserIdentity::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'email' => 'superadmin@ittoday.id',
            'provider' => 'basic',
            'hash' => Hash::make('superadmin'),
            'role' => 'superadmin',
            'is_verified' => 1,
            'verification_token' => Str::random(40),
            'verification_token_expiration' => now()->addYear(),
        ]);

        // Akun Admin 1 (admin)
        $adminUser = User::create([
            'id' => '22222222-2222-2222-2222-222222222222',
            'email' => 'admin@ittoday.id',
            'full_name' => 'Admin IT Today',
            'is_registration_complete' => 1,
        ]);

        UserIdentity::create([
            'id' => '22222222-2222-2222-2222-222222222222',
            'email' => 'admin@ittoday.id',
            'provider' => 'basic',
            'hash' => Hash::make('admin'),
            'role' => 'admin_keuangan',
            'is_verified' => 1,
            'verification_token' => Str::random(40),
            'verification_token_expiration' => now()->addYear(),
        ]);

        // Akun Panitia 1 (HackToday)
        $panitiaUser = User::create([
            'id' => '33333333-3333-3333-3333-333333333333',
            'email' => 'panitia@ittoday.id',
            'full_name' => 'Panitia HackToday',
            'is_registration_complete' => 1,
        ]);
        $panitiaIdentity = UserIdentity::create([
            'id' => '33333333-3333-3333-3333-333333333333', 'email' => 'panitia@ittoday.id', 'provider' => 'basic', 'hash' => Hash::make('panitia'),
            'role' => 'panitia', 'is_verified' => 1, 'verification_token' => Str::random(40), 'verification_token_expiration' => now()->addYear(),
        ]);
        $panitiaIdentity->events()->attach(['HackToday']);

        // Akun Panitia 2 (UX Today)
        $panitiaUxUser = User::create([
            'id' => '44444444-4444-4444-4444-444444444444',
            'email' => 'panitia.uxtoday@ittoday.id',
            'full_name' => 'Panitia UX Today',
            'is_registration_complete' => 1,
        ]);
        $panitiaUxIdentity = UserIdentity::create([
            'id' => '44444444-4444-4444-4444-444444444444', 'email' => 'panitia.uxtoday@ittoday.id', 'provider' => 'basic', 'hash' => Hash::make('panitia'),
            'role' => 'panitia', 'is_verified' => 1, 'verification_token' => Str::random(40), 'verification_token_expiration' => now()->addYear(),
        ]);
        $panitiaUxIdentity->events()->attach(['UXToday']);

        // Akun Panitia 3 (ITBrains)
        $panitiaItbrainsUser = User::create([
            'id' => '55555555-5555-5555-5555-555555555555',
            'email' => 'panitia.itbrains@ittoday.id',
            'full_name' => 'Panitia ITBrains',
            'is_registration_complete' => 1,
        ]);
        $panitiaItbrainsIdentity = UserIdentity::create([
            'id' => '55555555-5555-5555-5555-555555555555', 'email' => 'panitia.itbrains@ittoday.id', 'provider' => 'basic', 'hash' => Hash::make('panitia'),
            'role' => 'panitia', 'is_verified' => 1, 'verification_token' => Str::random(40), 'verification_token_expiration' => now()->addYear(),
        ]);
        $panitiaItbrainsIdentity->events()->attach(['ITBrains']);

        // Akun Panitia 4 (GameToday)
        $panitiaGameTodayUser = User::create([
            'id' => '66666666-6666-6666-6666-666666666666',
            'email' => 'panitia.gametoday@ittoday.id',
            'full_name' => 'Panitia GameToday',
            'is_registration_complete' => 1,
        ]);
        $panitiaGameTodayIdentity = UserIdentity::create([
            'id' => '66666666-6666-6666-6666-666666666666', 'email' => 'panitia.gametoday@ittoday.id', 'provider' => 'basic', 'hash' => Hash::make('panitia'),
            'role' => 'panitia', 'is_verified' => 1, 'verification_token' => Str::random(40), 'verification_token_expiration' => now()->addYear(),
        ]);
        $panitiaGameTodayIdentity->events()->attach(['GameToday']);

        // Akun Panitia 5 (Seminar - Non-Competition)
        $panitiaSeminarUser = User::create([
            'id' => '77777777-7777-7777-7777-777777777777',
            'email' => 'panitia.seminar@ittoday.id',
            'full_name' => 'Panitia Seminar',
            'is_registration_complete' => 1,
        ]);
        $panitiaSeminarIdentity = UserIdentity::create([
            'id' => '77777777-7777-7777-7777-777777777777', 'email' => 'panitia.seminar@ittoday.id', 'provider' => 'basic', 'hash' => Hash::make('panitia'),
            'role' => 'panitia', 'is_verified' => 1, 'verification_token' => Str::random(40), 'verification_token_expiration' => now()->addYear(),
        ]);
        $panitiaSeminarIdentity->events()->attach(['Seminar']);

        // Akun Panitia 6 (Exhibition - Non-Competition)
        $panitiaExhibitionUser = User::create([
            'id' => '88888888-8888-8888-8888-888888888888',
            'email' => 'panitia.exhibition@ittoday.id',
            'full_name' => 'Panitia Exhibition',
            'is_registration_complete' => 1,
        ]);
        $panitiaExhibitionIdentity = UserIdentity::create([
            'id' => '88888888-8888-8888-8888-888888888888', 'email' => 'panitia.exhibition@ittoday.id', 'provider' => 'basic', 'hash' => Hash::make('panitia'),
            'role' => 'panitia', 'is_verified' => 1, 'verification_token' => Str::random(40), 'verification_token_expiration' => now()->addYear(),
        ]);
        $panitiaExhibitionIdentity->events()->attach(['Exhibition']);

        // Generate 10 Peserta Users
        $peserta = [];
        $mediaKtm = [];
        for ($i = 1; $i <= 10; $i++) {
            $userId = (string) Str::uuid();
            $user = User::create([
                'id' => $userId,
                'email' => "peserta{$i}@gmail.com",
                'full_name' => "Peserta Dummy {$i}",
                'is_registration_complete' => 1,
            ]);
            UserIdentity::create([
                'id' => $userId, 'email' => "peserta{$i}@gmail.com", 'provider' => 'basic', 'hash' => Hash::make('password'),
                'role' => 'user', 'is_verified' => 1, 'verification_token' => Str::random(40), 'verification_token_expiration' => now()->addYear(),
            ]);
            $peserta[$i] = $user;
            
            $ktmId = (string) Str::uuid();
            $mediaKtm[$i] = $ktmId;
            Media::create([
                'id' => $ktmId,
                'uploader_id' => $userId,
                'name' => "KTM Peserta {$i}",
                'grouping' => 'dokum_tahun_lalu',
                'type' => 'image',
                'url' => "https://placehold.co/700x450/png?text=KTM+Peserta+{$i}",
            ]);
        }

        // Create 5 Payment Proof Media
        $mediaPay = [];
        for ($i = 1; $i <= 5; $i++) {
            $payId = (string) Str::uuid();
            $mediaPay[$i] = $payId;
            Media::create([
                'id' => $payId,
                'uploader_id' => $peserta[$i]->id,
                'name' => "Bukti Bayar Tim {$i}",
                'grouping' => 'payments',
                'type' => 'image',
                'url' => "https://placehold.co/500x800/png?text=Bukti+Bayar+Tim+{$i}",
            ]);
        }

        // ==========================================
        // SCENARIOS
        // ==========================================

        // Tim 1: Pending Berkas (HackToday, 2 Members)
        $t1 = (string) Str::uuid();
        Team::create([
            'id' => $t1, 'competition_id' => 'HackToday', 'team_name' => 'Tim Pending Berkas', 'team_code' => 'T1-PEND', 'max_member' => 3,
            'is_document_verified' => 0, 'is_verified' => 0, 'payment_proof_id' => $mediaPay[1],
        ]);
        TeamMember::create(['user_id' => $peserta[1]->id, 'team_id' => $t1, 'role' => 'leader', 'kartu_id' => $mediaKtm[1]]);
        TeamMember::create(['user_id' => $peserta[2]->id, 'team_id' => $t1, 'role' => 'member', 'kartu_id' => $mediaKtm[2]]);

        // Tim 2: Berkas Ditolak (UX Today, 1 Member)
        $t2 = (string) Str::uuid();
        Team::create([
            'id' => $t2, 'competition_id' => 'UXToday', 'team_name' => 'Tim Ditolak KTM', 'team_code' => 'T2-REJK', 'max_member' => 3,
            'is_document_verified' => 0, 'is_verified' => 0, 'payment_proof_id' => $mediaPay[2],
        ]);
        TeamMember::create([
            'user_id' => $peserta[3]->id, 'team_id' => $t2, 'role' => 'leader', 'kartu_id' => $mediaKtm[3],
            'verification_error' => 'KTM buram, tolong unggah ulang.'
        ]);

        // Tim 3: Menunggu Pembayaran (HackToday, 3 Members)
        $t3 = (string) Str::uuid();
        Team::create([
            'id' => $t3, 'competition_id' => 'HackToday', 'team_name' => 'Tim Menunggu Bayar', 'team_code' => 'T3-WAIT', 'max_member' => 3,
            'is_document_verified' => 1, 'is_verified' => 0, 'payment_proof_id' => $mediaPay[3],
        ]);
        TeamMember::create(['user_id' => $peserta[4]->id, 'team_id' => $t3, 'role' => 'leader', 'kartu_id' => $mediaKtm[4]]);
        TeamMember::create(['user_id' => $peserta[5]->id, 'team_id' => $t3, 'role' => 'member', 'kartu_id' => $mediaKtm[5]]);
        TeamMember::create(['user_id' => $peserta[6]->id, 'team_id' => $t3, 'role' => 'member', 'kartu_id' => $mediaKtm[6]]);

        // Tim 4: Pembayaran Ditolak (ITBrains, 2 Members)
        $t4 = (string) Str::uuid();
        Team::create([
            'id' => $t4, 'competition_id' => 'ITBrains', 'team_name' => 'Tim Ditolak Uang', 'team_code' => 'T4-NMON', 'max_member' => 3,
            'is_document_verified' => 1, 'is_verified' => 0, 'payment_proof_id' => $mediaPay[4],
            'verification_error' => 'Nominal transfer kurang Rp 50.000',
        ]);
        TeamMember::create(['user_id' => $peserta[7]->id, 'team_id' => $t4, 'role' => 'leader', 'kartu_id' => $mediaKtm[7]]);
        TeamMember::create(['user_id' => $peserta[8]->id, 'team_id' => $t4, 'role' => 'member', 'kartu_id' => $mediaKtm[8]]);

        // Tim 5: Lolos Sepenuhnya (HackToday, 1 Member)
        $t5 = (string) Str::uuid();
        Team::create([
            'id' => $t5, 'competition_id' => 'HackToday', 'team_name' => 'Tim Valid 100%', 'team_code' => 'T5-FULL', 'max_member' => 3,
            'is_document_verified' => 1, 'is_verified' => 1, 'payment_proof_id' => $mediaPay[5],
        ]);
        TeamMember::create(['user_id' => $peserta[9]->id, 'team_id' => $t5, 'role' => 'leader', 'kartu_id' => $mediaKtm[9]]);

        // ==========================================
        // OTHERS
        // ==========================================

        EventTimeline::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'HackToday',
            'title' => 'Open Registration HackToday',
            'date' => now()->addDays(7),
        ]);

        EventTimeline::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'HackToday',
            'title' => 'Close Registration HackToday',
            'date' => now()->addDays(14),
        ]);

        EventTimeline::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'UXToday',
            'title' => 'Open Registration UX Today',
            'date' => now()->addDays(7),
        ]);

        EventAnnouncement::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'Seminar',
            'author_id' => $superadminUser->id,
            'title' => 'Selamat Datang di IT Today 2026!',
            'description' => 'Pendaftaran resmi untuk seluruh cabang lomba IT Today 2026 akan dibuka serentak pada minggu depan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
