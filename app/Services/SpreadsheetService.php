<?php

namespace App\Services;

class SpreadsheetService
{
    /**
     * Karakter pembatas (delimiters) yang didukung untuk CSV
     */
    protected array $delimiters = [',', ';', "\t", '|'];

    /**
     * Atribut standar rekam medis klinis sesuai PRD Polara
     */
    public const STANDARD_ATTRIBUTES = [
        'periode'            => 'Periode / Tanggal Kunjungan',
        'jumlah_kunjungan'   => 'Frekuensi / Jumlah Kunjungan (Measure)',
        'nama_diagnosis'     => 'Nama Diagnosis Klinis',
        'kode_diagnosis'     => 'Kode Diagnosis (ICD-10)',
        'unit_pelayanan'     => 'Unit Pelayanan / Poliklinik',
        'jenis_kelamin'      => 'Jenis Kelamin (L/P)',
        'usia'               => 'Usia Pasien (Numerik)',
        'kelompok_usia'      => 'Kelompok Usia (Kategori)',
        'cara_bayar'         => 'Cara Bayar (BPJS / Umum / Asuransi)',
        'kategori_kunjungan' => 'Kategori Kunjungan (Baru / Lama)',
        'target'             => 'Target Klasifikasi (C4.5 / Naive Bayes)',
    ];

    /**
     * Cek apakah berkas merupakan format Excel OpenXML (.xlsx)
     */
    public function isXlsx(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'xlsx') {
            return true;
        }

        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        $sig = fread($handle, 4);
        fclose($handle);

        return $sig === "PK\x03\x04";
    }

    /**
     * Deteksi apakah berkas adalah Excel Binary Legacy (.xls — BIFF/Compound Document)
     */
    public function isXls(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'xls') {
            return true;
        }
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        $sig = fread($handle, 8);
        fclose($handle);
        // D0 CF 11 E0 A1 B1 1A E1 = OLE2 Compound Document
        return substr($sig, 0, 4) === "\xD0\xCF\x11\xE0";
    }

    /**
     * Decode BIFF RK compressed number format
     */
    protected function decodeRk(int $rk): float|int
    {
        if ($rk & 0x02) {
            $val = $rk >> 2;
        } else {
            $val = unpack('d', pack('V2', 0, $rk & 0xFFFFFFFC))[1];
        }
        if ($rk & 0x01) {
            $val /= 100;
        }
        return (round($val) == $val) ? (int)$val : round($val, 2);
    }

    /**
     * Ekstrak stream Workbook dari berkas OLE2 Compound Document
     */
    protected function extractOleWorkbook(string $filePath): ?string
    {
        $data = @file_get_contents($filePath);
        if ($data === false || strlen($data) < 512) {
            return null;
        }
        if (substr($data, 0, 4) !== "\xD0\xCF\x11\xE0") {
            return null;
        }

        $sectorSize = 512;
        $numFatSectors = unpack('V', substr($data, 0x2C, 4))[1];
        $firstDirSector = unpack('V', substr($data, 0x30, 4))[1];

        $fat = [];
        for ($i = 0; $i < min($numFatSectors, 109); $i++) {
            $fatSec = unpack('V', substr($data, 0x4C + ($i * 4), 4))[1];
            $secOffset = ($fatSec + 1) * $sectorSize;
            $secData = substr($data, $secOffset, $sectorSize);
            $entries = unpack('V128', $secData);
            foreach ($entries as $val) {
                $fat[] = $val;
            }
        }

        $dirSec = $firstDirSector;
        $dirData = '';
        while ($dirSec >= 0 && $dirSec < count($fat) && $dirSec != 0xFFFFFFFE) {
            $dirOffset = ($dirSec + 1) * $sectorSize;
            $dirData .= substr($data, $dirOffset, $sectorSize);
            $dirSec = $fat[$dirSec] ?? 0xFFFFFFFE;
        }

        $numEntries = (int)(strlen($dirData) / 128);
        $startSec = null;
        $streamSize = 0;

        for ($i = 0; $i < $numEntries; $i++) {
            $entry = substr($dirData, $i * 128, 128);
            $nameLen = unpack('v', substr($entry, 64, 2))[1];
            if ($nameLen <= 0) continue;
            $name = @mb_convert_encoding(substr($entry, 0, $nameLen - 2), 'UTF-8', 'UTF-16LE');
            if (strcasecmp($name, 'Workbook') === 0 || strcasecmp($name, 'Book') === 0) {
                $startSec = unpack('V', substr($entry, 116, 4))[1];
                $streamSize = unpack('V', substr($entry, 120, 4))[1];
                break;
            }
        }

        if ($startSec === null) return null;

        $stream = '';
        $sec = $startSec;
        while ($sec >= 0 && $sec < count($fat) && $sec != 0xFFFFFFFE) {
            $secOffset = ($sec + 1) * $sectorSize;
            $stream .= substr($data, $secOffset, $sectorSize);
            $sec = $fat[$sec] ?? 0xFFFFFFFE;
        }

        return substr($stream, 0, $streamSize);
    }

    /**
     * Parser XLS Legacy (BIFF8 OLE2) — membaca langsung sel dan SST dari dokumen Excel biner
     */
    public function parseXls(string $filePath, int $maxRows = 0): array
    {
        $wb = $this->extractOleWorkbook($filePath);
        if (!$wb) {
            throw new \RuntimeException("Berkas XLS tidak dapat dibaca atau bukan berkas Excel biner yang valid.");
        }

        $len = strlen($wb);
        $pos = 0;
        $strings = [];
        $cells = [];

        // Pass 1: Baca Shared String Table (SST)
        while ($pos + 4 <= $len) {
            $recType = ord($wb[$pos]) | (ord($wb[$pos + 1]) << 8);
            $recLen  = ord($wb[$pos + 2]) | (ord($wb[$pos + 3]) << 8);

            if ($recType === 0x00FC && $recLen >= 8) {
                $totalStrings = unpack('V', substr($wb, $pos + 4, 4))[1];
                $uniqueStrings = unpack('V', substr($wb, $pos + 8, 4))[1];

                $p = $pos + 12;
                for ($i = 0; $i < $uniqueStrings && $p < $len; $i++) {
                    if ($p + 2 <= $len && substr($wb, $p, 2) === "\x3C\x00") {
                        $p += 4;
                    }
                    if ($p + 3 > $len) break;
                    $charCount = ord($wb[$p]) | (ord($wb[$p + 1]) << 8);
                    $flags = ord($wb[$p + 2]);
                    $p += 3;

                    $isUtf16 = ($flags & 0x01) !== 0;
                    $hasExt = ($flags & 0x04) !== 0;
                    $hasRich = ($flags & 0x08) !== 0;

                    $richRuns = 0;
                    if ($hasRich) {
                        $richRuns = ord($wb[$p]) | (ord($wb[$p + 1]) << 8);
                        $p += 2;
                    }
                    $extLen = 0;
                    if ($hasExt) {
                        $extLen = unpack('V', substr($wb, $p, 4))[1];
                        $p += 4;
                    }

                    $byteCount = $charCount * ($isUtf16 ? 2 : 1);
                    $rawChars = substr($wb, $p, $byteCount);
                    $p += $byteCount;

                    if ($hasRich) $p += ($richRuns * 4);
                    if ($hasExt) $p += $extLen;

                    if ($isUtf16) {
                        $str = @mb_convert_encoding($rawChars, 'UTF-8', 'UTF-16LE');
                    } else {
                        $str = $rawChars;
                    }
                    $strings[] = trim($str);
                }
                break;
            }
            $pos += 4 + $recLen;
        }

        // Pass 2: Baca Cell Records (LABELSST, LABEL, NUMBER, RK, MULRK)
        $pos = 0;
        while ($pos + 4 <= $len) {
            $recType = ord($wb[$pos]) | (ord($wb[$pos + 1]) << 8);
            $recLen  = ord($wb[$pos + 2]) | (ord($wb[$pos + 3]) << 8);
            $recData = substr($wb, $pos + 4, $recLen);
            $pos += 4 + $recLen;

            if ($recType === 0x00FD) { // LABELSST
                if ($recLen >= 10) {
                    $row = unpack('v', substr($recData, 0, 2))[1];
                    $col = unpack('v', substr($recData, 2, 2))[1];
                    $sstIndex = unpack('V', substr($recData, 6, 4))[1];
                    if (isset($strings[$sstIndex])) {
                        $cells[$row][$col] = $strings[$sstIndex];
                    }
                }
            } elseif ($recType === 0x0204) { // LABEL
                if ($recLen >= 8) {
                    $row = unpack('v', substr($recData, 0, 2))[1];
                    $col = unpack('v', substr($recData, 2, 2))[1];
                    $strLen = unpack('v', substr($recData, 6, 2))[1];
                    $cells[$row][$col] = trim(substr($recData, 8, $strLen));
                }
            } elseif ($recType === 0x0203) { // NUMBER
                if ($recLen >= 14) {
                    $row = unpack('v', substr($recData, 0, 2))[1];
                    $col = unpack('v', substr($recData, 2, 2))[1];
                    $val = unpack('d', substr($recData, 6, 8))[1];
                    $cells[$row][$col] = (round($val) == $val) ? (int)$val : round($val, 2);
                }
            } elseif ($recType === 0x027E) { // RK
                if ($recLen >= 10) {
                    $row = unpack('v', substr($recData, 0, 2))[1];
                    $col = unpack('v', substr($recData, 2, 2))[1];
                    $rk = unpack('V', substr($recData, 6, 4))[1];
                    $cells[$row][$col] = $this->decodeRk($rk);
                }
            } elseif ($recType === 0x00BD) { // MULRK
                if ($recLen >= 6) {
                    $row = unpack('v', substr($recData, 0, 2))[1];
                    $firstCol = unpack('v', substr($recData, 2, 2))[1];
                    $numRk = ($recLen - 6) / 6;
                    for ($k = 0; $k < $numRk; $k++) {
                        $col = $firstCol + $k;
                        $rk = unpack('V', substr($recData, 4 + ($k * 6) + 2, 4))[1];
                        $cells[$row][$col] = $this->decodeRk($rk);
                    }
                }
            }
        }

        if (empty($cells)) {
            throw new \RuntimeException("Berkas XLS tidak mengandung sel data yang dapat dibaca.");
        }

        ksort($cells);
        $maxCol = 0;
        foreach ($cells as $rCols) {
            foreach ($rCols as $c => $v) {
                if ($c > $maxCol) $maxCol = $c;
            }
        }

        // Susun density grid
        $grid = [];
        foreach ($cells as $rIdx => $rCols) {
            $row = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $row[$c] = isset($rCols[$c]) ? (string)$rCols[$c] : '';
            }
            $grid[$rIdx] = $row;
        }

        // Deteksi baris header utama (baris yang memiliki >= 2 sel teks non-numerik)
        $headerRowIdx = 0;
        foreach ($grid as $rIdx => $row) {
            $nonEmpty = array_filter($row, function($v) {
                return trim($v) !== '' && !is_numeric(str_replace(['.', ','], '', $v));
            });
            if (count($nonEmpty) >= 2) {
                $headerRowIdx = $rIdx;
                break;
            }
        }

        // Cek apakah baris berikutnya adalah sub-header (header bertingkat)
        $headers = [];
        for ($c = 0; $c <= $maxCol; $c++) {
            $headers[$c] = trim($grid[$headerRowIdx][$c] ?? '');
        }

        $nextIdx = $headerRowIdx + 1;
        $dataStartIdx = $nextIdx;
        if (isset($grid[$nextIdx])) {
            $nextNonEmpty = array_filter($grid[$nextIdx], function($v) {
                return trim($v) !== '' && !is_numeric(str_replace(['.', ','], '', $v));
            });
            if (count($nextNonEmpty) >= 2) {
                for ($c = 0; $c <= $maxCol; $c++) {
                    $child = trim($grid[$nextIdx][$c] ?? '');
                    if ($child !== '') {
                        $headers[$c] = $child;
                    }
                }
                $dataStartIdx = $nextIdx + 1;
            }
        }

        // Saring kolom aktif yang memiliki header sebenarnya (bukan kolom pemisah atau catatan kosong)
        $activeCols = [];
        for ($c = 0; $c <= $maxCol; $c++) {
            $hVal = trim($headers[$c] ?? '');
            if ($hVal !== '' && !preg_match('/^(kolom\s*\d+)$/i', $hVal)) {
                $activeCols[] = $c;
            }
        }

        // Fallback jika tidak ada kolom dengan header teks yang cocok
        if (empty($activeCols)) {
            for ($c = 0; $c <= $maxCol; $c++) {
                $hasData = false;
                foreach ($grid as $rIdx => $row) {
                    if ($rIdx < $dataStartIdx) continue;
                    if (trim($row[$c] ?? '') !== '') {
                        $hasData = true;
                        break;
                    }
                }
                if ($hasData) {
                    $activeCols[] = $c;
                }
            }
        }

        // Header final
        $cleanHeaders = [];
        foreach ($activeCols as $idx => $c) {
            $h = trim($headers[$c] ?? '');
            $cleanHeaders[] = $h !== '' ? $h : ('Kolom ' . ($idx + 1));
        }

        // Ekstraksi baris data, pisahkan baris Jumlah/Total dan Catatan Kaki
        $rows = [];
        $summaryRow = null;
        $notes = [];

        foreach ($grid as $rIdx => $row) {
            if ($rIdx < $dataStartIdx) continue;

            $firstCell = trim($row[$activeCols[0]] ?? '');
            
            // Cek jika seluruh kolom aktif kosong
            $hasAnyVal = false;
            foreach ($activeCols as $c) {
                if (trim($row[$c] ?? '') !== '') {
                    $hasAnyVal = true;
                    break;
                }
            }
            if (!$hasAnyVal) continue;

            // Deteksi baris Jumlah / Total (untuk pembanding validasi)
            if (preg_match('/^(jumlah|total|indonesia)$/i', $firstCell)) {
                $sRow = [];
                foreach ($activeCols as $c) {
                    $sRow[] = trim($row[$c] ?? '');
                }
                $summaryRow = $sRow;
                continue;
            }

            // Deteksi catatan kaki atau metadata tambahan (misal baris Catatan, NA, RSE)
            $rowText = implode(' ', array_filter(array_map('trim', $row)));
            if (empty($firstCell) || preg_match('/^(catatan|note|sumber|source|na\s*\(|perbedaan\s*nilai)/i', $rowText) || preg_match('/^(catatan|note|sumber|source)/i', $firstCell)) {
                if ($rowText !== '') {
                    $notes[] = $rowText;
                }
                continue;
            }

            // Baris data entitas (provinsi / record)
            $cleanRow = [];
            foreach ($activeCols as $c) {
                $cleanRow[] = trim($row[$c] ?? '');
            }
            $rows[] = $cleanRow;

            if ($maxRows > 0 && count($rows) >= $maxRows) {
                break;
            }
        }

        return [
            'headers'     => $cleanHeaders,
            'rows'        => $rows,
            'total'       => count($rows),
            'summary_row' => $summaryRow,
            'notes'       => $notes,
        ];
    }

    /**
     * Konversi huruf kolom Excel (A, B, ..., AA, dll) ke indeks 0-based
     */
    protected function colLetterToIndex(string $col): int
    {
        $idx = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - 64);
        }
        return $idx - 1;
    }

    /**
     * Ekstrak isi file zip XLSX menggunakan DEFLATE bawaan PHP tanpa dependensi ekstensi eksternal
     */
    protected function extractZipEntries(string $filePath): array
    {
        $data = @file_get_contents($filePath);
        if ($data === false) {
            throw new \RuntimeException("Gagal membaca berkas: {$filePath}");
        }

        $files = [];
        $offset = 0;
        $len = strlen($data);

        while ($offset + 30 <= $len) {
            $sig = substr($data, $offset, 4);
            if ($sig !== "PK\x03\x04") {
                break;
            }

            $header = unpack('vversion/vflags/vmethod/vmodtime/vmoddate/Vcrc32/VcompSize/VuncompSize/vnameLen/vextraLen', substr($data, $offset + 4, 26));
            $nameLen  = $header['nameLen'];
            $extraLen = $header['extraLen'];
            $name     = substr($data, $offset + 30, $nameLen);
            $compData = substr($data, $offset + 30 + $nameLen + $extraLen, $header['compSize']);

            if ($header['method'] == 0) {
                $files[$name] = $compData;
            } elseif ($header['method'] == 8) {
                $uncomp = @gzinflate($compData);
                if ($uncomp !== false) {
                    $files[$name] = $uncomp;
                }
            }

            $offset += 30 + $nameLen + $extraLen + $header['compSize'];
        }

        return $files;
    }

    /**
     * Parser berkas XLSX OpenXML murni PHP
     */
    public function parseXlsx(string $filePath, int $maxRows = 0): array
    {
        $files = $this->extractZipEntries($filePath);

        // Baca Shared Strings
        $strings = [];
        if (isset($files['xl/sharedStrings.xml'])) {
            $xml = @simplexml_load_string($files['xl/sharedStrings.xml']);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $strings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $t = '';
                        foreach ($si->r as $run) {
                            $t .= (string) ($run->t ?? '');
                        }
                        $strings[] = $t;
                    } else {
                        $strings[] = '';
                    }
                }
            }
        }

        // Cari worksheet pertama
        $sheetXmlContent = null;
        foreach ($files as $name => $content) {
            if (preg_match('#xl/worksheets/sheet\d+\.xml#i', $name)) {
                $sheetXmlContent = $content;
                break;
            }
        }

        if (!$sheetXmlContent) {
            throw new \RuntimeException("Berkas Excel tidak memiliki lembar kerja yang dapat dibaca.");
        }

        $xml = @simplexml_load_string($sheetXmlContent);
        if (!$xml || !isset($xml->sheetData)) {
            throw new \RuntimeException("Lembar kerja Excel kosong atau tidak valid.");
        }

        $allRows = [];
        $maxCols = 0;

        foreach ($xml->sheetData->row as $r) {
            $rowArr = [];
            foreach ($r->c as $c) {
                $ref = (string) $c['r'];
                $colIdx = count($rowArr);
                if (preg_match('/^([A-Z]+)\d+$/', $ref, $m)) {
                    $colIdx = $this->colLetterToIndex($m[1]);
                }

                $type = (string) $c['t'];
                $val  = '';

                if ($type === 's' && isset($c->v)) {
                    $sIdx = (int) $c->v;
                    $val  = $strings[$sIdx] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string) $c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string) $c->v;
                }

                $rowArr[$colIdx] = trim($val);
                if ($colIdx + 1 > $maxCols) {
                    $maxCols = $colIdx + 1;
                }
            }

            // Ratakan baris
            $padded = [];
            for ($i = 0; $i < $maxCols; $i++) {
                $padded[$i] = $rowArr[$i] ?? '';
            }

            $allRows[] = $padded;
            if ($maxRows > 0 && count($allRows) > $maxRows) {
                break;
            }
        }

        if (empty($allRows)) {
            throw new \RuntimeException("Berkas Excel tidak memiliki baris data.");
        }

        // ── Smart Multi-Row & Complex Header Detection ──
        // 1. Temukan baris kandidat header terbaik (bukan judul sheet 1 sel atau teks tunggal)
        $headerRowIdx = 0;
        $maxRealText = 0;
        $scanLimit = min(10, count($allRows));

        for ($rIdx = 0; $rIdx < $scanLimit; $rIdx++) {
            $row = $allRows[$rIdx];
            $realTexts = array_filter($row, function($v) {
                $v = trim((string)$v);
                return $v !== '' 
                    && !preg_match('/^Kolom[_\s]\d+$/i', $v)
                    && !is_numeric(str_replace(['.', ','], '', $v))
                    && !preg_match('/^(19|20)\d{2}$/', $v);
            });
            $cnt = count($realTexts);
            if ($cnt > $maxRealText) {
                $maxRealText = $cnt;
                $headerRowIdx = $rIdx;
            }
        }

        // 2. Buat header awal dari baris kandidat terbaik
        $headers = $allRows[$headerRowIdx];

        // Isi kolom yang kosong dari baris di atasnya (misal label kategori di baris 0/1 seperti 'Kabupaten/Kota')
        for ($c = 0; $c < $maxCols; $c++) {
            $hVal = trim((string)($headers[$c] ?? ''));
            if ($hVal === '' || preg_match('/^Kolom[_\s]\d+$/i', $hVal)) {
                for ($back = $headerRowIdx - 1; $back >= 0; $back--) {
                    $prev = trim((string)($allRows[$back][$c] ?? ''));
                    if ($prev !== '' && !preg_match('/^Kolom[_\s]\d+$/i', $prev)) {
                        $headers[$c] = $prev;
                        break;
                    }
                }
            }
        }

        // 3. Periksa apakah baris berikutnya adalah subheader tahun/periode (misal baris '2023, 2023...')
        $dataStartIdx = $headerRowIdx + 1;
        if (isset($allRows[$dataStartIdx])) {
            $nextRow = $allRows[$dataStartIdx];
            $nextNonEmpty = 0;
            $allYears = true;
            foreach ($nextRow as $v) {
                $v = trim((string)$v);
                if ($v === '') continue;
                $nextNonEmpty++;
                if (!preg_match('/^(19|20)\d{2}$/', $v)) {
                    $allYears = false;
                }
            }
            if ($nextNonEmpty >= 2 && $allYears) {
                for ($c = 0; $c < $maxCols; $c++) {
                    $y = trim((string)($nextRow[$c] ?? ''));
                    if ($y !== '' && isset($headers[$c]) && $headers[$c] !== '' && !str_contains($headers[$c], $y)) {
                        $headers[$c] .= " ($y)";
                    }
                }
                $dataStartIdx++;
            }
        }

        // 4. Pastikan semua kolom memiliki nama header yang jelas
        for ($c = 0; $c < $maxCols; $c++) {
            $h = trim((string)($headers[$c] ?? ''));
            if ($h === '' || preg_match('/^Kolom[_\s]\d+$/i', $h)) {
                $headers[$c] = 'Kolom_' . ($c + 1);
            } else {
                $headers[$c] = trim($h, '"\'');
            }
        }

        // 5. Ekstraksi baris data aktual (lewati baris judul/header di atas $dataStartIdx)
        $cleanRows = [];
        $totalRaw = count($allRows);
        for ($i = $dataStartIdx; $i < $totalRaw; $i++) {
            $r = $allRows[$i];
            // Lewati baris yang sepenuhnya kosong
            $hasContent = false;
            foreach ($r as $val) {
                if (trim((string)$val) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if ($hasContent) {
                $cleanRows[] = $r;
            }
        }

        return [
            'headers' => $headers,
            'rows'    => $cleanRows,
            'total'   => count($cleanRows),
        ];
    }

    /**
     * Hitung total baris data pada berkas (CSV, XLSX, atau XLS Legacy)
     */
    public function countTotalRows(string $filePath): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        if ($this->isXlsx($filePath)) {
            try {
                $parsed = $this->parseXlsx($filePath, 0);
                return count($parsed['rows']);
            } catch (\Throwable $e) {
                return 0;
            }
        }

        if ($this->isXls($filePath)) {
            try {
                $parsed = $this->parseXls($filePath, 0);
                return count($parsed['rows']);
            } catch (\Throwable $e) {
                return 0;
            }
        }

        // CSV
        $rowCount = 0;
        $delimiter = $this->detectDelimiter($filePath);
        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle, 0, $delimiter); // skip header
            while (fgetcsv($handle, 0, $delimiter) !== false) {
                $rowCount++;
            }
            fclose($handle);
        }

        return $rowCount;
    }

    /**
     * Deteksi delimiter yang paling konsisten dalam berkas CSV
     */
    public function detectDelimiter(string $filePath): string
    {
        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            return ',';
        }

        $lines = [];
        for ($i = 0; $i < 5 && ($line = fgets($handle)) !== false; $i++) {
            $lines[] = $line;
        }
        fclose($handle);

        if (empty($lines)) {
            return ',';
        }

        $bestDelimiter = ',';
        $maxCount = 0;

        foreach ($this->delimiters as $delim) {
            $counts = [];
            foreach ($lines as $line) {
                $counts[] = count(str_getcsv($line, $delim));
            }
            if (count(array_unique($counts)) === 1 && $counts[0] > 1) {
                if ($counts[0] > $maxCount) {
                    $maxCount = $counts[0];
                    $bestDelimiter = $delim;
                }
            }
        }

        return $bestDelimiter;
    }

    /**
     * Baca header dan sampel baris data (default 15 baris)
     */
    public function readSample(string $filePath, int $maxRows = 15): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Berkas dataset tidak ditemukan pada: {$filePath}");
        }

        // Jika berkas adalah Excel OpenXML (.xlsx)
        if ($this->isXlsx($filePath)) {
            $parsed = $this->parseXlsx($filePath, $maxRows);
            $headers    = $parsed['headers'];
            $sampleRows = array_slice($parsed['rows'], 0, $maxRows);
            $totalRows  = $parsed['total'];

            $detectedTypes = $this->detectColumnTypes($headers, $sampleRows);
            $suggestedMappings = [];
            foreach ($headers as $idx => $header) {
                $suggestedMappings[$idx] = $this->suggestMapping($header, $detectedTypes[$idx] ?? 'string');
            }

            return [
                'delimiter'          => 'xlsx',
                'headers'            => $headers,
                'sample_rows'        => $sampleRows,
                'total_rows'         => $totalRows,
                'detected_types'     => $detectedTypes,
                'suggested_mappings' => $suggestedMappings,
            ];
        }

        // Jika berkas adalah Excel Binary Legacy (.xls)
        if ($this->isXls($filePath)) {
            $parsed = $this->parseXls($filePath, $maxRows);
            $headers    = $parsed['headers'];
            $sampleRows = array_slice($parsed['rows'], 0, $maxRows);
            $totalRows  = $parsed['total'];

            $detectedTypes = $this->detectColumnTypes($headers, $sampleRows);
            $suggestedMappings = [];
            foreach ($headers as $idx => $header) {
                $suggestedMappings[$idx] = $this->suggestMapping($header, $detectedTypes[$idx] ?? 'string');
            }

            return [
                'delimiter'          => 'xls',
                'headers'            => $headers,
                'sample_rows'        => $sampleRows,
                'total_rows'         => $totalRows,
                'summary_row'        => $parsed['summary_row'] ?? null,
                'notes'              => $parsed['notes'] ?? [],
                'detected_types'     => $detectedTypes,
                'suggested_mappings' => $suggestedMappings,
                'format_warning'     => 'Format .xls (Excel 97-2003) terdeteksi. Untuk hasil terbaik, gunakan format .xlsx atau .csv.',
            ];
        }

        // Format CSV
        $delimiter = $this->detectDelimiter($filePath);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Gagal membuka berkas dataset untuk dibaca.");
        }

        // Baca header
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new \RuntimeException("Berkas dataset kosong.");
        }

        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $headers = str_getcsv(trim($firstLine), $delimiter);

        $headers = array_map(function ($h) {
            return trim(trim($h), '"\'');
        }, $headers);

        $sampleRows = [];
        $totalRows = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $totalRows++;
            if (count($sampleRows) < $maxRows) {
                $sampleRows[] = array_pad($row, count($headers), '');
            }
        }
        fclose($handle);

        $detectedTypes = $this->detectColumnTypes($headers, $sampleRows);
        $suggestedMappings = [];
        foreach ($headers as $idx => $header) {
            $suggestedMappings[$idx] = $this->suggestMapping($header, $detectedTypes[$idx] ?? 'string');
        }

        return [
            'delimiter'          => $delimiter,
            'headers'            => $headers,
            'sample_rows'        => $sampleRows,
            'total_rows'         => $totalRows,
            'detected_types'     => $detectedTypes,
            'suggested_mappings' => $suggestedMappings,
        ];
    }

    /**
     * Deteksi tipe data kolom (integer, float, date, string/category)
     */
    public function detectColumnTypes(array $headers, array $sampleRows): array
    {
        $types = [];

        foreach ($headers as $colIdx => $header) {
            $values = [];
            foreach ($sampleRows as $row) {
                if (isset($row[$colIdx]) && trim($row[$colIdx]) !== '' && trim($row[$colIdx]) !== '–' && trim($row[$colIdx]) !== '...') {
                    $values[] = trim($row[$colIdx]);
                }
            }

            if (empty($values)) {
                $types[$colIdx] = 'string';
                continue;
            }

            $isInt = true;
            $isFloat = true;
            $isDate = true;

            foreach ($values as $val) {
                // Tangani penanda statistik non-kematian (NA, -, –)
                if (strcasecmp($val, 'NA') === 0 || $val === '-' || $val === '–') {
                    continue;
                }

                // Bersihkan format angka Indonesia: titik sebagai pemisah ribuan (1.234.567) atau koma sebagai desimal
                $valClean = $val;
                if (preg_match('/^\d{1,3}(\.\d{3})+$/', $valClean)) {
                    // Murni ribuan berformat Indonesia, cth: 4.456.007 -> 4456007
                    $valClean = str_replace('.', '', $valClean);
                } elseif (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $valClean)) {
                    // Ribuan dan desimal koma, cth: 1.234,56 -> 1234.56
                    $valClean = str_replace('.', '', $valClean);
                    $valClean = str_replace(',', '.', $valClean);
                } else {
                    $valClean = str_replace(',', '.', $valClean);
                }

                if (!is_numeric($valClean) || (string)(int)$valClean !== (string)(float)$valClean) {
                    $isInt = false;
                }
                if (!is_numeric($valClean)) {
                    $isFloat = false;
                }
                if (!preg_match('/^\d{4}[-\/]\d{1,2}[-\/]\d{1,2}/', $val) && !preg_match('/^\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}/', $val)) {
                    $isDate = false;
                }
            }

            if ($isInt) {
                $types[$colIdx] = 'integer';
            } elseif ($isFloat) {
                $types[$colIdx] = 'float';
            } elseif ($isDate) {
                $types[$colIdx] = 'date';
            } else {
                $types[$colIdx] = 'string';
            }
        }

        return $types;
    }

    /**
     * Rekomendasi otomatis pemetaan kolom semantik
     */
    public function suggestMapping(string $colName, string $detectedType): ?string
    {
        $lower = strtolower($colName);

        if (preg_match('/(usia|umur|age)/i', $lower)) {
            return 'usia';
        }
        if (preg_match('/(kelamin|gender|sex|jk)/i', $lower)) {
            return 'jenis_kelamin';
        }
        if (preg_match('/(icd|kode_diag|kode_icd|kode_penyakit)/i', $lower)) {
            return 'kode_diagnosis';
        }
        if (preg_match('/(diagnosa|diagnosis|penyakit|keluhan|nama_diag)/i', $lower)) {
            return 'nama_diagnosis';
        }
        if (preg_match('/(poli|poliklinik|unit|ruang|instalasi|layanan)/i', $lower)) {
            return 'unit_pelayanan';
        }
        if (preg_match('/(tgl|tanggal|periode|date|waktu|tahun|bulan)/i', $lower)) {
            return 'periode';
        }
        if (preg_match('/(target|label|kelas|rujukan|status_rujuk)/i', $lower)) {
            return 'target';
        }
        if (preg_match('/(jumlah|frekuensi|kunjungan|visit_count|total_kasus)/i', $lower) && in_array($detectedType, ['integer', 'float'])) {
            return 'jumlah_kunjungan';
        }

        return null;
    }

    /**
     * Konversi berbagai format tanggal (Excel serial number, ISO, DD/MM/YYYY, teks bulan Indonesia)
     * menjadi format standar basis data YYYY-MM-DD
     */
    public function parseDateValue(mixed $val, array $rawRow = [], array $headers = []): ?string
    {
        if ($val instanceof \DateTimeInterface) {
            return $val->format('Y-m-d');
        }

        $val = trim((string)$val);

        if (empty($val) || $val === '–' || $val === '...' || $val === 'NULL' || $val === 'null') {
            // Coba cari kolom tahun dan bulan di baris mentah
            $tahun = null;
            $bulan = null;
            foreach ($headers as $idx => $h) {
                $hLower = strtolower(trim((string)$h));
                if (in_array($hLower, ['tahun', 'year', 'thn'], true) && !empty($rawRow[$idx])) {
                    $tahun = trim((string)$rawRow[$idx]);
                }
                if (in_array($hLower, ['bulan', 'month', 'bln'], true) && !empty($rawRow[$idx])) {
                    $bulan = trim((string)$rawRow[$idx]);
                }
            }
            if ($tahun && $bulan) {
                $val = "$bulan $tahun";
            } elseif ($tahun) {
                $val = "$tahun";
            }
        }

        if (empty($val) || $val === '–' || $val === '...' || $val === 'NULL' || $val === 'null') {
            return null;
        }

        // 1. Cek Excel Serial Date (angka integer antara 30000 s/d 60000, ~tahun 1982 s/d 2064)
        // HANYA jika ada indikasi kolom tanggal pada header (misal 'tgl', 'date', 'periode') atau bukan kolom numerik biasa
        $hasDateHint = false;
        if (!empty($headers)) {
            foreach ($headers as $h) {
                if (preg_match('/(tgl|tanggal|date|periode|waktu|time)/i', (string)$h)) {
                    $hasDateHint = true;
                    break;
                }
            }
        }
        if ($hasDateHint && is_numeric($val) && (float)$val >= 30000 && (float)$val <= 60000 && !str_contains($val, '.')) {
            $serial = (float)$val;
            $ts = ($serial - 25569) * 86400;
            return gmdate('Y-m-d', (int)round($ts));
        }

        // 2. Format tahun saja (misal "2024" atau "2025")
        if (preg_match('/^(19\d\d|20\d\d)$/', $val, $m)) {
            return $m[1] . '-01-01';
        }

        // 3. Format Tahun dan Bulan numerik (misal "2025-01" atau "2025/01")
        if (preg_match('/^(19\d\d|20\d\d)[\/\-](\d{1,2})$/', $val, $m)) {
            return sprintf('%s-%02d-01', $m[1], (int)$m[2]);
        }

        // 4. Terjemahkan nama bulan Indonesia ke bahasa Inggris
        $indoMonths = [
            'januari' => 'January', 'februari' => 'February', 'pebruari' => 'February',
            'maret' => 'March', 'april' => 'April', 'mei' => 'May', 'juni' => 'June',
            'juli' => 'July', 'agustus' => 'August', 'september' => 'September',
            'oktober' => 'October', 'november' => 'November', 'nopember' => 'November',
            'desember' => 'December',
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
            'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'ags' => 'Aug',
            'agu' => 'Aug', 'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov',
            'des' => 'Dec'
        ];
        $cleanVal = str_ireplace(array_keys($indoMonths), array_values($indoMonths), $val);

        // 5. Format DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $cleanVal, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // 6. Format Bulan Tahun (misal "January 2025" atau "Jan 2025")
        if (preg_match('/^([a-zA-Z]+)\s*[\-\/]?\s*(\d{4})$/', $cleanVal, $m)) {
            $ts = strtotime("1 " . $m[1] . " " . $m[2]);
            if ($ts !== false && $ts > 0) {
                return date('Y-m-01', $ts);
            }
        }

        // 7. Format Tahun Bulan (misal "2025 - January" atau "2025 January")
        if (preg_match('/^(\d{4})\s*[\-\/]?\s*([a-zA-Z]+)$/', $cleanVal, $m)) {
            $ts = strtotime("1 " . $m[2] . " " . $m[1]);
            if ($ts !== false && $ts > 0) {
                return date('Y-m-01', $ts);
            }
        }

        // 8. Coba strtotime langsung HANYA jika mengandung tahun 4-digit (jangan pakai tahun berjalan sebagai default)
        if (preg_match('/\b(19\d\d|20\d\d)\b/', $cleanVal)) {
            $ts = strtotime($cleanVal);
            if ($ts !== false && $ts > 0 && (int)date('Y', $ts) >= 1990 && (int)date('Y', $ts) <= 2050) {
                return date('Y-m-d', $ts);
            }
        }

        return null;
    }

    /**
     * Baca seluruh baris dataset dan petakan ke atribut standar
     */
    public function parseAndMapAll(string $filePath, array $mappings): array
    {
        $mappedRecords = [];

        if ($this->isXlsx($filePath)) {
            $parsed = $this->parseXlsx($filePath, 0);
        } elseif ($this->isXls($filePath)) {
            $parsed = $this->parseXls($filePath, 0);
        } else {
            $parsed = null;
        }

        if ($parsed !== null) {
            foreach ($parsed['rows'] as $row) {
                $record = [];
                foreach ($mappings as $colIndex => $standardAttr) {
                    if (!empty($standardAttr) && isset($row[$colIndex])) {
                        $record[$standardAttr] = trim($row[$colIndex]);
                    }
                }
                // Simpan juga baris mentah untuk referensi atribut bebas
                $record['_raw_row'] = $row;
                $mappedRecords[] = $record;
            }
            return $mappedRecords;
        }

        // CSV
        $delimiter = $this->detectDelimiter($filePath);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Gagal membuka berkas dataset.");
        }

        // Header
        $firstLine = fgets($handle);
        $headers = str_getcsv(trim(preg_replace('/^\xEF\xBB\xBF/', '', $firstLine)), $delimiter);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $record = [];
            foreach ($mappings as $colIndex => $standardAttr) {
                if (!empty($standardAttr) && isset($row[$colIndex])) {
                    $record[$standardAttr] = trim($row[$colIndex]);
                }
            }
            $record['_raw_row'] = $row;
            $mappedRecords[] = $record;
        }
        fclose($handle);

        return $mappedRecords;
    }
}
