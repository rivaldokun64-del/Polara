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
}
