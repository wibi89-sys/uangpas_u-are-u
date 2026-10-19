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
            'permissions' => 'array',
        ];
    }

    /**
     * Daftar seluruh permission sistem yang dapat dicentang
     */
    public static function availablePermissions(): array
    {
        return [
            'wbp_manage' => [
                'label' => 'Data WBP & Biometrik',
                'desc' => 'Pendaftaran warga binaan, cetak kartu pintar, dan rekam sidik jari'
            ],
            'topup_manage' => [
                'label' => 'Loket Setor & Top-Up',
                'desc' => 'Menerima setoran uang tunai keluarga dan top-up saldo titipan WBP'
            ],
            'pos_checkout' => [
                'label' => 'Kasir Belanja POS',
                'desc' => 'Menjalankan terminal kasir belanja koperasi (Desktop & Mobile)'
            ],
            'inventory_manage' => [
                'label' => 'Katalog & Stok Barang',
                'desc' => 'Mengelola katalog produk, restock barang masuk, dan stock opname'
            ],
            'reports_finance' => [
                'label' => 'Laporan & Audit Keuangan',
                'desc' => 'Melihat buku transaksi, arus kas titipan, dan laba rugi koperasi'
            ],
            'settings_app' => [
                'label' => 'Pengaturan Aplikasi & Alat',
                'desc' => 'Mengubah konfigurasi UPT, format struk, dan perangkat biometrik'
            ],
            'users_manage' => [
                'label' => 'Pengaturan Akun Petugas',
                'desc' => 'Menambah, mengedit akun petugas, serta mengatur centang hak akses'
            ],
        ];
    }

    /**
     * Default centang hak akses per level peran
     */
    public static function defaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            'kanwil_admin', 'kalapas' => [
                'wbp_manage' => true,
                'topup_manage' => true,
                'pos_checkout' => true,
                'inventory_manage' => true,
                'reports_finance' => true,
                'settings_app' => true,
                'users_manage' => true,
            ],
            'admin_koperasi' => [
                'wbp_manage' => true,
                'topup_manage' => true,
                'pos_checkout' => true,
                'inventory_manage' => true,
                'reports_finance' => true,
                'settings_app' => true,
                'users_manage' => true, // Admin Utama UPT bisa mengelola akun petugas
            ],
            'petugas_loket' => [
                'wbp_manage' => true,
                'topup_manage' => true,
                'pos_checkout' => false,
                'inventory_manage' => false,
                'reports_finance' => false,
                'settings_app' => false,
                'users_manage' => false,
            ],
            'kasir' => [
                'wbp_manage' => false,
                'topup_manage' => true,
                'pos_checkout' => true,
                'inventory_manage' => false,
                'reports_finance' => false,
                'settings_app' => false,
                'users_manage' => false,
            ],
            'auditor' => [
                'wbp_manage' => false,
                'topup_manage' => false,
                'pos_checkout' => false,
                'inventory_manage' => false,
                'reports_finance' => true,
                'settings_app' => false,
                'users_manage' => false,
            ],
            default => [
                'wbp_manage' => false,
                'topup_manage' => false,
                'pos_checkout' => false,
                'inventory_manage' => false,
                'reports_finance' => false,
                'settings_app' => false,
                'users_manage' => false,
            ],
        };
    }

    /**
     * Cek apakah user memiliki hak akses tertentu (mempertimbangkan custom checkboxes)
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'kanwil_admin' || $this->role === 'kalapas') {
            return true;
        }

        $perms = $this->permissions;
        if (is_array($perms) && array_key_exists($permission, $perms)) {
            return (bool)$perms[$permission];
        }

        // Fallback default role
        $defaults = self::defaultPermissionsForRole($this->role);
        return $defaults[$permission] ?? false;
    }

    /**
     * Cek apakah user adalah Admin yang berhak mengelola Akun Petugas
     */
    public function canManageUsers(): bool
    {
        return $this->hasRole(['kanwil_admin', 'kalapas', 'admin_koperasi']) || $this->hasPermission('users_manage');
    }

    /**
     * Label peran pengguna dalam Bahasa Indonesia
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'kanwil_admin' => 'Superadmin Kantor Wilayah',
            'kalapas' => 'Kalapas / Kepala UPT',
            'admin_koperasi' => 'Admin Utama UPT & Koperasi',
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
