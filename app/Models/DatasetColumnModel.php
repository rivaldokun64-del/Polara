<?php

namespace App\Models;

use CodeIgniter\Model;

class DatasetColumnModel extends Model
{
    protected $table            = 'dataset_columns';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_dataset',
        'nama_kolom_sumber',
        'posisi_kolom',
        'tipe_terdeteksi',
        'pemetaan_atribut',
        'aturan_transformasi',
        'is_fitur',
        'is_target',
        'konfirmasi_pengguna',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'id_dataset'        => 'required|is_natural_no_zero',
        'nama_kolom_sumber' => 'required|min_length[1]|max_length[150]',
        'posisi_kolom'      => 'permit_empty|is_natural',
        'is_fitur'          => 'permit_empty|in_list[0,1]',
        'is_target'         => 'permit_empty|in_list[0,1]',
    ];

    /**
     * Dapatkan daftar kolom berdasarkan id_dataset
     */
    public function getByDatasetId(int $datasetId): array
    {
        return $this->where('id_dataset', $datasetId)
            ->orderBy('posisi_kolom', 'ASC')
            ->findAll();
    }

    /**
     * Dapatkan kolom yang ditandai sebagai atribut fitur
     */
    public function getFeatureColumns(int $datasetId): array
    {
        return $this->where('id_dataset', $datasetId)
            ->where('is_fitur', 1)
            ->orderBy('posisi_kolom', 'ASC')
            ->findAll();
    }

    /**
     * Dapatkan kolom yang ditandai sebagai target klasifikasi
     */
    public function getTargetColumn(int $datasetId): ?array
    {
        return $this->where('id_dataset', $datasetId)
            ->where('is_target', 1)
            ->first();
    }
}
