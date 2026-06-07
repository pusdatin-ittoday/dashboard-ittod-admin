<?php

namespace Tests\Feature;

use App\Models\UserIdentity;
use App\Models\Team;
use App\Models\EventTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed database dengan data awal
        $this->seed();
    }

    /** @test */
    public function guests_are_redirected_from_operation_routes()
    {
        $response = $this->get('/operation/teams');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_access_operation_teams_list()
    {
        // Ambil akun admin_keuangan yang dibuat oleh DatabaseSeeder
        $admin = UserIdentity::where('role', 'admin_keuangan')->first();

        $response = $this->actingAs($admin)
            ->get('/operation/teams');

        $response->assertStatus(200);
        $response->assertSee('Tuhan Maha Adil555');
    }

    /** @test */
    public function admin_can_update_team_verification_status()
    {
        $admin = UserIdentity::where('role', 'admin_keuangan')->first();
        $team = Team::first();

        $response = $this->actingAs($admin)
            ->post("/operation/teams/{$team->id}/verify", [
                'is_verified' => 1,
                'verification_error' => null
            ]);

        $response->assertRedirect('/operation/teams');
        $this->assertEquals(1, $team->fresh()->is_verified);
    }

    /** @test */
    public function admin_can_crud_non_competition_timelines()
    {
        $admin = UserIdentity::where('role', 'admin_keuangan')->first();

        // 1. Read
        $response = $this->actingAs($admin)->get('/operation/timeline');
        $response->assertStatus(200);

        // 2. Create
        $response = $this->actingAs($admin)->post('/operation/timeline', [
            'event_id' => 'Seminar',
            'title' => 'Sesi Panel Seminar',
            'date' => '2026-06-25 10:00:00',
        ]);
        $response->assertRedirect('/operation/timeline');
        $this->assertDatabaseHas('event_timeline', [
            'title' => 'Sesi Panel Seminar'
        ]);

        // 3. Edit & Update
        $timeline = EventTimeline::where('title', 'Sesi Panel Seminar')->first();
        $response = $this->actingAs($admin)->put("/operation/timeline/{$timeline->id}", [
            'event_id' => 'Seminar',
            'title' => 'Sesi Panel Seminar Updated',
            'date' => '2026-06-26 12:00:00',
        ]);
        $response->assertRedirect('/operation/timeline');
        $this->assertEquals('Sesi Panel Seminar Updated', $timeline->fresh()->title);

        // 4. Delete
        $response = $this->actingAs($admin)->delete("/operation/timeline/{$timeline->id}");
        $response->assertRedirect('/operation/timeline');
        $this->assertDatabaseMissing('event_timeline', [
            'id' => $timeline->id
        ]);
    }

    /** @test */
    public function data_freezing_middleware_blocks_verified_participants()
    {
        // Daftarkan rute sementara untuk menguji middleware data_frozen
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'data_frozen'])->get('/test-frozen', function () {
            return 'not frozen';
        });

        // Ambil akun peserta
        $user = UserIdentity::where('role', 'user')->first();
        $team = $user->user->teams->first();

        // Kasus 1: Tim belum verifikasi dan ada error (tidak frozen, boleh edit)
        $team->update([
            'is_verified' => 0,
            'verification_error' => 'Bukti pembayaran tidak jelas'
        ]);
        $response = $this->actingAs($user)->get('/test-frozen');
        $response->assertStatus(200);
        $response->assertSee('not frozen');

        // Kasus 2: Tim terverifikasi (frozen, blokir)
        $team->update([
            'is_verified' => 1,
            'verification_error' => null
        ]);
        $response = $this->actingAs($user)->get('/test-frozen');
        $response->assertStatus(403);

        // Kasus 3: Tim sedang diperiksa (belum verifikasi & tidak ada error) (frozen, blokir)
        $team->update([
            'is_verified' => 0,
            'verification_error' => null
        ]);
        $response = $this->actingAs($user)->get('/test-frozen');
        $response->assertStatus(403);
    }
}
