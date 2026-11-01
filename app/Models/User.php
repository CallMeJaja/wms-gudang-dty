<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Hanya user aktif yang boleh masuk panel Filament
     * (Admin, Operator, Manajer — pembatasan per-fitur lewat Policy).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    /**
     * Label tampilan di panel.
     */
    public function getFilamentName(): string
    {
        return $this->name ?: (string) $this->username;
    }

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Role
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isOperator(): bool
    {
        return $this->role === Role::Operator;
    }

    public function isManajer(): bool
    {
        return $this->role === Role::Manajer;
    }

    /**
     * Manajer bersifat read-only.
     */
    public function canWrite(): bool
    {
        return $this->role->canWrite();
    }

    public function canManage(): bool
    {
        return $this->role->canManage();
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function transaksiMasuk(): HasMany
    {
        return $this->hasMany(TransaksiMasuk::class, 'created_by');
    }

    public function transaksiKeluar(): HasMany
    {
        return $this->hasMany(TransaksiKeluar::class, 'created_by');
    }

    public function mutasiStok(): HasMany
    {
        return $this->hasMany(MutasiStok::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
