<?php

namespace App\Services;

class DatasetProfiler
{
    protected SpreadsheetService $spreadsheetService;

    /**
     * Daftar nama kolom yang WAJIB DIKECUALIKAN dari kolom jumlah/measure
     */
    protected const EXCLUDED_MEASURE_COLUMNS = [
        'no', 'nomor', 'id', 'no_rm', 'norm', 'nomor_rm', 'nomor_rekam_medis',
        'kode', 'kode_icd', 'kode_icd10', 'kode_diagnosis', 'icd10', 'icd',
        'usia', 'umur', 'age', 'tahun', 'year', 'thn', 'bulan', 'month', 'bln',
        'nik', 'telepon', 'telp', 'hp', 'no_hp', 'rt', 'rw', 'kodepos', 'kode_pos'
    ];

    /**
     * Kata kunci untuk kolom jumlah kunjungan (measure)
     */
    protected const MEASURE_KEYWORDS = [
        'jumlah_kunjungan', 'jumlahkunjungan', 'jml_kunjungan', 'jmlkunjungan',
        'jumlah_kasus', 'jumlahkasus', 'jml_kasus', 'jmlkasus',
        'total_kunjungan', 'totalkunjungan', 'total_kasus', 'totalkasus',
        'kunjungan', 'visit_count', 'visitcount', 'frekuensi', 'frequency',
        'total', 'jumlah', 'count', 'qty', 'n'
    ];

    public function __construct(?SpreadsheetService $spreadsheetService = null)
    {
        $this->spreadsheetService = $spreadsheetService ?? new SpreadsheetService();
    }

    /**
     * Normalisasi nama header kolom: hapus spasi, underscore, strip, dan karakter non-alfanumerik
     */
    public function normalizeHeader(string $header): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($header)));
    }

    /**
     * Profiling lengkap dataset berdasarkan array header dan baris data
     *
     * @param array $headers Nama-nama kolom
     * @param array $rows Sampel atau seluruh baris data
     * @param array $manualRoleOverrides Koreksi peran kolom manual dari pengguna [colName => role]
     * @return array Metadata profiling lengkap
     */
    public function profile(array $headers, array $rows, array $manualRoleOverrides = []): array
    {
        $totalRows = count($rows);
        $columnProfiles = [];
        $detectedRoles = [];
        $separateYearCol = null;
        $separateMonthCol = null;

        // 1. Profiling awal tiap kolom (tipe data, null count, unique count, sample)
        foreach ($headers as $colIdx => $colName) {
            $colNameStr = trim((string)$colName);
            $normName = $this->normalizeHeader($colNameStr);

            $values = [];
            $nullCount = 0;
            $numericValues = [];
            $textValues = [];

            foreach ($rows as $row) {
                $val = isset($row[$colIdx]) ? trim((string)$row[$colIdx]) : '';
                if ($val === '' || $val === '–' || $val === '...' || $val === 'NULL' || $val === 'null') {
                    $nullCount++;
                } else {
                    $values[] = $val;
                    $cleanNum = str_replace(['.', ','], '', $val);
                    if (is_numeric($val) || (is_numeric($cleanNum) && !preg_match('/^0\d+/', $val))) {
                        $numericValues[] = (float)str_replace(',', '.', $val);
                    } else {
                        $textValues[] = $val;
                    }
                }
            }

            $uniqueValues = array_values(array_unique($values));
            $cardinality = count($uniqueValues);

            // Deteksi tipe dasar
            $isNumericCol = (!empty($values) && count($numericValues) >= (count($values) * 0.7));
            $isDateCol = false;

            // Cek apakah tanggal
            if (!empty($values)) {
                $dateMatches = 0;
                $sampleCheck = array_slice($values, 0, 30);
                foreach ($sampleCheck as $sv) {
                    if ($this->spreadsheetService->parseDateValue($sv) !== null) {
                        $dateMatches++;
                    }
                }
                if ($dateMatches >= (count($sampleCheck) * 0.6)) {
                    $isDateCol = true;
                }
            }

            // Simpan profil dasar
            $columnProfiles[$colIdx] = [
                'index'         => $colIdx,
                'name'          => $colNameStr,
                'normalized'    => $normName,
                'cardinality'   => $cardinality,
                'null_count'    => $nullCount,
                'total_values'  => count($values),
                'is_numeric'    => $isNumericCol,
                'is_date'       => $isDateCol,
                'min'           => !empty($numericValues) ? min($numericValues) : null,
                'max'           => !empty($numericValues) ? max($numericValues) : null,
                'sample_values' => array_slice($uniqueValues, 0, 5),
                'role'          => 'lainnya',
                'is_feature'    => true,
            ];

            // Deteksi kolom tahun atau bulan terpisah
            if (in_array($normName, ['tahun', 'year', 'thn'], true) && $isNumericCol) {
                $separateYearCol = $colIdx;
            }
            if (in_array($normName, ['bulan', 'month', 'bln'], true)) {
                $separateMonthCol = $colIdx;
            }
        }

        // 2. Deteksi Peran Kolom Semantik (Semantic Role Detection)
        // A. Cek Kolom Identitas (no, no_rm, id) - TIDAK boleh jadi fitur atau jumlah
        foreach ($columnProfiles as $idx => &$prof) {
            $norm = $prof['normalized'];
            if (in_array($norm, ['no', 'nomor', 'id', 'no_rm', 'norm', 'nomor_rm', 'nomor_rekam_medis', 'nik'], true) ||
                preg_match('/^(id_|no_|nomor_)/', $prof['name']) ||
                preg_match('/(_id|_no)$/', $prof['name'])) {
                $prof['role'] = 'identitas';
                $prof['is_feature'] = false;
            }
        }
        unset($prof);

        // B. Deteksi Kolom Jumlah Kunjungan (Measure) - BUG 1 FIX
        // Prioritaskan nama header spesifik dan pastikan TIDAK termasuk kolom yang dikecualikan
        $measureColIdx = null;
        $bestMeasureScore = -1;

        foreach ($columnProfiles as $idx => $prof) {
            $norm = $prof['normalized'];

            // WAJIB DIKECUALIKAN
            if (in_array($norm, self::EXCLUDED_MEASURE_COLUMNS, true)) {
                continue;
            }
            // Harus memiliki nilai numerik
            if (!$prof['is_numeric'] && empty($prof['sample_values'])) {
                continue;
            }

            // Hitung skor kecocokan header
            $score = -1;
            if (in_array($norm, ['jumlahkunjungan', 'jmlkunjungan', 'totalkunjungan', 'visitcount', 'jumlahkasus', 'totalkasus'], true)) {
                $score = 100;
            } elseif (in_array($norm, ['kunjungan', 'frekuensi', 'frequency'], true)) {
                $score = 80;
            } elseif ($norm === 'jumlah' || $norm === 'total' || $norm === 'qty') {
                $score = 70;
            } elseif ($norm === 'count' || $norm === 'n') {
                $score = 50;
            } elseif (preg_match('/(kunjungan|kasus)/', $norm) && $prof['is_numeric']) {
                $score = 40;
            }

            if ($score > $bestMeasureScore) {
                $bestMeasureScore = $score;
                $measureColIdx = $idx;
            }
        }

        if ($measureColIdx !== null) {
            $columnProfiles[$measureColIdx]['role'] = 'jumlah';
            $columnProfiles[$measureColIdx]['is_feature'] = false;
            $detectedRoles['jumlah'] = $columnProfiles[$measureColIdx]['name'];
        }

        // C. Deteksi Kolom Periode / Tanggal - BUG 2 FIX
        $periodColIdx = null;
        $bestPeriodScore = -1;

        foreach ($columnProfiles as $idx => $prof) {
            if ($idx === $measureColIdx || $prof['role'] === 'identitas') {
                continue;
            }

            $norm = $prof['normalized'];

            // Kolom tahun dan bulan tidak boleh ikut diberi peran periode
            if (in_array($norm, ['tahun', 'year', 'thn', 'bulan', 'month', 'bln'], true)) {
                continue;
            }

            $score = -1;

            if (in_array($norm, ['periode', 'tanggal', 'tgl', 'tanggalkunjungan', 'tglkunjungan', 'date', 'visitdate'], true)) {
                $score = 100;
            } elseif ($prof['is_date']) {
                $score = 80;
            } elseif (in_array($norm, ['waktu', 'datetime', 'timestamp'], true)) {
                $score = 70;
            }

            if ($score > $bestPeriodScore) {
                $bestPeriodScore = $score;
                $periodColIdx = $idx;
            }
        }

        if ($periodColIdx !== null) {
            $columnProfiles[$periodColIdx]['role'] = 'periode';
            $columnProfiles[$periodColIdx]['is_feature'] = false;
            $detectedRoles['periode'] = $columnProfiles[$periodColIdx]['name'];
        }

        // Pastikan kolom tahun dan bulan berstatus informasi/waktu, bukan periode
        foreach ($columnProfiles as $idx => &$prof) {
            $norm = $prof['normalized'];
            if (in_array($norm, ['tahun', 'year', 'thn', 'bulan', 'month', 'bln'], true)) {
                $prof['role'] = 'informasi';
            }
        }
        unset($prof);

        // D. Deteksi Jenis Kelamin
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];

            $isHeaderMatch = in_array($norm, ['jeniskelamin', 'kelamin', 'gender', 'sex', 'jk'], true);
            $hasGenderValues = false;

            if (!empty($prof['sample_values'])) {
                $sampleStr = implode(' ', array_map('strtoupper', $prof['sample_values']));
                if (preg_match('/\b(L|P|LAKI-LAKI|PEREMPUAN|PRIA|WANITA|M|F)\b/', $sampleStr) && $prof['cardinality'] <= 5) {
                    $hasGenderValues = true;
                }
            }

            if ($isHeaderMatch || $hasGenderValues) {
                $prof['role'] = 'jenis_kelamin';
                $detectedRoles['jenis_kelamin'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // E. Deteksi Usia / Kelompok Usia
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];

            if (in_array($norm, ['kelompokusia', 'kategoriusia', 'klpusia', 'agegroup'], true)) {
                $prof['role'] = 'kelompok_usia';
                $detectedRoles['kelompok_usia'] = $prof['name'];
                break;
            } elseif (in_array($norm, ['usia', 'umur', 'age'], true) && $prof['is_numeric']) {
                $prof['role'] = 'usia';
                $detectedRoles['usia'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // F. Deteksi Kode ICD-10
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];

            $isHeaderMatch = in_array($norm, ['kodeicd10', 'kodeicd', 'icd10', 'icd', 'kodediagnosis', 'kodediag'], true);
            $hasIcdValues = false;

            if (!empty($prof['sample_values'])) {
                $icdMatches = 0;
                foreach ($prof['sample_values'] as $sv) {
                    if (preg_match('/^[A-Z]\d{2}(\.\d{1,2})?$/i', trim($sv))) {
                        $icdMatches++;
                    }
                }
                if ($icdMatches >= 2 || (count($prof['sample_values']) > 0 && $icdMatches === count($prof['sample_values']))) {
                    $hasIcdValues = true;
                }
            }

            if ($isHeaderMatch || $hasIcdValues) {
                $prof['role'] = 'kode_icd10';
                $detectedRoles['kode_icd10'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // G. Deteksi Diagnosis (Nama Penyakit)
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];

            if (in_array($norm, ['diagnosis', 'diagnosa', 'namadiagnosis', 'namadiagnosa', 'penyakit', 'namapenyakit', 'keluhan'], true)) {
                $prof['role'] = 'diagnosis';
                $detectedRoles['diagnosis'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // H. Deteksi Unit Pelayanan (Poli / Instalasi / Ruang)
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];

            $isHeaderMatch = in_array($norm, ['unitpelayanan', 'unit', 'poli', 'poliklinik', 'instalasi', 'ruangan', 'ruang', 'layanan'], true);
            if ($isHeaderMatch) {
                $prof['role'] = 'unit_pelayanan';
                $detectedRoles['unit_pelayanan'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // I. Deteksi Cara Bayar (BPJS, Umum, Asuransi)
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];

            if (in_array($norm, ['carabayar', 'penjamin', 'asuransi', 'statusbayar', 'metodebayar', 'pembayaran'], true)) {
                $prof['role'] = 'cara_bayar';
                $detectedRoles['cara_bayar'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // J. Deteksi Kategori Kunjungan (Baru / Lama)
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];

            if (in_array($norm, ['kategorikunjungan', 'jeniskunjungan', 'statuskunjungan', 'tipekunjungan'], true)) {
                $prof['role'] = 'kategori_kunjungan';
                $detectedRoles['kategori_kunjungan'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // 3. Terapkan Manual Role Overrides jika diberikan pengguna
        if (!empty($manualRoleOverrides)) {
            foreach ($columnProfiles as $idx => &$prof) {
                $colName = $prof['name'];
                if (isset($manualRoleOverrides[$colName])) {
                    $newRole = trim((string)$manualRoleOverrides[$colName]);
                    $prof['role'] = $newRole;
                    $prof['is_feature'] = !in_array($newRole, ['identitas', 'jumlah'], true);
                    $detectedRoles[$newRole] = $colName;
                    if ($newRole === 'jumlah') {
                        $measureColIdx = $idx;
                    }
                    if ($newRole === 'periode') {
                        $periodColIdx = $idx;
                    }
                }
            }
            unset($prof);
        }

        // 4. Hitung Akumulasi Total Kunjungan (BUG 1 FIX)
        $totalKunjungan = 0;
        if ($measureColIdx !== null) {
            foreach ($rows as $row) {
                $val = isset($row[$measureColIdx]) ? trim((string)$row[$measureColIdx]) : '';
                $cleanNum = str_replace(['.', ','], '', $val);
                if (is_numeric($cleanNum)) {
                    $totalKunjungan += max(0, (float)$cleanNum);
                }
            }
            $totalKunjungan = (int)round($totalKunjungan);
        } else {
            // Fallback: jika tidak ada kolom jumlah, 1 baris = 1 kunjungan
            $totalKunjungan = $totalRows;
        }

        // 5. Hitung Rentang Periode dan Titik YYYY-MM (BUG 2 FIX)
        $parsedDates = [];
        $uniqueYmList = [];

        if ($periodColIdx !== null || ($separateYearCol !== null && $separateMonthCol !== null)) {
            foreach ($rows as $row) {
                $dateVal = null;
                if ($periodColIdx !== null && isset($row[$periodColIdx])) {
                    $dateVal = $this->spreadsheetService->parseDateValue($row[$periodColIdx], $row, $headers);
                }
                if (!$dateVal && $separateYearCol !== null && $separateMonthCol !== null) {
                    $thn = trim($row[$separateYearCol] ?? '');
                    $bln = trim($row[$separateMonthCol] ?? '');
                    if (!empty($thn) && !empty($bln)) {
                        $dateVal = $this->spreadsheetService->parseDateValue("$bln $thn", $row, $headers);
                    }
                }

                if ($dateVal) {
                    $parsedDates[] = $dateVal;
                    $ym = substr($dateVal, 0, 7);
                    if (!in_array($ym, $uniqueYmList, true)) {
                        $uniqueYmList[] = $ym;
                    }
                }
            }
        }

        sort($uniqueYmList);
        $hasPeriod = !empty($parsedDates) && count($uniqueYmList) >= 1;
        $minDate = !empty($parsedDates) ? min($parsedDates) : null;
        $maxDate = !empty($parsedDates) ? max($parsedDates) : null;

        // Label cakupan waktu bahasa Indonesia
        $periodeLabel = null;
        if ($minDate && $maxDate) {
            $indoMonthsShort = [
                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
                '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
            ];
            $y1 = substr($minDate, 0, 4);
            $m1 = $indoMonthsShort[substr($minDate, 5, 2)] ?? substr($minDate, 5, 2);
            $y2 = substr($maxDate, 0, 4);
            $m2 = $indoMonthsShort[substr($maxDate, 5, 2)] ?? substr($maxDate, 5, 2);

            if ($y1 === $y2 && $m1 === $m2) {
                $periodeLabel = "$m1 $y1";
            } else {
                $periodeLabel = "$m1 $y1 – $m2 $y2";
            }
        }

        // 6. Hitung Total Atribut Fitur Analisis Valid (BUG 3 FIX)
        // Hitung HANYA kolom dengan is_feature = true (tanpa kolom identitas no/id dan tanpa measure jumlah)
        $validFeatureColumns = [];
        $extraCategoricals = [];

        foreach ($columnProfiles as $idx => &$prof) {
            $norm = $prof['normalized'];
            if (in_array($norm, ['no', 'nomor', 'id', 'no_rm', 'norm', 'nomor_rm', 'nomor_rekam_medis', 'nik'], true) ||
                in_array($prof['role'], ['identitas', 'jumlah', 'periode'], true)) {
                $prof['is_feature'] = false;
                continue;
            }
            if ($prof['is_feature']) {
                $validFeatureColumns[] = $prof['name'];
                if ($prof['role'] === 'lainnya' && $prof['cardinality'] >= 2 && $prof['cardinality'] <= 30) {
                    $extraCategoricals[] = $prof['name'];
                }
            }
        }
        unset($prof);

        // Susun summary hasil profiling
        return [
            'total_rows'               => $totalRows,
            'total_kunjungan'          => $totalKunjungan,
            'measure_column'           => $measureColIdx !== null ? $columnProfiles[$measureColIdx]['name'] : null,
            'has_measure_column'       => ($measureColIdx !== null),
            'total_features'           => count($validFeatureColumns),
            'feature_columns'          => $validFeatureColumns,
            'detected_roles'           => $detectedRoles,
            'has_period'               => $hasPeriod,
            'period_range'             => [
                'min'           => $minDate,
                'max'           => $maxDate,
                'label'         => $periodeLabel,
                'total_periods' => count($uniqueYmList),
                'periods'       => $uniqueYmList,
                'need_toggle'   => (count($uniqueYmList) > 24),
            ],
            'has_gender'               => isset($detectedRoles['jenis_kelamin']),
            'has_age'                  => (isset($detectedRoles['usia']) || isset($detectedRoles['kelompok_usia'])),
            'has_diagnosis'            => isset($detectedRoles['diagnosis']),
            'has_icd10'                => isset($detectedRoles['kode_icd10']),
            'has_unit'                 => isset($detectedRoles['unit_pelayanan']),
            'has_cara_bayar'           => isset($detectedRoles['cara_bayar']),
            'has_kategori_kunjungan'   => isset($detectedRoles['kategori_kunjungan']),
            'extra_categorical_columns'=> array_slice($extraCategoricals, 0, 2),
            'columns'                  => $columnProfiles,
            'profiled_at'              => date('Y-m-d H:i:s'),
        ];
    }
}
