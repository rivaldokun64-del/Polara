<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
    @media print {
        #sidebar, #topbar, .btn-no-print, footer {
            display: none !important;
        }
        #main-wrap { margin-left: 0 !important; }
        .card-p { box-shadow: none !important; break-inside: avoid; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="polara-breadcrumb">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Laporan</span>
            </div>
            <h1 class="page-title-text">Laporan Analitik Rekam Medis</h1>
            <p class="page-subtitle-text">Ringkasan eksekutif kunjungan klinis, tren 10 diagnosis terbanyak, dan pemodelan analitik data.</p>
        </div>
        <div class="d-flex align-items-center gap-2 btn-no-print">
            <?php if (!empty($activeDataset)): ?>
                <a href="<?= site_url('laporan/export-csv/' . $activeDataset['id']) ?>" class="btn-ghost">
                    <i class="bi bi-file-earmark-spreadsheet" style="color:#166534;"></i>
                    Unduh CSV
                </a>
            <?php endif; ?>
            <button type="button" class="btn-coral" onclick="window.print();">
                <i class="bi bi-printer"></i>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>
</div>

<!-- Dataset Filter Bar -->
<div class="card-p mb-4 btn-no-print">
    <div class="card-p-body">
        <form action="<?= site_url('laporan') ?>" method="get" class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="id_dataset" class="form-label mb-0">Pilih Dataset Rekam Medis:</label>
            </div>
            <div class="col-12 col-sm-6 col-md-5">
                <select name="id_dataset" id="id_dataset" class="form-select" onchange="this.form.submit();">
                    <?php if (empty($datasets)): ?>
                        <option value="">-- Tidak ada dataset siap --</option>
                    <?php else: ?>
                        <?php foreach ($datasets as $ds): ?>
                            <option value="<?= esc($ds['id']) ?>" <?= ($selectedDatasetId == $ds['id']) ? 'selected' : '' ?>>
                                <?= esc($ds['nama_dataset']) ?> (<?= number_format($ds['jumlah_baris']) ?> rekam &bull; <?= esc($ds['format_file'] ?? 'CSV') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn-ghost" style="padding:7px 14px; font-size:13px;">
                    <i class="bi bi-arrow-clockwise"></i> Muat Ulang
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($activeDataset) || $summary['total_kunjungan'] === 0): ?>
    <div class="card-p">
        <div class="empty-state">
            <div class="empty-state-icon">
                <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Polara Mascot">
            </div>
            <h5>Tidak Ada Data Kunjungan</h5>
            <p>Dataset yang dipilih belum memiliki baris kunjungan atau belum diimpor ke sistem.</p>
            <a href="<?= site_url('datasets') ?>" class="btn-coral mt-3">
                <i class="bi bi-database"></i> Buka Manajemen Dataset
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Print Report Header -->
    <div class="d-none d-print-block mb-4 p-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Polara" width="48" height="48">
            <div>
                <h3 class="fw-bold mb-0 text-navy">POLARA — Laporan Analitik Rekam Medis</h3>
                <div class="text-muted small">Dataset: <?= esc($activeDataset['nama_dataset']) ?> &bull; Tanggal Cetak: <?= date('d M Y, H:i') ?> WIB</div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-coral">
                    <i class="bi bi-person-heart"></i>
                </div>
                <div>
                    <div class="stat-num"><?= number_format($summary['total_kunjungan']) ?></div>
                    <div class="stat-label">Total Rekam Kunjungan</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <i class="bi bi-gender-ambiguous"></i>
                </div>
                <div>
                    <?php
                        $lk = 0; $pr = 0;
                        foreach ($summary['gender_dist'] as $g) {
                            if ($g['gender'] === 'L') $lk = $g['count'];
                            if ($g['gender'] === 'P') $pr = $g['count'];
                        }
                    ?>
                    <div class="stat-num"><?= number_format($lk) ?> / <?= number_format($pr) ?></div>
                    <div class="stat-label">Pasien L / P</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-navy">
                    <i class="bi bi-hospital"></i>
                </div>
                <div>
                    <div class="stat-num"><?= count($summary['service_unit_dist']) ?></div>
                    <div class="stat-label">Unit Pelayanan Aktif</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-cream">
                    <i class="bi bi-cpu"></i>
                </div>
                <div>
                    <div class="stat-num"><?= count($analysisRuns) ?></div>
                    <div class="stat-label">Model Analisis Terhubung</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Diagnoses Table -->
    <div class="card-p mb-4">
        <div class="card-p-header">
            <h2 class="card-p-title" style="font-size:14.5px;"><i class="bi bi-clipboard2-pulse"></i> 10 Besar Diagnosis Terbanyak (ICD-10)</h2>
            <span style="font-size:12px; color:var(--muted);">Rekapitulasi Klinis</span>
        </div>
        <div class="card-p-body-flush">
            <div class="table-responsive">
                <table class="table-polara">
                    <thead>
                        <tr>
                            <th style="padding-left:20px; width:70px;">Peringkat</th>
                            <th>Kode ICD-10</th>
                            <th>Nama Diagnosis</th>
                            <th>Jumlah Kasus</th>
                            <th style="width:220px;">Proporsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summary['top_diagnoses'])): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    Tidak ada data diagnosis yang terdaftar pada dataset ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($summary['top_diagnoses'] as $rank => $diag): ?>
                                <?php
                                    $pct = $summary['total_kunjungan'] > 0 ? round(($diag['total'] / $summary['total_kunjungan']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td style="padding-left:20px;">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-size:11.5px;font-weight:700;background:<?= ($rank < 3) ? 'var(--coral-light)' : 'var(--navy-06)' ?>;color:<?= ($rank < 3) ? 'var(--coral)' : 'var(--muted)' ?>;">
                                            <?= $rank + 1 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code style="font-size:12px; background:var(--navy-06); padding:2px 7px; border-radius:4px; color:var(--navy);"><?= esc($diag['kode_diagnosis'] ?: 'N/A') ?></code>
                                    </td>
                                    <td style="font-weight:600; color:var(--navy);">
                                        <?= esc($diag['nama_diagnosis']) ?>
                                    </td>
                                    <td>
                                        <strong><?= number_format($diag['total']) ?></strong> <span style="font-size:12px;color:var(--muted);">rekam</span>
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="flex:1;height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                                                <div style="width:<?= $pct ?>%;height:100%;background:var(--coral);border-radius:3px;"></div>
                                            </div>
                                            <span style="font-size:12px;color:var(--muted);min-width:38px;"><?= $pct ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Charts Row: Age Group & Service Unit -->
    <div class="row g-4 mb-4">
        <!-- Age Group Breakdown -->
        <div class="col-12 col-lg-6">
            <div class="card-p h-100">
                <div class="card-p-header">
                    <h2 class="card-p-title" style="font-size:14px;"><i class="bi bi-pie-chart"></i> Distribusi Kelompok Usia Pasien</h2>
                </div>
                <div class="card-p-body" style="display:flex;align-items:center;justify-content:center;">
                    <div style="max-width:260px;width:100%;">
                        <canvas id="chartAgeGroup"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Unit Breakdown -->
        <div class="col-12 col-lg-6">
            <div class="card-p h-100">
                <div class="card-p-header">
                    <h2 class="card-p-title" style="font-size:14px;"><i class="bi bi-hospital"></i> Kunjungan per Unit Pelayanan</h2>
                </div>
                <div class="card-p-body">
                    <canvas id="chartServiceUnit" style="max-height:260px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Associated Analysis Runs -->
    <div class="card-p mb-4">
        <div class="card-p-header">
            <h2 class="card-p-title" style="font-size:14.5px;"><i class="bi bi-cpu"></i> Riwayat Pemodelan Data Mining pada Dataset Ini</h2>
            <span style="font-size:12.5px; color:var(--muted);"><?= count($analysisRuns) ?> eksekusi</span>
        </div>
        <?php if (empty($analysisRuns)): ?>
            <div class="empty-state" style="padding:36px 24px;">
                <div style="font-size:13px; color:var(--muted); margin-bottom:12px;">Belum ada pemodelan data mining yang dijalankan pada dataset ini.</div>
                <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
                    <a href="<?= site_url('analisis/baru') ?>" class="btn-coral" style="font-size:13px;">
                        <i class="bi bi-play-circle"></i> Jalankan Sekarang
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-polara">
                    <thead>
                        <tr>
                            <th style="padding-left:20px;">ID Analisis</th>
                            <th>Algoritma</th>
                            <th>Atribut Target</th>
                            <th>Pengguna</th>
                            <th>Tanggal</th>
                            <th style="text-align:right; padding-right:20px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analysisRuns as $ar): ?>
                            <tr>
                                <td style="padding-left:20px; font-weight:700; color:var(--navy);">#<?= esc($ar['id']) ?></td>
                                <td>
                                    <span style="background:var(--navy-06);border:1px solid var(--border);border-radius:4px;font-size:11.5px;font-weight:700;text-transform:uppercase;padding:2px 9px;color:var(--navy);">
                                        <?= strtoupper(esc($ar['algoritma'])) ?>
                                    </span>
                                </td>
                                <td style="font-size:13px; color:var(--muted);"><?= esc($ar['atribut_target'] ?? '— (Unsupervised)') ?></td>
                                <td style="font-size:13px;"><?= esc($ar['nama_user'] ?? 'Sistem') ?></td>
                                <td style="font-size:12.5px; color:var(--muted);"><?= !empty($ar['created_at']) ? date('d M Y', strtotime($ar['created_at'])) : '-' ?></td>
                                <td style="text-align:right; padding-right:16px;">
                                    <a href="<?= site_url('analisis/detail/' . $ar['id']) ?>" class="btn-outline-coral" style="padding:5px 14px; font-size:12.5px;">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if (!empty($activeDataset) && $summary['total_kunjungan'] > 0): ?>
    <?php
        $ageLabels = [];
        $ageData   = [];
        foreach ($summary['age_group_dist'] as $ag) {
            $ageLabels[] = $ag['age_group'];
            $ageData[]   = (int) $ag['count'];
        }

        $unitLabels = [];
        $unitData   = [];
        foreach ($summary['service_unit_dist'] as $su) {
            $unitLabels[] = $su['unit'];
            $unitData[]   = (int) $su['count'];
        }
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Chart Kelompok Usia
            const ctxAge = document.getElementById('chartAgeGroup');
            if (ctxAge) {
                new Chart(ctxAge, {
                    type: 'doughnut',
                    data: {
                        labels: <?= json_encode($ageLabels) ?>,
                        datasets: [{
                            data: <?= json_encode($ageData) ?>,
                            backgroundColor: ['#F04432', '#F9B097', '#102B46', '#B9DDF4'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            // Chart Unit Pelayanan
            const ctxUnit = document.getElementById('chartServiceUnit');
            if (ctxUnit) {
                new Chart(ctxUnit, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($unitLabels) ?>,
                        datasets: [{
                            label: 'Jumlah Kunjungan',
                            data: <?= json_encode($unitData) ?>,
                            backgroundColor: 'rgba(240, 68, 50, 0.85)',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        });
    </script>
<?php endif; ?>
<?= $this->endSection() ?>
