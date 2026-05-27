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
            'contact_person1' => 'wa.me/628123456788',
            'contact_person2' => null,
            'max_noncompetition_participant' => 500,
        ]);




        // ==========================================
        // TESTING ACCOUNTS
        // ==========================================

        // Akun Superadmin 1 (superadmin)
        $superadminUser = User::create([
            'email' => 'superadmin@ittoday.id',
            'full_name' => 'Superadmin IT Today',
            'is_registration_complete' => 1,
        ]);

        UserIdentity::create([
            'id' => $superadminUser->id,
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
            'email' => 'admin@ittoday.id',
            'full_name' => 'Admin IT Today',
            'is_registration_complete' => 1,
        ]);

        UserIdentity::create([
            'id' => $adminUser->id,
            'email' => 'admin@ittoday.id',
            'provider' => 'basic',
            'hash' => Hash::make('admin'),
            'role' => 'admin_keuangan',
            'is_verified' => 1,
            'verification_token' => Str::random(40),
            'verification_token_expiration' => now()->addYear(),
        ]);

        // Akun Peserta 1 (hidup jokowi)
        $peserta1 = User::create([
            'email' => 'jokowi@gmail.com',
            'full_name' => 'hidup jokowi',
            'is_registration_complete' => 1,
        ]);

        UserIdentity::create([
            'id' => $peserta1->id,
            'email' => 'jokowi@gmail.com',
            'provider' => 'basic',
            'hash' => Hash::make('hidup jokowi'),
            'role' => 'user',
            'is_verified' => 1,
            'verification_token' => Str::random(40),
            'verification_token_expiration' => now()->addYear(),
        ]);

        // Akun Peserta 2 (akan lawan)
        $peserta2 = User::create([
            'email' => 'lawan@gmail.com',
            'full_name' => 'akan lawan',
            'is_registration_complete' => 1,
        ]);

        UserIdentity::create([
            'id' => $peserta2->id,
            'email' => 'lawan@gmail.com',
            'provider' => 'basic',
            'hash' => Hash::make('akan lawan'),
            'role' => 'user',
            'is_verified' => 1,
            'verification_token' => Str::random(40),
            'verification_token_expiration' => now()->addYear(),
        ]);

        Media::create([
            'id' => '1ed7406e-b415-4055-9aab-a61eb9d480a3',
            'uploader_id' => $peserta1->id,
            'name' => 'Something Here23',
            'grouping' => 'competition_submission',
            'type' => 'pdf',
            'url' => 'https://google.com.id',
        ]);

        Media::create([
            'id' => 'ede6f334-9448-4f15-a636-b8a9dd70d819',
            'uploader_id' => $peserta2->id,
            'name' => 'Something Here2',
            'grouping' => 'competition_submission',
            'type' => 'pdf',
            'url' => 'https://google.com.id',
        ]);

        Team::create([
            'id' => '246b5f88-e848-4adb-a6eb-0726116c4d7a',
            'competition_id' => 'HackToday',
            'team_name' => 'Tuhan Maha Adil555',
            'team_code' => '8u2gUZmE',
            'max_member' => 3,
            'is_verified' => 0,
        ]);

        Team::create([
            'id' => '4f433581-9da2-4d9b-b0b0-e523f7ec320a',
            'competition_id' => 'HackToday',
            'team_name' => 'Tuhan Maha Adil5551',
            'team_code' => 'lBLcuYvn',
            'max_member' => 3,
            'is_verified' => 0,
        ]);

        TeamMember::create([
            'user_id' => $peserta1->id,
            'team_id' => '246b5f88-e848-4adb-a6eb-0726116c4d7a',
            'role' => 'leader',
        ]);

        TeamMember::create([
            'user_id' => $peserta2->id,
            'team_id' => '4f433581-9da2-4d9b-b0b0-e523f7ec320a',
            'role' => 'leader',
        ]);

        CompetitionSubmission::create([
            'team_id' => '246b5f88-e848-4adb-a6eb-0726116c4d7a',
            'competition_id' => 'HackToday',
            'submission_object' => null,
        ]);

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
            'event_id' => 'HackToday',
            'title' => 'Final Stage HackToday',
            'date' => now()->addDays(30),
        ]);

        EventTimeline::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'UXToday',
            'title' => 'Open Registration UX Today',
            'date' => now()->addDays(7),
        ]);

        EventTimeline::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'UXToday',
            'title' => 'Close Registration UX Today',
            'date' => now()->addDays(14),
        ]);

        EventTimeline::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'UXToday',
            'title' => 'Final Presentation UX Today',
            'date' => now()->addDays(30),
        ]);

        EventParticipant::create([
            'user_id' => $peserta1->id,
            'event_id' => 'Seminar',
            'date_added' => now(),
            'payment_proof' => 'EDE6F334_proof.jpg',
            'payment_verification' => 'pending',
        ]);

        EventAnnouncement::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'Seminar',
            'author_id' => $superadminUser->id,
            'title' => 'Selamat Datang di IT Today 2026!',
            'description' => 'Pendaftaran resmi untuk seluruh cabang lomba IT Today 2026 akan dibuka serentak pada minggu depan. Harap mempersiapkan berkas administrasi tim Anda.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        EventAnnouncement::create([
            'id' => (string) Str::uuid(),
            'event_id' => 'HackToday',
            'author_id' => $superadminUser->id,
            'title' => 'Guidebook Resmi HackToday Dirilis',
            'description' => 'Panduan lengkap regulasi kompetisi Capture the Flag (CTF) HackToday 2026 telah dapat diunduh pada menu guidebook di dashboard Anda.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
