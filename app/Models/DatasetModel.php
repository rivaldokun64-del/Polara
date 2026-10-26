<?php

namespace App\Models;

use CodeIgniter\Model;

class DatasetModel extends Model
{
    protected $table            = 'datasets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_dataset',
        'nama_file_asli',
        'nama_file_internal',
        'ukuran_file',
        'format_file',
        'deskripsi',
        'periode_awal',
        'periode_akhir',
        'jumlah_baris',
        'status',
        'profiling_meta',
        'id_user',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama_dataset' => 'required|min_length[3]|max_length[255]',
        'id_user'      => 'required|is_natural_no_zero',
        'status'       => 'permit_empty|in_list[uploaded,diproses,siap,error]',
    ];

    protected $validationMessages = [
        'nama_dataset' => [
            'required'   => 'Nama dataset wajib diisi.',
            'min_length' => 'Nama dataset minimal 3 karakter.',
            'max_length' => 'Nama dataset maksimal 255 karakter.',
        ],
        'id_user' => [
            'required'           => 'ID pengguna pengunggah wajib disertakan.',
            'is_natural_no_zero' => 'ID pengguna tidak valid.',
        ],
    ];

    /**
     * Ambil daftar dataset dengan informasi pengunggah (user)
     */
    public function getWithUploader(int $limit = 20, int $offset = 0): array
    {
        return $this->select('datasets.*, users.nama as nama_pengunggah, users.role as role_pengunggah')
            ->join('users', 'users.id = datasets.id_user', 'left')
            ->orderBy('datasets.created_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Ambil detail dataset beserta kolom-kolom skemanya
     */
    public function getDetailWithColumns(int $datasetId): ?array
    {
        $dataset = $this->select('datasets.*, users.nama as nama_pengunggah')
            ->join('users', 'users.id = datasets.id_user', 'left')
            ->where('datasets.id', $datasetId)
            ->first();

        if (!$dataset) {
            return null;
        }

        $columnModel = new DatasetColumnModel();
        $dataset['columns'] = $columnModel->getByDatasetId($datasetId);

        return $dataset;
    }
}
