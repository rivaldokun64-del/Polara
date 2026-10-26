<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        // 1. Akun Admin Default (bila belum ada)
        if (!$userModel->where('username', 'admin')->first()) {
            $userModel->insert([
                'nama'     => 'Administrator Polara',
                'username' => 'admin',
                'password' => 'admin123', // Akan di-hash otomatis dengan bcrypt oleh UserModel
                'role'     => 'admin',
                'status'   => 'aktif',
            ]);
            echo "Akun admin berhasil dibuat.\n";
        } else {
            echo "Akun admin sudah ada, seeding dilewati.\n";
        }

        // 2. Akun Analis Default (bila belum ada)
        if (!$userModel->where('username', 'analis')->first()) {
            $userModel->insert([
                'nama'     => 'Analis Data Kesehatan',
                'username' => 'analis',
                'password' => 'analis123', // Akan di-hash otomatis dengan bcrypt oleh UserModel
                'role'     => 'analis',
                'status'   => 'aktif',
            ]);
            echo "Akun analis berhasil dibuat.\n";
        }

        // 3. Akun Pimpinan Default (bila belum ada)
        if (!$userModel->where('username', 'pimpinan')->first()) {
            $userModel->insert([
                'nama'     => 'Pimpinan / Pembaca Laporan',
                'username' => 'pimpinan',
                'password' => 'pimpinan123', // Akan di-hash otomatis dengan bcrypt oleh UserModel
                'role'     => 'pimpinan',
                'status'   => 'aktif',
            ]);
            echo "Akun pimpinan berhasil dibuat.\n";
        }
    }
}
