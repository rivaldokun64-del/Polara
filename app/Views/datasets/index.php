<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
/* File Drop Zone */
.file-dropzone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-md);
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    background: var(--warm-white);
}
.file-dropzone:hover, .file-dropzone.drag-over {
    border-color: var(--coral);
    background: var(--coral-light);
}
.file-dropzone .drop-icon {
    font-size: 32px;
    color: var(--coral);
    margin-bottom: 8px;
    display: block;
}
.file-name-display {
    font-size: 12.5px;
    color: var(--muted);
    margin-top: 8px;
    min-height: 20px;
}
.file-name-display.has-file { color: var(--coral); font-weight: 600; }

/* Import confirm modal details */
.import-detail-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
.import-detail-row:last-child { border-bottom: none; }
.import-detail-label { color: var(--muted); font-weight: 500; flex-shrink: 0; }
.import-detail-value { color: var(--navy); font-weight: 600; text-align: right; }

/* Table Column Width & Truncation Controls */
.table-datasets {
    width: 100%;
    min-width: 960px;
    table-layout: fixed;
    margin-bottom: 0;
}
.file-name-truncate {
    font-size: 11.5px;
    font-family: monospace;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    display: block;
    vertical-align: middle;
}
.dataset-name-cell {
    font-weight: 600;
    color: var(--navy);
    font-size: 13px;
    line-height: 1.35;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.dataset-desc-truncate {
    font-size: 11.5px;
    color: var(--muted);
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-top: 2px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="polara-breadcrumb">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span class="sep">/</span>
                <span class="current">Dataset</span>
            </div>
            <h1 class="page-title-text">Manajemen Dataset Rekam Medis</h1>
            <p class="page-subtitle-text">Kelola, pratinjau, dan petakan kolom file spreadsheet untuk analisis data mining.</p>
        </div>
        <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
        <button type="button" class="btn-coral" id="btnOpenUpload">
            <i class="bi bi-cloud-upload"></i> Unggah Dataset Baru
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Main Card -->
<div class="card-p">
    <?php if (empty($datasets)): ?>
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-state-icon">
            <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Maskot Polara">
        </div>
        <h5>Belum Ada Dataset yang Diunggah</h5>
        <p>Mulai dengan mengunggah file rekam medis dalam format spreadsheet tabular (.csv, .xlsx, .xls) yang memuat riwayat kunjungan pasien.</p>
        <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
        <button type="button" class="btn-coral mt-3" id="btnOpenUpload2">
            <i class="bi bi-cloud-upload"></i> Unggah Dataset Sekarang
        </button>
        <?php else: ?>
        <span class="badge-status badge-uploaded" style="font-size:12px;">Peran Pimpinan: hanya dapat membaca laporan dan visualisasi.</span>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Dataset Table -->
    <div class="table-responsive">
        <table class="table-polara table-datasets" id="datasetsTable">
            <thead>
                <tr>
                    <th style="width:36px; padding-left:14px;">#</th>
                    <th style="width:25%;">Nama Dataset</th>
                    <th style="width:20%;">File Sumber</th>
                    <th style="width:75px; text-align:center;">Format</th>
                    <th style="width:70px; text-align:center;">Baris</th>
                    <th style="width:85px; text-align:center;">Status</th>
                    <th style="width:14%;">Pengunggah</th>
                    <th style="width:110px;">Waktu</th>
                    <th style="width:105px; text-align:right; padding-right:14px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datasets as $idx => $ds): ?>
                <tr>
                    <td style="padding-left:14px; color:var(--muted); font-size:12px; font-weight:600;"><?= $idx + 1 ?></td>
                    <td>
                        <div class="dataset-name-cell"><?= esc($ds['nama_dataset']) ?></div>
                        <?php if (!empty($ds['deskripsi'])): ?>
                        <div class="dataset-desc-truncate" title="<?= esc($ds['deskripsi']) ?>"><?= esc($ds['deskripsi']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="file-name-truncate" title="<?= esc($ds['nama_file_asli'] ?? '-') ?>">
                            <i class="bi bi-file-earmark-spreadsheet text-muted me-1"></i><?= esc($ds['nama_file_asli'] ?? '-') ?>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <span style="background:#F1F5F9; color:var(--navy); border:1px solid var(--border); border-radius:4px; font-size:10.5px; font-weight:700; text-transform:uppercase; padding:2px 7px;"><?= esc($ds['format_file'] ?? '-') ?></span>
                        <?php if (!empty($ds['ukuran_file'])): ?>
                        <div style="font-size:10.5px; color:var(--muted); margin-top:2px;"><?= number_format($ds['ukuran_file'] / 1024, 1) ?> KB</div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <div style="font-weight:700; color:var(--navy); font-size:13px;"><?= number_format($ds['jumlah_baris'] ?? 0) ?></div>
                        <div style="font-size:11px; color:var(--muted);">baris</div>
                    </td>
                    <td style="text-align:center;">
                        <?php
                        $status = $ds['status'] ?? 'uploaded';
                        $statusClass = match($status) {
                            'siap'     => 'badge-siap',
                            'uploaded' => 'badge-uploaded',
                            'diproses' => 'badge-diproses',
                            'error'    => 'badge-error',
                            default    => 'badge-uploaded',
                        };
                        $statusLabel = match($status) {
                            'siap'     => 'Siap',
                            'uploaded' => 'Diunggah',
                            'diproses' => 'Diproses',
                            'error'    => 'Error',
                            default    => $status,
                        };
                        ?>
                        <span class="badge-status <?= $statusClass ?>" style="font-size:10.5px; padding:2px 8px;"><?= $statusLabel ?></span>
                    </td>
                    <td>
                        <div style="font-size:12.5px; font-weight:600; color:var(--navy); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%;" title="<?= esc($ds['nama_pengunggah'] ?? 'Sistem') ?>"><?= esc($ds['nama_pengunggah'] ?? 'Sistem') ?></div>
                        <span class="badge-role badge-role-<?= esc($ds['role_pengunggah'] ?? 'analis') ?>" style="font-size:9.5px; padding:1px 6px;"><?= esc($ds['role_pengunggah'] ?? 'analis') ?></span>
                    </td>
                    <td>
                        <span style="font-size:12px; color:var(--navy); font-weight:500;"><?= esc(date('d M Y', strtotime($ds['created_at']))) ?></span>
                        <div style="font-size:10.5px; color:var(--muted);"><?= esc(date('H:i', strtotime($ds['created_at']))) ?> WIB</div>
                    </td>
                    <td style="text-align:right; padding-right:14px;">
                        <div style="display:inline-flex; align-items:center; gap:5px;">
                            <a href="<?= site_url('datasets/preview/' . $ds['id']) ?>"
                               class="btn-ghost"
                               style="padding:5px 9px; font-size:11.5px; border-radius:6px;"
                               title="Pratinjau &amp; Pemetaan Kolom">
                                <i class="bi bi-table"></i>
                                <span>Peta</span>
                            </a>
                            <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
                            <button type="button"
                                class="btn-ghost"
                                style="padding:5px 8px; font-size:11.5px; color:#DC2626; border-color:#FECACA; border-radius:6px;"
                                title="Hapus Dataset"
                                onclick="confirmDeleteDataset(
                                    '<?= site_url('datasets/delete/' . $ds['id']) ?>',
                                    '<?= esc($ds['nama_dataset'], 'js') ?>',
                                    <?= (int)($ds['jumlah_baris'] ?? 0) ?>
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


<!-- ══════════ UPLOAD MODAL ══════════ -->
<?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
<div class="modal fade" id="modalUploadDataset" tabindex="-1" aria-labelledby="modalUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--coral-light);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--coral);">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalUploadLabel" style="color:var(--navy);">Unggah Dataset Baru</h5>
                        <div style="font-size:12px; color:var(--muted);">Dataset akan disimpan dan siap dipetakan kolom-kolomnya</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <!-- Step 1: Fill form -->
                <div id="uploadStep1">
                    <div class="mb-3">
                        <label class="form-label" for="nama_dataset">Nama Dataset <span style="color:var(--coral);">*</span></label>
                        <input type="text" class="form-control" id="nama_dataset" placeholder="Contoh: Rekam Kunjungan Poliklinik Q1 2026" value="<?= old('nama_dataset') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="deskripsi">Deskripsi / Catatan Sumber</label>
                        <textarea class="form-control" id="deskripsi" rows="2" placeholder="Catatan mengenai unit atau faskes asal"><?= old('deskripsi') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="file_dataset">File Spreadsheet <span style="color:var(--coral);">*</span></label>
                        <div class="file-dropzone" id="fileDropzone">
                            <i class="bi bi-file-earmark-spreadsheet drop-icon"></i>
                            <div style="font-size:13.5px; font-weight:600; color:var(--navy);">Klik atau seret file ke sini</div>
                            <div style="font-size:12px; color:var(--muted); margin-top:4px;">Format: .csv, .xlsx, .xls — Maks. 10 MB</div>
                        </div>
                        <input type="file" id="file_dataset" accept=".csv,.xlsx,.xls" style="display:none;">
                        <div class="file-name-display" id="fileNameDisplay">Belum ada file dipilih</div>
                    </div>
                </div>

                <!-- Step 2: Confirm preview (before submit) -->
                <div id="uploadStep2" style="display:none;">
                    <div style="text-align:center;padding:8px 0 16px;">
                        <div style="width:56px;height:56px;border-radius:50%;background:var(--coral-light);border:1px solid var(--peach);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:26px;color:var(--coral);">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <h6 style="font-size:15px;font-weight:700;color:var(--navy);margin:0 0 6px;">Konfirmasi Impor Dataset</h6>
                        <p style="font-size:13px;color:var(--muted);margin:0;">Periksa kembali detail berikut sebelum melanjutkan.</p>
                    </div>

                    <div style="background:var(--warm-white);border:1px solid var(--border);border-radius:var(--radius-md);padding:16px 18px;margin-bottom:16px;">
                        <div class="import-detail-row">
                            <span class="import-detail-label">Nama Dataset</span>
                            <span class="import-detail-value" id="confirmNama">—</span>
                        </div>
                        <div class="import-detail-row">
                            <span class="import-detail-label">File</span>
                            <span class="import-detail-value" id="confirmFile">—</span>
                        </div>
                        <div class="import-detail-row">
                            <span class="import-detail-label">Ukuran</span>
                            <span class="import-detail-value" id="confirmSize">—</span>
                        </div>
                        <div class="import-detail-row">
                            <span class="import-detail-label">Deskripsi</span>
                            <span class="import-detail-value" id="confirmDesc" style="max-width:220px;">—</span>
                        </div>
                    </div>

                    <div style="background:var(--amber-soft);border:1px solid #FDE68A;border-radius:var(--radius-sm);padding:12px 14px;font-size:12.5px;color:#92400E;display:flex;align-items:flex-start;gap:8px;">
                        <i class="bi bi-info-circle" style="font-size:15px;flex-shrink:0;margin-top:1px;"></i>
                        <div>Impor akan menyimpan data ke basis data. Setelah diunggah, Anda dapat memetakan kolom sesuai atribut standar rekam medis.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost" id="btnUploadBack" style="display:none;" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-ghost" id="btnUploadCancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-coral" id="btnUploadNext">
                    <i class="bi bi-arrow-right"></i> Lanjutkan &amp; Periksa
                </button>
                <button type="button" class="btn-coral" id="btnUploadSubmit" style="display:none;" onclick="submitUploadForm()">
                    <i class="bi bi-check-lg"></i> Ya, Impor Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden real form (submitted programmatically) -->
<form action="<?= site_url('datasets/upload') ?>" method="post" enctype="multipart/form-data" id="realUploadForm" style="display:none;">
    <?= csrf_field() ?>
    <input type="text"   name="nama_dataset" id="realNamaDataset">
    <input type="text"   name="deskripsi"    id="realDeskripsi">
    <input type="file"   name="file_dataset" id="realFileInput" accept=".csv,.xlsx,.xls">
</form>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
'use strict';

/* ── Upload Modal ── */
const modalEl   = document.getElementById('modalUploadDataset');
const bsModal   = modalEl ? new bootstrap.Modal(modalEl) : null;

// Open triggers
['btnOpenUpload','btnOpenUpload2'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.addEventListener('click', () => { resetUploadModal(); bsModal && bsModal.show(); });
});

/* File drag-drop */
const dropzone      = document.getElementById('fileDropzone');
const fileInput     = document.getElementById('file_dataset');
const fileNameDisplay = document.getElementById('fileNameDisplay');

if (dropzone && fileInput) {
    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            updateFileName(e.dataTransfer.files[0]);
        }
    });
    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) updateFileName(fileInput.files[0]);
    });
}

function updateFileName(file) {
    if (!fileNameDisplay) return;
    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
    fileNameDisplay.textContent = file.name + ' (' + sizeMB + ' MB)';
    fileNameDisplay.classList.add('has-file');
}

/* Step navigation */
const step1     = document.getElementById('uploadStep1');
const step2     = document.getElementById('uploadStep2');
const btnNext   = document.getElementById('btnUploadNext');
const btnBack   = document.getElementById('btnUploadBack');
const btnCancel = document.getElementById('btnUploadCancel');
const btnSubmit = document.getElementById('btnUploadSubmit');

if (btnNext) {
    btnNext.addEventListener('click', () => {
        const nama   = document.getElementById('nama_dataset');
        const file   = fileInput;
        if (!nama || !nama.value.trim()) { showToast('warning', '⚠️ Lengkapi Form', 'Nama dataset wajib diisi.'); return; }
        if (!file || !file.files.length) { showToast('warning', '⚠️ Pilih File', 'Silakan pilih file spreadsheet terlebih dahulu.'); return; }

        // Fill confirm step
        const f = file.files[0];
        document.getElementById('confirmNama').textContent = nama.value.trim();
        document.getElementById('confirmFile').textContent = f.name;
        document.getElementById('confirmSize').textContent = (f.size / 1024).toFixed(1) + ' KB';
        document.getElementById('confirmDesc').textContent = (document.getElementById('deskripsi').value.trim()) || '(tidak ada)';

        // Switch steps
        step1.style.display = 'none';
        step2.style.display = '';
        btnNext.style.display = 'none';
        btnCancel.style.display = 'none';
        btnBack.style.display = '';
        btnSubmit.style.display = '';
    });
}
if (btnBack) {
    btnBack.addEventListener('click', () => {
        step2.style.display = 'none';
        step1.style.display = '';
        btnSubmit.style.display = 'none';
        btnBack.style.display = 'none';
        btnNext.style.display = '';
        btnCancel.style.display = '';
    });
}

function resetUploadModal() {
    if (step1) { step1.style.display = ''; }
    if (step2) { step2.style.display = 'none'; }
    if (btnNext)   btnNext.style.display = '';
    if (btnCancel) btnCancel.style.display = '';
    if (btnBack)   btnBack.style.display = 'none';
    if (btnSubmit) btnSubmit.style.display = 'none';
    if (fileNameDisplay) { fileNameDisplay.textContent = 'Belum ada file dipilih'; fileNameDisplay.classList.remove('has-file'); }
    if (fileInput) fileInput.value = '';
    const n = document.getElementById('nama_dataset');
    const d = document.getElementById('deskripsi');
    if (n) n.value = '';
    if (d) d.value = '';
}

window.submitUploadForm = function() {
    const nama  = document.getElementById('nama_dataset').value.trim();
    const desc  = document.getElementById('deskripsi').value.trim();
    const file  = fileInput.files[0];
    if (!nama || !file) return;

    // Transfer to real form
    document.getElementById('realNamaDataset').value = nama;
    document.getElementById('realDeskripsi').value   = desc;

    // Clone file to real file input
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('realFileInput').files = dt.files;

    if (btnSubmit) {
        btnSubmit.innerHTML = '<span class="spin" style="width:14px;height:14px;border-width:2px;display:inline-block;"></span> Mengimpor...';
        btnSubmit.disabled = true;
    }
    document.getElementById('realUploadForm').submit();
};

/* ── Delete Confirm ── */
window.confirmDeleteDataset = function(url, name, rows) {
    const title = 'Hapus dataset "' + name + '"?';
    const body  = 'Dataset ini memiliki ' + rows.toLocaleString('id-ID') + ' baris data kunjungan. ' +
                  'Seluruh data kunjungan dan hasil analisis terkait akan ikut terhapus secara permanen. ' +
                  'Tindakan ini tidak dapat dibatalkan.';
    confirmDelete(url, title, body);
};

})();
</script>
<?= $this->endSection() ?>
