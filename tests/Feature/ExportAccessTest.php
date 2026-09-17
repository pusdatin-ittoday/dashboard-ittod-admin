<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\UserIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
    }

    /**
     * @dataProvider userRolesProvider
     */
    public function test_all_user_roles_can_export_csv(string $role)
    {
        $user = UserIdentity::where('role', $role)->first();
        $this->assertNotNull($user, "User with role {$role} not found");

        $event = Event::first();
        $this->assertNotNull($event, "Event not found");

        // 1. Global Teams CSV
        $response = $this->actingAs($user)->get('/export/teams/global');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // 2. Global Participants CSV
        $response = $this->actingAs($user)->get('/export/participants/global');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // 3. Global Users CSV
        $response = $this->actingAs($user)->get('/export/users/global');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // 4. Semnas Participants CSV
        $response = $this->actingAs($user)->get('/export/semnas-participants');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // 5. Per-event Teams CSV
        $response = $this->actingAs($user)->get("/export/teams?event_id={$event->id}");
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // 6. Per-event Participants CSV
        $response = $this->actingAs($user)->get("/export/participants?event_id={$event->id}");
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // 7. Submissions CSV
        $compEvent = Event::where('type', 'competition')->first() ?? $event;
        $response = $this->actingAs($user)->get("/export/competitions/{$compEvent->id}/submissions");
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }

    public static function userRolesProvider(): array
    {
        return [
            'superadmin'    => ['superadmin'],
            'admin_biasa'   => ['admin_biasa'],
            'panitia_lomba' => ['panitia_lomba'],
            'user'          => ['user'],
        ];
    }
}
