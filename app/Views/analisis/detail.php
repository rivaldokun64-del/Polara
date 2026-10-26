<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
    $algo = strtolower($run['algoritma']);
    $algoTitle = match($algo) {
        'kmeans'     => 'K-Means Clustering',
        'c45'        => 'C4.5 Decision Tree',
        'naivebayes' => 'Naive Bayes Classifier',
        default      => strtoupper($algo)
    };
?>

<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="polara-breadcrumb">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span class="sep">/</span>
                <a href="<?= site_url('analisis') ?>">Analisis Data Mining</a>
                <span class="sep">/</span>
                <span class="current">Detail #<?= esc($run['id']) ?></span>
            </div>
            <h1 class="page-title-text">
                Hasil Analisis: <?= esc($algoTitle) ?>
                <span style="font-size:13px; font-weight:600; color:var(--muted); background:var(--navy-06); padding:2px 9px; border-radius:4px; border:1px solid var(--border); margin-left:6px;">#<?= esc($run['id']) ?></span>
            </h1>
            <p class="page-subtitle-text">
                Dataset: <strong><?= esc($run['nama_dataset'] ?? '-') ?></strong> &bull;
                Dieksekusi oleh: <strong><?= esc($run['nama_user'] ?? 'Sistem') ?></strong> (<?= esc($run['role_user'] ?? '-') ?>) &bull;
                <?= !empty($run['created_at']) ? date('d M Y, H:i', strtotime($run['created_at'])) : '-' ?> WIB
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="<?= site_url('laporan') ?>" class="btn-ghost">
                <i class="bi bi-file-earmark-bar-graph"></i> Buka Laporan
            </a>
            <a href="<?= site_url('analisis/baru') ?>" class="btn-coral">
                <i class="bi bi-play-circle"></i> Analisis Baru
            </a>
        </div>
    </div>
</div>

<!-- Evaluation Metric Cards -->
<div class="row g-3 mb-4">
    <?php foreach ($run['evaluasi'] as $ev): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon stat-icon-coral">
                    <i class="bi bi-check2-all"></i>
                </div>
                <div>
                    <div class="stat-num">
                        <?= is_numeric($ev['nilai']) ? (float)$ev['nilai'] : esc($ev['nilai']) ?>
                        <?php if (str_contains(strtolower($ev['metrik']), 'akurasi')): ?>%<?php endif; ?>
                    </div>
                    <div class="stat-label"><?= esc($ev['metrik']) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Algorithm Specific Results -->
<?php if ($algo === 'kmeans'): ?>
    <?php
        $profiles = $params['cluster_profiles'] ?? [];
        $clusterLabels = [];
        $clusterCounts = [];
        $clusterColors = ['#F04432', '#0284C7', '#102B46', '#F59E0B', '#10B981', '#8B5CF6'];

        foreach ($run['cluster_counts'] ?? [] as $cc) {
            $clusterLabels[] = 'Klaster ' . $cc['cluster'];
            $clusterCounts[] = (int) $cc['count'];
        }
    ?>

    <div class="row g-4 mb-4">
        <!-- Distribution Chart -->
        <div class="col-12 col-lg-5">
            <div class="card-polara h-100">
                <div class="card-header">
                    <h2 class="card-title">Proporsi Distribusi Klaster</h2>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                    <div style="max-width: 280px; width: 100%;">
                        <canvas id="chartClusterDist"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cluster Profiles Table -->
        <div class="col-12 col-lg-7">
            <div class="card-polara h-100">
                <div class="card-header">
                    <h2 class="card-title">Karakteristik &amp; Centroid Klaster</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-3">Klaster</th>
                                    <th>Jumlah Anggota</th>
                                    <th>Rerata Usia</th>
                                    <th>Diagnosis Dominan</th>
                                    <th>Unit Pelayanan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($profiles)): ?>
                                    <?php foreach ($profiles as $cp): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-navy">
                                                <span class="badge rounded-circle p-1 me-1" style="background: <?= $clusterColors[($cp['cluster_index'] - 1) % count($clusterColors)] ?>;">&nbsp;</span>
                                                Klaster <?= esc($cp['cluster_index']) ?>
                                            </td>
                                            <td>
                                                <strong><?= number_format($cp['member_count']) ?></strong>
                                                <span class="text-muted small">(<?= esc($cp['percentage']) ?>%)</span>
                                            </td>
                                            <td><?= esc($cp['avg_age'] ?? $cp['centroid']['usia'] ?? '-') ?> thn</td>
                                            <td><?= esc($cp['top_diagnosis'] ?? '-') ?></td>
                                            <td><?= esc($cp['top_unit'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($run['cluster_counts'] ?? [] as $cc): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-navy">Klaster <?= esc($cc['cluster']) ?></td>
                                            <td><strong><?= number_format($cc['count']) ?></strong> data</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Members -->
    <div class="card-polara">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="card-title">Sampel Rekam Medis &amp; Hasil Penugasan Klaster (Top 25)</h2>
            <span class="badge bg-light text-muted border small">Database Table: hasil_cluster</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID Rekam</th>
                            <th>Klaster</th>
                            <th>Usia</th>
                            <th>Jenis Kelamin</th>
                            <th>Kode Diagnosis</th>
                            <th>Nama Diagnosis</th>
                            <th>Unit Pelayanan</th>
                            <th>Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($run['sample_members'] ?? [] as $sm): ?>
                            <tr>
                                <td class="ps-4 font-monospace small">#<?= esc($sm['id'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-primary px-3 py-1 rounded-pill">
                                        Klaster <?= esc($sm['cluster']) ?>
                                    </span>
                                </td>
                                <td><?= esc($sm['usia'] ?? '-') ?> thn</td>
                                <td><?= esc($sm['jenis_kelamin'] ?? '-') ?></td>
                                <td><code><?= esc($sm['kode_diagnosis'] ?? '-') ?></code></td>
                                <td><?= esc($sm['nama_diagnosis'] ?? '-') ?></td>
                                <td><?= esc($sm['unit_pelayanan'] ?? '-') ?></td>
                                <td><?= esc($sm['jumlah_kunjungan'] ?? 1) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($algo === 'c45'): ?>
    <?php
        $evalDetail = null;
        foreach ($run['evaluasi'] as $ev) {
            if (str_contains(strtolower($ev['metrik']), 'akurasi') && !empty($ev['detail'])) {
                $evalDetail = json_decode($ev['detail'], true);
                break;
            }
        }
        $matrix = $evalDetail['confusion_matrix'] ?? [];
        $classes = $evalDetail['classes'] ?? [];
    ?>

    <!-- C4.5 Rules Section -->
    <div class="card-polara mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="card-title">Aturan Keputusan IF-THEN (Ditemukan: <?= count($run['rules'] ?? []) ?> Aturan)</h2>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">C4.5 Decision Rules</span>
        </div>
        <div class="card-body p-4">
            <?php if (empty($run['rules'])): ?>
                <div class="text-muted small">Tidak ada aturan yang diekstraksi.</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($run['rules'] as $idx => $rule): ?>
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-start justify-content-between gap-3">
                            <div style="min-width: 0;">
                                <div class="badge bg-dark-subtle text-dark border small mb-1">Aturan #<?= $idx + 1 ?></div>
                                <div class="font-monospace small text-navy fw-semibold" style="word-break: break-all;">
                                    <?= esc($rule['aturan']) ?>
                                </div>
                                <div class="mt-1 small">
                                    <span class="text-muted">Maka Target:</span>
                                    <strong class="text-primary"><?= esc($rule['kelas']) ?></strong>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    Support: <?= number_format($rule['support']) ?> kasus
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Confusion Matrix -->
    <?php if (!empty($matrix) && !empty($classes)): ?>
        <div class="card-polara mb-4">
            <div class="card-header">
                <h2 class="card-title">Matriks Kebingungan (Confusion Matrix)</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="align-middle bg-light text-muted small text-uppercase">Kelas Aktual \ Prediksi</th>
                                <th colspan="<?= count($classes) ?>" class="text-uppercase small text-muted">Kelas Prediksi</th>
                            </tr>
                            <tr>
                                <?php foreach ($classes as $cls): ?>
                                    <th class="small fw-semibold"><?= esc($cls) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $act): ?>
                                <tr>
                                    <th class="table-light text-start ps-3 small fw-semibold"><?= esc($act) ?></th>
                                    <?php foreach ($classes as $prd): ?>
                                        <?php
                                            $val = $matrix[$act][$prd] ?? 0;
                                            $isDiagonal = ($act === $prd);
                                        ?>
                                        <td class="<?= $isDiagonal ? 'bg-success-subtle text-success fw-bold' : ($val > 0 ? 'bg-danger-subtle text-danger' : 'text-muted') ?>">
                                            <?= $val ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sample Predictions -->
    <div class="card-polara">
        <div class="card-header">
            <h2 class="card-title">Sampel Hasil Klasifikasi Rekam Medis (Top 25)</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID Kunjungan</th>
                            <th>Usia</th>
                            <th>Diagnosis</th>
                            <th>Unit Pelayanan</th>
                            <th>Kelas Aktual</th>
                            <th>Kelas Prediksi</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($run['sample_predictions'] ?? [] as $sp): ?>
                            <?php $isMatch = ($sp['kelas_aktual'] === $sp['kelas_prediksi']); ?>
                            <tr>
                                <td class="ps-4 font-monospace small">#<?= esc($sp['id_kunjungan']) ?></td>
                                <td><?= esc($sp['usia'] ?? '-') ?> thn</td>
                                <td><?= esc($sp['nama_diagnosis'] ?? '-') ?></td>
                                <td><?= esc($sp['unit_pelayanan'] ?? '-') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($sp['kelas_aktual']) ?></span></td>
                                <td><span class="badge bg-light text-primary border border-primary-subtle"><?= esc($sp['kelas_prediksi']) ?></span></td>
                                <td class="text-center">
                                    <?php if ($isMatch): ?>
                                        <i class="ri-checkbox-circle-fill text-success fs-5" title="Sesuai"></i>
                                    <?php else: ?>
                                        <i class="ri-close-circle-fill text-danger fs-5" title="Meleset"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($algo === 'naivebayes'): ?>
    <?php
        $evalDetail = null;
        foreach ($run['evaluasi'] as $ev) {
            if (str_contains(strtolower($ev['metrik']), 'akurasi') && !empty($ev['detail'])) {
                $evalDetail = json_decode($ev['detail'], true);
                break;
            }
        }
        $matrix = $evalDetail['confusion_matrix'] ?? [];
        $classes = $evalDetail['classes'] ?? [];
        $priors = $evalDetail['priors'] ?? ($params['priors'] ?? []);
    ?>

    <!-- Priors Probability Breakdown -->
    <div class="card-polara mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="card-title">Prior Probabilities: P(Kelas)</h2>
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Teorema Bayes</span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <?php foreach ($priors as $cls => $prob): ?>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small text-uppercase">P(<?= esc($cls) ?>)</div>
                            <div class="fs-4 fw-bold text-navy"><?= round($prob * 100, 2) ?>%</div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= round($prob * 100, 2) ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Confusion Matrix -->
    <?php if (!empty($matrix) && !empty($classes)): ?>
        <div class="card-polara mb-4">
            <div class="card-header">
                <h2 class="card-title">Matriks Kebingungan (Confusion Matrix)</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="align-middle bg-light text-muted small text-uppercase">Kelas Aktual \ Prediksi</th>
                                <th colspan="<?= count($classes) ?>" class="text-uppercase small text-muted">Kelas Prediksi</th>
                            </tr>
                            <tr>
                                <?php foreach ($classes as $cls): ?>
                                    <th class="small fw-semibold"><?= esc($cls) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $act): ?>
                                <tr>
                                    <th class="table-light text-start ps-3 small fw-semibold"><?= esc($act) ?></th>
                                    <?php foreach ($classes as $prd): ?>
                                        <?php
                                            $val = $matrix[$act][$prd] ?? 0;
                                            $isDiagonal = ($act === $prd);
                                        ?>
                                        <td class="<?= $isDiagonal ? 'bg-success-subtle text-success fw-bold' : ($val > 0 ? 'bg-danger-subtle text-danger' : 'text-muted') ?>">
                                            <?= $val ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sample Predictions -->
    <div class="card-polara">
        <div class="card-header">
            <h2 class="card-title">Sampel Prediksi Probabilistik (Top 25)</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID Rekam</th>
                            <th>Usia</th>
                            <th>Diagnosis</th>
                            <th>Unit Pelayanan</th>
                            <th>Kelas Aktual</th>
                            <th>Kelas Prediksi</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($run['sample_predictions'] ?? [] as $sp): ?>
                            <?php $isMatch = ($sp['kelas_aktual'] === $sp['kelas_prediksi']); ?>
                            <tr>
                                <td class="ps-4 font-monospace small">#<?= esc($sp['id_kunjungan']) ?></td>
                                <td><?= esc($sp['usia'] ?? '-') ?> thn</td>
                                <td><?= esc($sp['nama_diagnosis'] ?? '-') ?></td>
                                <td><?= esc($sp['unit_pelayanan'] ?? '-') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($sp['kelas_aktual']) ?></span></td>
                                <td><span class="badge bg-light text-warning-emphasis border border-warning-subtle"><?= esc($sp['kelas_prediksi']) ?></span></td>
                                <td class="text-center">
                                    <?php if ($isMatch): ?>
                                        <i class="ri-checkbox-circle-fill text-success fs-5" title="Sesuai"></i>
                                    <?php else: ?>
                                        <i class="ri-close-circle-fill text-danger fs-5" title="Meleset"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($algo === 'kmeans' && !empty($clusterLabels)): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('chartClusterDist');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($clusterLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($clusterCounts) ?>,
                        backgroundColor: <?= json_encode(array_slice($clusterColors, 0, count($clusterLabels))) ?>,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
<?php endif; ?>
<?= $this->endSection() ?>
