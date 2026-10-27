<?= $this->extend('layouts/app') ?>
<?= $this->section('styles') ?>
<style>
/* Modern Figma-Styled Dashboard Aesthetics */
:root {
    --bg-page: #F8F9FA;
    --card-bg: #FFFFFF;
    --border-subtle: #ECEEF1;
    --border-hover: #F9B097;
    --radius-card: 14px;
}

body {
    background-color: var(--bg-page);
}

.dashboard-container {
    max-width: 100%;
}

/* Page Top Header */
.dash-header-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.dash-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--navy);
    letter-spacing: -0.5px;
    margin: 0 0 4px;
    font-family: var(--font-brand);
}
.dash-subtitle {
    font-size: 13px;
    color: var(--muted);
    margin: 0;
}
.dash-top-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-top-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #FFFFFF;
    color: var(--navy);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
    box-shadow: var(--shadow-xs);
}
.btn-top-secondary:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
    color: var(--navy);
}

/* Dataset Selector Interactive Segment */
.dataset-picker-bar {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #FFFFFF;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-card);
    padding: 12px 18px;
    margin-bottom: 22px;
    box-shadow: 0 1px 3px rgba(16,43,70,0.03);
    flex-wrap: wrap;
}
.picker-title-col {
    display: flex;
    flex-direction: column;
}
.picker-label-top {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #9CA3AF;
}
.picker-label-sub {
    font-size: 12px;
    color: var(--muted);
}
.dataset-select-modern {
    flex: 1 1 260px;
    min-width: 220px;
    max-width: 480px;
    border: 1.5px solid var(--border-subtle);
    border-radius: 9px;
    padding: 8px 14px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--navy);
    background: #FAFBFC;
    cursor: pointer;
    outline: none;
    transition: all 0.15s ease;
}
.dataset-select-modern:focus {
    border-color: var(--coral);
    box-shadow: 0 0 0 3px var(--coral-glow);
    background: #FFFFFF;
}

/* 4 Statistik Cards */
.stat-cards-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 1199px) {
    .stat-cards-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 575px) {
    .stat-cards-grid { grid-template-columns: 1fr; }
}

.stat-box {
    background: #FFFFFF;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-card);
    padding: 18px 20px 18px 22px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    position: relative;
    box-shadow: 0 1px 4px rgba(16,43,70,0.04);
    overflow: hidden;
}
.stat-box::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    border-radius: 14px 0 0 14px;
    background: var(--stat-accent, #E5E7EB);
}
.stat-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(16,43,70,0.08);
    border-color: var(--border-hover);
}
.stat-box-icon {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: transform 0.22s cubic-bezier(.34,1.56,.64,1), box-shadow 0.22s ease;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    position: relative;
    overflow: hidden;
}
.stat-box-icon::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    top: -30%;
    left: -30%;
    width: 60%;
    height: 60%;
    border-radius: 50%;
    pointer-events: none;
}
.stat-box:hover .stat-box-icon {
    transform: scale(1.12);
    box-shadow: 0 6px 20px rgba(0,0,0,0.18);
}
.stat-box-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6B7280;
    margin-bottom: 4px;
}
.stat-box-num {
    font-size: 23px;
    font-weight: 800;
    color: var(--navy);
    font-family: var(--font-brand);
    line-height: 1.1;
    letter-spacing: -0.5px;
}
.stat-box-sub {
    font-size: 12px;
    color: #9CA3AF;
    margin-top: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Notice Bar Validasi Excel */
.validation-notice-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: 10px;
    margin-bottom: 22px;
    font-size: 12.5px;
    color: #1E40AF;
}

/* Figma Section Cards */
.figma-card {
    background: #FFFFFF;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-card);
    padding: 20px 22px;
    margin-bottom: 22px;
    box-shadow: 0 1px 3px rgba(16,43,70,0.02);
}
.figma-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 18px;
    gap: 12px;
    flex-wrap: wrap;
}
.figma-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--navy);
    margin: 0 0 2px;
}
.figma-card-subtitle {
    font-size: 12px;
    color: var(--muted);
    margin: 0;
}
.badge-tag-figma {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 6px;
    background: #F3F4F6;
    color: #4B5563;
    border: 1px solid #E5E7EB;
}

/* Table Design Sesuai Referensi Figma */
.table-death-wrap {
    overflow-x: auto;
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
}
.table-death {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13px;
}
.table-death thead th {
    background: #F9FAFB;
    color: #4B5563;
    font-weight: 700;
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 14px;
    border-bottom: 1px solid var(--border-subtle);
    white-space: nowrap;
}
.table-death tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid #F3F4F6;
    color: var(--navy);
    vertical-align: middle;
}
.table-death tbody tr:hover td {
    background: #FDF9F8;
}
.table-death tbody tr:last-child td {
    border-bottom: none;
}
.table-death tfoot td {
    background: #F8FAFC;
    font-weight: 800;
    color: var(--navy);
    border-top: 2px solid var(--border-subtle);
    padding: 12px 14px;
}

/* Progress bar proporsi di tabel */
.prop-bar-container {
    display: flex;
    align-items: center;
    gap: 8px;
}
.prop-bar-track {
    flex: 1;
    height: 7px;
    background: #E5E7EB;
    border-radius: 4px;
    overflow: hidden;
    min-width: 60px;
}
.prop-bar-fill {
    height: 100%;
    background: #0D9488;
    border-radius: 4px;
}

/* Filter Baris Tabel */
.table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.table-search-input {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12.5px;
    outline: none;
    width: 220px;
}
.table-search-input:focus {
    border-color: var(--coral);
}

/* Loading Spinner */
#dashLoadingOverlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(2px);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
}
#dashLoadingOverlay.show { display: flex; }
.spin {
    width: 28px;
    height: 28px;
    border: 3px solid #E5E7EB;
    border-top-color: var(--coral);
    border-radius: 50%;
    animation: spinAnim 0.7s linear infinite;
}
@keyframes spinAnim { to { transform: rotate(360deg); } }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="dashLoadingOverlay">
    <div class="spin"></div>
    <span style="font-size:13px;color:var(--muted);font-weight:600;">Memuat dan menyinkronkan data analitik...</span>
</div>

<div class="dashboard-container">
    <!-- Bagian Atas: Judul Dashboard Eksekutif, Deskripsi, Tombol Aksi -->
    <div class="dash-header-wrap">
        <div>
            <h1 class="dash-title" id="dashTitle">Dashboard Polara</h1>
            <p class="dash-subtitle" id="dashSubtitle">Memuat informasi dataset aktif...</p>
        </div>
        <div class="dash-top-actions">
            <?php if (in_array(session()->get('role'), ['admin', 'analis'])): ?>
            <a href="<?= site_url('datasets') ?>" class="btn-top-secondary">
                <i class="bi bi-folder2-open text-muted"></i> Kelola dataset
            </a>
            <?php endif; ?>
            <button type="button" class="btn-coral" onclick="exportActiveReport()">
                <i class="bi bi-file-earmark-arrow-down"></i> Ekspor laporan
            </button>
        </div>
    </div>

    <?php if (empty($allDatasets)): ?>
    <div class="card-p p-5 text-center">
        <div class="empty-state-icon mx-auto mb-3"><img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Polara" style="width:56px;height:56px;object-fit:contain;"></div>
        <h5 style="color:var(--navy);font-weight:700;">Belum Ada Dataset</h5>
        <p class="text-muted" style="max-width:400px;margin:0 auto 16px;">Silakan unggah dataset untuk mengaktifkan dashboard analitik.</p>
        <a href="<?= site_url('datasets') ?>" class="btn-coral mx-auto"><i class="bi bi-cloud-upload"></i> Unggah Dataset</a>
    </div>
    <?php else: ?>

    <!-- Pemilih Dataset: Aktif, Status Validasi, Jumlah Baris Valid, Tombol Detail Dataset -->
    <div class="dataset-picker-bar">
        <div class="picker-title-col">
            <span class="picker-label-top">DATASET AKTIF</span>
            <span class="picker-label-sub">Pilih konteks analisis</span>
        </div>

        <select id="datasetSelect" class="dataset-select-modern" aria-label="Pilih dataset aktif">
            <?php foreach ($allDatasets as $ds): ?>
            <option value="<?= (int)$ds['id'] ?>"
                <?= ($activeDataset && (int)$ds['id'] === (int)$activeDataset['id']) ? 'selected' : '' ?>
                data-status="<?= esc($ds['status']) ?>"
                data-rows="<?= (int)($ds['jumlah_baris'] ?? 0) ?>"
                data-name="<?= esc($ds['nama_dataset']) ?>">
                <?= esc($ds['nama_dataset']) ?> (<?= number_format($ds['jumlah_baris'] ?? 0, 0, ',', '.') ?> baris valid)
            </option>
            <?php endforeach; ?>
        </select>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <span class="badge-status badge-siap" id="badgeValidationStatus" style="font-size:11px;padding:4px 10px;">
                <i class="bi bi-check-circle-fill me-1"></i> Validasi: Lulus
            </span>
            <a href="<?= site_url('datasets/preview/' . ($activeDataset['id'] ?? 1)) ?>" id="btnDetailDataset" class="btn-top-secondary py-1 px-3" style="font-size:12px;">
                <i class="bi bi-eye"></i> Detail Dataset
            </a>
        </div>
    </div>

    <!-- 4 Kartu Ringkasan Statistik Adaptif -->
    <div class="stat-cards-grid">
        <div class="stat-box" style="--stat-accent:#DC2626;">
            <div class="stat-box-icon" id="cardIcon1" style="background:linear-gradient(135deg,#FF6B6B 0%,#DC2626 100%);"></div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="stat-box-label" id="cardLabel1">TOTAL DATA</div>
                <div class="stat-box-num" id="cardStat1">—</div>
                <div class="stat-box-sub" id="cardSub1">Memuat...</div>
            </div>
        </div>

        <div class="stat-box" style="--stat-accent:#0284C7;">
            <div class="stat-box-icon" id="cardIcon2" style="background:linear-gradient(135deg,#38BDF8 0%,#0284C7 100%);"></div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="stat-box-label" id="cardLabel2">DIMENSI DATA</div>
                <div class="stat-box-num" id="cardStat2">—</div>
                <div class="stat-box-sub" id="cardSub2">Memuat...</div>
            </div>
        </div>

        <div class="stat-box" style="--stat-accent:#D97706;">
            <div class="stat-box-icon" id="cardIcon3" style="background:linear-gradient(135deg,#FCD34D 0%,#D97706 100%);"></div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="stat-box-label" id="cardLabel3">RENTANG PERIODE</div>
                <div class="stat-box-num" id="cardStat3">—</div>
                <div class="stat-box-sub" id="cardSub3">Memuat...</div>
            </div>
        </div>

        <div class="stat-box" style="--stat-accent:#16A34A;">
            <div class="stat-box-icon" id="cardIcon4" style="background:linear-gradient(135deg,#4ADE80 0%,#16A34A 100%);"></div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="stat-box-label" id="cardLabel4">KATEGORI UTAMA</div>
                <div class="stat-box-num" id="cardStat4" style="font-size:18px;margin-top:2px;line-height:1.2;">—</div>
                <div class="stat-box-sub" id="cardSub4">Memuat...</div>
            </div>
        </div>
    </div>

    <!-- Notice Bar Validasi (dinamis, hanya untuk dataset mortalitas) -->
    <div class="validation-notice-bar" id="noticeValidationBar" style="display:none;">
        <i class="bi bi-shield-check" style="font-size:16px;"></i>
        <span id="noticeValidationText"><strong>Dataset Terverifikasi:</strong> Data dimuat dari sumber aktif.</span>
    </div>

    <!-- Baris 1: Grafik adaptif Distribusi + Top Entitas -->
    <div class="row g-4 mb-4" id="chartRowMain">
        <!-- Grafik 1 — Distribusi Utama -->
        <div class="col-12 col-xl-6" id="chartCol1">
            <div class="figma-card h-100">
                <div class="figma-card-header">
                    <div>
                        <h2 class="figma-card-title" id="chart1Title">Distribusi Data</h2>
                        <p class="figma-card-subtitle" id="chart1Subtitle">Distribusi berdasarkan dimensi utama dataset</p>
                    </div>
                    <span class="badge-tag-figma" id="chart1Badge">—</span>
                </div>
                <div style="position:relative;height:270px;">
                    <canvas id="chartCauses"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafik 2 — Top Entitas / Wilayah -->
        <div class="col-12 col-xl-6" id="chartCol2">
            <div class="figma-card h-100">
                <div class="figma-card-header">
                    <div>
                        <h2 class="figma-card-title" id="chart2Title">Top Entitas Data</h2>
                        <p class="figma-card-subtitle" id="chart2Subtitle">Lima entitas dengan nilai tertinggi pada dataset</p>
                    </div>
                    <span class="badge-tag-figma" id="chart2Badge">Top 5</span>
                </div>
                <div style="position:relative;height:270px;">
                    <canvas id="chartTopProvinces"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Tambahan: Tren Waktu (muncul jika ada data tren) -->
    <div class="figma-card mb-4" id="chartTrendSection" style="display:none;">
        <div class="figma-card-header">
            <div>
                <h2 class="figma-card-title" id="chartTrendTitle">Tren Data</h2>
                <p class="figma-card-subtitle" id="chartTrendSubtitle">Perubahan nilai indikator utama dari waktu ke waktu</p>
            </div>
            <span class="badge-tag-figma">Tren Periode</span>
        </div>
        <div style="position:relative;height:240px;">
            <canvas id="chartTrend"></canvas>
        </div>
    </div>

    <!-- Grafik Distribusi Gender (muncul jika ada data gender) -->
    <div class="row g-4 mb-4" id="chartGenderAgeSection" style="display:none;">
        <div class="col-12 col-md-6" id="chartGenderCol">
            <div class="figma-card h-100">
                <div class="figma-card-header">
                    <div>
                        <h2 class="figma-card-title">Distribusi Jenis Kelamin</h2>
                        <p class="figma-card-subtitle">Proporsi Laki-laki vs Perempuan</p>
                    </div>
                    <span class="badge-tag-figma">Gender</span>
                </div>
                <div style="position:relative;height:220px;">
                    <canvas id="chartGender"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6" id="chartAgeCol">
            <div class="figma-card h-100">
                <div class="figma-card-header">
                    <div>
                        <h2 class="figma-card-title">Distribusi Kelompok Usia</h2>
                        <p class="figma-card-subtitle">Komposisi usia pasien/subjek</p>
                    </div>
                    <span class="badge-tag-figma">Usia</span>
                </div>
                <div style="position:relative;height:220px;">
                    <canvas id="chartAge"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik 3 — Perbandingan Antarprovinsi (hanya mortalitas) -->
    <div class="figma-card mb-4" id="chartComparisonSection" style="display:none;">
        <div class="figma-card-header">
            <div>
                <h2 class="figma-card-title">Perbandingan Penyebab Kematian Antarprovinsi</h2>
                <p class="figma-card-subtitle">Eksplorasi perbandingan komparatif dengan filter kategori dan pemilihan provinsi</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="compareCategoryFilter" class="form-select form-select-sm" style="font-size:12px;font-weight:600;width:auto;" aria-label="Filter kategori penyebab">
                    <option value="all">Semua Kategori (Tumpuk)</option>
                    <option value="ptm">Penyakit Tidak Menular</option>
                    <option value="pm">Penyakit Menular</option>
                    <option value="kll">Kecelakaan Lalu Lintas</option>
                    <option value="kl">Kecelakaan Lainnya</option>
                    <option value="l">Lainnya</option>
                </select>
                <select id="compareSliceFilter" class="form-select form-select-sm" style="font-size:12px;font-weight:600;width:auto;" aria-label="Filter jumlah provinsi yang ditampilkan">
                    <option value="10">10 Provinsi Teratas</option>
                    <option value="20">20 Provinsi Teratas</option>
                    <option value="all">Seluruh 38 Provinsi</option>
                </select>
            </div>
        </div>
        <div style="position:relative;height:340px;">
            <canvas id="chartComparison"></canvas>
        </div>
    </div>

    <!-- Tabel Rincian: Adaptif untuk semua jenis dataset -->
    <div class="figma-card" id="tableMainSection">
        <div class="figma-card-header">
            <div>
                <h2 class="figma-card-title" id="tableMainTitle">Rincian Data</h2>
                <p class="figma-card-subtitle" id="tableMainSubtitle">Data lengkap dari dataset aktif</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn-top-secondary py-1 px-3" style="font-size:12px;" onclick="exportTableToCsv()">
                    <i class="bi bi-filetype-csv text-success"></i> CSV
                </button>
            </div>
        </div>

        <div class="table-toolbar">
            <input type="text" id="tableSearch" class="table-search-input" placeholder="Cari data..." aria-label="Cari data">
            <div style="font-size:12px;color:var(--muted);">
                Menampilkan <strong id="visibleRowCount">—</strong> baris data
            </div>
        </div>

        <div class="table-death-wrap">
            <table class="table-death" id="tableProvinces">
                <thead id="tableHead">
                    <!-- Diisi dinamis oleh JS berdasarkan jenis dataset -->
                </thead>
                <tbody id="tableBodyProvinces">
                    <!-- Baris diisi secara dinamis dari data aktif -->
                </tbody>
                <tfoot id="tableFootProvinces">
                    <!-- Total diisi secara dinamis -->
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function() {
    'use strict';

    const COLOR_CORAL  = '#F04432';
    const COLOR_TEAL   = '#0D9488';
    const COLOR_BLUE   = '#0284C7';
    const COLOR_AMBER  = '#D97706';
    const COLOR_PURPLE = '#7C3AED';
    const COLOR_GRAY   = '#64748B';
    const COLOR_NAVY   = '#102B46';

    Chart.defaults.font.family = "'Inter', 'Plus Jakarta Sans', sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#6B7280';

    let activeCharts = {};
    let globalDataCache = null;
    let tableCurrentRows = [];

    function destroyAllCharts() {
        Object.keys(activeCharts).forEach(key => {
            if (activeCharts[key]) {
                activeCharts[key].destroy();
                activeCharts[key] = null;
            }
        });
    }

    function formatNumberId(n) {
        return (Math.round(n) || 0).toLocaleString('id-ID');
    }

    function showLoading() {
        const el = document.getElementById('dashLoadingOverlay');
        if (el) el.classList.add('show');
    }
    function hideLoading() {
        const el = document.getElementById('dashLoadingOverlay');
        if (el) el.classList.remove('show');
    }

    function loadDashboardData(datasetId) {
        showLoading();
        fetch('<?= site_url("dashboard/dataset-stats") ?>/' + datasetId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            hideLoading();
            if (data.error) {
                if (window.showNotification) window.showNotification(data.error, 'error');
                return;
            }
            globalDataCache = data;
            renderFullDashboard(data);
        })
        .catch(err => {
            hideLoading();
            console.error(err);
            if (window.showNotification) window.showNotification('Gagal memuat statistik dataset.', 'error');
        });
    }

    function renderFullDashboard(data) {
        destroyAllCharts();

        const isDeath    = data.is_death_analysis;
        const dashCfg    = data.dashboard_config || {};
        const dsType     = data.dataset_type || 'umum';
        const totalCases = data.total_cases || data.total_rows || 0;

        // ── 1. Judul & Deskripsi Dashboard ──
        const titleEl    = document.getElementById('dashTitle');
        const subtitleEl = document.getElementById('dashSubtitle');
        if (titleEl)    titleEl.textContent    = dashCfg.title       || data.dataset?.nama_dataset || 'Dashboard Polara';
        if (subtitleEl) subtitleEl.textContent = dashCfg.description || 'Analitik data dari dataset aktif.';

        // ── 2. Kartu Statistik ──
        const kpiCards = dashCfg.kpi_cards || [];

        // Kartu 1: Total Kasus
        setText('cardLabel1', (kpiCards[0]?.label || data.card_total_label || 'TOTAL DATA').toUpperCase());
        setText('cardStat1',  formatNumberId(totalCases));
        setText('cardSub1',   kpiCards[0]?.sub   || data.card_total_sub  || 'Catatan terdata');
        setCardIcon('cardIcon1', kpiCards[0]?.icon || 'bi-database-fill', kpiCards[0]?.color || '#FEE2E2', kpiCards[0]?.textColor || '#DC2626');

        // Kartu 2: Dimensi / Wilayah
        if (isDeath) {
            setText('cardLabel2', 'PROVINSI TERDATA');
            setText('cardStat2',  data.province_rows ? data.province_rows.length : '—');
            setText('cardSub2',   'Cakupan wilayah Indonesia');
            setCardIcon('cardIcon2', 'bi-geo-alt-fill', '#E0F2FE', '#0284C7');
        } else {
            setText('cardLabel2', (kpiCards[1]?.label || 'TOTAL BARIS DATA').toUpperCase());
            setText('cardStat2',  formatNumberId(data.total_rows));
            setText('cardSub2',   kpiCards[1]?.sub || 'Baris valid tercatat');
            setCardIcon('cardIcon2', kpiCards[1]?.icon || 'bi-table', kpiCards[1]?.color || '#E0F2FE', kpiCards[1]?.textColor || '#0284C7');
        }

        // Kartu 3: Kualitas / Fitur
        if (isDeath) {
            const catCount = data.death_categories ? data.death_categories.length : 5;
            setText('cardLabel3', 'KATEGORI PENYEBAB');
            setText('cardStat3',  catCount + ' Kategori');
            setText('cardSub3',   'Klasifikasi penyebab mortalitas');
            setCardIcon('cardIcon3', 'bi-pie-chart-fill', '#FEF3C7', '#D97706');
        } else {
            // Jika ada metrik evaluasi model / akurasi
            const hasModelEval = data.best_accuracy && data.best_accuracy !== '-';
            if (hasModelEval) {
                setText('cardLabel3', 'AKURASI MODEL');
                setText('cardStat3',  data.best_accuracy);
                setText('cardSub3',   data.best_accuracy_desc || 'Model Terbaik');
                setCardIcon('cardIcon3', 'bi-cpu-fill', '#DCFCE7', '#16A34A');
            } else {
                const kpi3 = kpiCards[2] || {};
                setText('cardLabel3', (kpi3.label || 'RENTANG PERIODE').toUpperCase());
                setText('cardStat3',  kpi3.value || data.periode_label || 'Aktif');
                setText('cardSub3',   kpi3.sub || 'Cakupan waktu observasi');
                setCardIcon('cardIcon3', kpi3.icon || 'bi-calendar3', kpi3.color || '#FEF3C7', kpi3.textColor || '#D97706');
            }
        }

        // Kartu 4: Insight / Top Kategori
        if (isDeath) {
            const topCat  = (data.death_categories && data.death_categories.length > 0) ? data.death_categories[0] : null;
            setText('cardLabel4', 'KATEGORI TERBESAR');
            setText('cardStat4',  topCat ? topCat.kategori : '—');
            setText('cardSub4',   topCat ? formatNumberId(topCat.total) + ' jiwa (' + (totalCases > 0 ? ((topCat.total / totalCases) * 100).toFixed(1) : '0') + '%)' : '—');
            setCardIcon('cardIcon4', 'bi-award-fill', '#DCFCE7', '#16A34A');
        } else {
            const topDiag = data.top_diagnosis && data.top_diagnosis.length > 0 ? data.top_diagnosis[0] : null;
            // Pastikan tidak menampilkan judul dataset panjang yang terpotong jelek di kartu
            let displayTopLabel = topDiag ? topDiag.label : null;
            if (displayTopLabel && displayTopLabel.length > 28) {
                displayTopLabel = displayTopLabel.substring(0, 25) + '...';
            }

            if (topDiag && displayTopLabel && displayTopLabel !== data.dataset?.nama_dataset) {
                setText('cardLabel4', 'KATEGORI / ENTITAS UTAMA');
                setText('cardStat4',  displayTopLabel);
                setText('cardSub4',   formatNumberId(topDiag.total) + ' data tercatat');
                setCardIcon('cardIcon4', 'bi-award-fill', '#DCFCE7', '#16A34A');
            } else if (data.silhouette_score && data.silhouette_score !== '-') {
                setText('cardLabel4', 'SILHOUETTE SCORE');
                setText('cardStat4',  data.silhouette_score);
                setText('cardSub4',   data.silhouette_desc || 'K-Means Clustering');
                setCardIcon('cardIcon4', 'bi-diagram-3-fill', '#E0F2FE', '#0284C7');
            } else {
                const kpi4 = kpiCards[3] || {};
                setText('cardLabel4', (kpi4.label || 'KUALITAS DATASET').toUpperCase());
                setText('cardStat4',  '100% Valid');
                setText('cardSub4',   (data.total_features || 10) + ' atribut siap analisis');
                setCardIcon('cardIcon4', 'bi-shield-check', '#DCFCE7', '#16A34A');
            }
        }

        // ── 3. Notice Bar ──
        const noticeBar  = document.getElementById('noticeValidationBar');
        const noticeText = document.getElementById('noticeValidationText');
        if (isDeath && data.total_cases > 0) {
            if (noticeBar)  noticeBar.style.display = 'flex';
            if (noticeText) noticeText.innerHTML = '<strong>Validasi Dataset Terverifikasi:</strong> Total ' + formatNumberId(data.total_cases) + ' kasus pada ' + (data.province_rows?.length || '—') + ' wilayah selaras dengan sumber data aktif.';
        } else {
            if (noticeBar) noticeBar.style.display = 'none';
        }

        // ── 4. Judul Tabel ──
        setText('tableMainTitle',    dashCfg.table_title    || (isDeath ? 'Rincian Mortalitas' : 'Rincian Data'));
        setText('tableMainSubtitle', dashCfg.table_subtitle || 'Data lengkap dari dataset aktif');

        // ── 5. Render Grafik Adaptif ──
        renderAdaptiveCharts(data);

        // ── 6. Render Tabel Adaptif ──
        renderTableAdaptive(data);
    }

    // ── Helper: setText / setCardIcon ──
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '—';
    }
    function setCardIcon(id, iconClass, bgColor, textColor) {
        const el = document.getElementById(id);
        if (!el) return;
        // Mapping warna ke gradient cantik — tidak pakai icon
        const gradientMap = {
            '#FEE2E2': 'linear-gradient(135deg,#FF6B6B 0%,#DC2626 100%)',
            '#E0F2FE': 'linear-gradient(135deg,#38BDF8 0%,#0284C7 100%)',
            '#FEF3C7': 'linear-gradient(135deg,#FCD34D 0%,#D97706 100%)',
            '#DCFCE7': 'linear-gradient(135deg,#4ADE80 0%,#16A34A 100%)',
            '#EFF6FF': 'linear-gradient(135deg,#93C5FD 0%,#2563EB 100%)',
        };
        // textColor juga bisa sebagai patokan gradient
        const colorMap = {
            '#DC2626': 'linear-gradient(135deg,#FF6B6B 0%,#DC2626 100%)',
            '#0284C7': 'linear-gradient(135deg,#38BDF8 0%,#0284C7 100%)',
            '#D97706': 'linear-gradient(135deg,#FCD34D 0%,#D97706 100%)',
            '#16A34A': 'linear-gradient(135deg,#4ADE80 0%,#16A34A 100%)',
            '#2563EB': 'linear-gradient(135deg,#93C5FD 0%,#2563EB 100%)',
        };
        const gradient = gradientMap[bgColor] || colorMap[textColor] || 'linear-gradient(135deg,#6B7280 0%,#374151 100%)';
        el.style.background = gradient;
        el.style.border = 'none';
        el.innerHTML = ''; // Tidak ada icon, hanya lingkaran warna
    }

    // ── renderAdaptiveCharts: tentukan grafik mana yang ditampilkan ──
    function renderAdaptiveCharts(data) {
        const isDeath = data.is_death_analysis;
        const dashCfg = data.dashboard_config || {};
        const charts  = dashCfg.charts || {};

        // Grafik 1 — Distribusi utama
        renderChartCauses(data);

        // Grafik 2 — Top entitas
        renderChartTopProvinces(data);

        // Grafik Tren (hanya jika ada data tren)
        const trendSection = document.getElementById('chartTrendSection');
        if (data.trend && data.trend.length > 1) {
            if (trendSection) trendSection.style.display = '';
            renderChartTrend(data);
        } else {
            if (trendSection) trendSection.style.display = 'none';
        }

        // Grafik Gender & Usia (hanya jika ada data)
        const gaSection = document.getElementById('chartGenderAgeSection');
        const hasGender = data.gender_dist && data.gender_dist.length > 0;
        const hasAge    = data.age_dist    && data.age_dist.length    > 0;
        if (hasGender || hasAge) {
            if (gaSection) gaSection.style.display = '';
            if (hasGender) renderChartGender(data);
            if (hasAge)    renderChartAge(data);
        } else {
            if (gaSection) gaSection.style.display = 'none';
        }

        // Grafik Perbandingan Antarprovinsi (hanya untuk mortalitas)
        const compSection = document.getElementById('chartComparisonSection');
        if (isDeath && data.province_rows && data.province_rows.length > 0) {
            if (compSection) compSection.style.display = '';
            renderChartComparison(data);
        } else {
            if (compSection) compSection.style.display = 'none';
        }
    }

    // Grafik 1 — Distribusi Utama (kategori / penyebab / diagnosis)
    function renderChartCauses(data) {
        const ctx = document.getElementById('chartCauses');
        if (!ctx) return;

        const isDeath = data.is_death_analysis;
        let categories = [];
        let chartLabel = 'Total';

        if (isDeath && data.death_categories && data.death_categories.length > 0) {
            categories = data.death_categories.map(c => ({ label: c.kategori, total: c.total }));
            chartLabel = 'Jumlah Kematian';
            setText('chart1Title',  'Distribusi Kematian Menurut Penyebab');
            setText('chart1Subtitle', 'Perbandingan kategori penyebab utama mortalitas');
            const badgeEl = document.getElementById('chart1Badge');
            if (badgeEl) badgeEl.textContent = categories.length + ' Kategori';
        } else if (data.top_diagnosis && data.top_diagnosis.length > 0) {
            categories = data.top_diagnosis.map(d => ({ label: d.label, total: d.total }));
            chartLabel = data.measure_label || 'Total';
            const dsType = data.dataset_type || 'umum';
            const isWilayah = dsType === 'spasial' || dsType === 'epidemiologi' || (data.profiling_meta?.detected_roles?.wilayah);
            const titlePrefix = isWilayah ? 'Distribusi Kasus per Wilayah' : 'Distribusi Data Teratas';
            const subPrefix = isWilayah ? 'Peringkat wilayah dengan akumulasi data tertinggi' : 'Top ' + categories.length + ' kategori berdasarkan nilai tertinggi';
            setText('chart1Title', titlePrefix);
            setText('chart1Subtitle', subPrefix);
            const badgeEl = document.getElementById('chart1Badge');
            if (badgeEl) badgeEl.textContent = categories.length + (isWilayah ? ' Wilayah' : ' Kategori');
        }

        if (!categories.length) {
            ctx.closest('.figma-card')?.closest('.col-12')?.closest('.row')?.querySelectorAll('.col-12')[0]?.style.setProperty('display','none');
            return;
        }

        categories.sort((a,b) => b.total - a.total);
        const labels = categories.map(c => c.label);
        const values = categories.map(c => c.total);
        const total  = values.reduce((a, b) => a + b, 0);

        activeCharts['causes'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: chartLabel,
                    data: values,
                    backgroundColor: [COLOR_CORAL, '#F97316', COLOR_BLUE, COLOR_TEAL, COLOR_GRAY, COLOR_PURPLE, '#84CC16', '#EC4899'],
                    borderRadius: 6,
                    barThickness: 22
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const v   = ctx.parsed.x;
                                const pct = total > 0 ? ((v / total) * 100).toFixed(2) : '0';
                                return ' ' + formatNumberId(v) + ' (' + pct + '%)';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: function(v) {
                                if (v >= 1000000) return (v/1000000).toFixed(1) + ' jt';
                                if (v >= 1000)    return (v/1000).toFixed(0) + ' rb';
                                return v;
                            }
                        }
                    },
                    y: { grid: { display: false }, ticks: { font: { size: 12, weight: '600' } } }
                }
            }
        });
    }

    // Grafik 2 — Top Entitas / Wilayah
    function renderChartTopProvinces(data) {
        const ctx = document.getElementById('chartTopProvinces');
        if (!ctx) return;

        const isDeath  = data.is_death_analysis;
        const dashCfg  = data.dashboard_config || {};
        let items = [];
        let valueKey = 'jumlah';

        if (isDeath && data.province_rows && data.province_rows.length > 0) {
            items = [...data.province_rows].sort((a,b) => b.jumlah - a.jumlah).slice(0, 5);
            valueKey = 'jumlah';
            setText('chart2Title', 'Lima Wilayah Kematian Terbanyak');
            setText('chart2Subtitle', 'Peringkat 5 wilayah tertinggi berdasarkan jumlah kematian');
        } else if (data.top_unit && data.top_unit.length > 0) {
            items = data.top_unit.slice(0, 5).map(u => ({ label: u.unit_pelayanan, jumlah: u.total }));
            const dsType = data.dataset_type || 'umum';
            const c2Title = (dsType === 'epidemiologi') ? 'Komposisi Jenis Penyakit Terbanyak' : 'Top Unit / Kategori Data';
            const c2Sub   = (dsType === 'epidemiologi') ? 'Lima jenis penyakit dengan prevalensi/kasus tertinggi' : 'Lima unit/kategori dengan jumlah terbanyak';
            setText('chart2Title', c2Title);
            setText('chart2Subtitle', c2Sub);
        } else if (data.top_diagnosis && data.top_diagnosis.length > 0) {
            items = data.top_diagnosis.slice(0, 5).map(d => ({ label: d.label, jumlah: d.total }));
            setText('chart2Title', 'Top Kategori Data');
            setText('chart2Subtitle', 'Lima kategori dengan nilai tertinggi');
        }

        if (!items.length) {
            document.getElementById('chartCol2')?.style.setProperty('display','none');
            return;
        }

        const labels = items.map(p => p.label || p.provinsi || '—');
        const values = items.map(p => p.jumlah || 0);
        const chartLabel = data.measure_label || 'Total';
        const badge2 = document.getElementById('chart2Badge');
        if (badge2) badge2.textContent = 'Top ' + items.length;

        activeCharts['topProv'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: chartLabel,
                    data: values,
                    backgroundColor: isDeath ? '#E11D48' : COLOR_TEAL,
                    borderRadius: 6,
                    barThickness: 22
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => ' ' + formatNumberId(ctx.parsed.x) }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: function(v) {
                                if (v >= 1000000) return (v/1000000).toFixed(1) + ' jt';
                                if (v >= 1000)    return (v/1000).toFixed(0) + ' rb';
                                return v;
                            }
                        }
                    },
                    y: { grid: { display: false }, ticks: { font: { size: 12, weight: '600' } } }
                }
            }
        });
    }

    // Grafik Tren Waktu
    function renderChartTrend(data) {
        const ctx = document.getElementById('chartTrend');
        if (!ctx || !data.trend || !data.trend.length) return;

        const labels = data.trend.map(t => t.bulan);
        const values = data.trend.map(t => t.total);
        const mlabel = data.measure_label || 'Total';

        setText('chartTrendTitle',    data.dashboard_config?.charts?.trend?.label || 'Tren ' + mlabel + ' per Periode');
        setText('chartTrendSubtitle', 'Perubahan nilai ' + mlabel.toLowerCase() + ' dari waktu ke waktu');

        activeCharts['trend'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: mlabel,
                    data: values,
                    borderColor: COLOR_CORAL,
                    backgroundColor: 'rgba(240,68,50,0.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => ' ' + formatNumberId(ctx.parsed.y) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: v => {
                                if (v >= 1000000) return (v/1000000).toFixed(1) + ' jt';
                                if (v >= 1000)    return (v/1000).toFixed(0) + ' rb';
                                return v;
                            }
                        }
                    }
                }
            }
        });
    }

    // Grafik Distribusi Gender
    function renderChartGender(data) {
        const ctx = document.getElementById('chartGender');
        if (!ctx || !data.gender_dist || !data.gender_dist.length) return;

        const labels = data.gender_dist.map(g => g.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');
        const values = data.gender_dist.map(g => g.total);

        activeCharts['gender'] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [COLOR_BLUE, '#EC4899'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                const pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : '0';
                                return ' ' + formatNumberId(ctx.parsed) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Grafik Distribusi Kelompok Usia
    function renderChartAge(data) {
        const ctx = document.getElementById('chartAge');
        if (!ctx || !data.age_dist || !data.age_dist.length) return;

        const labels = data.age_dist.map(a => a.kelompok_usia);
        const values = data.age_dist.map(a => a.total);

        activeCharts['age'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Jumlah',
                    data: values,
                    backgroundColor: COLOR_TEAL,
                    borderRadius: 5,
                    barThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => ' ' + formatNumberId(ctx.parsed.y) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: '#F1F5F9' } }
                }
            }
        });
    }

    // Grafik Perbandingan Antarprovinsi (mortalitas)
    function renderChartComparison(data) {
        const ctx = document.getElementById('chartComparison');
        if (!ctx) return;

        let provs = data.province_rows ? [...data.province_rows] : [];
        if (!provs.length) return;

        const sliceVal = document.getElementById('compareSliceFilter')?.value || '10';
        const catVal   = document.getElementById('compareCategoryFilter')?.value || 'all';

        let displayed = provs;
        if (sliceVal === '10')      displayed = provs.slice(0, 10);
        else if (sliceVal === '20') displayed = provs.slice(0, 20);

        const labels = displayed.map(p => p.provinsi);
        let datasets = [];

        if (catVal === 'all') {
            datasets = [
                { label: 'Penyakit Tidak Menular', data: displayed.map(p => p.penyakit_tidak_menular), backgroundColor: COLOR_CORAL,  stack: 'Stack 0' },
                { label: 'Lainnya',                data: displayed.map(p => p.lainnya),                backgroundColor: '#F97316',    stack: 'Stack 0' },
                { label: 'Penyakit Menular',       data: displayed.map(p => p.penyakit_menular),       backgroundColor: COLOR_BLUE,   stack: 'Stack 0' },
                { label: 'Kecelakaan Lalu Lintas', data: displayed.map(p => p.kecelakaan_lalu_lintas), backgroundColor: COLOR_TEAL,   stack: 'Stack 0' },
                { label: 'Kecelakaan Lainnya',     data: displayed.map(p => p.kecelakaan_lainnya),     backgroundColor: COLOR_GRAY,   stack: 'Stack 0' }
            ];
        } else {
            const fieldMap = {
                'ptm': { name: 'Penyakit Tidak Menular', color: COLOR_CORAL, key: 'penyakit_tidak_menular' },
                'pm':  { name: 'Penyakit Menular',       color: COLOR_BLUE,  key: 'penyakit_menular' },
                'kll': { name: 'Kecelakaan Lalu Lintas', color: COLOR_TEAL,  key: 'kecelakaan_lalu_lintas' },
                'kl':  { name: 'Kecelakaan Lainnya',     color: COLOR_GRAY,  key: 'kecelakaan_lainnya' },
                'l':   { name: 'Lainnya',                color: '#F97316',   key: 'lainnya' }
            };
            const f = fieldMap[catVal] || fieldMap['ptm'];
            datasets = [{ label: f.name, data: displayed.map(p => p[f.key] || 0), backgroundColor: f.color, borderRadius: 4 }];
        }

        activeCharts['compare'] = new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, padding: 14 } },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: { label: ctx => ' ' + ctx.dataset.label + ': ' + formatNumberId(ctx.parsed.y) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: v => {
                                if (v >= 1000000) return (v/1000000).toFixed(1) + ' jt';
                                if (v >= 1000)    return (v/1000).toFixed(0) + ' rb';
                                return v;
                            }
                        }
                    }
                }
            }
        });
    }

    // ── Tabel Adaptif (mortalitas = multi kolom, lainnya = top entitas) ──
    function renderTableAdaptive(data) {
        const thead = document.getElementById('tableHead');
        const tbody = document.getElementById('tableBodyProvinces');
        const tfoot = document.getElementById('tableFootProvinces');
        if (!tbody) return;

        const isDeath  = data.is_death_analysis;

        if (isDeath && data.province_rows && data.province_rows.length > 0) {
            renderTableProvinces(data);
            return;
        }

        // Tabel umum: tampilkan top_diagnosis / top_unit / raw_file_data
        const rows = data.top_diagnosis || data.top_unit || [];
        if (!rows.length && (!data.raw_file_data || !data.raw_file_data.sample_rows?.length)) {
            if (tbody) tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:24px;color:#9CA3AF;">Tidak ada data untuk ditampilkan.</td></tr>';
            return;
        }

        tableCurrentRows = rows;
        const totalAll = data.total_cases || rows.reduce((sum, r) => sum + (r.total || 0), 0);
        const labelCol = data.dashboard_config?.charts?.distribution?.label || 'Kategori';
        const valueCol = data.measure_label || 'Total';

        // Render thead
        if (thead) {
            thead.innerHTML = `<tr>
                <th style="width:40px;text-align:center;">#</th>
                <th>${labelCol}</th>
                <th style="text-align:right;">${valueCol}</th>
                <th style="width:160px;">Proporsi</th>
            </tr>`;
        }

        let html = '';
        rows.forEach((r, idx) => {
            const val  = r.total || 0;
            const prop = totalAll > 0 ? ((val / totalAll) * 100).toFixed(2) : '0';
            const name = r.label || r.unit_pelayanan || r.provinsi || '—';
            html += `<tr>
                <td style="text-align:center;color:#9CA3AF;font-weight:600;font-size:11.5px;">${idx + 1}</td>
                <td style="font-weight:700;color:var(--navy);">${name}</td>
                <td style="text-align:right;">${formatNumberId(val)}</td>
                <td>
                    <div class="prop-bar-container">
                        <div class="prop-bar-track">
                            <div class="prop-bar-fill" style="width:${Math.min(100, Math.max(2, parseFloat(prop) * 4))}%;"></div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:#0D9488;min-width:42px;text-align:right;">${prop}%</span>
                    </div>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
        if (tfoot) tfoot.innerHTML = '';

        const countEl = document.getElementById('visibleRowCount');
        if (countEl) countEl.textContent = rows.length;
    }

    // Tabel Rincian Provinsi (mortalitas)
    function renderTableProvinces(data) {
        const thead = document.getElementById('tableHead');
        const tbody = document.getElementById('tableBodyProvinces');
        const tfoot = document.getElementById('tableFootProvinces');
        if (!tbody || !tfoot) return;

        // Render thead mortalitas
        if (thead) {
            thead.innerHTML = `<tr>
                <th style="width:40px;text-align:center;">#</th>
                <th style="cursor:pointer;" onclick="sortTable(1)">Provinsi <i class="bi bi-arrow-down-up" style="font-size:10px;"></i></th>
                <th style="text-align:right;cursor:pointer;" onclick="sortTable(2)">Penyakit Menular</th>
                <th style="text-align:right;cursor:pointer;" onclick="sortTable(3)">Penyakit Tidak Menular</th>
                <th style="text-align:right;cursor:pointer;" onclick="sortTable(4)">Kec. Lalu Lintas</th>
                <th style="text-align:right;cursor:pointer;" onclick="sortTable(5)">Kec. Lainnya</th>
                <th style="text-align:right;cursor:pointer;" onclick="sortTable(6)">Lainnya</th>
                <th style="text-align:right;cursor:pointer;" onclick="sortTable(7)">Jumlah</th>
                <th style="width:170px;">Proporsi Total</th>
            </tr>`;
        }

        let provs = data.province_rows ? [...data.province_rows] : [];
        if (!provs.length && data.top_diagnosis) {
            provs = data.top_diagnosis.map(d => ({
                provinsi: d.label,
                penyakit_menular: 0, penyakit_tidak_menular: 0,
                kecelakaan_lalu_lintas: 0, kecelakaan_lainnya: 0,
                lainnya: 0, jumlah: d.total
            }));
        }

        tableCurrentRows = provs;
        const totalAll = data.total_cases || provs.reduce((sum, p) => sum + p.jumlah, 0);

        let html = '';
        let sumPm = 0, sumPtm = 0, sumKll = 0, sumKl = 0, sumL = 0, sumJml = 0;

        provs.forEach((p, idx) => {
            sumPm  += p.penyakit_menular;
            sumPtm += p.penyakit_tidak_menular;
            sumKll += p.kecelakaan_lalu_lintas;
            sumKl  += p.kecelakaan_lainnya;
            sumL   += p.lainnya;
            sumJml += p.jumlah;

            const prop = totalAll > 0 ? ((p.jumlah / totalAll) * 100).toFixed(2) : '0';

            html += `<tr>
                <td style="text-align:center;color:#9CA3AF;font-weight:600;font-size:11.5px;">${idx + 1}</td>
                <td style="font-weight:700;color:var(--navy);">${p.provinsi}</td>
                <td style="text-align:right;">${formatNumberId(p.penyakit_menular)}</td>
                <td style="text-align:right;font-weight:600;color:var(--coral);">${formatNumberId(p.penyakit_tidak_menular)}</td>
                <td style="text-align:right;">${formatNumberId(p.kecelakaan_lalu_lintas)}</td>
                <td style="text-align:right;">${formatNumberId(p.kecelakaan_lainnya)}</td>
                <td style="text-align:right;">${formatNumberId(p.lainnya)}</td>
                <td style="text-align:right;font-weight:800;color:var(--navy);">${formatNumberId(p.jumlah)}</td>
                <td>
                    <div class="prop-bar-container">
                        <div class="prop-bar-track">
                            <div class="prop-bar-fill" style="width:${Math.min(100, Math.max(2, parseFloat(prop) * 4))}%;"></div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:#0D9488;min-width:42px;text-align:right;">${prop}%</span>
                    </div>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html;

        tfoot.innerHTML = `<tr>
            <td colspan="2" style="font-size:13px;">TOTAL SELURUH PROVINSI</td>
            <td style="text-align:right;">${formatNumberId(sumPm)}</td>
            <td style="text-align:right;color:var(--coral);">${formatNumberId(sumPtm)}</td>
            <td style="text-align:right;">${formatNumberId(sumKll)}</td>
            <td style="text-align:right;">${formatNumberId(sumKl)}</td>
            <td style="text-align:right;">${formatNumberId(sumL)}</td>
            <td style="text-align:right;color:var(--navy);font-size:14px;">${formatNumberId(sumJml)}</td>
            <td><strong>100%</strong></td>
        </tr>`;

        const countEl = document.getElementById('visibleRowCount');
        if (countEl) countEl.textContent = provs.length;
    }

    // Pencarian Tabel Provinsi
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#tableBodyProvinces tr');
            let visible = 0;
            rows.forEach(r => {
                const provName = r.children[1]?.textContent.toLowerCase() || '';
                if (provName.includes(query)) {
                    r.style.display = '';
                    visible++;
                } else {
                    r.style.display = 'none';
                }
            });
            const countEl = document.getElementById('visibleRowCount');
            if (countEl) countEl.textContent = visible;
        });
    }

    // Event listener perbandingan filter
    ['compareCategoryFilter', 'compareSliceFilter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => {
                if (globalDataCache) {
                    if (activeCharts['compare']) activeCharts['compare'].destroy();
                    renderChartComparison(globalDataCache);
                }
            });
        }
    });

    // Dataset Selector Event
    const dsSel = document.getElementById('datasetSelect');
    if (dsSel) {
        dsSel.addEventListener('change', function() {
            const dsId = this.value;
            const btnDetail = document.getElementById('btnDetailDataset');
            if (btnDetail) {
                btnDetail.href = '<?= site_url("datasets/preview/") ?>' + dsId;
            }
            loadDashboardData(dsId);
        });
        // Initial load
        if (dsSel.value) {
            loadDashboardData(dsSel.value);
        }
    }

    // Ekspor CSV Adaptif
    window.exportTableToCsv = function() {
        if (!tableCurrentRows || !tableCurrentRows.length) {
            if (window.showNotification) window.showNotification('Data tabel tidak tersedia untuk diekspor.', 'warning');
            return;
        }

        const isDeath = globalDataCache && globalDataCache.is_death_analysis;
        let csv = '';
        let blob, filename;

        if (isDeath) {
            csv = 'Provinsi,Penyakit Menular,Penyakit Tidak Menular,Kecelakaan Lalu Lintas,Kecelakaan Lainnya,Lainnya,Jumlah,Proporsi (%)\n';
            const total = tableCurrentRows.reduce((a,b) => a + b.jumlah, 0);
            tableCurrentRows.forEach(r => {
                const prop = total > 0 ? ((r.jumlah / total) * 100).toFixed(2) : '0';
                csv += `"${r.provinsi}",${r.penyakit_menular},${r.penyakit_tidak_menular},${r.kecelakaan_lalu_lintas},${r.kecelakaan_lainnya},${r.lainnya},${r.jumlah},${prop}\n`;
            });
            filename = `polara_mortalitas_${new Date().toISOString().slice(0,10)}.csv`;
        } else {
            const dsName = (globalDataCache?.dataset?.nama_dataset || 'data').replace(/[^a-z0-9]/gi,'_').toLowerCase();
            csv = 'Kategori,Total,Proporsi (%)\n';
            const total = tableCurrentRows.reduce((a,b) => a + (b.total || 0), 0);
            tableCurrentRows.forEach(r => {
                const val  = r.total || r.jumlah || 0;
                const prop = total > 0 ? ((val / total) * 100).toFixed(2) : '0';
                const name = r.label || r.unit_pelayanan || r.provinsi || '—';
                csv += `"${name}",${val},${prop}\n`;
            });
            filename = `polara_${dsName}_${new Date().toISOString().slice(0,10)}.csv`;
        }

        blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a   = document.createElement('a');
        a.href    = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        if (window.showNotification) window.showNotification('Data berhasil diekspor ke format CSV.', 'success');
    };

    window.exportActiveReport = function() {
        window.location.href = '<?= site_url("laporan") ?>';
    };

})();
</script>
<?= $this->endSection() ?>
