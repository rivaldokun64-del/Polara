<?php

namespace App\Services;

class DatasetProfiler
{
    protected SpreadsheetService $spreadsheetService;

    protected const IDENTITY_COLUMNS = [
        'no', 'nomor', 'id', 'no_rm', 'norm', 'nomor_rm', 'nomor_rekam_medis',
        'nik', 'telepon', 'telp', 'hp', 'no_hp', 'rt', 'rw', 'kodepos', 'kode_pos',
        'nip', 'nrp', 'npwp', 'ktp', 'sim', 'paspor',
    ];

    protected const CODE_COLUMNS = [
        'kode', 'kode_icd', 'kode_icd10', 'kode_diagnosis', 'icd10', 'icd',
        'kode_barang', 'kode_produk', 'kode_wilayah', 'kode_provinsi',
    ];

    protected const EXCLUDED_MEASURE_COLUMNS = [
        'no', 'nomor', 'id', 'no_rm', 'norm', 'nomor_rm', 'nomor_rekam_medis',
        'kode', 'kode_icd', 'kode_icd10', 'kode_diagnosis', 'icd10', 'icd',
        'usia', 'umur', 'age', 'tahun', 'year', 'thn', 'bulan', 'month', 'bln',
        'nik', 'telepon', 'telp', 'hp', 'no_hp', 'rt', 'rw', 'kodepos', 'kode_pos',
        'kode_barang', 'kode_produk', 'kode_wilayah', 'nip', 'nrp', 'npwp',
    ];

    public function __construct(?SpreadsheetService $spreadsheetService = null)
    {
        $this->spreadsheetService = $spreadsheetService ?? new SpreadsheetService();
    }

    public function normalizeHeader(string $header): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($header)));
    }

    public function profile(array $headers, array $rows, array $manualRoleOverrides = []): array
    {
        $totalRows = count($rows);
        $columnProfiles = [];
        $detectedRoles = [];
        $separateYearCol = null;
        $separateMonthCol = null;

        foreach ($headers as $colIdx => $colName) {
            $colNameStr = trim((string)$colName);
            $normName   = $this->normalizeHeader($colNameStr);
            $values = []; $nullCount = 0; $numericValues = []; $textValues = [];

            foreach ($rows as $row) {
                $val = isset($row[$colIdx]) ? trim((string)$row[$colIdx]) : '';
                if ($val === '' || $val === '' || $val === '...' || strtoupper($val) === 'NULL' || $val === '-') {
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
            $cardinality  = count($uniqueValues);
            $isNumericCol = (!empty($values) && count($numericValues) >= (count($values) * 0.7));
            $isDateCol    = false;

            if (!empty($values)) {
                $dateMatches = 0;
                $sampleCheck = array_slice($values, 0, 30);
                foreach ($sampleCheck as $sv) {
                    if ($this->spreadsheetService->parseDateValue($sv) !== null) $dateMatches++;
                }
                if ($dateMatches >= (count($sampleCheck) * 0.6)) $isDateCol = true;
            }

            $columnProfiles[$colIdx] = [
                'index' => $colIdx, 'name' => $colNameStr, 'normalized' => $normName,
                'cardinality' => $cardinality, 'null_count' => $nullCount,
                'total_values' => count($values), 'is_numeric' => $isNumericCol, 'is_date' => $isDateCol,
                'min' => !empty($numericValues) ? min($numericValues) : null,
                'max' => !empty($numericValues) ? max($numericValues) : null,
                'sum' => !empty($numericValues) ? array_sum($numericValues) : null,
                'sample_values' => array_slice($uniqueValues, 0, 5),
                'role' => 'lainnya', 'is_feature' => true,
            ];

            if (in_array($normName, ['tahun', 'year', 'thn'], true) && $isNumericCol) $separateYearCol = $colIdx;
            if (in_array($normName, ['bulan', 'month', 'bln'], true)) $separateMonthCol = $colIdx;
        }

        // A. Identitas
        foreach ($columnProfiles as $idx => &$prof) {
            $norm = $prof['normalized'];
            if (in_array($norm, self::IDENTITY_COLUMNS, true) ||
                preg_match('/^(id_|no_|nomor_)/', $prof['name']) ||
                preg_match('/(_id|_no)$/', $prof['name'])) {
                $prof['role'] = 'identitas'; $prof['is_feature'] = false;
            }
        }
        unset($prof);

        // B. Kode / ICD
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];
            if (in_array($norm, self::CODE_COLUMNS, true)) { $prof['role'] = 'kode'; $prof['is_feature'] = false; }
            if ($prof['role'] === 'lainnya' && !empty($prof['sample_values'])) {
                $icdMatches = 0;
                foreach ($prof['sample_values'] as $sv) {
                    if (preg_match('/^[A-Z]\d{2}(\.\d{1,2})?$/i', trim($sv))) $icdMatches++;
                }
                if ($icdMatches >= 2 || ($icdMatches > 0 && $icdMatches === count($prof['sample_values']))) {
                    $prof['role'] = 'kode_icd10'; $detectedRoles['kode_icd10'] = $prof['name'];
                }
            }
        }
        unset($prof);

        // C. Periode / Tanggal
        $periodColIdx = null; $bestPeriodScore = -1;
        foreach ($columnProfiles as $idx => $prof) {
            if (in_array($prof['role'], ['identitas', 'kode', 'kode_icd10'], true)) continue;
            $norm = $prof['normalized'];
            if (in_array($norm, ['tahun', 'year', 'thn', 'bulan', 'month', 'bln'], true)) continue;
            $score = -1;
            if (in_array($norm, ['periode','tanggal','tgl','tanggalkunjungan','tglkunjungan','date','visitdate','tglpenjualan','tanggalpenjualan','tgltransaksi','tanggaltransaksi','createdat','tglmasuk','tglkeluar','tglinput','waktu'], true)) $score = 100;
            elseif ($prof['is_date']) $score = 80;
            elseif (in_array($norm, ['datetime','timestamp','time'], true)) $score = 70;
            elseif (preg_match('/(tanggal|tgl|date|periode|waktu)/', $norm) && $prof['is_date']) $score = 60;
            if ($score > $bestPeriodScore) { $bestPeriodScore = $score; $periodColIdx = $idx; }
        }
        if ($periodColIdx !== null) {
            $columnProfiles[$periodColIdx]['role'] = 'periode';
            $columnProfiles[$periodColIdx]['is_feature'] = false;
            $detectedRoles['periode'] = $columnProfiles[$periodColIdx]['name'];
        }
        foreach ($columnProfiles as $idx => &$prof) {
            if (in_array($prof['normalized'], ['tahun','year','thn','bulan','month','bln'], true)) $prof['role'] = 'informasi';
        }
        unset($prof);

        // D. Measure / Metrik Numerik (Universal)
        $measureColIdx = null; $bestMeasureScore = -1; $allMeasureCols = [];
        foreach ($columnProfiles as $idx => $prof) {
            $norm = $prof['normalized'];
            if (!in_array($prof['role'], ['lainnya'], true)) continue;
            if (in_array($norm, self::EXCLUDED_MEASURE_COLUMNS, true)) continue;
            if (!$prof['is_numeric'] || $prof['cardinality'] <= 1) continue;
            $score = 0;
            if (in_array($norm, ['jumlahkunjungan','jmlkunjungan','totalkunjungan','visitcount','jumlahkasus','totalkasus','jumlahkematian','totalkematian','nilaitransaksi','totalpenjualan','totalrevenue'], true)) $score = 110;
            elseif (in_array($norm, ['kunjungan','frekuensi','frequency','visit','kematian','mortality','deaths','meninggal','penjualan','sales','revenue','omzet'], true)) $score = 95;
            elseif (in_array($norm, ['jumlah','total','qty','quantity','count','n','nilai','amount'], true)) $score = 75;
            elseif (in_array($norm, ['harga','price','transaksi','siswa','mahasiswa','peserta'], true)) $score = 90;
            elseif (preg_match('/(jumlah|total|kunjungan|kasus|kematian|penjualan|nilai|amount)/', $norm) && $prof['is_numeric']) $score = 60;
            elseif ($prof['is_numeric'] && $prof['cardinality'] >= 2) {
                $minV = $prof['min'] ?? 0; $maxV = $prof['max'] ?? 0;
                if ($minV >= 0 && $maxV > 0) $score = 30;
            }
            if ($score > 0) {
                $allMeasureCols[] = ['idx' => $idx, 'score' => $score, 'name' => $prof['name']];
                if ($score > $bestMeasureScore) { $bestMeasureScore = $score; $measureColIdx = $idx; }
            }
        }
        if ($measureColIdx !== null) {
            $columnProfiles[$measureColIdx]['role'] = 'jumlah';
            $columnProfiles[$measureColIdx]['is_feature'] = false;
            $detectedRoles['jumlah'] = $columnProfiles[$measureColIdx]['name'];
        }
        $numericMetricCols = array_column($allMeasureCols, 'name');

        // E. Wilayah / Geografis
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];
            $isWilayahMatch = in_array($norm, [
                'provinsi','province','kabupaten','kota','kabupatenkota','provinsikabupaten','kecamatan','kelurahan','desa','daerah','wilayah','region','area','district','negara','country','state','city'
            ], true) || preg_match('/^(kabupaten|provinsi|wilayah|daerah)/', $norm);

            if ($isWilayahMatch && !$prof['is_numeric'] && $prof['cardinality'] >= 2 && $prof['cardinality'] <= 500) {
                $prof['role'] = 'wilayah';
                $detectedRoles['wilayah'] = $prof['name'];
                break;
            }
        }
        unset($prof);

        // F. Koordinat
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];
            if (in_array($norm, ['latitude','lat','lintang'], true) && $prof['is_numeric']) { $prof['role'] = 'koordinat_lat'; $detectedRoles['koordinat_lat'] = $prof['name']; }
            elseif (in_array($norm, ['longitude','lon','lng','bujur'], true) && $prof['is_numeric']) { $prof['role'] = 'koordinat_lon'; $detectedRoles['koordinat_lon'] = $prof['name']; }
        }
        unset($prof);

        // G. Jenis Kelamin
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];
            $isMatch = in_array($norm, ['jeniskelamin','kelamin','gender','sex','jk'], true);
            $hasGenderVals = false;
            if (!empty($prof['sample_values'])) {
                $sStr = implode(' ', array_map('strtoupper', $prof['sample_values']));
                if (preg_match('/\b(L|P|LAKI-LAKI|PEREMPUAN|PRIA|WANITA|M|F)\b/', $sStr) && $prof['cardinality'] <= 5) $hasGenderVals = true;
            }
            if ($isMatch || $hasGenderVals) { $prof['role'] = 'jenis_kelamin'; $detectedRoles['jenis_kelamin'] = $prof['name']; break; }
        }
        unset($prof);

        // H. Usia
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];
            if (in_array($norm, ['kelompokusia','kategoriusia','klpusia','agegroup','golonganumur'], true)) { $prof['role'] = 'kelompok_usia'; $detectedRoles['kelompok_usia'] = $prof['name']; break; }
            elseif (in_array($norm, ['usia','umur','age'], true) && $prof['is_numeric']) { $prof['role'] = 'usia'; $detectedRoles['usia'] = $prof['name']; break; }
        }
        unset($prof);

        // I. Diagnosis
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            if (in_array($prof['normalized'], ['diagnosis','diagnosa','namadiagnosis','namadiagnosa','penyakit','namapenyakit','keluhan','kondisi'], true)) {
                $prof['role'] = 'diagnosis'; $detectedRoles['diagnosis'] = $prof['name']; break;
            }
        }
        unset($prof);

        // J. Unit Pelayanan
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            if (in_array($prof['normalized'], ['unitpelayanan','unit','poli','poliklinik','instalasi','ruangan','ruang','layanan','faskes','puskesmas','rumahsakit','rs','klinik','fasilitas'], true)) {
                $prof['role'] = 'unit_pelayanan'; $detectedRoles['unit_pelayanan'] = $prof['name']; break;
            }
        }
        unset($prof);

        // K. Cara Bayar
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            if (in_array($prof['normalized'], ['carabayar','penjamin','asuransi','statusbayar','metodebayar','pembayaran','payment'], true)) {
                $prof['role'] = 'cara_bayar'; $detectedRoles['cara_bayar'] = $prof['name']; break;
            }
        }
        unset($prof);

        // L. Kategori
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            $norm = $prof['normalized'];
            if (in_array($norm, ['kategorikunjungan','jeniskunjungan','statuskunjungan','tipekunjungan'], true)) {
                $prof['role'] = 'kategori_kunjungan'; $detectedRoles['kategori_kunjungan'] = $prof['name']; break;
            }
            if (in_array($norm, ['kategori','category','jenis','type','tipe','kategoriproduk','jenisproduk','subkategori'], true)
                && !$prof['is_numeric'] && $prof['cardinality'] >= 2 && $prof['cardinality'] <= 200) {
                $prof['role'] = 'kategori'; $detectedRoles['kategori'] = $prof['name']; break;
            }
        }
        unset($prof);

        // M. Nama Entitas
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            if (in_array($prof['normalized'], ['nama','name','namaproduk','namasiswa','namapasien','namapelanggan','pelanggan','customer','produk','product','barang','item','pegawai','karyawan','employee'], true) && !$prof['is_numeric']) {
                $prof['role'] = 'nama_entitas'; $detectedRoles['nama_entitas'] = $prof['name']; break;
            }
        }
        unset($prof);

        // N. Status
        foreach ($columnProfiles as $idx => &$prof) {
            if ($prof['role'] !== 'lainnya') continue;
            if (in_array($prof['normalized'], ['status','kondisi','keadaan','state'], true) && !$prof['is_numeric'] && $prof['cardinality'] >= 2 && $prof['cardinality'] <= 20) {
                $prof['role'] = 'status'; $detectedRoles['status'] = $prof['name']; break;
            }
        }
        unset($prof);

        // 3. Manual Overrides
        if (!empty($manualRoleOverrides)) {
            foreach ($columnProfiles as $idx => &$prof) {
                if (isset($manualRoleOverrides[$prof['name']])) {
                    $newRole = trim((string)$manualRoleOverrides[$prof['name']]);
                    $prof['role'] = $newRole;
                    $prof['is_feature'] = !in_array($newRole, ['identitas', 'jumlah'], true);
                    $detectedRoles[$newRole] = $prof['name'];
                    if ($newRole === 'jumlah') $measureColIdx = $idx;
                    if ($newRole === 'periode') $periodColIdx = $idx;
                }
            }
            unset($prof);
        }

        // 4. Tipe Dataset
        $datasetType = $this->inferDatasetType($detectedRoles, $columnProfiles, $headers);

        // 5. Total Measure
        $totalMeasure = 0;
        if ($measureColIdx !== null) {
            foreach ($rows as $row) {
                $val = isset($row[$measureColIdx]) ? trim((string)$row[$measureColIdx]) : '';
                $cleanNum = str_replace(['.', ','], '', $val);
                if (is_numeric($cleanNum)) $totalMeasure += max(0, (float)$cleanNum);
            }
            $totalMeasure = (int)round($totalMeasure);
        } else { $totalMeasure = $totalRows; }

        // 6. Rentang Periode
        $parsedDates = []; $uniqueYmList = [];
        if ($periodColIdx !== null || ($separateYearCol !== null && $separateMonthCol !== null)) {
            foreach ($rows as $row) {
                $dateVal = null;
                if ($periodColIdx !== null && isset($row[$periodColIdx]))
                    $dateVal = $this->spreadsheetService->parseDateValue($row[$periodColIdx], $row, $headers);
                if (!$dateVal && $separateYearCol !== null && $separateMonthCol !== null) {
                    $thn = trim($row[$separateYearCol] ?? ''); $bln = trim($row[$separateMonthCol] ?? '');
                    if (!empty($thn) && !empty($bln))
                        $dateVal = $this->spreadsheetService->parseDateValue("$bln $thn", $row, $headers);
                }
                if ($dateVal) {
                    $parsedDates[] = $dateVal;
                    $ym = substr($dateVal, 0, 7);
                    if (!in_array($ym, $uniqueYmList, true)) $uniqueYmList[] = $ym;
                }
            }
        }
        sort($uniqueYmList);
        $hasPeriod = !empty($parsedDates) && count($uniqueYmList) >= 1;
        $minDate = !empty($parsedDates) ? min($parsedDates) : null;
        $maxDate = !empty($parsedDates) ? max($parsedDates) : null;
        $periodeLabel = null;
        if ($minDate && $maxDate) {
            $indoMonths = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
            $y1 = substr($minDate,0,4); $m1 = $indoMonths[substr($minDate,5,2)] ?? substr($minDate,5,2);
            $y2 = substr($maxDate,0,4); $m2 = $indoMonths[substr($maxDate,5,2)] ?? substr($maxDate,5,2);
            $periodeLabel = ($y1===$y2 && $m1===$m2) ? "$m1 $y1" : "$m1 $y1  $m2 $y2";
        }

        // 7. Fitur Valid
        $validFeatureColumns = []; $categoricalColumns = [];
        foreach ($columnProfiles as $idx => &$prof) {
            if (in_array($prof['normalized'], self::IDENTITY_COLUMNS, true) ||
                in_array($prof['role'], ['identitas','jumlah','periode','kode','koordinat_lat','koordinat_lon'], true)) {
                $prof['is_feature'] = false; continue;
            }
            if ($prof['is_feature']) {
                $validFeatureColumns[] = $prof['name'];
                if (!$prof['is_numeric'] && $prof['cardinality'] >= 2 && $prof['cardinality'] <= 50) $categoricalColumns[] = $prof['name'];
            }
        }
        unset($prof);

        $hasGeoSupport = isset($detectedRoles['wilayah']) || (isset($detectedRoles['koordinat_lat']) && isset($detectedRoles['koordinat_lon']));

        // 8. Dashboard Config
        $dashboardConfig = $this->buildDashboardConfig($datasetType, $detectedRoles, $columnProfiles, $totalMeasure, $totalRows, $hasPeriod, $periodeLabel, $numericMetricCols);

        return [
            'total_rows' => $totalRows, 'total_measure' => $totalMeasure, 'total_kunjungan' => $totalMeasure,
            'measure_column' => $measureColIdx !== null ? $columnProfiles[$measureColIdx]['name'] : null,
            'has_measure_column' => ($measureColIdx !== null), 'numeric_metric_columns' => $numericMetricCols,
            'total_features' => count($validFeatureColumns), 'feature_columns' => $validFeatureColumns,
            'categorical_columns' => $categoricalColumns, 'detected_roles' => $detectedRoles,
            'dataset_type' => $datasetType,
            'has_period' => $hasPeriod,
            'period_range' => ['min' => $minDate, 'max' => $maxDate, 'label' => $periodeLabel,
                'total_periods' => count($uniqueYmList), 'periods' => $uniqueYmList, 'need_toggle' => (count($uniqueYmList) > 24)],
            'has_gender' => isset($detectedRoles['jenis_kelamin']),
            'has_age' => (isset($detectedRoles['usia']) || isset($detectedRoles['kelompok_usia'])),
            'has_diagnosis' => isset($detectedRoles['diagnosis']), 'has_icd10' => isset($detectedRoles['kode_icd10']),
            'has_unit' => isset($detectedRoles['unit_pelayanan']), 'has_cara_bayar' => isset($detectedRoles['cara_bayar']),
            'has_kategori_kunjungan' => isset($detectedRoles['kategori_kunjungan']),
            'has_geo' => $hasGeoSupport, 'has_wilayah' => isset($detectedRoles['wilayah']),
            'has_coordinates' => (isset($detectedRoles['koordinat_lat']) && isset($detectedRoles['koordinat_lon'])),
            'dashboard_config' => $dashboardConfig, 'columns' => $columnProfiles,
            'extra_categorical_columns' => array_slice($categoricalColumns, 0, 2),
            'profiled_at' => date('Y-m-d H:i:s'),
        ];
    }

    protected function inferDatasetType(array $detectedRoles, array $columnProfiles, array $headers): string
    {
        $roles = array_keys($detectedRoles);
        foreach ($columnProfiles as $prof) {
            $norm = $prof['normalized'];
            if (in_array($norm, ['kematian','mortality','deaths','meninggal','jumlahkematian','totalkematian'], true)) return 'mortalitas';
        }
        if (in_array('unit_pelayanan', $roles, true) || in_array('kategori_kunjungan', $roles, true)) return 'kunjungan';
        foreach ($columnProfiles as $prof) {
            if (in_array($prof['normalized'], ['kunjungan','jumlahkunjungan','totalkunjungan','puskesmas','faskes'], true)) return 'kunjungan';
        }
        foreach ($columnProfiles as $prof) {
            if (in_array($prof['normalized'], ['penjualan','sales','transaksi','revenue','omzet','nilaitransaksi','totalpenjualan','harga','produk'], true)) return 'penjualan';
        }
        foreach ($columnProfiles as $prof) {
            if (in_array($prof['normalized'], ['siswa','mahasiswa','peserta','murid','sekolah','universitas','kampus','nilaiujian','ipk'], true)) return 'pendidikan';
        }
        foreach ($columnProfiles as $prof) {
            $norm = $prof['normalized'];
            if (in_array($norm, ['penyakit','kasuspenyakit','jenispenyakit','kasus','malaria','tbparu','kusta','campak','dbd','aids','diare'], true) ||
                preg_match('/(penyakit|kasus)/i', $norm)) {
                return 'epidemiologi';
            }
        }
        if (in_array('diagnosis', $roles, true) || in_array('kode_icd10', $roles, true)) return 'rekam_medis';
        if (in_array('wilayah', $roles, true)) return 'spasial';
        return 'umum';
    }

    protected function buildDashboardConfig(string $datasetType, array $detectedRoles, array $columnProfiles, int $totalMeasure, int $totalRows, bool $hasPeriod, ?string $periodeLabel, array $numericMetricCols): array
    {
        $measureColName = $detectedRoles['jumlah'] ?? null;
        $periodColName  = $detectedRoles['periode'] ?? null;
        $wilayahColName = $detectedRoles['wilayah'] ?? null;
        $kategoriName   = $detectedRoles['kategori'] ?? $detectedRoles['kategori_kunjungan'] ?? null;
        $unitName       = $detectedRoles['unit_pelayanan'] ?? null;
        $diagnosisName  = $detectedRoles['diagnosis'] ?? null;

        switch ($datasetType) {
            case 'mortalitas':   $title='Dashboard Analisis Mortalitas'; $description='Analisis distribusi kematian, penyebab, dan persebaran wilayah berdasarkan data aktif.'; $measureLabel='Total Kematian'; $measureUnit='jiwa'; break;
            case 'epidemiologi': $title='Dashboard Persebaran Kasus Penyakit'; $description='Analisis surveilans morbiditas, sebaran penyakit, dan persebaran wilayah berdasarkan data aktif.'; $measureLabel='Total Kasus Penyakit'; $measureUnit='kasus'; break;
            case 'kunjungan':    $title='Dashboard Kunjungan Fasilitas Kesehatan'; $description='Pantau tren kunjungan, distribusi layanan, dan pola kunjungan pasien berdasarkan dataset aktif.'; $measureLabel='Total Kunjungan'; $measureUnit='kunjungan'; break;
            case 'penjualan':    $title='Dashboard Analisis Penjualan'; $description='Analisis tren penjualan, distribusi produk/kategori, dan kinerja berdasarkan data aktif.'; $measureLabel='Total Penjualan'; $measureUnit='transaksi'; break;
            case 'pendidikan':   $title='Dashboard Statistik Pendidikan'; $description='Analisis distribusi peserta didik, capaian, dan sebaran berdasarkan dataset aktif.'; $measureLabel='Total Peserta'; $measureUnit='orang'; break;
            case 'rekam_medis':  $title='Dashboard Analisis Rekam Medis'; $description='Analisis diagnosis, distribusi penyakit, dan pola rekam medis berdasarkan dataset aktif.'; $measureLabel='Total Rekam Medis'; $measureUnit='rekam'; break;
            case 'spasial':      $title='Dashboard Analisis Spasial'; $description='Analisis distribusi data berdasarkan wilayah dan lokasi geografis.'; $measureLabel='Total Data'; $measureUnit='entri'; break;
            default:
                $title = 'Dashboard Analisis Dataset';
                $description = 'Ringkasan statistik dan visualisasi berdasarkan dataset yang sedang aktif.';
                $measureLabel = $measureColName ? 'Total '.ucwords(str_replace('_',' ',$measureColName)) : 'Total Data';
                $measureUnit = 'entri';
        }

        $kpiCards = [];
        $kpiCards[] = [
            'id' => 'kpi1', 'label' => strtoupper($measureLabel), 'value' => $totalMeasure,
            'format' => 'number', 'sub' => $measureColName ? "Dari kolom: $measureColName" : "Jumlah baris data",
            'icon' => 'activity', 'color' => '#FEE2E2', 'textColor' => '#DC2626',
        ];

        if ($wilayahColName) {
            $uv = []; foreach ($columnProfiles as $p) { if ($p['name']===$wilayahColName) { $uv=$p['sample_values']; break; } }
            $dim2Label='JUMLAH WILAYAH'; $dim2Value=count($uv)>0?'≥ '.count($uv):'—'; $dim2Sub="Dari kolom: $wilayahColName";
        } elseif ($unitName) {
            $dim2Label='UNIT LAYANAN'; $dim2Value=null;
            foreach ($columnProfiles as $p) { if ($p['name']===$unitName) { $dim2Value=$p['cardinality']; break; } }
            $dim2Sub="Dari kolom: $unitName";
        } elseif ($kategoriName) {
            $dim2Label='JUMLAH KATEGORI'; $dim2Value=null;
            foreach ($columnProfiles as $p) { if ($p['name']===$kategoriName) { $dim2Value=$p['cardinality']; break; } }
            $dim2Sub="Dari kolom: $kategoriName";
        } else { $dim2Label='TOTAL BARIS DATA'; $dim2Value=$totalRows; $dim2Sub='Baris valid pada dataset'; }
        $kpiCards[] = [
            'id' => 'kpi2', 'label' => $dim2Label, 'value' => $dim2Value ?? $totalRows,
            'format' => 'text_or_number', 'sub' => $dim2Sub ?? 'Baris valid',
            'icon' => 'layers-fill', 'color' => '#E0F2FE', 'textColor' => '#0284C7',
        ];

        if ($hasPeriod && $periodeLabel) {
            $kpiCards[] = ['id'=>'kpi3','label'=>'RENTANG PERIODE','value'=>$periodeLabel,'format'=>'text','sub'=>$periodColName?"Dari kolom: $periodColName":'Periode data','icon'=>'calendar-range','color'=>'#FEF3C7','textColor'=>'#D97706'];
        } else {
            $kpiCards[] = ['id'=>'kpi3','label'=>'JUMLAH KOLOM','value'=>count($columnProfiles),'format'=>'number','sub'=>'Total atribut dataset','icon'=>'table','color'=>'#FEF3C7','textColor'=>'#D97706'];
        }
        $kpiCards[] = ['id'=>'kpi4','label'=>'KATEGORI UTAMA','value'=>null,'format'=>'model','sub'=>null,'icon'=>'award-fill','color'=>'#DCFCE7','textColor'=>'#16A34A'];

        $charts = [];
        if ($hasPeriod) $charts['trend'] = ['enabled'=>true,'type'=>'line','title'=>'Tren '.$measureLabel.' per Periode','x_label'=>'Periode','y_label'=>$measureLabel,'unit'=>$measureUnit];
        $dimColName = $diagnosisName ?? $unitName ?? $kategoriName ?? $wilayahColName;
        if ($dimColName) $charts['distribution'] = ['enabled'=>true,'type'=>'bar_horizontal','title'=>'Top '.ucwords(str_replace('_',' ',$dimColName)).' Berdasarkan '.$measureLabel,'x_label'=>$measureLabel,'y_label'=>ucwords(str_replace('_',' ',$dimColName)),'unit'=>$measureUnit,'col_name'=>$dimColName];
        if (isset($detectedRoles['jenis_kelamin'])) $charts['gender'] = ['enabled'=>true,'type'=>'doughnut','title'=>'Distribusi Jenis Kelamin','col_name'=>$detectedRoles['jenis_kelamin'],'unit'=>$measureUnit];
        if (isset($detectedRoles['kelompok_usia']) || isset($detectedRoles['usia'])) $charts['age'] = ['enabled'=>true,'type'=>'bar','title'=>'Distribusi Kelompok Usia','col_name'=>$detectedRoles['kelompok_usia']??$detectedRoles['usia'],'unit'=>$measureUnit];
        if (isset($detectedRoles['wilayah']) && count($numericMetricCols) > 1) $charts['comparison'] = ['enabled'=>true,'type'=>'bar_stacked','title'=>'Perbandingan Metrik per Wilayah','col_name'=>$detectedRoles['wilayah'],'metrics'=>$numericMetricCols,'unit'=>$measureUnit];

        switch ($datasetType) {
            case 'mortalitas': $tableTitle='Rincian Mortalitas'; $tableSubtitle='Distribusi data berdasarkan dimensi utama'; break;
            case 'kunjungan':  $tableTitle='Rincian Kunjungan'; $tableSubtitle='Detail kunjungan per unit layanan dan periode'; break;
            case 'penjualan':  $tableTitle='Ringkasan Penjualan'; $tableSubtitle='Detail transaksi per kategori dan periode'; break;
            default: $tableTitle='Ringkasan Data'; $tableSubtitle='Data dari dataset aktif';
        }

        $mapEnabled=false; $mapType=null; $mapRequirement=null;
        if (isset($detectedRoles['koordinat_lat']) && isset($detectedRoles['koordinat_lon'])) { $mapEnabled=true; $mapType='marker'; $mapRequirement='Koordinat tersedia'; }
        elseif (isset($detectedRoles['wilayah'])) { $mapEnabled=true; $mapType='choropleth'; $mapRequirement='Nama wilayah terdeteksi'; }

        return ['title'=>$title,'description'=>$description,'dataset_type'=>$datasetType,'measure_label'=>$measureLabel,'measure_unit'=>$measureUnit,'kpi_cards'=>$kpiCards,'charts'=>$charts,'table_title'=>$tableTitle,'table_subtitle'=>$tableSubtitle,'map_enabled'=>$mapEnabled,'map_type'=>$mapType,'map_requirement'=>$mapRequirement];
    }

    protected function getKpiIcon(string $datasetType, string $position): string
    {
        $icons = [
            'mortalitas'   => ['main'=>'heart-pulse-fill','dimension'=>'geo-alt-fill'],
            'epidemiologi' => ['main'=>'virus','dimension'=>'geo-alt-fill'],
            'kunjungan'    => ['main'=>'people-fill','dimension'=>'hospital-fill'],
            'penjualan'    => ['main'=>'cart-fill','dimension'=>'tags-fill'],
            'pendidikan'   => ['main'=>'mortarboard-fill','dimension'=>'building-fill'],
            'rekam_medis'  => ['main'=>'file-medical-fill','dimension'=>'clipboard2-pulse-fill'],
            'spasial'      => ['main'=>'pin-map-fill','dimension'=>'map-fill'],
            'umum'         => ['main'=>'database-fill','dimension'=>'grid-fill']
        ];
        return $icons[$datasetType][$position] ?? 'bar-chart-fill';
    }
}