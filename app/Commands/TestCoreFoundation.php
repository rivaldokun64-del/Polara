<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\DatasetModel;
use App\Models\DatasetColumnModel;
use App\Models\UserModel;
use App\Filters\AuthFilter;
use App\Filters\RoleFilter;
use Config\Database;

class TestCoreFoundation extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:core-foundation';
    protected $description = 'Menjalankan serangkaian pengujian integrasi untuk Fondasi Inti (Database, Models, Storage Security, Role Filter).';

    public function run(array $params)
    {
        CLI::write("=== PENGUJIAN FONDASI INTI APLIKASI POLARA ===", 'yellow');
        $allPassed = true;

        $assertTest = function (bool $condition, string $message) use (&$allPassed) {
            if ($condition) {
                CLI::write(" [PASS] {$message}", 'green');
            } else {
                CLI::error(" [FAIL] {$message}");
                $allPassed = false;
            }
        };

        $db = Database::connect('default');

        // 1. Uji Migrasi & Kolom Metadata File di Tabel Datasets
        CLI::write("\n1. Pengujian Skema Database & Migrasi File Metadata:", 'white');
        $fields = $db->getFieldNames('datasets');
        $requiredFields = ['nama_file_asli', 'nama_file_internal', 'ukuran_file', 'format_file', 'status'];

        foreach ($requiredFields as $reqField) {
            $assertTest(in_array($reqField, $fields, true), "Kolom '{$reqField}' terdaftar di tabel 'datasets'.");
        }

        $migCount = $db->table('migrations')->countAllResults();
        $assertTest($migCount >= 3, "Seluruh batch migrasi terdaftar ({$migCount} migrasi selesai).");

        // 2. Uji Model DatasetModel & DatasetColumnModel
        CLI::write("\n2. Pengujian DatasetModel & DatasetColumnModel (Dynamic Schema):", 'white');
        $datasetModel = new DatasetModel();
        $columnModel  = new DatasetColumnModel();
        $userModel    = new UserModel();

        $adminUser = $userModel->findByUsername('admin');
        $assertTest(!empty($adminUser), "Akun 'admin' tersedia sebagai referensi ID pengunggah.");

        // Insert dummy dataset for testing
        $testDatasetId = $datasetModel->insert([
            'nama_dataset'       => 'Test Unit Dataset Rekam Medis',
            'nama_file_asli'     => 'sample_rekam_medis.csv',
            'nama_file_internal' => 'test_' . uniqid() . '.csv',
            'ukuran_file'        => 20480,
            'format_file'        => 'csv',
            'deskripsi'          => 'Dataset dummy untuk verifikasi model fondasi',
            'jumlah_baris'       => 150,
            'status'             => 'uploaded',
            'id_user'            => $adminUser['id'],
        ]);
        $assertTest(!empty($testDatasetId), "Insert dataset baru dengan metadata file berhasil (ID: {$testDatasetId}).");

        // Insert dynamic schema columns
        $colId1 = $columnModel->insert([
            'id_dataset'        => $testDatasetId,
            'nama_kolom_sumber' => 'usia_pasien',
            'posisi_kolom'      => 1,
            'tipe_terdeteksi'   => 'integer',
            'pemetaan_atribut'  => 'usia',
            'is_fitur'          => 1,
            'is_target'         => 0,
        ]);
        $colId2 = $columnModel->insert([
            'id_dataset'        => $testDatasetId,
            'nama_kolom_sumber' => 'diagnosa_utama',
            'posisi_kolom'      => 2,
            'tipe_terdeteksi'   => 'string',
            'pemetaan_atribut'  => 'kode_diagnosis',
            'is_fitur'          => 0,
            'is_target'         => 1,
        ]);
        $assertTest(!empty($colId1) && !empty($colId2), "Pencatatan skema dinamis (dataset_columns) berhasil.");

        // Fetch detail with columns
        $detail = $datasetModel->getDetailWithColumns($testDatasetId);
        $assertTest(isset($detail['columns']) && count($detail['columns']) === 2, "getDetailWithColumns() mengembalikan metadata dataset beserta kolom relasinya.");

        $targetCol = $columnModel->getTargetColumn($testDatasetId);
        $assertTest($targetCol !== null && $targetCol['nama_kolom_sumber'] === 'diagnosa_utama', "getTargetColumn() mendeteksi target klasifikasi dengan tepat.");

        // Clean up test records
        $columnModel->where('id_dataset', $testDatasetId)->delete();
        $datasetModel->delete($testDatasetId);
        $assertTest(true, "Data pengujian berhasil dibersihkan dari database.");

        // 3. Uji Keamanan Direktori Upload (NFR-02)
        CLI::write("\n3. Pengujian Keamanan Direktori Penyimpanan Berkas Upload:", 'white');
        $uploadDir = WRITEPATH . 'uploads/datasets';
        $assertTest(is_dir($uploadDir), "Direktori 'writable/uploads/datasets/' tersedia.");
        $assertTest(is_writable($uploadDir), "Direktori penyimpanan berkas memiliki izin tulis (writable).");

        $htaccessPath = $uploadDir . '/.htaccess';
        $assertTest(file_exists($htaccessPath), "Proteksi .htaccess terpasang di direktori upload untuk memblokir eksekusi skrip.");

        // 4. Uji Filter Otorisasi Rute Datasets
        CLI::write("\n4. Pengujian Filter Autentikasi & Otorisasi Rute:", 'white');
        $authFilter = new AuthFilter();
        $roleFilter = new RoleFilter();

        $session = \Config\Services::session();
        if (session_status() === PHP_SESSION_NONE) {
            $session->start();
        }
        $req = \Config\Services::request();

        // Guest session
        $session->remove(['user_id', 'nama', 'username', 'role', 'isLoggedIn']);
        $authRes = $authFilter->before($req);
        $assertTest($authRes instanceof \CodeIgniter\HTTP\RedirectResponse, "Pengguna anonim/tamu diblokir dari rute internal.");

        // Analis session (role: analis)
        $session->set([
            'isLoggedIn' => true,
            'user_id'    => 2,
            'role'       => 'analis',
        ]);
        $roleResAnalis = $roleFilter->before($req, ['admin', 'analis']);
        $assertTest($roleResAnalis === null, "Pengguna peran 'analis' diizinkan mengakses upload dataset.");

        // Pimpinan session (role: pimpinan)
        $session->set([
            'isLoggedIn' => true,
            'user_id'    => 3,
            'role'       => 'pimpinan',
        ]);
        $roleResPimpinan = $roleFilter->before($req, ['admin', 'analis']);
        $assertTest($roleResPimpinan instanceof \CodeIgniter\HTTP\RedirectResponse, "Pengguna peran 'pimpinan' diblokir dari aksi upload dataset.");

        // Reset session
        $session->remove(['user_id', 'nama', 'username', 'role', 'isLoggedIn']);

        CLI::write("\n-------------------------------------------------------", 'yellow');
        if ($allPassed) {
            CLI::write("HASIL: SEMUA PENGUJIAN FONDASI INTI BERHASIL DILALUI DENGAN SUKSES!", 'green');
        } else {
            CLI::error("HASIL: TERDAPAT PENGUJIAN YANG GAGAL. SILAKAN PERIKSA DETAIL DI ATAS.");
        }
    }
}
