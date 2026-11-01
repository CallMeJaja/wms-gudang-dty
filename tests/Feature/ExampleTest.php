<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman root mengarahkan ke dashboard; tamu lalu dilempar ke login.
     */
    public function test_root_redirects_to_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Tamu yang membuka dashboard harus diarahkan ke halaman login.
     */
    public function test_guest_cannot_open_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
