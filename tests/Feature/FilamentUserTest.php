<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilamentUserTest extends TestCase
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

    #[Test]
    public function admin_bisa_membuka_daftar_dan_form_pengguna(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/users')->assertOk();

        $this->get('/admin/users/create')
            ->assertOk()
            ->assertDontSee('buat lainnya')
            ->assertDontSee('Simpan & input lagi')
            ->assertSee('Simpan');

        $this->get("/admin/users/{$this->operator->getKey()}/edit")->assertOk();
    }

    #[Test]
    public function operator_dan_manajer_tidak_bisa_mengakses_pengguna(): void
    {
        foreach ([$this->operator, $this->manajer] as $user) {
            $this->actingAs($user);

            $this->get('/admin/users')->assertForbidden();
            $this->get('/admin/users/create')->assertForbidden();
            $this->get("/admin/users/{$this->admin->getKey()}/edit")->assertForbidden();
        }
    }

    #[Test]
    public function admin_bisa_membuat_pengguna_baru(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Pengguna Baru',
                'username' => 'pengguna.baru',
                'email' => 'pengguna.baru@example.test',
                'role' => Role::Operator->value,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $user = User::query()->where('username', 'pengguna.baru')->firstOrFail();

        $this->assertSame('Pengguna Baru', $user->name);
        $this->assertSame(Role::Operator, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    #[Test]
    public function edit_memperbarui_data_dan_kata_sandi_hanya_bila_diisi(): void
    {
        $this->actingAs($this->admin);

        $hashLama = $this->operator->password;

        Livewire::test(EditUser::class, ['record' => $this->operator->getKey()])
            ->fillForm([
                'name' => 'Operator Diubah',
                'role' => Role::Manajer->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->operator->refresh();

        $this->assertSame('Operator Diubah', $this->operator->name);
        $this->assertSame(Role::Manajer, $this->operator->role);
        $this->assertSame($hashLama, $this->operator->password);

        Livewire::test(EditUser::class, ['record' => $this->operator->getKey()])
            ->fillForm([
                'password' => 'sandi-baru-123',
                'password_confirmation' => 'sandi-baru-123',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->operator->refresh();

        $this->assertNotSame($hashLama, $this->operator->password);
        $this->assertTrue(Hash::check('sandi-baru-123', $this->operator->password));
    }

    #[Test]
    public function username_dan_email_harus_unik(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Duplikat',
                'username' => $this->operator->username,
                'email' => $this->operator->email,
                'role' => Role::Operator->value,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'username' => 'unique',
                'email' => 'unique',
            ]);

        $this->assertDatabaseMissing('users', ['name' => 'Duplikat']);
    }

    #[Test]
    public function pengguna_tidak_bisa_menurunkan_role_atau_menonaktifkan_dirinya_sendiri(): void
    {
        $this->actingAs($this->admin);
        User::factory()->admin()->create();

        Livewire::test(EditUser::class, ['record' => $this->admin->getKey()])
            ->fillForm(['role' => Role::Operator->value])
            ->call('save')
            ->assertHasFormErrors(['role' => 'Peran akun sendiri tidak dapat diubah dari Admin.']);

        $this->assertSame(Role::Admin, $this->admin->fresh()->role);

        Livewire::test(EditUser::class, ['record' => $this->admin->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasFormErrors(['is_active' => 'Akun sendiri tidak dapat dinonaktifkan.']);

        $this->assertTrue($this->admin->fresh()->is_active);
    }

    #[Test]
    public function admin_aktif_terakhir_tidak_bisa_dinonaktifkan_atau_diturunkan(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(EditUser::class, ['record' => $this->admin->getKey()])
            ->fillForm(['role' => Role::Operator->value])
            ->call('save')
            ->assertHasFormErrors(['role' => 'Minimal harus ada satu Admin aktif.']);

        $this->assertSame(Role::Admin, $this->admin->fresh()->role);

        Livewire::test(EditUser::class, ['record' => $this->admin->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasFormErrors(['is_active' => 'Minimal harus ada satu Admin aktif.']);

        $this->assertTrue($this->admin->fresh()->is_active);
    }

    #[Test]
    public function pengguna_tidak_memiliki_aksi_atau_route_hapus(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(UserResource::canDelete($this->operator));
        $this->assertFalse(Route::has('filament.admin.resources.users.destroy'));

        $this->delete("/admin/users/{$this->operator->getKey()}")->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $this->operator->getKey()]);
    }
}
