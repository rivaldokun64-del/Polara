<?php

namespace App\Models;

use CodeIgniter\Model;

class DataKunjunganModel extends Model
{
    protected $table            = 'data_kunjungan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_dataset',
        'periode',
        'usia',
        'kelompok_usia',
        'jenis_kelamin',
        'kode_diagnosis',
        'nama_diagnosis',
        'kelompok_diagnosis',
        'unit_pelayanan',
        'jumlah_kunjungan',
        'jenis_data',
        'id_diagnosis',
        'nama_diagnosis_asli',
        'jumlah',
        'peringkat_asal',
        'sumber',
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'id_dataset' => 'required|is_natural_no_zero',
    ];

    /**
     * Hitung total rekam kunjungan untuk suatu dataset
     */
    public function countByDataset(int $datasetId): int
    {
        return $this->where('id_dataset', $datasetId)->countAllResults();
    }

    /**
     * Ambil sampel data kunjungan untuk verifikasi atau pratinjau
     */
    public function getSampleByDataset(int $datasetId, int $limit = 10): array
    {
        return $this->where('id_dataset', $datasetId)->findAll($limit);
    }
}
