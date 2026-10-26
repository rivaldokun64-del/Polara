<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\SpreadsheetService;

class TestKematianExcel extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:kematian-excel';
    protected $description = 'Uji impor dan sinkronisasi dataset Excel kematian';

    public function run(array $params)
    {
        CLI::write("\n=== LAPORAN PENGUJIAN SINKRONISASI DATASET KEMATIAN EXCEL ===", 'yellow');

        $ss = new SpreadsheetService();
        $filePath = ROOTPATH . 'tests/datasets/jumlah-kematian-menurut-provinsi-dan-penyebab-utama-kematian-sejak-1-juli-2022.xls';
        if (!file_exists($filePath)) {
            $files = glob(WRITEPATH . 'uploads/datasets/*.xls*');
            foreach ($files as $f) {
                if (str_contains($f, '1793022025') || str_contains($f, 'kematian')) {
                    $filePath = $f;
                    break;
                }
            }
        }

        $parsed = $ss->parseXls($filePath, 0);
        $headers = $parsed['headers'];
        $rows = $parsed['rows'];
        $summaryRow = $parsed['summary_row'];
        $totalSumber = count($rows);

        $parseNum = function($v) {
            $v = trim((string)$v);
            if ($v === '' || $v === 'NA' || $v === '-' || $v === '–') return 0;
            return (float)str_replace(['.', ','], ['', '.'], $v);
        };

        // Kategori acuan
        $expected = [
            'Total kematian'          => 4456007,
            'Penyakit Menular'        => 111217,
            'Penyakit Tidak Menular'  => 3795918,
            'Kecelakaan Lalu Lintas'  => 85390,
            'Kecelakaan Lainnya'      => 75118,
            'Lainnya'                 => 388363,
            'Jumlah provinsi'         => 38,
        ];

        $importedCategories = [
            'Penyakit Menular'        => 0,
            'Penyakit Tidak Menular'  => 0,
            'Kecelakaan Lalu Lintas'  => 0,
            'Kecelakaan Lainnya'      => 0,
            'Lainnya'                 => 0,
            'Total kematian'          => 0,
        ];

        $provinsiList = [];
        foreach ($rows as $r) {
            $prov = trim($r[0] ?? '');
            $pm  = $parseNum($r[1] ?? 0);
            $ptm = $parseNum($r[2] ?? 0);
            $kll = $parseNum($r[3] ?? 0);
            $kl  = $parseNum($r[4] ?? 0);
            $l   = $parseNum($r[5] ?? 0);
            $jml = $parseNum($r[6] ?? 0);

            $importedCategories['Penyakit Menular']       += $pm;
            $importedCategories['Penyakit Tidak Menular'] += $ptm;
            $importedCategories['Kecelakaan Lalu Lintas'] += $kll;
            $importedCategories['Kecelakaan Lainnya']     += $kl;
            $importedCategories['Lainnya']                += $l;
            $importedCategories['Total kematian']         += $jml;

            $provinsiList[] = [
                'provinsi' => $prov,
                'total'    => $jml,
                'pm'       => $pm,
                'ptm'      => $ptm,
                'kll'      => $kll,
                'kl'       => $kl,
                'l'        => $l,
            ];
        }

        usort($provinsiList, fn($a, $b) => $b['total'] <=> $a['total']);
        $top5 = array_slice($provinsiList, 0, 5);
        $bottom5 = array_slice(array_reverse($provinsiList), 0, 5);

        CLI::write("----------------------------------------------------------------", 'cyan');
        CLI::write("1. AUDIT PEMBACAAN EXCEL:", 'yellow');
        CLI::write("• Lokasi Berkas: " . $filePath, 'white');
        CLI::write("• Header Terdeteksi (" . count($headers) . " Kolom): " . implode(' | ', $headers), 'green');
        CLI::write("• Jumlah Provinsi Terdata: " . count($provinsiList) . " (Target: 38)", count($provinsiList) === 38 ? 'green' : 'red');
        CLI::write("----------------------------------------------------------------", 'cyan');
        CLI::write("2. VALIDASI KESESUAIAN ANGKA DENGAN SUMBER EXCEL:", 'yellow');

        $allPass = true;
        // Total Kematian dari baris summary Excel
        $sumExcelTotal = $summaryRow ? $parseNum($summaryRow[6] ?? 0) : 4456007;
        CLI::write(sprintf("%-26s | %-12s | %-12s | %-8s | %s", "Kategori", "Sumber Excel", "Hasil Impor", "Selisih", "Status"), 'cyan');
        CLI::write(str_repeat('-', 70), 'cyan');

        $catMap = [
            'Total kematian'         => 6,
            'Penyakit Menular'       => 1,
            'Penyakit Tidak Menular' => 2,
            'Kecelakaan Lalu Lintas' => 3,
            'Kecelakaan Lainnya'     => 4,
            'Lainnya'                => 5,
        ];

        foreach ($catMap as $cName => $colIdx) {
            $srcVal = $summaryRow ? $parseNum($summaryRow[$colIdx] ?? 0) : $expected[$cName];
            $impVal = $importedCategories[$cName];
            $diff   = $impVal - $srcVal;
            // Catatan Excel menyatakan selisih kecil pada kategori disebabkan pembulatan program pengolahan sumber
            $isOk   = ($cName === 'Total kematian') ? ($diff == 0) : (abs($diff) <= 100);
            if (!$isOk) $allPass = false;

            CLI::write(sprintf(
                "%-26s | %-12s | %-12s | %-8s | %s",
                $cName,
                number_format($srcVal, 0, ',', '.'),
                number_format($impVal, 0, ',', '.'),
                ($diff >= 0 ? '+' : '') . number_format($diff, 0, ',', '.'),
                $isOk ? "LULUS" : "GAGAL"
            ), $isOk ? 'green' : 'red');
        }

        CLI::write("----------------------------------------------------------------", 'cyan');
        CLI::write("3. PROVINSI DENGAN KEMATIAN TERTINGGI (TOP 5):", 'yellow');
        foreach ($top5 as $idx => $t) {
            CLI::write("  " . ($idx + 1) . ". {$t['provinsi']} : " . number_format($t['total'], 0, ',', '.') . " jiwa", 'white');
        }
        CLI::write("PROVINSI DENGAN KEMATIAN TERENDAH (BOTTOM 5):", 'yellow');
        foreach ($bottom5 as $idx => $t) {
            CLI::write("  " . ($idx + 1) . ". {$t['provinsi']} : " . number_format($t['total'], 0, ',', '.') . " jiwa", 'white');
        }
        CLI::write("----------------------------------------------------------------", 'cyan');
        CLI::write("STATUS KESELURUHAN: " . ($allPass ? "SEMUA PENGUJIAN LULUS DENGAN VALIDASI AKURAT!" : "ADA VALIDASI YANG PERLU DIPERIKSA"), $allPass ? 'green' : 'red');
        CLI::write("----------------------------------------------------------------", 'cyan');
    }
}
