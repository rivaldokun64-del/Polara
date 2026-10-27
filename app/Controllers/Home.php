<?php

namespace App\Controllers;

use App\Models\DatasetModel;
use App\Models\DataKunjunganModel;
use App\Services\SpreadsheetService;
use App\Services\DatasetProfiler;
use Config\Database;

class Home extends BaseController
{
    /**
     * Halaman Beranda Publik Polara
     */
    public function index(): string
    {
        return view('home', [
            'title'      => 'Polara — Platform Analitik Data Kesehatan',
            'isLoggedIn' => (bool) session()->get('isLoggedIn'),
            'nama'       => session()->get('nama'),
            'role'       => session()->get('role'),
        ]);
    }

    public function migrateTables()
    {
        $db = Database::connect('default');

        $queries = [
            // 1. users
            "CREATE TABLE IF NOT EXISTS `users` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `nama` varchar(100) NOT NULL,
              `username` varchar(50) NOT NULL,
              `password` varchar(255) NOT NULL,
              `role` enum('admin','analis','pimpinan') NOT NULL DEFAULT 'analis',
              `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 2. datasets
            "CREATE TABLE IF NOT EXISTS `datasets` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `nama_dataset` varchar(255) NOT NULL,
              `nama_file_asli` varchar(255) DEFAULT NULL,
              `nama_file_internal` varchar(255) DEFAULT NULL,
              `ukuran_file` bigint(20) unsigned DEFAULT 0,
              `format_file` varchar(20) DEFAULT NULL,
              `deskripsi` text DEFAULT NULL,
              `periode_awal` date DEFAULT NULL,
              `periode_akhir` date DEFAULT NULL,
              `jumlah_baris` int(10) unsigned DEFAULT 0,
              `status` enum('uploaded','diproses','siap','error') DEFAULT 'uploaded',
              `profiling_meta` longtext DEFAULT NULL,
              `id_user` int(10) unsigned NOT NULL,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 3. dataset_columns (sesuai DatasetColumnModel)
            "DROP TABLE IF EXISTS `dataset_columns`",
            "CREATE TABLE `dataset_columns` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_dataset` int(10) unsigned NOT NULL,
              `nama_kolom_sumber` varchar(150) NOT NULL,
              `posisi_kolom` int(10) unsigned NOT NULL DEFAULT 0,
              `tipe_terdeteksi` varchar(50) DEFAULT NULL,
              `pemetaan_atribut` varchar(100) DEFAULT NULL,
              `aturan_transformasi` text DEFAULT NULL,
              `is_fitur` tinyint(1) NOT NULL DEFAULT 1,
              `is_target` tinyint(1) NOT NULL DEFAULT 0,
              `konfirmasi_pengguna` tinyint(1) NOT NULL DEFAULT 0,
              `created_at` datetime DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `id_dataset` (`id_dataset`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 4. data_kunjungan (sesuai DataKunjunganModel)
            "CREATE TABLE IF NOT EXISTS `data_kunjungan` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_dataset` int(10) unsigned NOT NULL,
              `periode` date DEFAULT NULL,
              `usia` int(10) unsigned DEFAULT NULL,
              `kelompok_usia` varchar(100) DEFAULT NULL,
              `jenis_kelamin` varchar(10) DEFAULT NULL,
              `kode_diagnosis` varchar(10) DEFAULT NULL,
              `nama_diagnosis` varchar(255) DEFAULT NULL,
              `kelompok_diagnosis` varchar(100) DEFAULT NULL,
              `unit_pelayanan` varchar(100) DEFAULT NULL,
              `jumlah_kunjungan` int(10) unsigned DEFAULT 0,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              `jenis_data` varchar(20) DEFAULT 'KUNJUNGAN',
              `id_diagnosis` int(10) unsigned DEFAULT NULL,
              `nama_diagnosis_asli` varchar(255) DEFAULT NULL,
              `jumlah` int(10) unsigned DEFAULT 0,
              `peringkat_asal` int(10) unsigned DEFAULT NULL,
              `sumber` varchar(50) DEFAULT 'top10',
              `raw_data` longtext DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_dataset` (`id_dataset`),
              KEY `idx_periode` (`periode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 5. analisis (sesuai AnalisisModel)
            "DROP TABLE IF EXISTS `analisis`",
            "CREATE TABLE `analisis` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_dataset` int(10) unsigned NOT NULL,
              `algoritma` enum('kmeans','c45','naivebayes') NOT NULL,
              `parameter` longtext DEFAULT NULL,
              `atribut_target` varchar(100) DEFAULT NULL,
              `id_user` int(10) unsigned NOT NULL,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `id_dataset` (`id_dataset`),
              KEY `id_user` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 6. aturan_c45
            "CREATE TABLE IF NOT EXISTS `aturan_c45` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_analisis` int(10) unsigned NOT NULL,
              `aturan` text NOT NULL,
              `kelas` varchar(100) NOT NULL,
              `support` int(10) unsigned DEFAULT 0,
              `confidence` decimal(5,4) DEFAULT 0.0000,
              PRIMARY KEY (`id`),
              KEY `id_analisis` (`id_analisis`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 7. evaluasi
            "CREATE TABLE IF NOT EXISTS `evaluasi` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_analisis` int(10) unsigned NOT NULL,
              `metrik` varchar(50) NOT NULL,
              `nilai` decimal(10,6) DEFAULT NULL,
              `detail` longtext DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `id_analisis` (`id_analisis`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 8. hasil_cluster
            "CREATE TABLE IF NOT EXISTS `hasil_cluster` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_analisis` int(10) unsigned NOT NULL,
              `id_kunjungan` int(10) unsigned NOT NULL,
              `cluster` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `id_analisis` (`id_analisis`),
              KEY `id_kunjungan` (`id_kunjungan`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 9. hasil_klasifikasi
            "CREATE TABLE IF NOT EXISTS `hasil_klasifikasi` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_analisis` int(10) unsigned NOT NULL,
              `id_kunjungan` int(10) unsigned NOT NULL,
              `kelas_aktual` varchar(100) DEFAULT NULL,
              `kelas_prediksi` varchar(100) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `id_analisis` (`id_analisis`),
              KEY `id_kunjungan` (`id_kunjungan`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // 10. diagnosis_master & diagnosis_alias & ekspor_log & laporan_bulanan
            "CREATE TABLE IF NOT EXISTS `diagnosis_master` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kode_icd` varchar(20) NOT NULL,
              `nama_baku` varchar(255) NOT NULL,
              `kelompok_diagnosis` varchar(100) DEFAULT NULL,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `kode_icd` (`kode_icd`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `diagnosis_alias` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_diagnosis` int(10) unsigned NOT NULL,
              `nama_alias` varchar(255) NOT NULL,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `id_diagnosis` (`id_diagnosis`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `ekspor_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_user` int(10) unsigned NOT NULL,
              `jenis_ekspor` varchar(100) NOT NULL,
              `format` varchar(10) NOT NULL,
              `filter` longtext DEFAULT NULL,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `id_user` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS `laporan_bulanan` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_dataset` int(10) unsigned NOT NULL,
              `nama_faskes` varchar(255) NOT NULL,
              `periode` date NOT NULL,
              `jumlah_kapitasi` int(10) unsigned DEFAULT 0,
              `total_kunjungan` int(10) unsigned DEFAULT 0,
              `kunjungan_l` int(10) unsigned DEFAULT 0,
              `kunjungan_p` int(10) unsigned DEFAULT 0,
              `total_rujukan` int(10) unsigned DEFAULT 0,
              `rujukan_l` int(10) unsigned DEFAULT 0,
              `rujukan_p` int(10) unsigned DEFAULT 0,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($queries as $q) {
            $db->query($q);
        }

        return redirect()->to(site_url('dashboard'))->with('success', 'Skema tabel sistem berhasil diperbarui.');
    }

    /**

     * Halaman Dashboard Terproteksi Polara (Memerlukan Autentikasi)
     */
    public function dashboard(): string
    {
        $db = Database::connect('default');

        // Global stats
        $stats = [
            'total_datasets'  => (int) $db->table('datasets')->countAllResults(),
            'total_kunjungan' => (int) $db->table('data_kunjungan')->countAllResults(),
            'total_analisis'  => (int) $db->table('analisis')->countAllResults(),
            'total_users'     => (int) $db->table('users')->where('status', 'aktif')->countAllResults(),
        ];

        // Semua dataset untuk dropdown pemilihan
        $datasetModel   = new DatasetModel();
        $allDatasets    = $datasetModel->getWithUploader(100);
        $recentDatasets = array_slice($allDatasets, 0, 5);

        // Dataset aktif: prioritaskan yang berstatus 'siap'
        $activeDataset = null;
        foreach ($allDatasets as $ds) {
            if ($ds['status'] === 'siap') {
                $activeDataset = $ds;
                break;
            }
        }
        if (!$activeDataset && !empty($allDatasets)) {
            $activeDataset = $allDatasets[0];
        }

        // Riwayat analisis terbaru
        $recentAnalisis = $db->table('analisis')
            ->select('analisis.*, datasets.nama_dataset, users.nama as nama_user')
            ->join('datasets', 'datasets.id = analisis.id_dataset', 'left')
            ->join('users', 'users.id = analisis.id_user', 'left')
            ->orderBy('analisis.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return view('dashboard', [
            'title'          => 'Dashboard — Polara',
            'stats'          => $stats,
            'allDatasets'    => $allDatasets,
            'recentDatasets' => $recentDatasets,
            'activeDataset'  => $activeDataset,
            'recentAnalisis' => $recentAnalisis,
        ]);
    }

    /**
     * AJAX: Statistik dan grafik per dataset yang dipilih
     * Mendukung semua jenis dataset (SIMRS maupun non-SIMRS)
     */
    public function datasetStats(int $datasetId)
    {
        $db = Database::connect('default');

        // Validasi dataset exists
        $dataset = $db->table('datasets')->where('id', $datasetId)->get()->getRowArray();
        if (!$dataset) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Dataset tidak ditemukan']);
        }

        // Pengunggah
        $uploader = $db->table('users')->where('id', (int)($dataset['id_user'] ?? 0))->get()->getRowArray();
        $dataset['uploader_nama'] = $uploader['nama'] ?? 'Sistem';
        $dataset['uploader_role'] = $uploader['role'] ?? 'analis';

        // ── Auto-generate / refresh profiling_meta ──
        // Re-profile jika: belum ada, atau format lama yang mengandung 'Kolom_2' padahal header aslinya sudah terdeteksi
        $profilingMeta = !empty($dataset['profiling_meta']) ? json_decode($dataset['profiling_meta'], true) : null;
        $hasDummyHeader = false;
        if (!empty($profilingMeta['columns'])) {
            foreach ($profilingMeta['columns'] as $colP) {
                if (preg_match('/^Kolom_\d+$/i', $colP['name'] ?? '')) {
                    $hasDummyHeader = true;
                    break;
                }
            }
        }
        $needsReprofiling = empty($profilingMeta) || empty($profilingMeta['dashboard_config']) || $hasDummyHeader;
        if ($needsReprofiling && !empty($dataset['nama_file_internal'])) {
            $filePath = WRITEPATH . 'uploads/datasets/' . $dataset['nama_file_internal'];
            if (file_exists($filePath)) {
                try {
                    $ss = new SpreadsheetService();
                    $sample = $ss->readSample($filePath, 50);
                    $profiler = new DatasetProfiler($ss);
                    $profilingMeta = $profiler->profile($sample['headers'], $sample['sample_rows']);
                    $db->table('datasets')->where('id', $datasetId)->update([
                        'profiling_meta' => json_encode($profilingMeta, JSON_UNESCAPED_UNICODE)
                    ]);
                    $dataset['profiling_meta'] = json_encode($profilingMeta, JSON_UNESCAPED_UNICODE);
                } catch (\Throwable $e) {
                    // Abaikan jika gagal baca file
                }
            }
        }

        // ── Jumlah baris di data_kunjungan ──
        $totalRows = (int) $db->table('data_kunjungan')->where('id_dataset', $datasetId)->countAllResults();

        // ── Akumulasi total kunjungan / kasus ──
        $sumQuery = $db->table('data_kunjungan')
            ->select("SUM(CASE WHEN jumlah_kunjungan > 0 THEN jumlah_kunjungan WHEN jumlah > 0 THEN jumlah ELSE 1 END) as sum_cases")
            ->where('id_dataset', $datasetId)
            ->get()->getRowArray();
        $totalCases = (int) ($sumQuery['sum_cases'] ?? 0);
        if ($totalCases <= 0) {
            $totalCases = $totalRows;
        }

        // ── Label total adaptif dari DatasetProfiler ──
        $dashCfg     = $profilingMeta['dashboard_config'] ?? null;
        $datasetType = $profilingMeta['dataset_type']    ?? 'umum';
        if ($dashCfg) {
            // Gunakan label dari dashboard_config hasil profiling
            $cardTotalLabel = $dashCfg['measure_label'] ?? 'Total Data';
            $cardTotalSub   = $dashCfg['measure_unit']  ?? 'Catatan Terdata';
        } else {
            // Fallback berdasarkan nama dataset
            $dsNameLower = strtolower(($dataset['nama_dataset'] ?? '') . ' ' . ($dataset['nama_file_asli'] ?? ''));
            if (str_contains($dsNameLower, 'kematian') || str_contains($dsNameLower, 'jiwa')) {
                $cardTotalLabel = 'Kasus Kematian';
                $cardTotalSub   = 'Jiwa / Kasus Tercatat';
            } elseif (str_contains($dsNameLower, 'kunjungan')) {
                $cardTotalLabel = 'Total Kunjungan';
                $cardTotalSub   = 'Kunjungan Tercatat';
            } else {
                $cardTotalLabel = 'Total Kasus / Data';
                $cardTotalSub   = 'Catatan Terdata';
            }
        }

        // ── Akurasi Model Terbaik & Silhouette Clustering ──
        $bestAccuracy = '-';
        $bestAccuracyDesc = 'Belum dievaluasi';
        $bestAnalisis = $db->table('analisis')
            ->select('analisis.id, analisis.algoritma, evaluasi.nilai')
            ->join('evaluasi', 'evaluasi.id_analisis = analisis.id', 'inner')
            ->where('analisis.id_dataset', $datasetId)
            ->like('evaluasi.metrik', 'Akurasi', 'both')
            ->orderBy('evaluasi.nilai', 'DESC')
            ->get()->getRowArray();
        if ($bestAnalisis) {
            $bestAccuracy = number_format((float)$bestAnalisis['nilai'], 1) . '%';
            $bestAccuracyDesc = strtoupper($bestAnalisis['algoritma']);
        }

        $silhouetteScore = '-';
        $silhouetteDesc = 'K-Means belum dijalankan';
        $kmeansRun = $db->table('analisis')
            ->select('evaluasi.nilai')
            ->join('evaluasi', 'evaluasi.id_analisis = analisis.id', 'inner')
            ->where('analisis.id_dataset', $datasetId)
            ->where('analisis.algoritma', 'kmeans')
            ->like('evaluasi.metrik', 'Silhouette', 'both')
            ->orderBy('analisis.created_at', 'DESC')
            ->get()->getRowArray();
        if ($kmeansRun) {
            $silhouetteScore = number_format((float)$kmeansRun['nilai'], 3);
            $silhouetteDesc = 'K-Means dievaluasi';
        }

        $totalDatasetsCount = (int) $db->table('datasets')->countAllResults();
        $totalDatasetsLabel = $totalDatasetsCount . ' Total Dataset';

        // ── Kolom dan Atribut dataset ──
        $columns = $db->table('dataset_columns')
            ->where('id_dataset', $datasetId)
            ->orderBy('posisi_kolom', 'ASC')
            ->get()->getResultArray();

        $totalFeatures = 0;
        $targetColumnName = null;
        foreach ($columns as &$c) {
            $colNorm = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$c['nama_kolom_sumber']));
            
            // Kolom no, nomor, id, nik, jumlah, dan periode jangan pernah jadi atribut fitur
            $isIdentitasOrMeasure = in_array($colNorm, ['no', 'nomor', 'id', 'norm', 'nomorrm', 'nik'], true) ||
                in_array($c['pemetaan_atribut'] ?? '', ['identitas', 'jumlah', 'periode', 'jumlah_kunjungan'], true);

            if ($isIdentitasOrMeasure) {
                $c['is_fitur'] = 0;
            }

            if (!empty($c['is_fitur'])) {
                $totalFeatures++;
            }
            if (!empty($c['is_target'])) {
                $targetColumnName = $c['nama_kolom_sumber'];
            }
            // Tambah alias untuk frontend
            $c['nama_kolom'] = $c['nama_kolom_sumber'] ?? $c['nama_kolom'] ?? 'Kolom';
            $c['tipe_data'] = $c['tipe_terdeteksi'] ?? 'kategorik';
            
            // Tentukan peran semantik yang akurat
            $detectedRole = $c['pemetaan_atribut'] ?? null;
            if (empty($detectedRole) && !empty($profilingMeta['column_profiles'])) {
                foreach ($profilingMeta['column_profiles'] as $cp) {
                    if (($cp['name'] ?? '') === $c['nama_kolom_sumber']) {
                        $detectedRole = $cp['role'] ?? null;
                        break;
                    }
                }
            }
            $c['peran'] = $c['is_target'] ? 'target' : ($detectedRole ?: ($c['is_fitur'] ? 'fitur' : 'informasi'));
        }
        unset($c);

        if ($totalFeatures === 0) {
            // Hitung kolom non-identitas & non-measure
            $validCols = array_filter($columns, function($c) {
                $norm = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$c['nama_kolom_sumber']));
                return !in_array($norm, ['no', 'nomor', 'id', 'norm', 'nik'], true) &&
                    !in_array($c['pemetaan_atribut'] ?? '', ['identitas', 'jumlah', 'periode', 'jumlah_kunjungan'], true);
            });
            $totalFeatures = count($validCols);
        }

        // ── Jika dataset memiliki data_kunjungan, gunakan itu ──
        // ── Jika tidak (dataset tidak dipetakan), baca langsung dari file ──
        $hasKunjunganData = ($totalRows > 0);

        // ── Rentang periode ──
        $periodeLabel = 'Semua Periode';
        if ($hasKunjunganData) {
            $range = $db->table('data_kunjungan')
                ->selectMin('periode', 'min_date')
                ->selectMax('periode', 'max_date')
                ->where('id_dataset', $datasetId)
                ->where('periode IS NOT NULL')
                ->where("periode != '0000-00-00'")
                ->get()->getRowArray();
            $indoMonthsShort = [
                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
                '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
            ];
            if (!empty($range['min_date']) && !empty($range['max_date'])) {
                $y1 = substr($range['min_date'], 0, 4);
                $m1 = $indoMonthsShort[substr($range['min_date'], 5, 2)] ?? substr($range['min_date'], 5, 2);
                $y2 = substr($range['max_date'], 0, 4);
                $m2 = $indoMonthsShort[substr($range['max_date'], 5, 2)] ?? substr($range['max_date'], 5, 2);
                $periodeLabel = ($y1 === $y2 && $m1 === $m2) ? "$m1 $y1" : "$m1 $y1 – $m2 $y2";
            }
        }
        if (!empty($dataset['periode_awal']) && !empty($dataset['periode_akhir'])) {
            $p1 = date('M Y', strtotime($dataset['periode_awal']));
            $p2 = date('M Y', strtotime($dataset['periode_akhir']));
            $periodeLabel = ($p1 === $p2) ? $p1 : "$p1 – $p2";
        }

        // ── Distribusi Jenis Kelamin (Agregasi SUM jumlah_kunjungan) ──
        $genderDist = [];
        if ($hasKunjunganData) {
            $genderDist = $db->table('data_kunjungan')
                ->select("jenis_kelamin, SUM(CASE WHEN jumlah_kunjungan > 0 THEN jumlah_kunjungan WHEN jumlah > 0 THEN jumlah ELSE 1 END) as total")
                ->where('id_dataset', $datasetId)
                ->whereIn('jenis_kelamin', ['L', 'P'])
                ->groupBy('jenis_kelamin')
                ->orderBy('total', 'DESC')
                ->get()->getResultArray();
        }

        // ── Distribusi Kelompok Usia (Nilai APA ADANYA, Agregasi SUM jumlah_kunjungan, Urut Termuda) ──
        $ageDist = [];
        if ($hasKunjunganData) {
            $rawAge = $db->table('data_kunjungan')
                ->select("kelompok_usia, SUM(CASE WHEN jumlah_kunjungan > 0 THEN jumlah_kunjungan WHEN jumlah > 0 THEN jumlah ELSE 1 END) as total")
                ->where('id_dataset', $datasetId)
                ->where('kelompok_usia IS NOT NULL')
                ->where("kelompok_usia != ''")
                ->where("kelompok_usia != 'Umum'")
                ->groupBy('kelompok_usia')
                ->get()->getResultArray();

            if (empty($rawAge)) {
                $rawAge = $db->table('data_kunjungan')
                    ->select("kelompok_usia, SUM(CASE WHEN jumlah_kunjungan > 0 THEN jumlah_kunjungan WHEN jumlah > 0 THEN jumlah ELSE 1 END) as total")
                    ->where('id_dataset', $datasetId)
                    ->where('kelompok_usia IS NOT NULL')
                    ->where("kelompok_usia != ''")
                    ->groupBy('kelompok_usia')
                    ->get()->getResultArray();
            }

            // Urutkan dari yang termuda (Anak -> Remaja -> Dewasa -> Lansia)
            $ageRank = function($label) {
                $l = strtolower(trim((string)$label));
                if (preg_match('/(balita|bayi|toddler|infant)/i', $l)) return 1;
                if (preg_match('/(anak|child|kid|pediatri)/i', $l)) return 2;
                if (preg_match('/(remaja|teen|adolescent)/i', $l)) return 3;
                if (preg_match('/(dewasa|adult)/i', $l)) return 4;
                if (preg_match('/(lansia|elderly|geriatri|tua)/i', $l)) return 5;
                if (preg_match('/(\d+)/', $l, $m)) {
                    return 10 + (int)$m[1];
                }
                return 99;
            };

            usort($rawAge, fn($a, $b) => $ageRank($a['kelompok_usia']) <=> $ageRank($b['kelompok_usia']));
            $ageDist = array_values($rawAge);
        }

        // ── Top Kategori / Diagnosis ──
        // Strategi universal: coba dari nama_diagnosis, lalu fallback ke sumber (target field), 
        // lalu fallback ke unit_pelayanan sebagai kategori pengganti
        $topDiagnosis = [];
        if ($hasKunjunganData) {
            // Cek apakah nama_diagnosis berisi data valid (bukan binary garbage)
            $checkDiag = $db->table('data_kunjungan')
                ->select('nama_diagnosis')
                ->where('id_dataset', $datasetId)
                ->where('nama_diagnosis IS NOT NULL')
                ->where("nama_diagnosis != ''")
                ->limit(5)
                ->get()->getResultArray();

            $diagIsValid = false;
            foreach ($checkDiag as $row) {
                $val = $row['nama_diagnosis'] ?? '';
                // Cek apakah string bisa dibaca sebagai teks (bukan binary)
                if (mb_detect_encoding($val, 'UTF-8', true) !== false
                    && preg_match('/^[\x20-\x7E\xC0-\xFF\s]{2,}/u', $val)) {
                    $diagIsValid = true;
                    break;
                }
            }

            if ($diagIsValid) {
                $topDiagnosis = $db->table('data_kunjungan')
                    ->select('COALESCE(nama_diagnosis, kode_diagnosis, "Lainnya") as label, SUM(jumlah_kunjungan) as total')
                    ->where('id_dataset', $datasetId)
                    ->where('nama_diagnosis IS NOT NULL')
                    ->where("nama_diagnosis != ''")
                    ->groupBy('COALESCE(nama_diagnosis, kode_diagnosis, "Lainnya")')
                    ->orderBy('total', 'DESC')
                    ->limit(10)
                    ->get()->getResultArray();
                // Filter: buang label yang tampak seperti binary
                $topDiagnosis = array_filter($topDiagnosis, function($r) {
                    $l = $r['label'] ?? '';
                    return strlen($l) > 0
                        && mb_detect_encoding($l, 'UTF-8', true) !== false
                        && preg_match('/[\x20-\x7E\xC0-\xFF]/u', $l)
                        && !preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $l);
                });
                $topDiagnosis = array_values($topDiagnosis);
            }

            // Fallback: gunakan unit_pelayanan sebagai top kategori
            if (empty($topDiagnosis)) {
                $topDiagnosis = $db->table('data_kunjungan')
                    ->select('unit_pelayanan as label, SUM(jumlah_kunjungan) as total')
                    ->where('id_dataset', $datasetId)
                    ->where('unit_pelayanan IS NOT NULL')
                    ->where("unit_pelayanan != ''")
                    ->where("unit_pelayanan != 'Umum'")
                    ->groupBy('unit_pelayanan')
                    ->orderBy('total', 'DESC')
                    ->limit(10)
                    ->get()->getResultArray();
            }

            // Jika hanya berisi nama_dataset atau kosong, biarkan kosong agar diekstrak cerdas dari file
            if (!empty($topDiagnosis) && count($topDiagnosis) === 1 && ($topDiagnosis[0]['label'] === $dataset['nama_dataset'] || $topDiagnosis[0]['label'] === 'Umum')) {
                $topDiagnosis = [];
            }
        }

        // ── Top Unit Pelayanan / Indikator Penyakit ──
        $topUnit = [];
        if ($hasKunjunganData) {
            $rawUnit = $db->table('data_kunjungan')
                ->select('unit_pelayanan, SUM(jumlah_kunjungan) as total')
                ->where('id_dataset', $datasetId)
                ->where('unit_pelayanan IS NOT NULL')
                ->where("unit_pelayanan != ''")
                ->where("unit_pelayanan != 'Umum'")
                ->groupBy('unit_pelayanan')
                ->orderBy('total', 'DESC')
                ->limit(8)
                ->get()->getResultArray();

            // Jika unit_pelayanan hanya memiliki 1 variasi (misal 'Dinkes / RS / Puskesmas' atau 'Umum'),
            // lakukan breakdown indikator/penyakit dari berkas fisik atau raw_data agar grafik 2 variatif & bermakna
            if (count($rawUnit) <= 1 && !empty($dataset['nama_file_internal'])) {
                try {
                    $ss = new SpreadsheetService();
                    $filePath = WRITEPATH . 'uploads/datasets/' . $dataset['nama_file_internal'];
                    if (file_exists($filePath)) {
                        $parsedFile = $ss->parseXlsx($filePath, 50);
                        $fileHeaders = $parsedFile['headers'] ?? [];
                        $fileRows    = $parsedFile['rows'] ?? [];

                        $indicatorBreakdown = [];
                        for ($c = 1; $c < count($fileHeaders); $c++) {
                            $indName = preg_replace('/\s*\(\d{4}\)$/', '', trim($fileHeaders[$c]));
                            $sum = 0;
                            foreach ($fileRows as $fr) {
                                $wName = trim((string)($fr[0] ?? ''));
                                if (preg_match('/^(jumlah|total|dki\s*jakarta|indonesia)$/i', $wName)) continue;
                                $v = str_replace(['.', ','], '', trim((string)($fr[$c] ?? '0')));
                                if (is_numeric($v)) $sum += (float)$v;
                            }
                            if ($sum > 0) {
                                $indicatorBreakdown[] = [
                                    'unit_pelayanan' => $indName,
                                    'total'          => (int)round($sum),
                                ];
                            }
                        }
                        usort($indicatorBreakdown, fn($a, $b) => $b['total'] <=> $a['total']);
                        if (!empty($indicatorBreakdown)) {
                            $rawUnit = array_slice($indicatorBreakdown, 0, 8);
                        }
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika gagal
                }
            }

            if (empty($rawUnit)) {
                $rawUnit = $db->table('data_kunjungan')
                    ->select('unit_pelayanan, SUM(jumlah_kunjungan) as total')
                    ->where('id_dataset', $datasetId)
                    ->where('unit_pelayanan IS NOT NULL')
                    ->where("unit_pelayanan != ''")
                    ->groupBy('unit_pelayanan')
                    ->orderBy('total', 'DESC')
                    ->limit(8)
                    ->get()->getResultArray();
            }
            $topUnit = $rawUnit;
        }

        // ── Tren kunjungan per periode (monthly / daily / yearly) ──
        $trendData = [];
        if ($hasKunjunganData) {
            $rawTrend = $db->table('data_kunjungan')
                ->select("DATE_FORMAT(periode, '%Y-%m') as bulan, SUM(CASE WHEN jumlah_kunjungan > 0 THEN jumlah_kunjungan WHEN jumlah > 0 THEN jumlah ELSE 1 END) as total")
                ->where('id_dataset', $datasetId)
                ->where('periode IS NOT NULL')
                ->where("periode != '0000-00-00'")
                ->groupBy("DATE_FORMAT(periode, '%Y-%m')")
                ->orderBy('bulan', 'ASC')
                ->limit(60)
                ->get()->getResultArray();

            $uniqueMonths = array_unique(array_column($rawTrend, 'bulan'));
            if (count($uniqueMonths) > 1) {
                // Multibulan: tampilkan tren bulanan
                $trendData = $rawTrend;
            } elseif (count($uniqueMonths) === 1) {
                // Jika hanya 1 bulan terdeteksi, cek apakah ada variasi harian
                $dailyTrend = $db->table('data_kunjungan')
                    ->select("DATE_FORMAT(periode, '%d %b') as bulan, SUM(CASE WHEN jumlah_kunjungan > 0 THEN jumlah_kunjungan WHEN jumlah > 0 THEN jumlah ELSE 1 END) as total")
                    ->where('id_dataset', $datasetId)
                    ->where('periode IS NOT NULL')
                    ->where("periode != '0000-00-00'")
                    ->groupBy("DATE_FORMAT(periode, '%Y-%m-%d')")
                    ->orderBy('periode', 'ASC')
                    ->limit(31)
                    ->get()->getResultArray();

                if (count($dailyTrend) > 1) {
                    $trendData = $dailyTrend;
                } elseif (!empty($rawTrend)) {
                    $trendData = $rawTrend;
                }
            }
        }

        // ── Baca langsung dari file jika kolom tidak terdaftar atau data_kunjungan kosong ──
        // Ini memungkinkan dashboard bisa menampilkan apapun jenis dataset yang diunggah
        $rawFileData = [];
        if ((!$hasKunjunganData || empty($topDiagnosis)) && !empty($dataset['nama_file_internal'])) {
            $filePath = WRITEPATH . 'uploads/datasets/' . $dataset['nama_file_internal'];
            if (file_exists($filePath)) {
                try {
                    $ss = new SpreadsheetService();
                    $sample = $ss->readSample($filePath, 50);
                    $headers = $sample['headers'] ?? [];
                    $rows = $sample['sample_rows'] ?? [];
                    $totalFileRows = $sample['total_rows'] ?? count($rows);

                    $rawFileData = [
                        'headers'     => $headers,
                        'sample_rows' => array_slice($rows, 0, 15),
                        'total_rows'  => $totalFileRows,
                    ];

                    if ($totalRows === 0) {
                        $totalRows = $totalFileRows;
                        $totalFeatures = count($headers);
                    }

                    // Buat columns jika belum terdaftar
                    if (empty($columns) && !empty($headers)) {
                        foreach ($headers as $pos => $h) {
                            $columns[] = [
                                'id' => $pos + 1,
                                'id_dataset' => $datasetId,
                                'nama_kolom_sumber' => $h,
                                'nama_kolom' => $h,
                                'nama_kolom_asli' => $h,
                                'posisi_kolom' => $pos,
                                'tipe_data' => $sample['detected_types'][$pos] ?? 'kategorik',
                                'peran' => ($pos === 0 ? 'entitas' : 'indikator'),
                                'is_fitur' => 1,
                                'is_target' => 0,
                            ];
                        }
                    }

                    // Jika top_diagnosis masih kosong, ekstrak dari baris file
                    if (empty($topDiagnosis) && !empty($rows) && !empty($headers)) {
                        // Cari kolom numerik terlebih dahulu
                        $numericCols = [];
                        foreach ($headers as $cIdx => $h) {
                            $numCount = 0;
                            foreach ($rows as $r) {
                                $v = str_replace(['.', ','], '', trim($r[$cIdx] ?? ''));
                                if (is_numeric($v)) $numCount++;
                            }
                            if ($numCount > count($rows) * 0.3) {
                                $numericCols[] = $cIdx;
                            }
                        }

                        // Cari kolom label terbaik (kolom teks non-numerik, bukan no/id, dan bukan kolom numerik)
                        $labelCol = 0;
                        $maxTextScore = -1;
                        foreach ($headers as $cIdx => $h) {
                            if (in_array($cIdx, $numericCols, true)) continue;
                            $hLower = strtolower($h);
                            if (in_array($hLower, ['no', 'nomor', 'id', 'kode'], true)) continue;

                            $textCount = 0;
                            foreach ($rows as $r) {
                                $val = trim($r[$cIdx] ?? '');
                                $cleanVal = str_replace(['.', ','], '', $val);
                                if (!empty($val) && !is_numeric($cleanVal) && strlen($val) > 1) {
                                    $textCount++;
                                }
                            }
                            if ($textCount > $maxTextScore) {
                                $maxTextScore = $textCount;
                                $labelCol = $cIdx;
                            }
                        }
                        if ($maxTextScore <= 0) {
                            foreach ($headers as $cIdx => $h) {
                                if (!in_array($cIdx, $numericCols, true)) {
                                    $labelCol = $cIdx;
                                    break;
                                }
                            }
                        }

                        if (!empty($numericCols)) {
                            $calcCases = 0;
                            $entityRank = [];
                            foreach ($rows as $r) {
                                $name = trim($r[$labelCol] ?? '');
                                if (empty($name) || preg_match('/^(jumlah|total|indonesia)$/i', $name)) continue;
                                $sum = 0;
                                foreach ($numericCols as $c) {
                                    $sum += (float)str_replace(['.', ','], '', trim($r[$c] ?? '0'));
                                }
                                $calcCases += $sum;
                                $entityRank[] = [
                                    'label' => $name,
                                    'total' => (int)round($sum)
                                ];
                            }
                            usort($entityRank, fn($a, $b) => $b['total'] <=> $a['total']);
                            $topDiagnosis = array_slice($entityRank, 0, 10);
                            if ($totalCases <= 0 && $calcCases > 0) {
                                $totalCases = (int)round($calcCases);
                            }

                            // Buat breakdown indikator untuk unit pelayanan chart
                            if (empty($topUnit)) {
                                $indicatorBreakdown = [];
                                foreach ($numericCols as $c) {
                                    $colName = $headers[$c];
                                    $cleanName = preg_replace('/^Jumlah Kasus Penyakit - /i', '', $colName);
                                    $sum = 0;
                                    foreach ($rows as $r) {
                                        $sum += (float)str_replace(['.', ','], '', trim($r[$c] ?? '0'));
                                    }
                                    if ($sum > 0) {
                                        $indicatorBreakdown[] = [
                                            'unit_pelayanan' => $cleanName,
                                            'total' => (int)round($sum)
                                        ];
                                    }
                                }
                                usort($indicatorBreakdown, fn($a, $b) => $b['total'] <=> $a['total']);
                                $topUnit = array_slice($indicatorBreakdown, 0, 8);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Abaikan error baca file
                }
            }
        }

        // ── Riwayat analisis untuk dataset ini ──
        $analisisHistory = $db->table('analisis')
            ->select('analisis.*, users.nama as nama_user')
            ->join('users', 'users.id = analisis.id_user', 'left')
            ->where('analisis.id_dataset', $datasetId)
            ->orderBy('analisis.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // ── Ekstraksi Struktur Multi-Kategori (Khusus Dataset Analisis Kematian / Tabular Agregat) ──
        $deathCategories = [];
        $provinceRows = [];
        $isDeathAnalysisDataset = false;

        if (!empty($dataset['nama_file_internal'])) {
            $filePath = WRITEPATH . 'uploads/datasets/' . $dataset['nama_file_internal'];
            if (file_exists($filePath)) {
                try {
                    $ss = new SpreadsheetService();
                    if ($ss->isXls($filePath)) {
                        $pXls = $ss->parseXls($filePath, 0);
                        if (!empty($pXls['headers']) && count($pXls['headers']) >= 6 && in_array('Provinsi', $pXls['headers'], true)) {
                            $isDeathAnalysisDataset = true;
                            $pHeaders = $pXls['headers']; // ['Provinsi', 'Penyakit Menular', 'Penyakit Tidak Menular', 'Kecelakaan Lalu Lintas', 'Kecelakaan Lainnya', 'Lainnya', 'Jumlah']
                            $rawRows = $pXls['rows'];
                            $pSummary = $pXls['summary_row'];

                            $parseNum = function($v) {
                                $v = trim((string)$v);
                                if ($v === '' || $v === 'NA' || $v === '-' || $v === '–') return 0;
                                return (float)str_replace(['.', ','], ['', '.'], $v);
                            };

                            // Hitung total kategori dari baris-baris provinsi aktual
                            $catSums = [];
                            foreach ($pHeaders as $ci => $ch) {
                                if ($ci === 0 || $ci === count($pHeaders) - 1) continue; // skip Provinsi & Jumlah
                                $catSums[$ch] = 0;
                            }

                            $totProvDeaths = 0;
                            foreach ($rawRows as $r) {
                                $pName = trim($r[0] ?? '');
                                $rowTotal = $parseNum($r[count($pHeaders) - 1] ?? 0);
                                $totProvDeaths += $rowTotal;

                                $catVals = [];
                                foreach ($pHeaders as $ci => $ch) {
                                    if ($ci === 0 || $ci === count($pHeaders) - 1) continue;
                                    $cVal = $parseNum($r[$ci] ?? 0);
                                    $catSums[$ch] += $cVal;
                                    $catVals[$ch] = $cVal;
                                }

                                $provinceRows[] = [
                                    'provinsi'                  => $pName,
                                    'penyakit_menular'          => $catVals['Penyakit Menular'] ?? 0,
                                    'penyakit_tidak_menular'    => $catVals['Penyakit Tidak Menular'] ?? 0,
                                    'kecelakaan_lalu_lintas'    => $catVals['Kecelakaan Lalu Lintas'] ?? 0,
                                    'kecelakaan_lainnya'        => $catVals['Kecelakaan Lainnya'] ?? 0,
                                    'lainnya'                   => $catVals['Lainnya'] ?? 0,
                                    'jumlah'                    => $rowTotal,
                                    'raw_values'                => $catVals,
                                ];
                            }

                            // Urutkan kategori penyebab kematian dari nilai terbesar ke terkecil
                            foreach ($catSums as $catName => $sumVal) {
                                $deathCategories[] = [
                                    'kategori' => $catName,
                                    'total'    => (int)round($sumVal),
                                ];
                            }
                            usort($deathCategories, fn($a, $b) => $b['total'] <=> $a['total']);

                            // Top provinsi terdata
                            usort($provinceRows, fn($a, $b) => $b['jumlah'] <=> $a['jumlah']);

                            // Pastikan total kasus konsisten dengan total kematian
                            if ($totProvDeaths > 0) {
                                $totalCases = (int)round($totProvDeaths);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika bukan format xls
                }
            }
        }

        return $this->response->setJSON([
            'dataset'                   => $dataset,
            'total_rows'                => $totalRows,
            'total_cases'               => $totalCases,
            'card_total_label'          => $cardTotalLabel,
            'card_total_sub'            => $cardTotalSub,
            'best_accuracy'             => $bestAccuracy,
            'best_accuracy_desc'        => $bestAccuracyDesc,
            'silhouette_score'          => $silhouetteScore,
            'silhouette_desc'           => $silhouetteDesc,
            'total_datasets_label'      => $totalDatasetsLabel,
            'total_features'            => $totalFeatures,
            'target_column'             => $targetColumnName,
            'periode_label'             => $periodeLabel,
            'columns'                   => $columns,
            'gender_dist'               => $genderDist,
            'age_dist'                  => $ageDist,
            'top_diagnosis'             => array_values($topDiagnosis),
            'top_unit'                  => $topUnit,
            'trend'                     => $isDeathAnalysisDataset ? [] : (count($trendData) > 1 ? $trendData : []),
            'analisis_history'          => $analisisHistory,
            'raw_file_data'             => $rawFileData,
            'has_mapping'               => !empty($columns),
            'profiling_meta'            => $profilingMeta,
            'dashboard_config'          => $dashCfg,
            'dataset_type'              => $datasetType,
            'measure_label'             => $cardTotalLabel,
            'measure_unit'              => $cardTotalSub,
            'map_enabled'               => (bool)($dashCfg['map_enabled'] ?? false),
            'map_type'                  => $dashCfg['map_type'] ?? null,
            'is_death_analysis'         => $isDeathAnalysisDataset,
            'death_categories'          => $deathCategories,
            'province_rows'             => $provinceRows,
            'summary_row'               => $pSummary ?? null,
        ]);
    }
}
