<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="polara-breadcrumb">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span class="sep">/</span>
                <a href="<?= site_url('datasets') ?>">Dataset</a>
                <span class="sep">/</span>
                <span class="current">Konfirmasi Kolom</span>
            </div>
            <h1 class="page-title-text">Konfirmasi Kolom Dataset</h1>
            <p class="page-subtitle-text">Tinjau kolom berkas <strong><?= esc($dataset['nama_file_asli'] ?? $dataset['nama_dataset']) ?></strong> untuk digunakan pada analisis data mining.</p>
        </div>
        <a href="<?= site_url('datasets') ?>" class="btn-ghost">
            <i class="bi bi-arrow-left"></i> Kembali ke Dataset
        </a>
    </div>
</div>

<!-- Dataset Metadata Summary Card -->
<div class="card-p mb-4">
    <div class="card-p-body">
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <div class="text-muted" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Nama Dataset</div>
                <div class="fw-bold" style="color: var(--navy); font-size: 14px; margin-top: 2px;"><?= esc($dataset['nama_dataset']) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Format Berkas</div>
                <div style="margin-top: 2px;">
                    <span style="background: #F1F5F9; color: var(--navy); border: 1px solid var(--border); border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 2px 8px;">
                        <?= esc($dataset['format_file'] ?? 'csv') ?>
                    </span>
                    <span class="text-muted ms-1" style="font-size: 11.5px;"><?= count($preview['headers']) ?> Kolom</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Jumlah Baris Data</div>
                <div class="fw-bold" style="color: var(--coral); font-size: 14px; margin-top: 2px;">
                    <?= number_format($preview['total_rows'] ?? $dataset['jumlah_baris'] ?? 0) ?> baris
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="text-muted" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
                <div style="margin-top: 2px;">
                    <span class="badge-status badge-siap" style="font-size: 11px; padding: 2px 8px;">Siap Dikonfirmasi</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Column Confirmation Form -->
<form action="<?= site_url('datasets/save-mapping/' . $dataset['id']) ?>" method="post">
    <?= csrf_field() ?>

    <div class="card-p mb-4">
        <div class="card-p-header">
            <div>
                <h5 class="card-p-title">
                    <i class="bi bi-layout-three-columns"></i> Daftar Kolom Dataset
                </h5>
                <span class="text-muted" style="font-size: 12.5px;">
                    Sistem mendeteksi <strong><?= count($preview['headers']) ?> kolom</strong>. Seluruh kolom otomatis diaktifkan untuk analisis data mining.
                </span>
            </div>
        </div>

        <div class="card-p-body-flush">
            <div class="table-responsive">
                <table class="table-polara" style="width: 100%; margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 48px; text-align: center; padding-left: 16px;">#</th>
                            <th style="min-width: 220px;">Nama Kolom Berkas</th>
                            <th style="width: 110px; text-align: center;">Tipe Data</th>
                            <th style="width: 150px; text-align: center;">Gunakan Kolom</th>
                            <th style="width: 150px; text-align: center;">Target Klasifikasi</th>
                            <th style="min-width: 200px; padding-right: 16px;">Atribut Standar (Opsional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview['headers'] as $colIdx => $header): ?>
                            <?php
                                $detectedType = $preview['detected_types'][$colIdx] ?? 'string';
                                $currentMapping = $savedMappings[$header] ?? $preview['suggested_mappings'][$colIdx] ?? '';
                                $isFeature = isset($savedFeatures[$header]) ? $savedFeatures[$header] : 1;
                                $isTarget = isset($savedTargets[$header]) ? $savedTargets[$header] : ($currentMapping === 'target' ? 1 : 0);

                                $typeLabel = match($detectedType) {
                                    'integer' => 'Angka',
                                    'float'   => 'Desimal',
                                    'date'    => 'Tanggal',
                                    default   => 'Teks',
                                };
                                $typeBadgeStyle = match($detectedType) {
                                    'integer', 'float' => 'background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;',
                                    'date'             => 'background: #FDF4FF; color: #86198F; border: 1px solid #F5D0FE;',
                                    default            => 'background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;',
                                };
                            ?>
                            <tr>
                                <td style="text-align: center; padding-left: 16px; color: var(--muted); font-weight: 600; font-size: 12px;">
                                    <?= $colIdx + 1 ?>
                                </td>
                                <td>
                                    <input type="hidden" name="headers[<?= $colIdx ?>]" value="<?= esc($header) ?>">
                                    <input type="hidden" name="types[<?= $colIdx ?>]" value="<?= esc($detectedType) ?>">
                                    <div style="font-weight: 600; color: var(--navy); font-size: 13.5px; word-break: break-word;">
                                        <?= esc($header) ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span style="<?= $typeBadgeStyle ?> font-size: 11px; font-weight: 600; border-radius: 4px; padding: 2px 8px; display: inline-block;">
                                        <?= $typeLabel ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 500; color: var(--navy); margin-bottom: 0;">
                                        <input class="form-check-input" type="checkbox" name="is_feature[<?= $colIdx ?>]" value="1" <?= $isFeature ? 'checked' : '' ?> style="cursor: pointer;">
                                        <span>Gunakan</span>
                                    </label>
                                </td>
                                <td style="text-align: center;">
                                    <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 500; color: var(--navy); margin-bottom: 0;">
                                        <input class="form-check-input" type="radio" name="target_column" value="<?= $colIdx ?>" <?= $isTarget ? 'checked' : '' ?> style="cursor: pointer;">
                                        <span>Target</span>
                                    </label>
                                </td>
                                <td style="padding-right: 16px;">
                                    <select name="mapping[<?= $colIdx ?>]" class="form-select form-select-sm" style="font-size: 12px; border-radius: 6px;">
                                        <option value="">-- Otomatis / Sesuai Kolom --</option>
                                        <?php foreach ($standardAttributes as $attrKey => $attrLabel): ?>
                                            <option value="<?= $attrKey ?>" <?= ($currentMapping === $attrKey) ? 'selected' : '' ?>>
                                                <?= esc($attrLabel) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Action & Guide -->
            <div style="padding: 16px 20px; background: var(--warm-white); border-top: 1px solid var(--border); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 600; color: var(--navy); margin-bottom: 0;">
                        <input class="form-check-input" type="radio" name="target_column" value="" <?= !in_array(1, $savedTargets, true) ? 'checked' : '' ?> style="cursor: pointer;">
                        <span>Tanpa Target Klasifikasi (Cocok untuk Klasterisasi K-Means)</span>
                    </label>
                </div>
                <button type="submit" class="btn-coral">
                    <i class="bi bi-check2-circle"></i>
                    <span>Simpan &amp; Aktifkan Dataset</span>
                </button>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>
