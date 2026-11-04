<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilamentLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function user_nonaktif_mendapat_pesan_akun_tidak_aktif(): void
    {
        $user = User::factory()->operator()->create([
            'is_active' => false,
            'password' => 'rahasia123',
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'rahasia123',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasErrors([
                'data.email' => 'Akun tidak aktif. Hubungi Admin untuk mengaktifkan akun Anda.',
            ]);
    }
}
