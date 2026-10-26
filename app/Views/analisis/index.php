<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="polara-breadcrumb">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Riwayat Analisis</span>
            </div>
            <h1 class="page-title-text">Riwayat Analisis Data Mining</h1>
            <p class="page-subtitle-text">Daftar seluruh pemodelan K-Means, C4.5, dan Naive Bayes yang telah dieksekusi.</p>
        </div>
        <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
        <a href="<?= site_url('analisis/baru') ?>" class="btn-coral">
            <i class="bi bi-play-circle"></i> Jalankan Analisis Baru
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-coral"><i class="bi bi-cpu"></i></div>
            <div>
                <div class="stat-num"><?= number_format($counts['total']) ?></div>
                <div class="stat-label">Total Pemodelan</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="bi bi-diagram-3"></i></div>
            <div>
                <div class="stat-num"><?= number_format($counts['kmeans']) ?></div>
                <div class="stat-label">K-Means Clustering</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-navy"><i class="bi bi-git"></i></div>
            <div>
                <div class="stat-num"><?= number_format($counts['c45']) ?></div>
                <div class="stat-label">Pohon C4.5</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-cream"><i class="bi bi-bar-chart-steps"></i></div>
            <div>
                <div class="stat-num"><?= number_format($counts['naivebayes']) ?></div>
                <div class="stat-label">Naive Bayes</div>
            </div>
        </div>
    </div>
</div>

<!-- History Table -->
<div class="card-p">
    <div class="card-p-header">
        <h2 class="card-p-title" style="font-size:14.5px;">
            <i class="bi bi-clock-history"></i> Daftar Eksekusi Algoritma
        </h2>
        <span style="font-size:12.5px; color:var(--muted);"><?= count($history) ?> riwayat terdata</span>
    </div>

    <?php if (empty($history)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Polara Mascot">
        </div>
        <h5>Belum Ada Analisis yang Dijalankan</h5>
        <p>Mulai lakukan klasterisasi data kunjungan atau klasifikasi pola diagnosis rekam medis menggunakan data yang telah diunggah.</p>
        <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
        <a href="<?= site_url('analisis/baru') ?>" class="btn-coral mt-3">
            <i class="bi bi-play-circle"></i> Mulai Analisis Sekarang
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table-polara">
            <thead>
                <tr>
                    <th style="padding-left:20px;">ID &amp; Tanggal</th>
                    <th>Algoritma</th>
                    <th>Dataset</th>
                    <th>Atribut Target</th>
                    <th>Pengguna</th>
                    <th style="text-align:right; padding-right:20px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <?php
                    $algo = strtolower($h['algoritma']);
                    $algoClass = match($algo) {
                        'kmeans'     => 'algo-kmeans',
                        'c45'        => 'algo-c45',
                        'naivebayes' => 'algo-naivebayes',
                        default      => 'algo-kmeans'
                    };
                    $algoName = match($algo) {
                        'kmeans'     => 'K-Means Clustering',
                        'c45'        => 'C4.5 Decision Tree',
                        'naivebayes' => 'Naive Bayes',
                        default      => strtoupper($algo)
                    };
                ?>
                <tr>
                    <td style="padding-left:20px;">
                        <div style="font-weight:700; color:var(--navy); font-size:13px;">#<?= esc($h['id']) ?></div>
                        <div style="font-size:12px; color:var(--muted);">
                            <?= !empty($h['created_at']) ? date('d M Y, H:i', strtotime($h['created_at'])) : '-' ?>
                        </div>
                    </td>
                    <td>
                        <span class="algo-badge <?= $algoClass ?>"><?= esc($algoName) ?></span>
                    </td>
                    <td>
                        <div style="font-weight:600; color:var(--navy); font-size:13px;"><?= esc($h['nama_dataset'] ?? 'Dataset Terhapus') ?></div>
                        <div style="font-size:11.5px; color:var(--muted);">ID Dataset: <?= esc($h['id_dataset']) ?></div>
                    </td>
                    <td>
                        <?php if (!empty($h['atribut_target'])): ?>
                        <span style="background:var(--navy-06); border:1px solid var(--border); border-radius:4px; font-size:12px; font-weight:600; padding:2px 9px; color:var(--navy);"><?= esc($h['atribut_target']) ?></span>
                        <?php else: ?>
                        <span style="font-size:12px; color:var(--muted);">— Unsupervised</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:13px; font-weight:600; color:var(--navy);"><?= esc($h['nama_user'] ?? 'Sistem') ?></div>
                        <span class="badge-role badge-role-<?= esc($h['role_user'] ?? 'analis') ?>"><?= esc($h['role_user'] ?? 'analis') ?></span>
                    </td>
                    <td style="text-align:right; padding-right:16px;">
                        <div style="display:inline-flex; align-items:center; gap:6px;">
                            <a href="<?= site_url('analisis/detail/' . $h['id']) ?>"
                               class="btn-outline-coral"
                               style="padding:6px 14px; font-size:12.5px;"
                               title="Lihat Hasil Lengkap">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
                            <button type="button"
                                class="btn-ghost"
                                style="padding:6px 10px; font-size:12.5px; color:#DC2626; border-color:#FECACA;"
                                title="Hapus Riwayat Analisis"
                                onclick="confirmDeleteAnalisis(
                                    '<?= site_url('analisis/delete/' . $h['id']) ?>',
                                    <?= (int)$h['id'] ?>,
                                    '<?= esc($h['nama_dataset'] ?? 'Dataset Terhapus', 'js') ?>'
                                )">
                                <i class="bi bi-trash3"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.algo-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.algo-kmeans     { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }
.algo-c45        { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
.algo-naivebayes { background: #FAF5FF; color: #7C3AED; border: 1px solid #DDD6FE; }
</style>
<script>
window.confirmDeleteAnalisis = function(url, id, datasetName) {
    confirmDelete(
        url,
        'Hapus riwayat analisis #' + id + '?',
        'Analisis dari dataset "' + datasetName + '" beserta seluruh hasil evaluasi, data klaster, dan aturan terkait akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.'
    );
};
</script>
<?= $this->endSection() ?>
