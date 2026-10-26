<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\DatasetModel;
use App\Models\DatasetColumnModel;
use App\Models\DataKunjunganModel;
use App\Services\SpreadsheetService;
use App\Services\DatasetProfiler;

class SyncKematianDataset extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'sync:kematian-dataset';
    protected $description = 'Sinkronisasi ulang dataset kematian di basis data dengan parsing yang telah diperbaiki';

    public function run(array $params)
    {
        $datasetModel   = new DatasetModel();
        $columnModel    = new DatasetColumnModel();
        $kunjunganModel = new DataKunjunganModel();
        $ss             = new SpreadsheetService();
        $profiler       = new DatasetProfiler($ss);

        $ds = $datasetModel->where('id', 24)->first();
        if (!$ds) {
            CLI::error("Dataset ID 24 tidak ditemukan!");
            return;
        }

        $sourceFile = ROOTPATH . 'tests/datasets/jumlah-kematian-menurut-provinsi-dan-penyebab-utama-kematian-sejak-1-juli-2022.xls';
        $destFile   = WRITEPATH . 'uploads/datasets/' . $ds['nama_file_internal'];

        if (file_exists($sourceFile)) {
            copy($sourceFile, $destFile);
        }

        $parsed = $ss->parseXls($destFile, 0);
        $headers = $parsed['headers'];
        $rows = $parsed['rows'];
        $summaryRow = $parsed['summary_row'];

        CLI::write("Sinkronisasi Kolom Dataset ID 24...", 'yellow');
        $columnModel->where('id_dataset', 24)->delete();

        $types = $ss->detectColumnTypes($headers, $rows);
        foreach ($headers as $colIdx => $colName) {
            $columnModel->insert([
                'id_dataset'          => 24,
                'nama_kolom_sumber'   => $colName,
                'posisi_kolom'        => $colIdx,
                'tipe_terdeteksi'     => $types[$colIdx] ?? 'string',
                'pemetaan_atribut'    => ($colIdx === 0 ? 'nama_diagnosis' : ($colIdx === 6 ? 'jumlah_kunjungan' : null)),
                'is_fitur'            => ($colIdx >= 1 && $colIdx <= 5) ? 1 : 0,
                'is_target'           => 0,
                'konfirmasi_pengguna' => 1,
            ]);
        }

        CLI::write("Sinkronisasi Baris data_kunjungan ID 24...", 'yellow');
        $kunjunganModel->where('id_dataset', 24)->delete();

        $parseNum = function($v) {
            $v = trim((string)$v);
            if ($v === '' || $v === 'NA' || $v === '-' || $v === '–') return 0;
            return (float)str_replace(['.', ','], ['', '.'], $v);
        };

        $batch = [];
        $totalCases = 0;
        foreach ($rows as $r) {
            $prov = trim($r[0] ?? '');
            $totalJml = (int)round($parseNum($r[6] ?? 0));
            $totalCases += $totalJml;

            $batch[] = [
                'id_dataset'         => 24,
                'periode'            => '2022-07-01',
                'usia'               => null,
                'kelompok_usia'      => 'Umum',
                'jenis_kelamin'      => null,
                'kode_diagnosis'     => null,
                'nama_diagnosis'     => $prov,
                'kelompok_diagnosis' => null,
                'unit_pelayanan'     => 'Kematian',
                'jumlah_kunjungan'   => $totalJml,
                'jumlah'             => $totalJml,
                'jenis_data'         => 'KUNJUNGAN',
                'sumber'             => null,
                'raw_data'           => json_encode($r, JSON_UNESCAPED_UNICODE),
            ];
        }

        $kunjunganModel->insertBatch($batch);

        $profiling = $profiler->profile($headers, $rows);
        $datasetModel->update(24, [
            'nama_dataset'   => 'Kasus Kematian Provinsi & Penyebab Utama (Sejak 1 Juli 2022)',
            'jumlah_baris'   => count($rows),
            'status'         => 'siap',
            'profiling_meta' => json_encode($profiling, JSON_UNESCAPED_UNICODE),
        ]);

        CLI::write("Sinkronisasi Selesai!", 'green');
        CLI::write("Total Baris: " . count($rows) . " (Provinsi)", 'green');
        CLI::write("Total Kasus: " . number_format($totalCases, 0, ',', '.'), 'green');
    }
}
