<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Filters\AuthFilter;
use App\Filters\RoleFilter;
use Config\Database;
use Config\Services;

class TestPhase1 extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:phase1';
    protected $description = 'Menjalankan serangkaian pengujian integrasi untuk Tahap 1 (Database & Autentikasi).';

    public function run(array $params)
    {
        CLI::write("=== PENGUJIAN TAHAP 1: DATABASE & AUTENTIKASI POLARA ===", 'yellow');
        $allPassed = true;

        $assertTest = function (bool $condition, string $message) use (&$allPassed) {
            if ($condition) {
                CLI::write(" [PASS] {$message}", 'green');
            } else {
                CLI::error(" [FAIL] {$message}");
                $allPassed = false;
            }
        };

        // 1. Uji Database & Migrations
        CLI::write("\n1. Pengujian Koneksi Database & Migrasi:", 'white');
        $db = Database::connect('default');
        try {
            $db->initialize();
            $assertTest(true, "Koneksi ke database '{$db->getDatabase()}' berhasil.");
        } catch (\Throwable $e) {
            $assertTest(false, "Gagal terhubung ke database: " . $e->getMessage());
        }

        $tables = $db->listTables();
        $assertTest(in_array('users', $tables), "Tabel 'users' terdaftar di database.");
        $assertTest(in_array('datasets', $tables), "Tabel 'datasets' terdaftar di database.");
        $assertTest(in_array('dataset_columns', $tables), "Tabel 'dataset_columns' terdaftar di database.");
        $assertTest(in_array('migrations', $tables), "Tabel 'migrations' terdaftar di database.");

        $migCount = $db->table('migrations')->countAllResults();
        $assertTest($migCount >= 2, "Riwayat migrasi tercatat di batch ({$migCount} migrasi selesai).");

        // 2. Uji UserModel & Bcrypt Password Hashing
        CLI::write("\n2. Pengujian UserModel & Password Hashing:", 'white');
        $userModel = new UserModel();

        $testUsername = 'test_user_' . time();
        $plainPass     = 'Rahasia123!';
        $insertId      = $userModel->insert([
            'nama'     => 'Unit Tester User',
            'username' => $testUsername,
            'password' => $plainPass,
            'role'     => 'analis',
            'status'   => 'aktif',
        ]);
        $assertTest($insertId !== false, "Insert user baru via UserModel berhasil (ID: {$insertId}).");

        $savedUser = $userModel->find($insertId);
        $assertTest($savedUser['username'] === $testUsername, "Data username tersimpan dengan konsisten.");
        $assertTest(str_starts_with($savedUser['password'], '$2y$'), "Password otomatis di-hash dengan standar BCRYPT ($2y$).");
        $assertTest(password_verify($plainPass, $savedUser['password']), "password_verify() mengonfirmasi kecocokan hash password.");

        // Uji findByUsername
        $foundUser = $userModel->findByUsername($testUsername);
        $assertTest($foundUser && (int) $foundUser['id'] === (int) $insertId, "UserModel::findByUsername() mengembalikan data yang tepat.");

        // Uji Validasi Server-Side
        $invalidInsert = $userModel->insert([
            'nama'     => 'A', // terlalu pendek
            'username' => 'x', // terlalu pendek
            'password' => '12', // terlalu pendek
            'role'     => 'invalid_role',
            'status'   => 'invalid_status',
        ]);
        $assertTest($invalidInsert === false, "Validasi server-side menggagalkan input data tidak valid.");
        $validationErrors = $userModel->errors();
        $assertTest(
            isset($validationErrors['nama']) &&
            isset($validationErrors['username']) &&
            isset($validationErrors['password']) &&
            isset($validationErrors['role']) &&
            isset($validationErrors['status']),
            "Seluruh aturan validasi (nama, username, password, role, status) memicu pesan error yang tepat."
        );

        // Bersihkan data user uji
        $userModel->delete($insertId, true);
        $assertTest($userModel->find($insertId) === null, "Data uji dibersihkan dari database.");

        // 3. Uji Autentikasi Pengguna Bawaan (Admin, Analis, Pimpinan)
        CLI::write("\n3. Pengujian Akun Default:", 'white');
        $admin = $userModel->findByUsername('admin');
        $assertTest($admin !== null, "Akun default 'admin' ditemukan.");
        $assertTest($admin['role'] === 'admin', "Peran akun admin adalah 'admin'.");
        $assertTest($admin['status'] === 'aktif', "Status akun admin adalah 'aktif'.");

        // 4. Uji AuthFilter & RoleFilter
        CLI::write("\n4. Pengujian AuthFilter & RoleFilter:", 'white');
        $authFilter = new AuthFilter();
        $request    = Services::request();

        // Kondisi Sesi Kosong
        $session = Services::session();
        if (session_status() === PHP_SESSION_NONE) {
            $session->start();
        }
        $session->remove(['user_id', 'nama', 'username', 'role', 'isLoggedIn']);
        $unauthRedirect = $authFilter->before($request);
        $assertTest($unauthRedirect instanceof \CodeIgniter\HTTP\RedirectResponse, "AuthFilter memblokir akses belum login dan mengarahkan ke halaman login.");

        // Kondisi Sesi Login
        $session->set([
            'user_id'    => (int) $admin['id'],
            'nama'       => $admin['nama'],
            'username'   => $admin['username'],
            'role'       => 'admin',
            'isLoggedIn' => true,
        ]);
        $authPass = $authFilter->before($request);
        $assertTest($authPass === null, "AuthFilter meloloskan request untuk pengguna yang sudah terotentikasi.");

        // Uji RoleFilter
        $roleFilter   = new RoleFilter();
        $rolePass     = $roleFilter->before($request, ['admin']);
        $assertTest($rolePass === null, "RoleFilter meloloskan user dengan role 'admin' ke rute khusus admin.");

        $roleBlocked  = $roleFilter->before($request, ['analis']);
        $assertTest($roleBlocked instanceof \CodeIgniter\HTTP\RedirectResponse, "RoleFilter memblokir user role 'admin' saat mengakses rute khusus 'analis'.");

        // Bersihkan sesi
        $session->remove(['user_id', 'nama', 'username', 'role', 'isLoggedIn']);

        CLI::write("\n-------------------------------------------------------", 'white');
        if ($allPassed) {
            CLI::write("HASIL: SEMUA PENGUJIAN TAHAP 1 BERHASIL DILALUI DENGAN SUKSES!", 'green');
        } else {
            CLI::error("HASIL: TERDAPAT PENGUJIAN YANG GAGAL. SILAKAN PERIKSA DETAIL DI ATAS.");
        }
    }
}
