<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Label peran pengguna dalam Bahasa Indonesia
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'kanwil_admin' => 'Superadmin Kantor Wilayah',
            'kalapas' => 'Kalapas / Kepala UPT',
            'admin_koperasi' => 'Admin Koperasi & Gudang',
            'petugas_loket' => 'Petugas Loket & Registrasi',
            'kasir' => 'Kasir POS Koperasi',
            'auditor' => 'Auditor & Pengawas',
            default => 'Petugas Lapas',
        };
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'kanwil_admin' => 'pill-danger',
            'kalapas' => 'pill-primary',
            'admin_koperasi' => 'pill-success',
            'petugas_loket' => 'pill-warning',
            'kasir' => 'pill-primary',
            default => 'pill-secondary',
        };
    }

    public function isKanwil(): bool
    {
        return $this->role === 'kanwil_admin';
    }

    public function isKalapas(): bool
    {
        return $this->role === 'kalapas';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function canAccessUpt(string $uptCode): bool
    {
        if ($this->isKanwil()) return true;
        return $this->upt_code === $uptCode;
    }
}
