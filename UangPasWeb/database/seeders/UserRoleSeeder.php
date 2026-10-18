<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Bpk. Kakanwil / Kadivpas',
                'email' => 'kanwil@lapas.go.id',
                'role' => 'kanwil_admin',
                'upt_code' => 'KANWIL-BANTEN',
                'nip' => '197501011999031001',
                'phone' => '081299998888',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Bpk. Kepala Lapas Kelas I',
                'email' => 'kalapas@lapas.go.id',
                'role' => 'kalapas',
                'upt_code' => 'LPS-01',
                'nip' => '198002022005011002',
                'phone' => '081277776666',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Pengelola Koperasi & Gudang',
                'email' => 'gudang@lapas.go.id',
                'role' => 'admin_koperasi',
                'upt_code' => 'LPS-01',
                'nip' => '198705052010011003',
                'phone' => '081355554444',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Petugas Loket Kunjungan & Registrasi',
                'email' => 'loket@lapas.go.id',
                'role' => 'petugas_loket',
                'upt_code' => 'LPS-01',
                'nip' => '199208082015021004',
                'phone' => '081333332222',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Petugas Kasir Toko Koperasi',
                'email' => 'kasir@lapas.go.id',
                'role' => 'kasir',
                'upt_code' => 'LPS-01',
                'nip' => '199511112019031005',
                'phone' => '081311110000',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Tim Auditor / Pengawas Internal',
                'email' => 'auditor@lapas.go.id',
                'role' => 'auditor',
                'upt_code' => 'LPS-01',
                'nip' => '198304042008011006',
                'phone' => '081288887777',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
