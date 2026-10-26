<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="polara-breadcrumb">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span class="sep">/</span>
                <a href="<?= site_url('analisis') ?>">Analisis Data Mining</a>
                <span class="sep">/</span>
                <span class="current">Konfigurasi Baru</span>
            </div>
            <h1 class="page-title-text">Jalankan Analisis Data Mining</h1>
            <p class="page-subtitle-text">Konfigurasikan algoritma data mining (K-Means, C4.5, atau Naive Bayes) pada dataset aktif.</p>
        </div>
    </div>
</div>

<?php if (empty($datasets)): ?>
    <div class="card-p">
        <div class="empty-state">
            <div class="empty-state-icon">
                <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Polara Mascot">
            </div>
            <h5>Belum Ada Dataset yang Siap</h5>
            <p>Untuk menjalankan analisis, Anda perlu mengunggah berkas dataset dan mengonfirmasi pemetaan kolom terlebih dahulu.</p>
            <a href="<?= site_url('datasets') ?>" class="btn-coral mt-3">
                <i class="bi bi-cloud-upload"></i> Buka Manajemen Dataset
            </a>
        </div>
    </div>
<?php else: ?>
    <form action="<?= site_url('analisis/run') ?>" method="post" id="formRunAnalisis">
        <?= csrf_field() ?>

        <div class="row g-4">
            <!-- Left Column: Dataset & Algorithm Selection -->
            <div class="col-12 col-lg-7">
                <!-- Step 1: Dataset Selection -->
                <div class="card-p mb-4">
                    <div class="card-p-header d-flex align-items-center justify-content-between">
                        <h2 class="card-p-title m-0">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--coral);color:#fff;font-size:12px;font-weight:700;">1</span>
                            Pilih Dataset Rekam Medis
                        </h2>
                        <a href="<?= site_url('datasets') ?>" class="btn btn-sm text-decoration-none" style="font-size:12px;font-weight:600;color:var(--coral);background:var(--coral-light);border-radius:6px;padding:4px 10px;">
                            <i class="bi bi-database me-1"></i> Kelola Dataset
                        </a>
                    </div>
                    <div class="card-p-body">
                        <div class="mb-3">
                            <label for="id_dataset" class="form-label fw-semibold" style="font-size:13px;color:var(--navy);">Pilih Sumber Data Aktif:</label>
                            <select class="form-select form-select-md" name="id_dataset" id="id_dataset" required style="border-color:var(--border);font-weight:500;">
                                <?php foreach ($datasets as $ds): ?>
                                    <option value="<?= esc($ds['id']) ?>" 
                                            data-rows="<?= number_format($ds['jumlah_baris']) ?>" 
                                            data-format="<?= esc(strtoupper($ds['format_file'] ?? 'XLS')) ?>"
                                            data-name="<?= esc($ds['nama_dataset']) ?>"
                                            <?= (isset($activeDataset['id']) && $activeDataset['id'] == $ds['id']) ? 'selected' : '' ?>>
                                        <?= esc($ds['nama_dataset']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Ringkasan Dataset Aktif (Ringkas & Informatif) -->
                        <div id="datasetSummaryBox" class="p-3" style="background:#F9FBFC;border:1px solid #E5E9EE;border-radius:10px;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div style="width:34px;height:34px;border-radius:8px;background:var(--coral-light);color:var(--coral);display:flex;align-items:center;justify-content:center;font-size:16px;">
                                        <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                                    </div>
                                    <div>
                                        <div id="summaryDsName" style="font-size:13px;font-weight:700;color:var(--navy);line-height:1.2;">
                                            <?= esc($activeDataset['nama_dataset'] ?? ($datasets[0]['nama_dataset'] ?? 'Dataset Rekam Medis')) ?>
                                        </div>
                                        <div style="font-size:11.5px;color:var(--muted);margin-top:2px;">
                                            Database Polara &bull; Status Siap Analisis
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" id="summaryDsFormat" style="background:#EBF3FB;color:var(--blue-mid);font-size:11px;font-weight:700;padding:5px 9px;border-radius:6px;border:1px solid #D2E4F6;">
                                        <?= esc(strtoupper($activeDataset['format_file'] ?? ($datasets[0]['format_file'] ?? 'XLS'))) ?>
                                    </span>
                                    <span class="badge" id="summaryDsRows" style="background:#F0FDF4;color:#16A34A;font-size:11px;font-weight:700;padding:5px 9px;border-radius:6px;border:1px solid #BBF7D0;">
                                        <i class="bi bi-check2 me-0.5"></i> <?= number_format($activeDataset['jumlah_baris'] ?? ($datasets[0]['jumlah_baris'] ?? 0)) ?> baris
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Algorithm Selection -->
                <div class="card-p mb-4">
                    <div class="card-p-header">
                        <h2 class="card-p-title">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--coral);color:#fff;font-size:12px;font-weight:700;">2</span>
                            Pilih Algoritma Data Mining
                        </h2>
                    </div>
                    <div class="card-p-body">
                        <div class="row g-3">
                            <!-- K-Means Card -->
                            <div class="col-12">
                                <label class="algo-card" for="algo_kmeans" style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:var(--radius-md);cursor:pointer;transition:border-color .15s,background .15s;">
                                    <input class="form-check-input mt-1" type="radio" name="algoritma" id="algo_kmeans" value="kmeans" checked>
                                    <div>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="font-weight:700;color:var(--navy);">K-Means Clustering</span>
                                            <span class="algo-badge algo-kmeans">Unsupervised</span>
                                        </div>
                                        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;">Segmentasi pola kunjungan rekam medis ke dalam <em>k</em> kelompok berdasar kemiripan karakteristik usia dan frekuensi kunjungan.</div>
                                    </div>
                                </label>
                            </div>

                            <!-- C4.5 Card -->
                            <div class="col-12">
                                <label class="algo-card" for="algo_c45" style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:var(--radius-md);cursor:pointer;transition:border-color .15s,background .15s;">
                                    <input class="form-check-input mt-1" type="radio" name="algoritma" id="algo_c45" value="c45">
                                    <div>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="font-weight:700;color:var(--navy);">C4.5 Decision Tree</span>
                                            <span class="algo-badge algo-c45">Supervised</span>
                                        </div>
                                        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;">Pembentukan pohon keputusan dan aturan IF-THEN menggunakan Shannon Entropy &amp; Gain Ratio untuk klasifikasi target klinis.</div>
                                    </div>
                                </label>
                            </div>

                            <!-- Naive Bayes Card -->
                            <div class="col-12">
                                <label class="algo-card" for="algo_naivebayes" style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:var(--radius-md);cursor:pointer;transition:border-color .15s,background .15s;">
                                    <input class="form-check-input mt-1" type="radio" name="algoritma" id="algo_naivebayes" value="naivebayes">
                                    <div>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="font-weight:700;color:var(--navy);">Naive Bayes Classifier</span>
                                            <span class="algo-badge algo-naivebayes">Supervised</span>
                                        </div>
                                        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;">Klasifikasi berbasis probabilitas Teorema Bayes dengan Laplace smoothing untuk fitur kategorikal dan Gaussian density untuk numerik.</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Algorithm Parameters & Attributes -->
            <div class="col-12 col-lg-5">
                <div class="card-p mb-4">
                    <div class="card-p-header">
                        <h2 class="card-p-title">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:var(--coral);color:#fff;font-size:12px;font-weight:700;">3</span>
                            Konfigurasi Parameter
                        </h2>
                    </div>
                    <div class="card-p-body">
                        <!-- K-Means Parameters -->
                        <div id="kmeans_params" class="algo-section">
                            <div class="mb-3">
                                <label for="k_clusters" class="form-label">Jumlah Klaster (K):</label>
                                <input type="number" name="k" id="k_clusters" class="form-control" value="3" min="2" max="10" required>
                                <div class="form-text small">Rekomendasi nilai K: 3 s/d 5 klaster.</div>
                            </div>

                            <div class="mb-3">
                                <label for="max_iter" class="form-label">Maksimum Iterasi:</label>
                                <input type="number" name="max_iter" id="max_iter" class="form-control" value="100" min="10" max="300">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Atribut Fitur Klasterisasi:</label>
                                <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px;display:flex;flex-direction:column;gap:8px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="usia" id="feat_usia" checked>
                                        <label class="form-check-label small" for="feat_usia"><strong>Usia Pasien</strong> (Numerik)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="jumlah_kunjungan" id="feat_kunjungan" checked>
                                        <label class="form-check-label small" for="feat_kunjungan"><strong>Jumlah Kunjungan</strong> (Numerik)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- C4.5 & Naive Bayes Parameters -->
                        <div id="supervised_params" class="algo-section" style="display: none;">
                            <div class="mb-3">
                                <label for="target_attr" class="form-label">Atribut Target (Kelas Klasifikasi):</label>
                                <select name="target" id="target_attr" class="form-select">
                                    <option value="unit_pelayanan" selected>Unit Pelayanan (Poli Umum, KIA/KB, Gigi, IGD, dll)</option>
                                    <option value="kelompok_usia">Kelompok Usia (Anak, Dewasa, Lansia)</option>
                                    <option value="jenis_kelamin">Jenis Kelamin (L, P)</option>
                                    <option value="sumber">Target Asal / Klasifikasi Sumber</option>
                                    <option value="kode_diagnosis">Kode Diagnosis ICD-10</option>
                                </select>
                                <div class="form-text small">Atribut ini akan diprediksi oleh pohon keputusan atau model Naive Bayes.</div>
                            </div>

                            <div id="c45_depth_box" class="mb-3">
                                <label for="max_depth" class="form-label">Batas Kedalaman Pohon (Max Depth):</label>
                                <input type="number" name="max_depth" id="max_depth" class="form-control" value="5" min="2" max="10">
                                <div class="form-text small">Mencegah overfitting pada pohon keputusan C4.5.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Atribut Fitur Prediktor:</label>
                                <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px;display:flex;flex-direction:column;gap:8px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="kelompok_usia" id="feat_pred_kelompok_usia" checked>
                                        <label class="form-check-label small" for="feat_pred_kelompok_usia">Kelompok Usia (Kategorikal)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="jenis_kelamin" id="feat_pred_jk" checked>
                                        <label class="form-check-label small" for="feat_pred_jk">Jenis Kelamin (Kategorikal)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="unit_pelayanan" id="feat_pred_unit">
                                        <label class="form-check-label small" for="feat_pred_unit">Unit Pelayanan (Kategorikal)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="usia" id="feat_pred_usia" checked>
                                        <label class="form-check-label small" for="feat_pred_usia">Usia Pasien (Numerik)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="jumlah_kunjungan" id="feat_pred_kunjungan">
                                        <label class="form-check-label small" for="feat_pred_kunjungan">Jumlah Kunjungan (Numerik)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Submit Button -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn-coral justify-content-center py-2" id="btnSubmitAnalisis">
                                <i class="bi bi-play-circle-fill"></i>
                                <span>Mulai Eksekusi Pemodelan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const algoRadios = document.querySelectorAll('input[name="algoritma"]');
        const kmeansParams = document.getElementById('kmeans_params');
        const supervisedParams = document.getElementById('supervised_params');
        const c45DepthBox = document.getElementById('c45_depth_box');
        const targetAttrSelect = document.getElementById('target_attr');

        function updateFormState() {
            const selected = document.querySelector('input[name="algoritma"]:checked')?.value;
            if (selected === 'kmeans') {
                if (kmeansParams) kmeansParams.style.display = 'block';
                if (supervisedParams) supervisedParams.style.display = 'none';
            } else if (selected === 'c45') {
                if (kmeansParams) kmeansParams.style.display = 'none';
                if (supervisedParams) supervisedParams.style.display = 'block';
                if (c45DepthBox) c45DepthBox.style.display = 'block';
            } else if (selected === 'naivebayes') {
                if (kmeansParams) kmeansParams.style.display = 'none';
                if (supervisedParams) supervisedParams.style.display = 'block';
                if (c45DepthBox) c45DepthBox.style.display = 'none';
            }
        }

        algoRadios.forEach(radio => {
            radio.addEventListener('change', updateFormState);
        });

        updateFormState();

        // Dynamic update dataset summary card
        const selectDataset = document.getElementById('id_dataset');
        const summaryName = document.getElementById('summaryDsName');
        const summaryFormat = document.getElementById('summaryDsFormat');
        const summaryRows = document.getElementById('summaryDsRows');

        if (selectDataset) {
            selectDataset.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt && summaryName && summaryFormat && summaryRows) {
                    summaryName.textContent = opt.getAttribute('data-name') || opt.text;
                    summaryFormat.textContent = opt.getAttribute('data-format') || 'CSV';
                    summaryRows.innerHTML = '<i class="bi bi-check2 me-0.5"></i> ' + (opt.getAttribute('data-rows') || '0') + ' baris';
                }
            });
        }

        // Highlight active radio card
        function highlightCards() {
            document.querySelectorAll('.algo-card').forEach(card => {
                const r = card.querySelector('input[type="radio"]');
                if (r && r.checked) {
                    card.style.borderColor = 'var(--color-coral)';
                    card.style.backgroundColor = 'var(--color-peach-light)';
                } else {
                    card.style.borderColor = 'var(--color-border)';
                    card.style.backgroundColor = '#ffffff';
                }
            });
        }

        algoRadios.forEach(radio => {
            radio.addEventListener('change', highlightCards);
        });
        highlightCards();
    });
</script>
<?= $this->endSection() ?>
