<?php

namespace Tests\Feature;

use App\Models\UserIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionExpirationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
    }

    public function test_check_session_endpoint_reports_guest_when_unauthenticated()
    {
        $response = $this->getJson('/check-session');
        $response->assertStatus(200);
        $response->assertJson(['authenticated' => false]);
    }

    public function test_check_session_endpoint_reports_authenticated_when_logged_in()
    {
        $user = UserIdentity::where('role', 'superadmin')->first();
        $response = $this->actingAs($user)->getJson('/check-session');
        $response->assertStatus(200);
        $response->assertJson(['authenticated' => true]);
    }

    public function test_csrf_mismatch_redirects_to_login_with_status_message()
    {
        // Without actingAs or without valid CSRF on a stateful web route
        $response = $this->withoutMiddleware(\Illuminate\Routing\Middleware\SubstituteBindings::class)
            ->withSession([])
            ->call('POST', '/operation/teams/approve-all-documents', [
                '_token' => 'invalid-expired-token',
            ]);

        // When TokenMismatchException is rendered, it should redirect to login
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Sesi Anda telah berakhir. Silakan login kembali.');
    }

    public function test_logout_route_exempt_from_csrf_and_redirects()
    {
        $user = UserIdentity::where('role', 'superadmin')->first();
        $response = $this->actingAs($user)->post('/logout', [
            '_token' => 'expired-token',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
