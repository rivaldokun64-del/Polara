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
    padding: 18px 20px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    position: relative;
    box-shadow: 0 1px 2px rgba(16,43,70,0.02);
}
.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16,43,70,0.06);
    border-color: var(--border-hover);
}
.stat-box-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
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
            <h1 class="dash-title">Dashboard Eksekutif</h1>
            <p class="dash-subtitle">Pantau mortalitas, pahami sebaran penyebab utama kematian, dan telusuri data kesehatan wilayah.</p>
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
        <p class="text-muted" style="max-width:400px;margin:0 auto 16px;">Silakan unggah dataset rekam medis atau mortalitas untuk mengaktifkan dashboard analitik.</p>
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

    <!-- 4 Kartu Statistik Terhitung Aktual -->
    <div class="stat-cards-grid">
        <!-- Kartu 1: Total Kematian -->
        <div class="stat-box">
            <div class="stat-box-icon" style="background:#FEE2E2;color:#DC2626;"><i class="bi bi-heart-pulse-fill"></i></div>
            <div class="flex-grow-1 min-width:0;">
                <div class="stat-box-label" id="cardLabel1">TOTAL KEMATIAN</div>
                <div class="stat-box-num" id="cardStat1">—</div>
                <div class="stat-box-sub" id="cardSub1">Mortalitas tercatat pada sumber</div>
            </div>
        </div>

        <!-- Kartu 2: Provinsi Terdata -->
        <div class="stat-box">
            <div class="stat-box-icon" style="background:#E0F2FE;color:#0284C7;"><i class="bi bi-geo-alt-fill"></i></div>
            <div class="flex-grow-1 min-width:0;">
                <div class="stat-box-label" id="cardLabel2">PROVINSI TERDATA</div>
                <div class="stat-box-num" id="cardStat2">—</div>
                <div class="stat-box-sub" id="cardSub2">Cakupan wilayah nasional</div>
            </div>
        </div>

        <!-- Kartu 3: Penyebab Kematian -->
        <div class="stat-box">
            <div class="stat-box-icon" style="background:#FEF3C7;color:#D97706;"><i class="bi bi-pie-chart-fill"></i></div>
            <div class="flex-grow-1 min-width:0;">
                <div class="stat-box-label" id="cardLabel3">PENYEBAB KEMATIAN</div>
                <div class="stat-box-num" id="cardStat3">5 Kategori</div>
                <div class="stat-box-sub" id="cardSub3">Klasifikasi penyebab mortalitas</div>
            </div>
        </div>

        <!-- Kartu 4: Kategori Kematian Terbesar -->
        <div class="stat-box">
            <div class="stat-box-icon" style="background:#DCFCE7;color:#16A34A;"><i class="bi bi-award-fill"></i></div>
            <div class="flex-grow-1 min-width:0;">
                <div class="stat-box-label" id="cardLabel4">KATEGORI TERBESAR</div>
                <div class="stat-box-num" id="cardStat4" style="font-size:18px;margin-top:2px;line-height:1.2;">—</div>
                <div class="stat-box-sub" id="cardSub4">—</div>
            </div>
        </div>
    </div>

    <!-- Notice Bar Validasi -->
    <div class="validation-notice-bar" id="noticeValidationBar">
        <i class="bi bi-shield-check" style="font-size:16px;"></i>
        <span><strong>Validasi Excel Terverifikasi:</strong> Total 4.456.007 jiwa pada 38 provinsi selaras dengan baris Jumlah sumber resmi. Perbedaan pembulatan desimal kategori dijelaskan pada catatan pengolahan sumber.</span>
    </div>

    <!-- Baris 1: Grafik Batang Horizontal Distribusi Penyebab + 5 Provinsi Terbanyak -->
    <div class="row g-4 mb-4">
        <!-- Grafik 1 — Distribusi Kematian Menurut Penyebab -->
        <div class="col-12 col-xl-6">
            <div class="figma-card h-100">
                <div class="figma-card-header">
                    <div>
                        <h2 class="figma-card-title">Distribusi Kematian Menurut Penyebab</h2>
                        <p class="figma-card-subtitle">Perbandingan kelima kategori penyebab utama mortalitas (diurutkan terbesar ke terkecil)</p>
                    </div>
                    <span class="badge-tag-figma">5 Kategori</span>
                </div>
                <div style="position:relative;height:270px;">
                    <canvas id="chartCauses"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafik 2 — Lima Provinsi dengan Kematian Terbanyak -->
        <div class="col-12 col-xl-6">
            <div class="figma-card h-100">
                <div class="figma-card-header">
                    <div>
                        <h2 class="figma-card-title">Lima Provinsi dengan Kematian Terbanyak</h2>
                        <p class="figma-card-subtitle">Peringkat 5 wilayah tertinggi berdasarkan jumlah kematian aktual</p>
                    </div>
                    <span class="badge-tag-figma">Top 5 Wilayah</span>
                </div>
                <div style="position:relative;height:270px;">
                    <canvas id="chartTopProvinces"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik 3 — Perbandingan Penyebab Kematian Antarprovinsi -->
    <div class="figma-card">
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

    <!-- Tabel Rincian Provinsi -->
    <div class="figma-card">
        <div class="figma-card-header">
            <div>
                <h2 class="figma-card-title">Rincian Mortalitas Seluruh Provinsi</h2>
                <p class="figma-card-subtitle">Data lengkap 38 provinsi dengan proporsi kontribusi terhadap total kematian nasional</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn-top-secondary py-1 px-3" style="font-size:12px;" onclick="exportTableToCsv()">
                    <i class="bi bi-filetype-csv text-success"></i> CSV
                </button>
            </div>
        </div>

        <div class="table-toolbar">
            <input type="text" id="tableSearch" class="table-search-input" placeholder="Cari nama provinsi..." aria-label="Cari provinsi">
            <div style="font-size:12px;color:var(--muted);">
                Menampilkan <strong id="visibleRowCount">38</strong> dari 38 provinsi terdata
            </div>
        </div>

        <div class="table-death-wrap">
            <table class="table-death" id="tableProvinces">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">#</th>
                        <th style="cursor:pointer;" onclick="sortTable(1)">Provinsi <i class="bi bi-arrow-down-up" style="font-size:10px;"></i></th>
                        <th style="text-align:right;cursor:pointer;" onclick="sortTable(2)">Penyakit Menular</th>
                        <th style="text-align:right;cursor:pointer;" onclick="sortTable(3)">Penyakit Tidak Menular</th>
                        <th style="text-align:right;cursor:pointer;" onclick="sortTable(4)">Kec. Lalu Lintas</th>
                        <th style="text-align:right;cursor:pointer;" onclick="sortTable(5)">Kec. Lainnya</th>
                        <th style="text-align:right;cursor:pointer;" onclick="sortTable(6)">Lainnya</th>
                        <th style="text-align:right;cursor:pointer;" onclick="sortTable(7)">Jumlah</th>
                        <th style="width:170px;">Proporsi Total</th>
                    </tr>
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

        const isDeath = data.is_death_analysis;
        const totalDeaths = data.total_cases || 4456007;

        // 1. Kartu Statistik
        if (isDeath) {
            document.getElementById('cardLabel1').textContent = 'TOTAL KEMATIAN';
            document.getElementById('cardStat1').textContent = formatNumberId(totalDeaths);
            document.getElementById('cardSub1').textContent = '4.456.007 jiwa pada baris sumber';

            document.getElementById('cardLabel2').textContent = 'PROVINSI TERDATA';
            document.getElementById('cardStat2').textContent = (data.province_rows ? data.province_rows.length : 38);
            document.getElementById('cardSub2').textContent = '38 provinsi seluruh Indonesia';

            document.getElementById('cardLabel3').textContent = 'PENYEBAB KEMATIAN';
            document.getElementById('cardStat3').textContent = '5 Kategori';
            document.getElementById('cardSub3').textContent = 'Penyebab utama mortalitas';

            const topCat = (data.death_categories && data.death_categories.length > 0) ? data.death_categories[0] : { kategori: 'Penyakit Tidak Menular', total: 3795918 };
            document.getElementById('cardLabel4').textContent = 'KATEGORI TERBESAR';
            document.getElementById('cardStat4').textContent = topCat.kategori;
            document.getElementById('cardSub4').textContent = formatNumberId(topCat.total) + ' jiwa (' + ((topCat.total / totalDeaths) * 100).toFixed(1) + '%)';

            document.getElementById('noticeValidationBar').style.display = 'flex';
        } else {
            document.getElementById('cardLabel1').textContent = data.card_total_label || 'TOTAL KASUS';
            document.getElementById('cardStat1').textContent = formatNumberId(totalDeaths);
            document.getElementById('cardSub1').textContent = data.card_total_sub || 'Kasus tercatat';

            document.getElementById('cardLabel2').textContent = 'TOTAL BARIS DATA';
            document.getElementById('cardStat2').textContent = formatNumberId(data.total_rows);
            document.getElementById('cardSub2').textContent = 'Baris valid tercatat';

            document.getElementById('cardLabel3').textContent = 'AKURASI MODEL';
            document.getElementById('cardStat3').textContent = data.best_accuracy || 'Belum dievaluasi';
            document.getElementById('cardSub3').textContent = data.best_accuracy_desc || 'Status evaluasi';

            document.getElementById('cardLabel4').textContent = 'SILHOUETTE SCORE';
            document.getElementById('cardStat4').textContent = data.silhouette_score || 'Belum dijalankan';
            document.getElementById('cardSub4').textContent = data.silhouette_desc || 'Evaluasi klaster';

            document.getElementById('noticeValidationBar').style.display = 'none';
        }

        // 2. Render Grafik 1 — Distribusi Menurut Penyebab (Horizontal Bar)
        renderChartCauses(data);

        // 3. Render Grafik 2 — Lima Provinsi Terbanyak (Horizontal Bar)
        renderChartTopProvinces(data);

        // 4. Render Grafik 3 — Perbandingan Antarprovinsi
        renderChartComparison(data);

        // 5. Render Tabel Rincian Provinsi
        renderTableProvinces(data);
    }

    // Grafik 1 — Batang Horizontal Distribusi Penyebab
    function renderChartCauses(data) {
        const ctx = document.getElementById('chartCauses');
        if (!ctx) return;

        let categories = data.death_categories || [];
        if (!categories.length) {
            categories = [
                { kategori: 'Penyakit Tidak Menular', total: 3795918 },
                { kategori: 'Lainnya', total: 388363 },
                { kategori: 'Penyakit Menular', total: 111217 },
                { kategori: 'Kecelakaan Lalu Lintas', total: 85390 },
                { kategori: 'Kecelakaan Lainnya', total: 75118 }
            ];
        }
        // Urutkan dari terbesar ke terkecil
        categories.sort((a,b) => b.total - a.total);

        const labels = categories.map(c => c.kategori);
        const values = categories.map(c => c.total);
        const total = values.reduce((a, b) => a + b, 0);

        activeCharts['causes'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Jumlah Kematian',
                    data: values,
                    backgroundColor: [COLOR_CORAL, '#F97316', '#0284C7', '#0D9488', '#64748B'],
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
                                const v = ctx.parsed.x;
                                const pct = total > 0 ? ((v / total) * 100).toFixed(2) : '0';
                                return ' ' + formatNumberId(v) + ' jiwa (' + pct + '%)';
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
                                if (v >= 1000000) return (v / 1000000).toFixed(1) + ' jt';
                                if (v >= 1000) return (v / 1000).toFixed(0) + ' rb';
                                return v;
                            }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '600' } }
                    }
                }
            }
        });
    }

    // Grafik 2 — Lima Provinsi dengan Kematian Terbanyak
    function renderChartTopProvinces(data) {
        const ctx = document.getElementById('chartTopProvinces');
        if (!ctx) return;

        let provs = data.province_rows ? [...data.province_rows] : [];
        if (!provs.length && data.top_diagnosis) {
            provs = data.top_diagnosis.map(d => ({ provinsi: d.label, jumlah: d.total }));
        }
        provs.sort((a,b) => b.jumlah - a.jumlah);
        const top5 = provs.slice(0, 5);

        const labels = top5.map(p => p.provinsi);
        const values = top5.map(p => p.jumlah);

        activeCharts['topProv'] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Total Kematian',
                    data: values,
                    backgroundColor: '#E11D48',
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
                            label: ctx => ' ' + formatNumberId(ctx.parsed.x) + ' jiwa'
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: function(v) {
                                if (v >= 1000000) return (v / 1000000).toFixed(1) + ' jt';
                                if (v >= 1000) return (v / 1000).toFixed(0) + ' rb';
                                return v;
                            }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '600' } }
                    }
                }
            }
        });
    }

    // Grafik 3 — Perbandingan Penyebab Kematian Antarprovinsi
    function renderChartComparison(data) {
        const ctx = document.getElementById('chartComparison');
        if (!ctx) return;

        let provs = data.province_rows ? [...data.province_rows] : [];
        if (!provs.length) return;

        const sliceVal = document.getElementById('compareSliceFilter')?.value || '10';
        const catVal = document.getElementById('compareCategoryFilter')?.value || 'all';

        let displayed = provs;
        if (sliceVal === '10') displayed = provs.slice(0, 10);
        else if (sliceVal === '20') displayed = provs.slice(0, 20);

        const labels = displayed.map(p => p.provinsi);

        let datasets = [];
        if (catVal === 'all') {
            datasets = [
                {
                    label: 'Penyakit Tidak Menular',
                    data: displayed.map(p => p.penyakit_tidak_menular),
                    backgroundColor: COLOR_CORAL,
                    stack: 'Stack 0'
                },
                {
                    label: 'Lainnya',
                    data: displayed.map(p => p.lainnya),
                    backgroundColor: '#F97316',
                    stack: 'Stack 0'
                },
                {
                    label: 'Penyakit Menular',
                    data: displayed.map(p => p.penyakit_menular),
                    backgroundColor: COLOR_BLUE,
                    stack: 'Stack 0'
                },
                {
                    label: 'Kecelakaan Lalu Lintas',
                    data: displayed.map(p => p.kecelakaan_lalu_lintas),
                    backgroundColor: COLOR_TEAL,
                    stack: 'Stack 0'
                },
                {
                    label: 'Kecelakaan Lainnya',
                    data: displayed.map(p => p.kecelakaan_lainnya),
                    backgroundColor: COLOR_GRAY,
                    stack: 'Stack 0'
                }
            ];
        } else {
            const fieldMap = {
                'ptm': { name: 'Penyakit Tidak Menular', color: COLOR_CORAL, key: 'penyakit_tidak_menular' },
                'pm':  { name: 'Penyakit Menular', color: COLOR_BLUE, key: 'penyakit_menular' },
                'kll': { name: 'Kecelakaan Lalu Lintas', color: COLOR_TEAL, key: 'kecelakaan_lalu_lintas' },
                'kl':  { name: 'Kecelakaan Lainnya', color: COLOR_GRAY, key: 'kecelakaan_lainnya' },
                'l':   { name: 'Lainnya', color: '#F97316', key: 'lainnya' }
            };
            const f = fieldMap[catVal] || fieldMap['ptm'];
            datasets = [{
                label: f.name,
                data: displayed.map(p => p[f.key] || 0),
                backgroundColor: f.color,
                borderRadius: 4
            }];
        }

        activeCharts['compare'] = new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, padding: 14 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.dataset.label + ': ' + formatNumberId(ctx.parsed.y) + ' jiwa';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, maxRotation: 45 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: function(v) {
                                if (v >= 1000000) return (v / 1000000).toFixed(1) + ' jt';
                                if (v >= 1000) return (v / 1000).toFixed(0) + ' rb';
                                return v;
                            }
                        }
                    }
                }
            }
        });
    }

    // Tabel Rincian Provinsi
    function renderTableProvinces(data) {
        const tbody = document.getElementById('tableBodyProvinces');
        const tfoot = document.getElementById('tableFootProvinces');
        if (!tbody || !tfoot) return;

        let provs = data.province_rows ? [...data.province_rows] : [];
        if (!provs.length && data.top_diagnosis) {
            provs = data.top_diagnosis.map(d => ({
                provinsi: d.label,
                penyakit_menular: 0,
                penyakit_tidak_menular: 0,
                kecelakaan_lalu_lintas: 0,
                kecelakaan_lainnya: 0,
                lainnya: 0,
                jumlah: d.total
            }));
        }

        tableCurrentRows = provs;
        const totalAll = data.total_cases || provs.reduce((sum, p) => sum + p.jumlah, 0);

        let html = '';
        let sumPm = 0, sumPtm = 0, sumKll = 0, sumKl = 0, sumL = 0, sumJml = 0;

        provs.forEach((p, idx) => {
            sumPm += p.penyakit_menular;
            sumPtm += p.penyakit_tidak_menular;
            sumKll += p.kecelakaan_lalu_lintas;
            sumKl += p.kecelakaan_lainnya;
            sumL += p.lainnya;
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
                            <div class="prop-bar-fill" style="width:${Math.min(100, Math.max(2, prop * 4))}%;"></div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:#0D9488;min-width:42px;text-align:right;">${prop}%</span>
                    </div>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html;

        // Tfoot: Total Seluruh Provinsi
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

    // Ekspor CSV dari Data Tabel Provinsi
    window.exportTableToCsv = function() {
        if (!tableCurrentRows || !tableCurrentRows.length) {
            if (window.showNotification) window.showNotification('Data tabel tidak tersedia untuk diekspor.', 'warning');
            return;
        }

        let csv = 'Provinsi,Penyakit Menular,Penyakit Tidak Menular,Kecelakaan Lalu Lintas,Kecelakaan Lainnya,Lainnya,Jumlah,Proporsi (%)\n';
        const total = tableCurrentRows.reduce((a,b) => a + b.jumlah, 0);

        tableCurrentRows.forEach(r => {
            const prop = total > 0 ? ((r.jumlah / total) * 100).toFixed(2) : '0';
            csv += `"${r.provinsi}",${r.penyakit_menular},${r.penyakit_tidak_menular},${r.kecelakaan_lalu_lintas},${r.kecelakaan_lainnya},${r.lainnya},${r.jumlah},${prop}\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `polara_analisis_kematian_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        if (window.showNotification) window.showNotification('Data rincian provinsi berhasil diekspor ke format CSV.', 'success');
    };

    window.exportActiveReport = function() {
        window.location.href = '<?= site_url("laporan") ?>';
    };

})();
</script>
<?= $this->endSection() ?>
