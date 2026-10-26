<?php

namespace App\Models;

use CodeIgniter\Model;

class AnalisisModel extends Model
{
    protected $table            = 'analisis';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_dataset',
        'algoritma',
        'parameter',
        'atribut_target',
        'id_user',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false;

    /**
     * Dapatkan daftar riwayat analisis beserta nama dataset dan pengunggah
     */
    public function getHistoryWithDetails(int $limit = 50): array
    {
        return $this->select('analisis.*, datasets.nama_dataset, users.nama as nama_user, users.role as role_user')
            ->join('datasets', 'datasets.id = analisis.id_dataset', 'left')
            ->join('users', 'users.id = analisis.id_user', 'left')
            ->orderBy('analisis.created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Dapatkan detail analisis tertentu beserta metrik evaluasinya
     */
    public function getDetail(int $id): ?array
    {
        $run = $this->select('analisis.*, datasets.nama_dataset, datasets.nama_file_asli, users.nama as nama_user, users.role as role_user')
            ->join('datasets', 'datasets.id = analisis.id_dataset', 'left')
            ->join('users', 'users.id = analisis.id_user', 'left')
            ->where('analisis.id', $id)
            ->first();

        if (!$run) {
            return null;
        }

        $db = \Config\Database::connect();
        $run['evaluasi'] = $db->table('evaluasi')->where('id_analisis', $id)->get()->getResultArray();

        if ($run['algoritma'] === 'kmeans') {
            $run['cluster_counts'] = $db->table('hasil_cluster')
                ->select('cluster, COUNT(*) as count')
                ->where('id_analisis', $id)
                ->groupBy('cluster')
                ->orderBy('cluster', 'ASC')
                ->get()
                ->getResultArray();

            $run['sample_members'] = $db->table('hasil_cluster')
                ->select('hasil_cluster.cluster, data_kunjungan.*')
                ->join('data_kunjungan', 'data_kunjungan.id = hasil_cluster.id_kunjungan', 'left')
                ->where('hasil_cluster.id_analisis', $id)
                ->limit(25)
                ->get()
                ->getResultArray();
        } elseif ($run['algoritma'] === 'c45') {
            $run['rules'] = $db->table('aturan_c45')
                ->where('id_analisis', $id)
                ->orderBy('support', 'DESC')
                ->get()
                ->getResultArray();

            $run['sample_predictions'] = $db->table('hasil_klasifikasi')
                ->select('hasil_klasifikasi.*, data_kunjungan.usia, data_kunjungan.jenis_kelamin, data_kunjungan.nama_diagnosis, data_kunjungan.unit_pelayanan')
                ->join('data_kunjungan', 'data_kunjungan.id = hasil_klasifikasi.id_kunjungan', 'left')
                ->where('hasil_klasifikasi.id_analisis', $id)
                ->limit(25)
                ->get()
                ->getResultArray();
        } elseif ($run['algoritma'] === 'naivebayes') {
            $run['sample_predictions'] = $db->table('hasil_klasifikasi')
                ->select('hasil_klasifikasi.*, data_kunjungan.usia, data_kunjungan.jenis_kelamin, data_kunjungan.nama_diagnosis, data_kunjungan.unit_pelayanan')
                ->join('data_kunjungan', 'data_kunjungan.id = hasil_klasifikasi.id_kunjungan', 'left')
                ->where('hasil_klasifikasi.id_analisis', $id)
                ->limit(25)
                ->get()
                ->getResultArray();
        }

        return $run;
    }

    /**
     * Hapus riwayat analisis beserta seluruh data evaluasi dan hasil terkait
     */
    public function deleteCascade(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('evaluasi')->where('id_analisis', $id)->delete();
        $db->table('hasil_cluster')->where('id_analisis', $id)->delete();
        $db->table('aturan_c45')->where('id_analisis', $id)->delete();
        $db->table('hasil_klasifikasi')->where('id_analisis', $id)->delete();
        $this->delete($id);

        $db->transComplete();
        return $db->transStatus();
    }
}
