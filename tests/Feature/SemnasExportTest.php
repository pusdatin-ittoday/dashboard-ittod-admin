<?php

namespace Tests\Feature;

use App\Models\UserIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemnasExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
    }

    public function test_guests_are_redirected_from_semnas_routes()
    {
        $response = $this->get('/admin/semnas');
        $response->assertRedirect('/login');

        $responseExport = $this->get('/export/semnas-participants');
        $responseExport->assertRedirect('/login');
    }

    public function test_admin_can_access_semnas_page()
    {
        $admin = UserIdentity::where('role', 'admin_biasa')->first();

        $response = $this->actingAs($admin)->get('/admin/semnas');

        $response->assertStatus(200);
        $response->assertSee('Peserta Semnas');
        $response->assertSee('Export CSV');
    }

    public function test_admin_can_export_semnas_participants_csv()
    {
        $admin = UserIdentity::where('role', 'admin_biasa')->first();

        $response = $this->actingAs($admin)->get('/export/semnas-participants');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }

    public function test_unauthorized_role_cannot_access_semnas()
    {
        $panitia = UserIdentity::where('role', 'panitia_lomba')->first();
        if ($panitia) {
            $response = $this->actingAs($panitia)->get('/admin/semnas');
            $response->assertStatus(403);

            $responseExport = $this->actingAs($panitia)->get('/export/semnas-participants');
            $responseExport->assertStatus(403);
        }
    }
}
