<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DatasetModel;
use App\Models\DatasetColumnModel;
use App\Models\DataKunjunganModel;
use App\Services\SpreadsheetService;
use App\Services\DatasetProfiler;
use Config\Database;

class Datasets extends BaseController
{
    protected DatasetModel $datasetModel;
    protected DatasetColumnModel $columnModel;
    protected DataKunjunganModel $kunjunganModel;
    protected SpreadsheetService $spreadsheetService;
    protected DatasetProfiler $datasetProfiler;

    public function __construct()
    {
        $this->datasetModel       = new DatasetModel();
        $this->columnModel        = new DatasetColumnModel();
        $this->kunjunganModel     = new DataKunjunganModel();
        $this->spreadsheetService = new SpreadsheetService();
        $this->datasetProfiler    = new DatasetProfiler($this->spreadsheetService);
    }

    /**
     * Tampilkan daftar dataset rekam medis
     */
    public function index()
    {
        $datasets = $this->datasetModel->getWithUploader(50);

        return view('datasets/index', [
            'title'    => 'Manajemen Dataset — Polara',
            'datasets' => $datasets,
        ]);
    }

    /**
     * Proses upload berkas spreadsheet dataset secara aman
     */
    public function upload()
    {
        $userRole = (string) session()->get('role');
        if (!in_array($userRole, ['admin', 'analis'], true)) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Akses ditolak: Hanya Administrator dan Analis yang diizinkan mengunggah dataset.');
        }

        $rules = [
            'nama_dataset' => [
                'label' => 'Nama Dataset',
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required'   => '{field} wajib diisi.',
                    'min_length' => '{field} minimal 3 karakter.',
                    'max_length' => '{field} maksimal 255 karakter.',
                ],
            ],
            'file_dataset' => [
                'label' => 'File Dataset',
                'rules' => [
                    'uploaded[file_dataset]',
                    'max_size[file_dataset,10240]',
                    'ext_in[file_dataset,csv,xlsx,xls]',
                ],
                'errors' => [
                    'uploaded' => '{field} wajib dipilih.',
                    'max_size' => 'Ukuran {field} maksimal 10 MB.',
                    'ext_in'   => 'Format berkas harus berupa .csv, .xlsx, atau .xls.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file_dataset');

        if (!$file->isValid()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Berkas tidak valid atau transmisi gagal: ' . $file->getErrorString());
        }

        $clientName   = $file->getClientName();
        $extension    = strtolower($file->getClientExtension());
        $fileSize     = $file->getSize();
        $internalName = $file->getRandomName();
        $uploadTarget = WRITEPATH . 'uploads/datasets';

        if (!is_dir($uploadTarget)) {
            mkdir($uploadTarget, 0755, true);
        }

        $file->move($uploadTarget, $internalName);

        // Hitung estimasi baris awal (mendukung CSV dan XLSX)
        $filePath = $uploadTarget . DIRECTORY_SEPARATOR . $internalName;
        $rowCount = $this->spreadsheetService->countTotalRows($filePath);

        // Jalankan profiling otomatis awal saat berkas diunggah
        $profilingMeta = null;
        try {
            $sample = $this->spreadsheetService->readSample($filePath, 50);
            $profiling = $this->datasetProfiler->profile($sample['headers'] ?? [], $sample['sample_rows'] ?? []);
            $profilingMeta = json_encode($profiling, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            $profilingMeta = null;
        }

        $datasetData = [
            'nama_dataset'       => (string) $this->request->getPost('nama_dataset'),
            'nama_file_asli'     => $clientName,
            'nama_file_internal' => $internalName,
            'ukuran_file'        => $fileSize,
            'format_file'        => $extension,
            'deskripsi'          => (string) $this->request->getPost('deskripsi'),
            'jumlah_baris'       => $rowCount,
            'status'             => 'uploaded',
            'profiling_meta'     => $profilingMeta,
            'id_user'            => (int) session()->get('user_id'),
        ];

        $insertedId = $this->datasetModel->insert($datasetData);

        if (!$insertedId) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Gagal mencatat metadata dataset ke basis data.');
        }

        return redirect()->to(site_url('datasets/preview/' . $insertedId))
            ->with('import_success', "Dataset '" . esc($clientName) . "' berhasil diunggah. Silakan konfirmasi kolom dataset.");
    }

    /**
     * Halaman Pratinjau & Pemetaan Kolom Semantik (FR-05 & FR-06)
     */
    public function preview($id)
    {
        $dataset = $this->datasetModel->find($id);
        if (!$dataset) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Dataset tidak ditemukan.');
        }

        $filePath = WRITEPATH . 'uploads/datasets/' . $dataset['nama_file_internal'];
        if (!file_exists($filePath)) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Berkas fisik dataset tidak ditemukan di server.');
        }

        try {
            $preview = $this->spreadsheetService->readSample($filePath, 15);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Gagal membaca isi berkas dataset: ' . $e->getMessage());
        }

        // Ambil data profiling tersimpan atau generate baru jika belum ada
        $profiling = !empty($dataset['profiling_meta']) ? json_decode($dataset['profiling_meta'], true) : null;
        if (!$profiling) {
            try {
                $profiling = $this->datasetProfiler->profile($preview['headers'] ?? [], $preview['sample_rows'] ?? []);
                $this->datasetModel->update($id, ['profiling_meta' => json_encode($profiling, JSON_UNESCAPED_UNICODE)]);
            } catch (\Throwable $e) {
                $profiling = [];
            }
        }

        // Ambil data pemetaan tersimpan jika sebelumnya sudah pernah dipetakan
        $existingCols = $this->columnModel->getByDatasetId((int) $id);
        $savedMappings = [];
        $savedFeatures = [];
        $savedTargets  = [];

        foreach ($existingCols as $col) {
            $savedMappings[$col['nama_kolom_sumber']] = $col['pemetaan_atribut'];
            $savedFeatures[$col['nama_kolom_sumber']] = (int) $col['is_fitur'];
            $savedTargets[$col['nama_kolom_sumber']]  = (int) $col['is_target'];
        }

        // Jika belum ada pemetaan tersimpan, gunakan rekomendasi dari DatasetProfiler
        if (empty($savedMappings) && !empty($profiling['detected_roles'])) {
            foreach ($profiling['detected_roles'] as $role => $colName) {
                $savedMappings[$colName] = $role;
            }
        }

        return view('datasets/preview', [
            'title'              => 'Pratinjau & Pemetaan — Polara',
            'dataset'            => $dataset,
            'preview'            => $preview,
            'profiling'          => $profiling,
            'standardAttributes' => SpreadsheetService::STANDARD_ATTRIBUTES,
            'savedMappings'      => $savedMappings,
            'savedFeatures'      => $savedFeatures,
            'savedTargets'       => $savedTargets,
        ]);
    }

    /**
     * Simpan hasil pemetaan kolom dan lakukan impor data ke staging/data_kunjungan
     */
    public function saveMapping($id)
    {
        $userRole = (string) session()->get('role');
        if (!in_array($userRole, ['admin', 'analis'], true)) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Akses ditolak: Hanya Administrator dan Analis yang dapat mengonfirmasi pemetaan.');
        }

        $dataset = $this->datasetModel->find($id);
        if (!$dataset) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Dataset tidak ditemukan.');
        }

        $headers      = (array) $this->request->getPost('headers');
        $types        = (array) $this->request->getPost('types');
        $mappings     = (array) $this->request->getPost('mapping');
        $isFeatures   = (array) $this->request->getPost('is_feature');
        $targetColumn = $this->request->getPost('target_column');

        if (empty($headers)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tidak ada kolom yang terdeteksi pada dataset.');
        }

        // Enforce: Hanya SATU kolom yang boleh berperan 'periode'.
        // Kolom tahun dan bulan tidak boleh ikut diberi peran periode.
        $hasAssignedPeriode = false;
        foreach ($headers as $colIdx => $colName) {
            $norm = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$colName));
            if (in_array($norm, ['tahun', 'year', 'thn', 'bulan', 'month', 'bln'], true)) {
                if (($mappings[$colIdx] ?? '') === 'periode') {
                    $mappings[$colIdx] = null;
                }
            }
            if (($mappings[$colIdx] ?? '') === 'periode') {
                if ($hasAssignedPeriode) {
                    $mappings[$colIdx] = null;
                } else {
                    $hasAssignedPeriode = true;
                }
            }
        }

        // Jika tidak ada fitur dicentang secara manual, aktifkan semua kolom analisis
        if (empty($isFeatures)) {
            foreach ($headers as $colIdx => $colName) {
                $isFeatures[$colIdx] = 1;
            }
        }

        $db = Database::connect('default');
        $db->transStart();

        // 1. Bersihkan pemetaan kolom lama untuk dataset ini
        $this->columnModel->where('id_dataset', $id)->delete();

        // 2. Simpan skema baru ke dataset_columns
        foreach ($headers as $colIdx => $colName) {
            $mappedAttr = $mappings[$colIdx] ?? null;
            $colNorm    = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$colName));
            $isTarget   = ((string)$targetColumn === (string)$colIdx) ? 1 : 0;
            
            // Kolom no, nomor, id, nik, jumlah, dan periode jangan pernah jadi atribut fitur
            if (in_array($colNorm, ['no', 'nomor', 'id', 'norm', 'nomorrm', 'nik'], true) ||
                in_array($mappedAttr, ['identitas', 'jumlah', 'periode', 'jumlah_kunjungan'], true)) {
                $isFitur = 0;
            } else {
                $isFitur = isset($isFeatures[$colIdx]) ? (int)$isFeatures[$colIdx] : 1;
            }

            $this->columnModel->insert([
                'id_dataset'          => (int) $id,
                'nama_kolom_sumber'   => (string) $colName,
                'posisi_kolom'        => (int) $colIdx,
                'tipe_terdeteksi'     => $types[$colIdx] ?? 'string',
                'pemetaan_atribut'    => !empty($mappedAttr) ? $mappedAttr : null,
                'is_fitur'            => $isFitur,
                'is_target'           => $isTarget,
                'konfirmasi_pengguna' => 1,
            ]);
        }

        // Cari indeks kolom periode dan kelompok_usia dari headers sebagai referensi langsung
        $periodeColIdx = null;
        $kelompokUsiaColIdx = null;
        foreach ($headers as $cIdx => $h) {
            $hNorm = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$h));
            if ($periodeColIdx === null && in_array($hNorm, ['periode', 'tanggal', 'tgl', 'date'], true)) {
                $periodeColIdx = $cIdx;
            }
            if ($kelompokUsiaColIdx === null && in_array($hNorm, ['kelompokusia', 'kategoriusia', 'klpusia', 'agegroup'], true)) {
                $kelompokUsiaColIdx = $cIdx;
            }
        }

        // 3. Impor data baris ke tabel data_kunjungan
        $filePath = WRITEPATH . 'uploads/datasets/' . $dataset['nama_file_internal'];
        $mappedRows = $this->spreadsheetService->parseAndMapAll($filePath, $mappings);

        // Hapus rekam data_kunjungan lama untuk dataset ini
        $this->kunjunganModel->where('id_dataset', $id)->delete();

        $batchData = [];
        $totalImported = 0;
        $minDate = null;
        $maxDate = null;

        foreach ($mappedRows as $row) {
            $rawRow = $row['_raw_row'] ?? [];

            // Usia: bersihkan dan pastikan dalam rentang manusia normal (0-120)
            $rawUsia = null;
            if (isset($row['usia']) && is_numeric($row['usia']) && (int)$row['usia'] <= 120) {
                $rawUsia = (int)$row['usia'];
            }

            // Kelompok usia: simpan nilai kolom APA ADANYA (jangan dipotong, digabung, atau di-map ulang)
            $kelompokUsia = 'Umum';
            if (!empty($row['kelompok_usia'])) {
                $kelompokUsia = trim($row['kelompok_usia']);
            } elseif ($kelompokUsiaColIdx !== null && isset($rawRow[$kelompokUsiaColIdx]) && trim((string)$rawRow[$kelompokUsiaColIdx]) !== '') {
                $kelompokUsia = trim((string)$rawRow[$kelompokUsiaColIdx]);
            } elseif (!empty($row['usia']) && !is_numeric($row['usia'])) {
                $kelompokUsia = trim($row['usia']);
            } elseif ($rawUsia !== null) {
                $kelompokUsia = ($rawUsia < 18 ? 'Anak' : ($rawUsia < 60 ? 'Dewasa' : 'Lansia'));
            } else {
                foreach ($rawRow as $cVal) {
                    if (preg_match('/(lansia|dewasa|anak|remaja|balita)/i', $cVal)) {
                        $kelompokUsia = trim($cVal);
                        break;
                    }
                }
            }

            $rawJK = isset($row['jenis_kelamin']) ? strtoupper(substr(trim($row['jenis_kelamin']), 0, 1)) : null;
            if ($rawJK !== 'L' && $rawJK !== 'P') {
                $rawJK = null;
            }

            // Periode / Tanggal: gunakan kolom tanggal penuh (jangan biarkan tertimpa bulan)
            $rawPeriode = ($periodeColIdx !== null && isset($rawRow[$periodeColIdx])) 
                ? $rawRow[$periodeColIdx] 
                : ($row['periode'] ?? null);
            $parsedPeriode = $this->spreadsheetService->parseDateValue($rawPeriode, $rawRow, $headers);
            if (!$parsedPeriode && !empty($rawRow)) {
                foreach ($rawRow as $cellVal) {
                    $p = $this->spreadsheetService->parseDateValue($cellVal, $rawRow, $headers);
                    if ($p) {
                        $parsedPeriode = $p;
                        break;
                    }
                }
            }
            $periodeFinal = $parsedPeriode ?: date('Y-m-d');
            if ($parsedPeriode) {
                if ($minDate === null || $parsedPeriode < $minDate) $minDate = $parsedPeriode;
                if ($maxDate === null || $parsedPeriode > $maxDate) $maxDate = $parsedPeriode;
            }

            // Nama diagnosis: jika tidak dipetakan, cari teks pertama pada baris atau gunakan nama dataset
            $namaDiag = $row['nama_diagnosis'] ?? null;
            if (empty($namaDiag) && !empty($rawRow)) {
                foreach ($rawRow as $cVal) {
                    if (!empty($cVal) && !is_numeric($cVal) && strlen($cVal) > 2) {
                        $namaDiag = substr(trim($cVal), 0, 150);
                        break;
                    }
                }
            }
            if (empty($namaDiag)) {
                $namaDiag = $dataset['nama_dataset'];
            }

            // Jumlah kunjungan / kasus (tangani pemisah ribuan titik Indonesia)
            $jmlKunjungan = 1;
            if (isset($row['jumlah_kunjungan'])) {
                $rawVal = trim((string)$row['jumlah_kunjungan']);
                $cleanNum = $rawVal;
                if (preg_match('/^\d{1,3}(\.\d{3})+$/', $cleanNum)) {
                    $cleanNum = str_replace('.', '', $cleanNum);
                } elseif (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $cleanNum)) {
                    $cleanNum = str_replace('.', '', $cleanNum);
                    $cleanNum = str_replace(',', '.', $cleanNum);
                } else {
                    $cleanNum = str_replace(',', '.', $cleanNum);
                }
                if (is_numeric($cleanNum) && (float)$cleanNum > 0) {
                    $jmlKunjungan = max(1, (int)round((float)$cleanNum));
                }
            } else {
                // Cari kolom yang bernama 'jumlah' atau 'total' pada headers
                $totalIdx = null;
                foreach ($headers as $cIdx => $hName) {
                    $hn = strtolower(trim((string)$hName));
                    if (in_array($hn, ['jumlah', 'total', 'jumlah_kunjungan', 'jumlahkunjungan', 'total_kematian', 'total_kasus'], true)) {
                        $totalIdx = $cIdx;
                        break;
                    }
                }

                if ($totalIdx !== null && isset($rawRow[$totalIdx])) {
                    $cNum = trim((string)$rawRow[$totalIdx]);
                    if (preg_match('/^\d{1,3}(\.\d{3})+$/', $cNum)) {
                        $cNum = str_replace('.', '', $cNum);
                    } else {
                        $cNum = str_replace(',', '.', $cNum);
                    }
                    if (is_numeric($cNum) && (float)$cNum > 0) {
                        $jmlKunjungan = max(1, (int)round((float)$cNum));
                    }
                } else {
                    foreach ($rawRow as $cVal) {
                        $cNum = trim((string)$cVal);
                        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $cNum)) {
                            $cNum = str_replace('.', '', $cNum);
                        } else {
                            $cNum = str_replace(',', '.', $cNum);
                        }
                        if (is_numeric($cNum) && (float)$cNum > 0) {
                            $jmlKunjungan = max(1, (int)round((float)$cNum));
                            break;
                        }
                    }
                }
            }

            // Target jika dipilih
            $targetVal = null;
            if ($targetColumn !== null && $targetColumn !== '' && isset($rawRow[(int)$targetColumn])) {
                $targetVal = trim($rawRow[(int)$targetColumn]);
            } elseif (!empty($row['target'])) {
                $targetVal = trim($row['target']);
            }

            $batchData[] = [
                'id_dataset'         => (int) $id,
                'periode'            => $periodeFinal,
                'usia'               => $rawUsia,
                'kelompok_usia'      => $kelompokUsia,
                'jenis_kelamin'      => $rawJK,
                'kode_diagnosis'     => $row['kode_diagnosis'] ?? null,
                'nama_diagnosis'     => $namaDiag,
                'kelompok_diagnosis' => null,
                'unit_pelayanan'     => $row['unit_pelayanan'] ?? 'Umum',
                'jumlah_kunjungan'   => $jmlKunjungan,
                'jumlah'             => $jmlKunjungan,
                'jenis_data'         => 'KUNJUNGAN',
                'sumber'             => $targetVal,
                'raw_data'           => json_encode($rawRow, JSON_UNESCAPED_UNICODE),
            ];

            if (count($batchData) >= 500) {
                $this->kunjunganModel->insertBatch($batchData);
                $totalImported += count($batchData);
                $batchData = [];
            }
        }

        if (!empty($batchData)) {
            $this->kunjunganModel->insertBatch($batchData);
            $totalImported += count($batchData);
        }

        // 4. Update status dataset, rentang periode, dan metadata profiling
        $roleOverrides = [];
        foreach ($mappings as $colIdx => $stdAttr) {
            if (!empty($stdAttr) && isset($headers[$colIdx])) {
                $roleOverrides[$headers[$colIdx]] = $stdAttr;
            }
        }
        $rawRowsForProfiling = array_map(fn($r) => $r['_raw_row'] ?? [], $mappedRows);
        $profiling = $this->datasetProfiler->profile($headers, $rawRowsForProfiling, $roleOverrides);

        $datasetUpdate = [
            'jumlah_baris'   => $totalImported,
            'status'         => 'siap',
            'profiling_meta' => json_encode($profiling, JSON_UNESCAPED_UNICODE),
        ];
        if (!empty($profiling['period_range']['min']) && !empty($profiling['period_range']['max'])) {
            $datasetUpdate['periode_awal']  = $profiling['period_range']['min'];
            $datasetUpdate['periode_akhir'] = $profiling['period_range']['max'];
        } elseif ($minDate && $maxDate) {
            $datasetUpdate['periode_awal']  = $minDate;
            $datasetUpdate['periode_akhir'] = $maxDate;
        }
        $this->datasetModel->update($id, $datasetUpdate);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan pemetaan dan data.');
        }

        return redirect()->to(site_url('datasets'))
            ->with('import_success', "Konfirmasi kolom selesai. Sebanyak " . number_format($totalImported) . " baris data siap digunakan untuk analisis.");
    }

    /**
     * Hapus dataset beserta relasi dan berkas fisiknya
     */
    public function delete($id)
    {
        $userRole = (string) session()->get('role');
        if (!in_array($userRole, ['admin', 'analis'], true)) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Akses ditolak: Hanya Administrator dan Analis yang dapat menghapus dataset.');
        }

        $dataset = $this->datasetModel->find($id);
        if (!$dataset) {
            return redirect()->to(site_url('datasets'))
                ->with('error', 'Dataset tidak ditemukan.');
        }

        $db = Database::connect('default');
        $db->transStart();

        $this->kunjunganModel->where('id_dataset', $id)->delete();
        $this->columnModel->where('id_dataset', $id)->delete();
        $this->datasetModel->delete($id);

        $db->transComplete();

        // Hapus berkas fisik jika ada
        if (!empty($dataset['nama_file_internal'])) {
            $filePath = WRITEPATH . 'uploads/datasets/' . $dataset['nama_file_internal'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return redirect()->to(site_url('datasets'))
            ->with('delete_success', "Dataset '{$dataset['nama_dataset']}' telah berhasil dihapus dari sistem.");
    }
}
