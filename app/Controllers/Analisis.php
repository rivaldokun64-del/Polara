<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AnalisisModel;
use App\Models\DatasetModel;
use App\Models\DatasetColumnModel;
use App\Models\DataKunjunganModel;
use App\Services\KMeansService;
use App\Services\C45Service;
use App\Services\NaiveBayesService;
use Config\Database;

class Analisis extends BaseController
{
    protected AnalisisModel $analisisModel;
    protected DatasetModel $datasetModel;
    protected DatasetColumnModel $columnModel;
    protected DataKunjunganModel $kunjunganModel;
    protected KMeansService $kMeansService;
    protected C45Service $c45Service;
    protected NaiveBayesService $naiveBayesService;

    public function __construct()
    {
        $this->analisisModel     = new AnalisisModel();
        $this->datasetModel      = new DatasetModel();
        $this->columnModel       = new DatasetColumnModel();
        $this->kunjunganModel    = new DataKunjunganModel();
        $this->kMeansService     = new KMeansService();
        $this->c45Service        = new C45Service();
        $this->naiveBayesService = new NaiveBayesService();
    }

    /**
     * Tampilkan riwayat pelaksanaan data mining
     */
    public function index()
    {
        $history = $this->analisisModel->getHistoryWithDetails(50);

        $counts = [
            'total'      => count($history),
            'kmeans'     => 0,
            'c45'        => 0,
            'naivebayes' => 0,
        ];

        foreach ($history as $h) {
            $algo = strtolower($h['algoritma']);
            if (isset($counts[$algo])) {
                $counts[$algo]++;
            }
        }

        return view('analisis/index', [
            'title'   => 'Riwayat Analisis Data Mining — Polara',
            'history' => $history,
            'counts'  => $counts,
        ]);
    }

    /**
     * Form inisiasi analisis data mining baru
     */
    public function create()
    {
        $userRole = (string) session()->get('role');
        if (!in_array($userRole, ['admin', 'analis'], true)) {
            return redirect()->to(site_url('analisis'))
                ->with('error', 'Akses ditolak: Hanya Administrator dan Analis yang diizinkan menjalankan data mining.');
        }

        $datasets = $this->datasetModel->where('status', 'siap')->orderBy('created_at', 'DESC')->findAll();

        $selectedDatasetId = $this->request->getGet('dataset_id');
        $activeDataset = null;
        $columns = [];

        if (!empty($selectedDatasetId)) {
            $activeDataset = $this->datasetModel->find($selectedDatasetId);
            if ($activeDataset) {
                $columns = $this->columnModel->where('id_dataset', $selectedDatasetId)->findAll();
            }
        } elseif (!empty($datasets)) {
            $activeDataset = $datasets[0];
            $columns = $this->columnModel->where('id_dataset', $activeDataset['id'])->findAll();
        }

        return view('analisis/create', [
            'title'         => 'Jalankan Analisis Data Mining — Polara',
            'datasets'      => $datasets,
            'activeDataset' => $activeDataset,
            'columns'       => $columns,
        ]);
    }

    /**
     * Proses eksekusi algoritma data mining
     */
    public function run()
    {
        $userRole = (string) session()->get('role');
        if (!in_array($userRole, ['admin', 'analis'], true)) {
            return redirect()->to(site_url('analisis'))
                ->with('error', 'Akses ditolak: Hanya Administrator dan Analis yang diizinkan menjalankan analisis.');
        }

        $idDataset = (int) $this->request->getPost('id_dataset');
        $algoritma = strtolower(trim((string) $this->request->getPost('algoritma')));

        $dataset = $this->datasetModel->find($idDataset);
        if (!$dataset || $dataset['status'] !== 'siap') {
            return redirect()->back()->withInput()->with('error', 'Dataset tidak ditemukan atau belum siap dianalisis.');
        }

        // Ambil data kunjungan dari database
        $rows = $this->kunjunganModel->where('id_dataset', $idDataset)->findAll();
        if (empty($rows)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada baris data kunjungan pada dataset terpilih.');
        }

        $db = Database::connect('default');

        try {
            if ($algoritma === 'kmeans') {
                $features = (array) $this->request->getPost('features');
                if (empty($features)) {
                    $features = ['usia', 'jumlah_kunjungan'];
                }
                $k = max(2, min(10, (int) $this->request->getPost('k') ?: 3));
                $maxIter = max(10, min(300, (int) $this->request->getPost('max_iter') ?: 100));

                $result = $this->kMeansService->run($rows, $features, $k, $maxIter);

                $db->transStart();

                $analisisId = $this->analisisModel->insert([
                    'id_dataset'     => $idDataset,
                    'algoritma'      => 'kmeans',
                    'parameter'      => json_encode([
                        'k'                => $k,
                        'max_iter'         => $maxIter,
                        'features'         => $features,
                        'cluster_profiles' => $result['cluster_profiles'],
                        'converged'        => $result['converged'],
                        'iterations'       => $result['iterations'],
                    ]),
                    'atribut_target' => null,
                    'id_user'        => session()->get('user_id'),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                // Simpan evaluasi metrik (detail wajib berformat JSON valid sesuai skema MySQL)
                $db->table('evaluasi')->insertBatch([
                    [
                        'id_analisis' => $analisisId,
                        'metrik'      => 'Inertia (WCSS)',
                        'nilai'       => $result['inertia'],
                        'detail'      => json_encode(['deskripsi' => 'Total Sum of Squared Errors dalam klaster', 'inertia' => $result['inertia']]),
                    ],
                    [
                        'id_analisis' => $analisisId,
                        'metrik'      => 'Silhouette Coefficient',
                        'nilai'       => $result['silhouette'],
                        'detail'      => json_encode(['deskripsi' => 'Estimasi skor kerapatan dan keterpisahan klaster', 'score' => $result['silhouette']]),
                    ],
                    [
                        'id_analisis' => $analisisId,
                        'metrik'      => 'Iterasi Konvergensi',
                        'nilai'       => $result['iterations'],
                        'detail'      => json_encode(['status' => $result['converged'] ? 'Konvergen' : 'Batas iterasi', 'iterasi' => $result['iterations']]),
                    ],
                ]);

                // Simpan penugasan klaster
                $clusterBatch = [];
                foreach ($rows as $i => $row) {
                    $clusterBatch[] = [
                        'id_analisis'  => $analisisId,
                        'id_kunjungan' => $row['id'],
                        'cluster'      => $result['assignments'][$i] + 1,
                    ];

                    if (count($clusterBatch) >= 500) {
                        $db->table('hasil_cluster')->insertBatch($clusterBatch);
                        $clusterBatch = [];
                    }
                }
                if (!empty($clusterBatch)) {
                    $db->table('hasil_cluster')->insertBatch($clusterBatch);
                }

                $db->transComplete();

                return redirect()->to(site_url('analisis/detail/' . $analisisId))
                    ->with('analysis_success', "Klasterisasi K-Means sukses dijalankan! Terbentuk {$k} klaster dari " . number_format(count($rows)) . " rekam medis.");

            } elseif ($algoritma === 'c45') {
                $target = trim((string) $this->request->getPost('target'));
                $features = (array) $this->request->getPost('features');
                $maxDepth = max(2, min(10, (int) $this->request->getPost('max_depth') ?: 5));

                if (empty($target)) {
                    return redirect()->back()->withInput()->with('error', 'Pilih atribut target untuk klasifikasi C4.5.');
                }

                // Buang target dari fitur jika terpilih tidak sengaja
                $features = array_values(array_filter($features, fn($f) => $f !== $target));
                if (empty($features)) {
                    $features = ['kelompok_usia', 'jenis_kelamin', 'unit_pelayanan'];
                }

                $result = $this->c45Service->run($rows, $features, $target, $maxDepth);

                $db->transStart();

                $analisisId = $this->analisisModel->insert([
                    'id_dataset'     => $idDataset,
                    'algoritma'      => 'c45',
                    'parameter'      => json_encode([
                        'features'  => $features,
                        'max_depth' => $maxDepth,
                        'tree'      => $result['tree'],
                        'classes'   => $result['classes'],
                    ]),
                    'atribut_target' => $target,
                    'id_user'        => session()->get('user_id'),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                // Simpan evaluasi akurasi
                $db->table('evaluasi')->insertBatch([
                    [
                        'id_analisis' => $analisisId,
                        'metrik'      => 'Akurasi Klasifikasi',
                        'nilai'       => $result['accuracy'],
                        'detail'      => json_encode([
                            'correct'          => $result['correct_count'],
                            'total'            => $result['total_tested'],
                            'classes'          => $result['classes'],
                            'confusion_matrix' => $result['confusion_matrix'],
                        ]),
                    ],
                    [
                        'id_analisis' => $analisisId,
                        'metrik'      => 'Jumlah Aturan Pohon',
                        'nilai'       => $result['total_rules'],
                        'detail'      => json_encode([
                            'total_rules' => $result['total_rules'],
                            'deskripsi'   => "Sebanyak {$result['total_rules']} aturan IF-THEN berhasil diekstraksi"
                        ]),
                    ],
                ]);

                // Simpan aturan ke aturan_c45 (confidence decimal(5,4) -> 1.0000 = 100%)
                $rulesBatch = [];
                foreach ($result['rules'] as $r) {
                    $condStr = empty($r['conditions']) ? 'SEMUA KONDISI' : 'IF ' . implode(' AND ', $r['conditions']);
                    $rulesBatch[] = [
                        'id_analisis' => $analisisId,
                        'aturan'      => $condStr,
                        'kelas'       => (string) $r['class'],
                        'support'     => (int) $r['total_cases'],
                        'confidence'  => 1.0000,
                    ];
                }
                if (!empty($rulesBatch)) {
                    $db->table('aturan_c45')->insertBatch($rulesBatch);
                }

                // Simpan sampel prediksi ke hasil_klasifikasi
                $predBatch = [];
                $sampleLimit = min(200, count($rows));
                for ($i = 0; $i < $sampleLimit; $i++) {
                    $row = $rows[$i];
                    $actual = isset($row[$target]) ? trim($row[$target]) : '';
                    $predicted = $this->c45Service->predict($result['tree'], $row);

                    $predBatch[] = [
                        'id_analisis'    => $analisisId,
                        'id_kunjungan'   => $row['id'],
                        'kelas_aktual'   => $actual,
                        'kelas_prediksi' => $predicted,
                    ];
                }
                if (!empty($predBatch)) {
                    $db->table('hasil_klasifikasi')->insertBatch($predBatch);
                }

                $db->transComplete();

                return redirect()->to(site_url('analisis/detail/' . $analisisId))
                    ->with('analysis_success', "Klasifikasi C4.5 selesai! Akurasi: {$result['accuracy']}%, diekstraksi {$result['total_rules']} aturan keputusan.");

            } elseif ($algoritma === 'naivebayes') {
                $target = trim((string) $this->request->getPost('target'));
                $features = (array) $this->request->getPost('features');

                if (empty($target)) {
                    return redirect()->back()->withInput()->with('error', 'Pilih atribut target untuk klasifikasi Naive Bayes.');
                }

                $features = array_values(array_filter($features, fn($f) => $f !== $target));
                if (empty($features)) {
                    $features = ['usia', 'jenis_kelamin', 'kelompok_usia', 'unit_pelayanan'];
                }

                $result = $this->naiveBayesService->run($rows, $features, $target);

                $db->transStart();

                $analisisId = $this->analisisModel->insert([
                    'id_dataset'     => $idDataset,
                    'algoritma'      => 'naivebayes',
                    'parameter'      => json_encode([
                        'features' => $features,
                        'priors'   => $result['priors'],
                        'classes'  => $result['classes'],
                    ]),
                    'atribut_target' => $target,
                    'id_user'        => session()->get('user_id'),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                // Simpan evaluasi metrik
                $db->table('evaluasi')->insertBatch([
                    [
                        'id_analisis' => $analisisId,
                        'metrik'      => 'Akurasi Naive Bayes',
                        'nilai'       => $result['accuracy'],
                        'detail'      => json_encode([
                            'correct'          => $result['correct_count'],
                            'total'            => $result['total_tested'],
                            'classes'          => $result['classes'],
                            'confusion_matrix' => $result['confusion_matrix'],
                            'priors'           => $result['priors'],
                        ]),
                    ],
                ]);

                // Simpan sampel prediksi ke hasil_klasifikasi
                $predBatch = [];
                foreach ($result['sample_preds'] as $i => $sp) {
                    $predBatch[] = [
                        'id_analisis'    => $analisisId,
                        'id_kunjungan'   => $rows[$i]['id'] ?? ($i + 1),
                        'kelas_aktual'   => $sp['actual'],
                        'kelas_prediksi' => $sp['predicted'],
                    ];
                }
                if (!empty($predBatch)) {
                    $db->table('hasil_klasifikasi')->insertBatch($predBatch);
                }

                $db->transComplete();

                return redirect()->to(site_url('analisis/detail/' . $analisisId))
                    ->with('analysis_success', "Klasifikasi Naive Bayes selesai! Akurasi: {$result['accuracy']}% pada " . number_format($result['total_tested']) . " rekam medis.");

            } else {
                return redirect()->back()->withInput()->with('error', 'Algoritma yang dipilih tidak valid.');
            }

        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menjalankan analisis: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan visualisasi dan detail hasil analisis data mining
     */
    public function detail($id)
    {
        $run = $this->analisisModel->getDetail((int) $id);
        if (!$run) {
            return redirect()->to(site_url('analisis'))->with('error', 'Riwayat analisis tidak ditemukan.');
        }

        $params = json_decode($run['parameter'] ?? '{}', true) ?: [];

        return view('analisis/detail', [
            'title'  => 'Hasil Analisis ' . strtoupper($run['algoritma']) . ' — Polara',
            'run'    => $run,
            'params' => $params,
        ]);
    }

    /**
     * Hapus riwayat analisis dan data turunannya
     */
    public function delete($id)
    {
        $userRole = (string) session()->get('role');
        if (!in_array($userRole, ['admin', 'analis'], true)) {
            return redirect()->to(site_url('analisis'))
                ->with('error', 'Akses ditolak: Hanya Administrator dan Analis yang dapat menghapus riwayat analisis.');
        }

        $run = $this->analisisModel->find($id);
        if (!$run) {
            return redirect()->to(site_url('analisis'))->with('error', 'Riwayat analisis tidak ditemukan.');
        }

        $this->analisisModel->deleteCascade((int) $id);

        return redirect()->to(site_url('analisis'))
            ->with('delete_success', 'Riwayat analisis #' . $id . ' beserta hasil evaluasinya berhasil dihapus.');
    }
}
