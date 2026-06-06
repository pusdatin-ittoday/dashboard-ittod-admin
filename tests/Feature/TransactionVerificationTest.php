<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\UserIdentity;
use App\Jobs\SendVerificationEmailJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationStatusMail;
use Tests\TestCase;

class TransactionVerificationTest extends TestCase
{
    use RefreshDatabase;

    private $event;
    private $team;
    private $leaderUser;
    private $leaderIdentity;
    private $adminIdentity;
    private $regularIdentity;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create an Event
        $this->event = Event::create([
            'id' => 'TestEvent',
            'title' => 'Test Event Title',
            'description' => 'Test Event Description',
            'guide_book_url' => 'https://example.com/guidebook.pdf',
            'type' => 'competition',
            'price' => 150000,
            'contact_person1' => 'wa.me/628123456780',
        ]);

        // 2. Create a Team
        $this->team = Team::create([
            'id' => 'test-team-uuid',
            'competition_id' => $this->event->id,
            'team_name' => 'Awesome Developers',
            'team_code' => 'TESTCODE',
            'max_member' => 3,
            'is_verified' => 0,
        ]);

        // 3. Create a Leader User and their Identity
        $this->leaderUser = User::create([
            'email' => 'leader@example.com',
            'full_name' => 'Team Leader',
            'is_registration_complete' => 1,
        ]);

        $this->leaderIdentity = UserIdentity::create([
            'id' => $this->leaderUser->id,
            'email' => 'leader@example.com',
            'provider' => 'basic',
            'role' => 'user',
            'is_verified' => 1,
        ]);

        // Link leader to team
        TeamMember::create([
            'user_id' => $this->leaderUser->id,
            'team_id' => $this->team->id,
            'role' => 'leader',
        ]);

        // 4. Create an Admin User Identity
        $adminUser = User::create([
            'email' => 'admin@example.com',
            'full_name' => 'Admin Keuangan',
        ]);

        $this->adminIdentity = UserIdentity::create([
            'id' => $adminUser->id,
            'email' => 'admin@example.com',
            'provider' => 'basic',
            'role' => 'admin_keuangan',
            'is_verified' => 1,
        ]);

        // 5. Create a Regular User Identity
        $regularUser = User::create([
            'email' => 'regular@example.com',
            'full_name' => 'Regular User',
        ]);

        $this->regularIdentity = UserIdentity::create([
            'id' => $regularUser->id,
            'email' => 'regular@example.com',
            'provider' => 'basic',
            'role' => 'user',
            'is_verified' => 1,
        ]);
    }

    /**
     * Test Guest is redirected to login for verification/recap routes.
     */
    public function test_guests_are_unauthorized(): void
    {
        $response1 = $this->get('/transaction/recap');
        $response1->assertRedirect('/login');

        $response2 = $this->post("/transaction/{$this->team->id}/verify", ['action' => 'verify']);
        $response2->assertRedirect('/login');
    }

    /**
     * Test Regular user with 'user' role is forbidden.
     */
    public function test_regular_users_are_forbidden(): void
    {
        // For recap
        $response1 = $this->actingAs($this->regularIdentity)
            ->getJson('/transaction/recap');
        $response1->assertStatus(403);

        // For verify
        $response2 = $this->actingAs($this->regularIdentity)
            ->postJson("/transaction/{$this->team->id}/verify", ['action' => 'verify']);
        $response2->assertStatus(403);
    }

    /**
     * Test Admin Keuangan can get recap of total accumulated funds.
     */
    public function test_admin_keuangan_can_get_recap(): void
    {
        // Set team as verified
        $this->team->is_verified = 1;
        $this->team->save();

        $response = $this->actingAs($this->adminIdentity)
            ->getJson('/transaction/recap');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'total_accumulated_funds' => 150000,
            ]);
    }

    /**
     * Test Admin Keuangan can verify a transaction.
     */
    public function test_admin_keuangan_can_verify_transaction(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminIdentity)
            ->postJson("/transaction/{$this->team->id}/verify", [
                'action' => 'verify',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verifikasi pembayaran berhasil diproses.',
            ]);

        $this->team->refresh();
        $this->assertEquals(1, $this->team->is_verified);
        $this->assertNull($this->team->verification_error);

        Queue::assertPushed(SendVerificationEmailJob::class);
    }

    /**
     * Test Admin Keuangan can reject a transaction with a note.
     */
    public function test_admin_keuangan_can_reject_transaction_with_note(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminIdentity)
            ->postJson("/transaction/{$this->team->id}/verify", [
                'action' => 'reject',
                'verification_error' => 'Bukti transfer buram/tidak terbaca.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verifikasi pembayaran berhasil diproses.',
            ]);

        $this->team->refresh();
        $this->assertEquals(2, $this->team->is_verified); // 2 = Rejected
        $this->assertEquals('Bukti transfer buram/tidak terbaca.', $this->team->verification_error);

        Queue::assertPushed(SendVerificationEmailJob::class);
    }

    /**
     * Test validation requires verification note when rejecting.
     */
    public function test_reject_action_requires_verification_error(): void
    {
        $response = $this->actingAs($this->adminIdentity)
            ->postJson("/transaction/{$this->team->id}/verify", [
                'action' => 'reject',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['verification_error']);
    }

    /**
     * Test the SendVerificationEmailJob handle method sends the email.
     */
    public function test_send_verification_email_job_sends_email(): void
    {
        Mail::fake();

        $job = new SendVerificationEmailJob($this->team);
        $job->handle();

        Mail::assertSent(VerificationStatusMail::class, function ($mail) {
            return $mail->hasTo('leader@example.com') &&
                   $mail->team->id === $this->team->id;
        });
    }
}
