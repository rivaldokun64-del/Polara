<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DatasetModel;
use App\Models\DataKunjunganModel;
use App\Models\AnalisisModel;
use Config\Database;

class Laporan extends BaseController
{
    protected DatasetModel $datasetModel;
    protected DataKunjunganModel $kunjunganModel;
    protected AnalisisModel $analisisModel;

    public function __construct()
    {
        $this->datasetModel    = new DatasetModel();
        $this->kunjunganModel  = new DataKunjunganModel();
        $this->analisisModel   = new AnalisisModel();
    }

    /**
     * Halaman Utama Laporan Rekam Medis & Analitik Kesehatan
     */
    public function index()
    {
        $datasets = $this->datasetModel->where('status', 'siap')->orderBy('created_at', 'DESC')->findAll();

        $selectedDatasetId = (int) $this->request->getGet('id_dataset');
        $activeDataset = null;

        if ($selectedDatasetId > 0) {
            $activeDataset = $this->datasetModel->find($selectedDatasetId);
        } elseif (!empty($datasets)) {
            $activeDataset = $datasets[0];
            $selectedDatasetId = (int) $activeDataset['id'];
        }

        $summary = [
            'total_kunjungan'   => 0,
            'gender_dist'       => [],
            'age_group_dist'    => [],
            'service_unit_dist' => [],
            'top_diagnoses'     => [],
        ];

        $analysisRuns = [];

        if ($selectedDatasetId > 0) {
            $db = Database::connect();

            // Total Kunjungan
            $summary['total_kunjungan'] = $this->kunjunganModel->where('id_dataset', $selectedDatasetId)->countAllResults();

            // Distribusi Jenis Kelamin
            $summary['gender_dist'] = $db->table('data_kunjungan')
                ->select("IFNULL(jenis_kelamin, 'Tidak Diketahui') as gender, COUNT(*) as count")
                ->where('id_dataset', $selectedDatasetId)
                ->groupBy('gender')
                ->get()
                ->getResultArray();

            // Distribusi Kelompok Usia
            $summary['age_group_dist'] = $db->table('data_kunjungan')
                ->select("IFNULL(kelompok_usia, 'Lainnya') as age_group, COUNT(*) as count")
                ->where('id_dataset', $selectedDatasetId)
                ->groupBy('age_group')
                ->orderBy('count', 'DESC')
                ->get()
                ->getResultArray();

            // Distribusi Unit Pelayanan
            $summary['service_unit_dist'] = $db->table('data_kunjungan')
                ->select("IFNULL(unit_pelayanan, 'Tidak Terdata') as unit, COUNT(*) as count")
                ->where('id_dataset', $selectedDatasetId)
                ->groupBy('unit')
                ->orderBy('count', 'DESC')
                ->limit(8)
                ->get()
                ->getResultArray();

            // Top 10 Diagnosis Terbanyak (ICD-10)
            $summary['top_diagnoses'] = $db->table('data_kunjungan')
                ->select("kode_diagnosis, IFNULL(nama_diagnosis, 'Diagnosis Tanpa Nama') as nama_diagnosis, COUNT(*) as total")
                ->where('id_dataset', $selectedDatasetId)
                ->where('nama_diagnosis IS NOT NULL', null, false)
                ->groupBy('kode_diagnosis, nama_diagnosis')
                ->orderBy('total', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            // Riwayat Model untuk dataset ini
            $analysisRuns = $this->analisisModel
                ->select('analisis.*, users.nama as nama_user')
                ->join('users', 'users.id = analisis.id_user', 'left')
                ->where('id_dataset', $selectedDatasetId)
                ->orderBy('created_at', 'DESC')
                ->findAll(10);
        }

        return view('laporan/index', [
            'title'             => 'Laporan Analitik Rekam Medis — Polara',
            'datasets'          => $datasets,
            'activeDataset'     => $activeDataset,
            'selectedDatasetId' => $selectedDatasetId,
            'summary'           => $summary,
            'analysisRuns'      => $analysisRuns,
        ]);
    }

    /**
     * Ekspor Rekap Diagnosis dan Kunjungan ke Format CSV
     */
    public function exportCsv($idDataset)
    {
        $dataset = $this->datasetModel->find($idDataset);
        if (!$dataset) {
            return redirect()->to(site_url('laporan'))->with('error', 'Dataset tidak ditemukan.');
        }

        $records = $this->kunjunganModel->where('id_dataset', $idDataset)->findAll();

        $filename = 'Rekap_Kunjungan_' . url_title($dataset['nama_dataset'], '_') . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // UTF-8 BOM agar terbaca sempurna di Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");

        // Header kolom CSV
        fputcsv($output, [
            'ID Rekam',
            'Periode Kunjungan',
            'Usia Pasien',
            'Kelompok Usia',
            'Jenis Kelamin',
            'Kode Diagnosis (ICD-10)',
            'Nama Diagnosis',
            'Unit Pelayanan',
            'Jumlah Kunjungan'
        ]);

        foreach ($records as $r) {
            fputcsv($output, [
                $r['id'],
                $r['periode'] ?? '-',
                $r['usia'] ?? '-',
                $r['kelompok_usia'] ?? '-',
                $r['jenis_kelamin'] ?? '-',
                $r['kode_diagnosis'] ?? '-',
                $r['nama_diagnosis'] ?? '-',
                $r['unit_pelayanan'] ?? '-',
                $r['jumlah_kunjungan'] ?? 1,
            ]);
        }

        fclose($output);
        exit;
    }
}
