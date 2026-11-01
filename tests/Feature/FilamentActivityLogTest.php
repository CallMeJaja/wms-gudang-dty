<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilamentActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private User $manajer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->operator()->create();
        $this->manajer = User::factory()->manajer()->create();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    /**
     * @return array{0: ActivityLog, 1: ActivityLog, 2: ActivityLog}
     */
    private function buatLog(): array
    {
        $this->actingAs($this->admin);

        return [
            ActivityLog::catat('LOGIN', 'AUTH', null, 'Login admin', [
                'user_id' => $this->admin->getKey(),
                'created_at' => now()->subDays(3),
            ]),
            ActivityLog::catat('CREATE', 'MASTER_BARANG', 10, 'Buat barang uji', [
                'user_id' => $this->operator->getKey(),
                'created_at' => now()->subDays(2),
            ]),
            ActivityLog::catat('UPDATE', 'MASTER_SATUAN', 20, 'Ubah satuan uji', [
                'user_id' => $this->admin->getKey(),
                'created_at' => now(),
            ]),
        ];
    }

    #[Test]
    public function admin_bisa_membuka_activity_log(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/activity-logs')->assertOk();
    }

    #[Test]
    public function operator_dan_manajer_tidak_bisa_membuka_activity_log(): void
    {
        foreach ([$this->operator, $this->manajer] as $user) {
            $this->actingAs($user);

            $this->get('/admin/activity-logs')->assertForbidden();
        }
    }

    #[Test]
    public function log_yang_dicatat_muncul_di_tabel(): void
    {
        [$login, $create, $update] = $this->buatLog();

        $this->actingAs($this->admin);

        Livewire::test(ListActivityLogs::class)
            ->assertCanSeeTableRecords([$login, $create, $update])
            ->assertSee('Barang')
            ->assertSee('Buat barang uji')
            ->assertSee($this->operator->name);
    }

    #[Test]
    public function filter_event_modul_user_dan_tanggal_menyaring_tabel(): void
    {
        [$login, $create, $update] = $this->buatLog();

        $this->actingAs($this->admin);

        Livewire::test(ListActivityLogs::class)
            ->filterTable('event', 'CREATE')
            ->assertCanSeeTableRecords([$create])
            ->assertCanNotSeeTableRecords([$login, $update]);

        Livewire::test(ListActivityLogs::class)
            ->filterTable('modul', 'MASTER_SATUAN')
            ->assertCanSeeTableRecords([$update])
            ->assertCanNotSeeTableRecords([$login, $create]);

        Livewire::test(ListActivityLogs::class)
            ->filterTable('user', $this->operator->getKey())
            ->assertCanSeeTableRecords([$create])
            ->assertCanNotSeeTableRecords([$login, $update]);

        Livewire::test(ListActivityLogs::class)
            ->filterTable('created_at', [
                'dari' => now()->toDateString(),
                'sampai' => now()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$update])
            ->assertCanNotSeeTableRecords([$login, $create]);
    }

    #[Test]
    public function activity_log_bersifat_baca_saja(): void
    {
        [$login] = $this->buatLog();

        $this->actingAs($this->admin);

        $this->assertFalse(ActivityLogResource::canCreate());
        $this->assertFalse(ActivityLogResource::canEdit($login));
        $this->assertFalse(ActivityLogResource::canDelete($login));

        $this->assertFalse(Route::has('filament.admin.resources.activity-logs.create'));
        $this->assertFalse(Route::has('filament.admin.resources.activity-logs.edit'));

        $this->get('/admin/activity-logs/create')->assertNotFound();
        $this->get("/admin/activity-logs/{$login->getKey()}/edit")->assertNotFound();
    }
}
