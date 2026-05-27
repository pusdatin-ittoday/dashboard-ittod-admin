<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserIdentity;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Media;
use App\Models\CompetitionSubmission;
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
        $event = Event::create([
            'id' => 'Example',
            'title' => 'ExampleCompetition',
            'description' => 'This is just an Example Competition',
            'guide_book_url' => 'google.com',
            'type' => 'competition',
            'contact_person1' => 'wa.me/6281256518375',
            'contact_person2' => 'wa.me/6281256518375',
            'max_noncompetition_participant' => null,
        ]);

        $actualUser1 = User::create([
            'id' => '7fd62376-411a-4352-8dba-8ea9b2c483fe',
            'email' => 'development@ittoday.web.id',
            'full_name' => 'Pusdatiners Adminers Developmentners',
            'birth_date' => '2024-06-10 00:00:00',
            'pendidikan' => 's1',
            'nama_sekolah' => 'IPB University',
            'phone_number' => '081256518375',
            'id_line' => 'admin',
            'id_discord' => 'admin',
            'id_instagram' => 'admin',
            'is_registration_complete' => 1,
            'jenis_kelamin' => 'laki2',
            'ktm_key' => '3085f464-a44b-425e-bd16-93c463a8f53d_pov_youre_my_laptop.png',
        ]);

        UserIdentity::create([
            'id' => $actualUser1->id,
            'email' => 'development@ittoday.web.id',
            'provider' => 'basic',
            'hash' => '$argon2id$v=19$m=4096,t=3,p=1$TQwsvynTzsyTsCV8OqwXRw$iMJ8G1c7et1+IHaX0T/pzAL5m7msMVWmQ30+pED0QPA', // Password is hash from dump
            'is_verified' => 1,
            'verification_token' => 'BASIC_VERIFIED',
            'verification_token_expiration' => '2125-04-24 09:46:16',
            'role' => 'panitia', // mapped 'admin' role to new 'panitia'
        ]);

        $actualUser2 = User::create([
            'id' => 'a38113a1-e39b-4afc-8bcc-47a6fb31c34e',
            'email' => 'sadritaufiq@gmail.com',
            'full_name' => 'Taufiq',
            'is_registration_complete' => 0,
        ]);

        UserIdentity::create([
            'id' => $actualUser2->id,
            'email' => 'sadritaufiq@gmail.com',
            'provider' => 'basic',
            'hash' => '$argon2id$v=19$m=4096,t=3,p=1$YtuYESgvRJg6121Qzw1zdA$sj1CGSpqZeFUYA0HovVifxKV7/BoYNpBxkeHgtPajLY',
            'is_verified' => 1,
            'verification_token' => 'BASIC_VERIFIED',
            'verification_token_expiration' => '2125-04-29 14:15:24',
            'role' => 'user',
        ]);

        Media::create([
            'id' => '1ed7406e-b415-4055-9aab-a61eb9d480a3',
            'uploader_id' => '7fd62376-411a-4352-8dba-8ea9b2c483fe',
            'name' => 'Something Here23',
            'grouping' => 'competition_submission',
            'type' => 'pdf',
            'url' => 'https://google.com.id',
        ]);

        Media::create([
            'id' => 'ede6f334-9448-4f15-a636-b8a9dd70d819',
            'uploader_id' => '7fd62376-411a-4352-8dba-8ea9b2c483fe',
            'name' => 'Something Here2',
            'grouping' => 'competition_submission',
            'type' => 'pdf',
            'url' => 'https://google.com.id',
        ]);

        $actualTeam1 = Team::create([
            'id' => '246b5f88-e848-4adb-a6eb-0726116c4d7a',
            'competition_id' => 'Example',
            'team_name' => 'Tuhan Maha Adil555',
            'team_code' => '8u2gUZmE',
            'max_member' => 3,
            'is_verified' => 0,
        ]);

        $actualTeam2 = Team::create([
            'id' => '4f433581-9da2-4d9b-b0b0-e523f7ec320a',
            'competition_id' => 'Example',
            'team_name' => 'Tuhan Maha Adil5551',
            'team_code' => 'lBLcuYvn',
            'max_member' => 3,
            'is_verified' => 0,
        ]);

        TeamMember::create([
            'user_id' => '7fd62376-411a-4352-8dba-8ea9b2c483fe',
            'team_id' => '246b5f88-e848-4adb-a6eb-0726116c4d7a',
            'role' => 'leader',
        ]);

        TeamMember::create([
            'user_id' => 'a38113a1-e39b-4afc-8bcc-47a6fb31c34e',
            'team_id' => '4f433581-9da2-4d9b-b0b0-e523f7ec320a',
            'role' => 'leader',
        ]);

        CompetitionSubmission::create([
            'team_id' => '246b5f88-e848-4adb-a6eb-0726116c4d7a',
            'competition_id' => 'Example',
            'submission_object' => null,
        ]);


        // ==========================================
        // SEED REQUESTED TESTING ACCOUNTS
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
            'hash' => Hash::make('superadmin'), // Password: superadmin
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
            'hash' => Hash::make('admin'), // Password: admin
            'role' => 'admin_keuangan', // admin keuangan
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
            'hash' => Hash::make('hidup jokowi'), // Password: hidup jokowi
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
            'hash' => Hash::make('akan lawan'), // Password: akan lawan
            'role' => 'user',
            'is_verified' => 1,
            'verification_token' => Str::random(40),
            'verification_token_expiration' => now()->addYear(),
        ]);
    }
}
