<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Halaman root mengarahkan ke panel admin.
     */
    public function test_root_redirects_to_admin(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin');
    }

    /**
     * Bookmark lama /dashboard dialihkan ke panel admin.
     */
    public function test_dashboard_redirects_to_admin(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/admin');
    }

    /**
     * Tamu yang membuka panel admin harus diarahkan ke login Filament.
     */
    public function test_guest_is_redirected_to_filament_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Admin terautentikasi bisa membuka panel, dan root tetap mengarah ke panel.
     */
    public function test_authenticated_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/')->assertRedirect('/admin');
    }
}
