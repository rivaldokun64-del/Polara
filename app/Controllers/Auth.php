<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Tampilkan form login
     */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('auth/login', [
            'title' => 'Login — Polara',
        ]);
    }

    /**
     * Proses autentikasi pengguna
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                ],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $user = $this->userModel->findByUsername($username);

        // Pesan generik untuk mencegah user enumeration
        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username atau password yang Anda masukkan tidak sesuai.');
        }

        // Cek status keaktifan akun
        if ($user['status'] !== 'aktif') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');
        }

        // Simpan sesi autentikasi
        $sessionData = [
            'user_id'    => (int) $user['id'],
            'nama'       => $user['nama'],
            'username'   => $user['username'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ];

        // Regenerasi session ID untuk mitigasi session fixation
        session()->regenerate();
        session()->set($sessionData);

        // Hapus redirect_url lama jika ada agar tidak mengarahkan kembali ke homepage
        session()->remove('redirect_url');

        // Pengguna selalu langsung diarahkan ke dashboard setelah autentikasi berhasil
        return redirect()->to(site_url('dashboard'))->with('login_success', 'Hai ' . esc($user['nama']) . ', selamat datang kembali di sistem.');
    }

    /**
     * Proses keluar (logout)
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Helper sementara untuk reset password admin ke admin123
     */
    public function setupAdmin()
    {
        $passwordPlain = 'admin123';
        $hashed = password_hash($passwordPlain, PASSWORD_BCRYPT);

        $db = \Config\Database::connect();
        $db->table('users')->where('username', 'admin')->update(['password' => $hashed, 'status' => 'aktif']);
        $db->table('users')->where('username', 'rival')->update(['password' => $hashed, 'status' => 'aktif']);

        return $this->response->setBody("
            <div style='font-family:sans-serif;padding:30px;background:#ecfdf5;border:2px solid #10b981;border-radius:12px;margin:40px auto;max-width:600px;text-align:center;'>
                <h2 style='color:#065f46;'>Reset Akun Berhasil!</h2>
                <p>Akun <strong>admin</strong> dan <strong>rival</strong> sekarang memiliki password:</p>
                <div style='background:#fff;padding:12px 20px;font-size:22px;font-weight:bold;color:#047857;border-radius:8px;display:inline-block;margin:15px 0;letter-spacing:1px;'>admin123</div>
                <p><a href='" . site_url('login') . "' style='display:inline-block;padding:10px 24px;background:#10b981;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>Buka Form Login</a></p>
            </div>
        ");
    }
}

