<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Tampilkan daftar akun pengguna
     */
    public function index()
    {
        // Guard peran khusus Administrator
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak: Hanya Administrator yang diizinkan mengelola pengguna.');
        }

        $users = $this->userModel->orderBy('id', 'ASC')->findAll();

        $stats = [
            'total'    => count($users),
            'admin'    => 0,
            'analis'   => 0,
            'pimpinan' => 0,
            'aktif'    => 0,
        ];

        foreach ($users as $u) {
            if (isset($stats[$u['role']])) $stats[$u['role']]++;
            if ($u['status'] === 'aktif') $stats['aktif']++;
        }

        return view('users/index', [
            'title' => 'Manajemen Pengguna — Polara',
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Tambah akun pengguna baru
     */
    public function create()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak: Hanya Administrator yang diizinkan menambah pengguna.');
        }

        $rules = [
            'nama'     => 'required|min_length[3]|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,analis,pimpinan]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->userModel->insert([
            'nama'     => trim((string) $this->request->getPost('nama')),
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'password' => (string) $this->request->getPost('password'),
            'role'     => (string) $this->request->getPost('role'),
            'status'   => 'aktif',
        ]);

        return redirect()->to(site_url('users'))
            ->with('success', 'Akun pengguna baru berhasil ditambahkan.');
    }

    /**
     * Ubah status aktif / nonaktif akun pengguna
     */
    public function toggleStatus($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak: Hanya Administrator yang diizinkan mengubah status akun.');
        }

        $id = (int) $id;
        $currentUserId = (int) session()->get('user_id');

        // Proteksi: jangan izinkan admin menonaktifkan akunnya sendiri yang sedang aktif
        if ($id === $currentUserId) {
            return redirect()->to(site_url('users'))
                ->with('error', 'Keamanan: Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(site_url('users'))->with('error', 'Pengguna tidak ditemukan.');
        }

        $newStatus = ($user['status'] === 'aktif') ? 'nonaktif' : 'aktif';
        $this->userModel->update($id, ['status' => $newStatus]);

        return redirect()->to(site_url('users'))
            ->with('success', "Status akun '{$user['username']}' berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Edit data pengguna (Nama, Role, dan/atau Password baru)
     */
    public function update($id)

    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak: Hanya Administrator yang diizinkan mengedit akun pengguna.');
        }

        $id = (int) $id;
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(site_url('users'))->with('error', 'Pengguna tidak ditemukan.');
        }

        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]',
            'role' => 'required|in_list[admin,analis,pimpinan]',
        ];

        $newPassword = (string) $this->request->getPost('password');
        if (!empty($newPassword)) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'nama' => trim((string) $this->request->getPost('nama')),
            'role' => (string) $this->request->getPost('role'),
        ];

        if (!empty($newPassword)) {
            $updateData['password'] = $newPassword; // Otomatis di-hash oleh UserModel callback
        }

        $this->userModel->update($id, $updateData);

        // Jika mengupdate akun yang sedang login, update sesi nama & role
        if ($id === (int) session()->get('user_id')) {
            session()->set([
                'nama' => $updateData['nama'],
                'role' => $updateData['role'],
            ]);
        }

        return redirect()->to(site_url('users'))
            ->with('success', "Akun '{$user['username']}' berhasil diperbarui.");
    }
}

