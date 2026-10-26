<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama',
        'username',
        'password',
        'role',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama'     => 'required|min_length[3]|max_length[100]',
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'password' => 'required|min_length[6]',
        'role'     => 'required|in_list[admin,analis,pimpinan]',
        'status'   => 'required|in_list[aktif,nonaktif]',
    ];

    protected $validationMessages = [
        'nama' => [
            'required'   => 'Nama lengkap wajib diisi.',
            'min_length' => 'Nama minimal 3 karakter.',
            'max_length' => 'Nama maksimal 100 karakter.',
        ],
        'username' => [
            'required'   => 'Username wajib diisi.',
            'min_length' => 'Username minimal 3 karakter.',
            'max_length' => 'Username maksimal 50 karakter.',
            'is_unique'  => 'Username sudah terdaftar, gunakan username lain.',
        ],
        'password' => [
            'required'   => 'Password wajib diisi.',
            'min_length' => 'Password minimal 6 karakter.',
        ],
        'role' => [
            'required' => 'Peran pengguna wajib dipilih.',
            'in_list'  => 'Peran pengguna tidak valid.',
        ],
        'status' => [
            'required' => 'Status akun wajib ditentukan.',
            'in_list'  => 'Status akun tidak valid.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hashing password secara aman menggunakan BCRYPT
     */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            // Hindari double-hashing bila input sudah berupa hash bcrypt
            $info = password_get_info($data['data']['password']);
            if ($info['algoName'] === 'unknown' || $info['algo'] === null) {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
            }
        } else {
            unset($data['data']['password']);
        }

        return $data;
    }

    /**
     * Cari pengguna aktif/nonaktif berdasarkan username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }
}
