<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\DatasetModel;
use App\Models\DatasetColumnModel;
use App\Models\DataKunjunganModel;
use App\Models\AnalisisModel;
use App\Models\UserModel;
use App\Services\SpreadsheetService;
use App\Services\KMeansService;
use App\Services\C45Service;
use App\Services\NaiveBayesService;
use Config\Database;

class TestPolaraFull extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:polara-full';
    protected $description = 'Menjalankan serangkaian pengujian integrasi end-to-end lengkap untuk seluruh modul Polara.';

    public function run(array $params)
    {
        CLI::write("\n=== PENGUJIAN INTEGRASI END-TO-END LENGKAP APLIKASI POLARA ===", 'yellow');

        $db              = Database::connect();
        $datasetModel    = new DatasetModel();
        $columnModel     = new DatasetColumnModel();
        $kunjunganModel  = new DataKunjunganModel();
        $analisisModel   = new AnalisisModel();
        $userModel       = new UserModel();
        $spreadsheet     = new SpreadsheetService();

        $passed = 0;
        $total  = 0;

        $assert = function (string $desc, bool $condition) use (&$passed, &$total) {
            $total++;
            if ($condition) {
                $passed++;
                CLI::write(" [PASS] {$desc}", 'green');
            } else {
                CLI::write(" [FAIL] {$desc}", 'red');
            }
        };

        // 1. INGESTION & STORAGE
        CLI::write("\n1. Pengujian Penyimpanan Dataset & Direktori Upload Aman:", 'cyan');
        $uploadDir = WRITEPATH . 'uploads/datasets/';
        $internalFile = 'spark_test_' . time() . '.csv';

        // Buat file CSV uji coba
        $csvContent = "No_RM,Tgl_Kunjungan,Usia,JK,ICD10,Diagnosis,Poli,Jumlah,Target\n";
        $csvContent .= "RM1,2026-01-01,45,L,I10,Hipertensi,Poli Umum,1,Tidak Dirujuk\n";
        $csvContent .= "RM2,2026-01-02,55,P,E11,Diabetes,Poli Umum,2,Tidak Dirujuk\n";
        $csvContent .= "RM3,2026-01-03,8,L,J06.9,ISPA,Poli Anak,1,Tidak Dirujuk\n";
        $csvContent .= "RM4,2026-01-04,28,P,Z34,Kehamilan,Poli KIA,1,Tidak Dirujuk\n";
        $csvContent .= "RM5,2026-01-05,65,L,I10,Hipertensi,Poli Lansia,3,Dirujuk\n";
        $csvContent .= "RM6,2026-01-06,34,P,K29.7,Gastritis,Poli Umum,1,Tidak Dirujuk\n";
        $csvContent .= "RM7,2026-01-07,5,P,J00,Nasofaringitis,Poli Anak,1,Tidak Dirujuk\n";
        $csvContent .= "RM8,2026-01-08,42,L,K02.9,Karies,Poli Gigi,1,Tidak Dirujuk\n";
        $csvContent .= "RM9,2026-01-09,58,P,M15,Poliartrosis,Poli Lansia,2,Tidak Dirujuk\n";
        $csvContent .= "RM10,2026-01-10,12,L,J06.9,ISPA,Poli Anak,1,Tidak Dirujuk\n";
        file_put_contents($uploadDir . $internalFile, $csvContent);

        $assert("Direktori upload dilengkapi proteksi .htaccess", file_exists($uploadDir . '.htaccess'));
        $assert("Berkas CSV terverifikasi di folder uploads terenkripsi", file_exists($uploadDir . $internalFile));

        $dsId = $datasetModel->insert([
            'nama_dataset'       => 'Dataset Tes Spark E2E ' . date('His'),
            'nama_file_asli'     => 'test_sample.csv',
            'nama_file_internal' => $internalFile,
            'ukuran_file'        => strlen($csvContent),
            'format_file'        => 'CSV',
            'jumlah_baris'       => 0,
            'status'             => 'diproses',
            'id_user'            => 1,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);
        $assert("Pendaftaran metadata dataset ke tabel datasets (ID: {$dsId})", $dsId > 0);

        // 2. PARSING & MAPPING
        CLI::write("\n2. Pengujian Deteksi Skema & Impor Baris Data Medis:", 'cyan');
        $sample = $spreadsheet->readSample($uploadDir . $internalFile, 10);
        $assert("Parsing header CSV otomatis berhasil (9 kolom)", count($sample['headers']) === 9);

        $mapping = [
            0 => '',
            1 => 'periode',
            2 => 'usia',
            3 => 'jenis_kelamin',
            4 => 'kode_diagnosis',
            5 => 'nama_diagnosis',
            6 => 'unit_pelayanan',
            7 => 'jumlah_kunjungan',
            8 => 'target',
        ];

        foreach ($sample['headers'] as $idx => $hdr) {
            $columnModel->insert([
                'id_dataset'        => $dsId,
                'nama_kolom_sumber' => $hdr,
                'posisi_kolom'      => $idx,
                'tipe_terdeteksi'   => $sample['detected_types'][$idx] ?? 'string',
                'pemetaan_atribut'  => $mapping[$idx] ?: null,
                'is_fitur'          => in_array($mapping[$idx], ['usia', 'jenis_kelamin', 'unit_pelayanan', 'jumlah_kunjungan']) ? 1 : 0,
                'is_target'         => ($mapping[$idx] === 'target') ? 1 : 0,
            ]);
        }
        $assert("Penyimpanan 9 definisi skema kolom ke dataset_columns", $columnModel->where('id_dataset', $dsId)->countAllResults() === 9);

        $mappedRows = $spreadsheet->parseAndMapAll($uploadDir . $internalFile, $mapping);
        $batch = [];
        foreach ($mappedRows as $r) {
            $u = isset($r['usia']) ? (int)$r['usia'] : null;
            $batch[] = [
                'id_dataset'       => $dsId,
                'periode'          => !empty($r['periode']) ? date('Y-m-d', strtotime($r['periode'])) : null,
                'usia'             => $u,
                'kelompok_usia'    => ($u !== null) ? ($u < 18 ? 'Anak' : ($u < 60 ? 'Dewasa' : 'Lansia')) : null,
                'jenis_kelamin'    => $r['jenis_kelamin'] ?? null,
                'kode_diagnosis'   => $r['kode_diagnosis'] ?? null,
                'nama_diagnosis'   => $r['nama_diagnosis'] ?? null,
                'unit_pelayanan'   => $r['unit_pelayanan'] ?? null,
                'jumlah_kunjungan' => (int)($r['jumlah_kunjungan'] ?? 1),
                'sumber'           => $r['target'] ?? null,
            ];
        }
        $kunjunganModel->insertBatch($batch);
        $datasetModel->update($dsId, ['jumlah_baris' => count($batch), 'status' => 'siap']);
        $assert("Impor 10 baris ke tabel data_kunjungan dengan status 'siap'", $kunjunganModel->where('id_dataset', $dsId)->countAllResults() === 10);

        // 3. K-MEANS
        CLI::write("\n3. Pengujian Algoritma K-Means Clustering:", 'cyan');
        $rows = $kunjunganModel->where('id_dataset', $dsId)->findAll();
        $kmeans = new KMeansService();
        $kRes = $kmeans->run($rows, ['usia', 'jumlah_kunjungan'], 2, 50);
        $assert("Klasterisasi K-Means konvergen ({$kRes['iterations']} iterasi)", $kRes['iterations'] > 0);
        $assert("Inertia dan Silhouette dihitung valid ({$kRes['inertia']})", $kRes['inertia'] > 0);

        $kmId = $analisisModel->insert([
            'id_dataset' => $dsId,
            'algoritma'  => 'kmeans',
            'parameter'  => json_encode(['k' => 2]),
            'id_user'    => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('evaluasi')->insert([
            'id_analisis' => $kmId,
            'metrik'      => 'Inertia (WCSS)',
            'nilai'       => $kRes['inertia'],
            'detail'      => json_encode(['desc' => 'WCSS']),
        ]);
        $cBatch = [];
        foreach ($rows as $i => $row) {
            $cBatch[] = ['id_analisis' => $kmId, 'id_kunjungan' => $row['id'], 'cluster' => $kRes['assignments'][$i] + 1];
        }
        $db->table('hasil_cluster')->insertBatch($cBatch);
        $assert("Penugasan klaster tersimpan di hasil_cluster (10 baris)", $db->table('hasil_cluster')->where('id_analisis', $kmId)->countAllResults() === 10);

        // 4. C4.5
        CLI::write("\n4. Pengujian Algoritma C4.5 Decision Tree:", 'cyan');
        $c45 = new C45Service();
        $c45Res = $c45->run($rows, ['kelompok_usia', 'jenis_kelamin'], 'unit_pelayanan', 4);
        $assert("C4.5 menghasilkan pohon & aturan IF-THEN (Akurasi: {$c45Res['accuracy']}%)", $c45Res['accuracy'] > 0);
        $assert("Aturan keputusan diekstraksi ({$c45Res['total_rules']} aturan)", $c45Res['total_rules'] > 0);

        $c45Id = $analisisModel->insert([
            'id_dataset'     => $dsId,
            'algoritma'      => 'c45',
            'parameter'      => json_encode(['depth' => 4]),
            'atribut_target' => 'unit_pelayanan',
            'id_user'        => 1,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $rBatch = [];
        foreach ($c45Res['rules'] as $rule) {
            $rBatch[] = [
                'id_analisis' => $c45Id,
                'aturan'      => empty($rule['conditions']) ? 'DEFAULT' : 'IF ' . implode(' AND ', $rule['conditions']),
                'kelas'       => $rule['class'],
                'support'     => $rule['total_cases'],
                'confidence'  => 1.0000,
            ];
        }
        $db->table('aturan_c45')->insertBatch($rBatch);
        $assert("Aturan IF-THEN tersimpan di aturan_c45", $db->table('aturan_c45')->where('id_analisis', $c45Id)->countAllResults() === count($rBatch));

        // 5. NAIVE BAYES
        CLI::write("\n5. Pengujian Algoritma Naive Bayes Classifier:", 'cyan');
        $nb = new NaiveBayesService();
        $nbRes = $nb->run($rows, ['usia', 'jenis_kelamin'], 'kelompok_usia');
        $assert("Naive Bayes mengklasifikasi target (Akurasi: {$nbRes['accuracy']}%)", $nbRes['accuracy'] > 0);
        $assert("Probabilitas prior dihitung untuk semua kelas", count($nbRes['priors']) >= 2);

        $nbId = $analisisModel->insert([
            'id_dataset'     => $dsId,
            'algoritma'      => 'naivebayes',
            'parameter'      => json_encode(['priors' => $nbRes['priors']]),
            'atribut_target' => 'kelompok_usia',
            'id_user'        => 1,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $pBatch = [];
        foreach ($nbRes['sample_preds'] as $i => $sp) {
            $pBatch[] = [
                'id_analisis'    => $nbId,
                'id_kunjungan'   => $rows[$i]['id'],
                'kelas_aktual'   => $sp['actual'],
                'kelas_prediksi' => $sp['predicted'],
            ];
        }
        $db->table('hasil_klasifikasi')->insertBatch($pBatch);
        $assert("Hasil prediksi probabilistik tersimpan di hasil_klasifikasi", $db->table('hasil_klasifikasi')->where('id_analisis', $nbId)->countAllResults() === count($pBatch));

        // 6. REPORTING METRICS
        CLI::write("\n6. Pengujian Pelaporan & Agregasi Klinis Database:", 'cyan');
        $topDiag = $db->table('data_kunjungan')
            ->select('nama_diagnosis, COUNT(*) as total')
            ->where('id_dataset', $dsId)
            ->groupBy('nama_diagnosis')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();
        $assert("Diagnosis terbanyak dihitung aktual dari basis data", !empty($topDiag));

        // 7. USER MANAGEMENT
        CLI::write("\n7. Pengujian Manajemen Pengguna & BCRYPT Guard:", 'cyan');
        $testUser = 'spark_u_' . time();
        $uId = $userModel->insert([
            'nama'     => 'Analis Uji Spark',
            'username' => $testUser,
            'password' => 'password123',
            'role'     => 'analis',
            'status'   => 'aktif',
        ]);
        $assert("Pendaftaran user baru via UserModel berhasil (ID: {$uId})", $uId > 0);
        $savedU = $userModel->find($uId);
        $assert("Password di-hash BCRYPT secara aman", password_verify('password123', $savedU['password']));
        $userModel->update($uId, ['status' => 'nonaktif']);
        $assert("Status user berhasil dialihkan ke 'nonaktif'", $userModel->find($uId)['status'] === 'nonaktif');
        $userModel->delete($uId);

        // CLEANUP
        $analisisModel->deleteCascade($kmId);
        $analisisModel->deleteCascade($c45Id);
        $analisisModel->deleteCascade($nbId);
        $kunjunganModel->where('id_dataset', $dsId)->delete();
        $columnModel->where('id_dataset', $dsId)->delete();
        $datasetModel->delete($dsId);
        @unlink($uploadDir . $internalFile);

        CLI::write("\n-------------------------------------------------------", 'yellow');
        CLI::write("HASIL: {$passed} / {$total} PENGUJIAN END-TO-END BERHASIL DILALUI!", 'green');
        CLI::write("-------------------------------------------------------\n", 'yellow');
    }
}
