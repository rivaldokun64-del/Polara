<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

class CreateAdmin extends BaseCommand
{
    /**
     * Group command
     */
    protected $group = 'Auth';

    /**
     * Nama perintah CLI
     */
    protected $name = 'user:create-admin';

    /**
     * Deskripsi perintah
     */
    protected $description = 'Membuat akun Administrator baru secara aman dari baris perintah (CLI).';

    /**
     * Penggunaan
     */
    protected $usage = 'user:create-admin [options]';

    /**
     * Opsi argumen
     */
    protected $options = [
        '--username' => 'Username untuk akun admin baru',
        '--nama'     => 'Nama lengkap admin',
        '--password' => 'Password untuk akun admin baru (minimal 6 karakter)',
    ];

    public function run(array $params)
    {
        CLI::write("=== Pembuatan Akun Administrator Polara ===", 'green');

        $username = $params['username'] ?? CLI::getOption('username') ?? CLI::prompt('Username', 'admin');
        $nama     = $params['nama'] ?? CLI::getOption('nama') ?? CLI::prompt('Nama Lengkap', 'Administrator');
        $password = $params['password'] ?? CLI::getOption('password') ?? CLI::prompt('Password (min 6 karakter)');

        if (empty($username) || empty($nama) || empty($password)) {
            CLI::error('Semua kolom (username, nama, password) wajib diisi.');
            return;
        }

        $userModel = new UserModel();

        // Cek duplikasi username
        $existing = $userModel->findByUsername($username);
        if ($existing) {
            $confirm = CLI::prompt("User '{$username}' sudah ada. Apakah ingin memperbarui password dan menjadikannya admin aktif? (y/n)", ['y', 'n']);
            if (strtolower($confirm) === 'y') {
                $userModel->update($existing['id'], [
                    'nama'     => $nama,
                    'password' => $password, // Akan otomatis di-hash oleh UserModel
                    'role'     => 'admin',
                    'status'   => 'aktif',
                ]);
                CLI::write("Berhasil memperbarui akun admin '{$username}'.", 'green');
            } else {
                CLI::write("Operasi dibatalkan.", 'yellow');
            }
            return;
        }

        $saved = $userModel->insert([
            'nama'     => $nama,
            'username' => $username,
            'password' => $password,
            'role'     => 'admin',
            'status'   => 'aktif',
        ]);

        if ($saved === false) {
            CLI::error("Gagal membuat akun admin:");
            foreach ($userModel->errors() as $err) {
                CLI::error(" - " . $err);
            }
            return;
        }

        CLI::write("Akun Administrator '{$username}' berhasil dibuat dengan aman!", 'green');
    }
}
