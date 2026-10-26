<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Polara — Platform Analitik Data Kesehatan. Discover the Patterns.">
    <title><?= esc($title ?? 'Polara — Platform Analitik Data Kesehatan') ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>">

    <!--=============== REMIXICONS ===============-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css">

    <!--=============== CUSTOM CSS ===============-->
    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>">
</head>
<body>
    <!--==================== HEADER / NAVBAR ====================-->
    <header class="header" id="header">
        <nav class="nav container">
            <!-- Brand Logo -->
            <a href="<?= site_url('/') ?>" class="nav__logo" aria-label="Polara Beranda">
                <div class="nav__logo-icon">
                    <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Maskot Rubah Polara" style="width: 26px; height: 26px; object-fit: contain;">
                </div>
                <div class="nav__logo-text">
                    polara<span>.</span>
                </div>
            </a>

            <!-- Nav Menu -->
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="<?= site_url('/') ?>" class="nav__link active">Beranda</a>
                    </li>
                    <li class="nav__item">
                        <a href="#tentang" class="nav__link">Tentang Polara</a>
                    </li>
                    <li class="nav__item">
                        <a href="#fitur" class="nav__link">Fitur Analisis</a>
                    </li>
                    <li class="nav__item">
                        <a href="#panduan" class="nav__link">Panduan SIMRS</a>
                    </li>
                    <li class="nav__item">
                        <a href="#kontak" class="nav__link">Kontak Kami</a>
                    </li>
                </ul>

                <!-- Close button for mobile menu -->
                <div class="nav__close" id="nav-close" title="Tutup Menu">
                    <i class="ri-close-line"></i>
                </div>
            </div>

            <!-- Nav Actions -->
            <div class="nav__actions">
                <!-- Search Button -->
                <div class="nav__search" id="search-btn" title="Cari Panduan & Modul" role="button" tabindex="0">
                    <i class="ri-search-2-line"></i>
                </div>

                <?php if (session()->get('isLoggedIn')): ?>
                    <!-- State Pengguna Sudah Login -->
                    <a href="<?= site_url('dashboard') ?>" class="nav__login-btn-wrapper" id="nav-dashboard-wrapper" title="Buka Dashboard">
                        <i class="ri-dashboard-line"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= site_url('logout') ?>" class="nav__logout-link" title="Keluar dari Sistem">
                        <i class="ri-logout-box-r-line"></i>
                    </a>
                <?php else: ?>
                    <!-- State Pengguna Belum Login -->
                    <a href="<?= site_url('login') ?>" class="nav__login-btn-wrapper" id="nav-login-wrapper" title="Buka Form Login">
                        <i class="ri-user-3-line"></i>
                        <span>Masuk</span>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Toggle Button -->
                <div class="nav__toggle" id="nav-toggle" title="Menu Navigasi" role="button" tabindex="0">
                    <i class="ri-menu-4-line"></i>
                </div>
            </div>
        </nav>
    </header>

    <!--==================== SEARCH OVERLAY ====================-->
    <div class="search" id="search" aria-modal="true" role="dialog">
        <form action="#" class="search__form" onsubmit="event.preventDefault();">
            <i class="ri-search-2-line search__icon"></i>
            <input type="search" placeholder="Cari modul analisis, panduan, atau informasi..." class="search__input" autocomplete="off">
            <span class="search__hint">ESC</span>
        </form>

        <div class="search__close" id="search-close" title="Tutup Pencarian" role="button" tabindex="0">
            <i class="ri-close-line"></i>
        </div>
    </div>

    <!--==================== MAIN HERO SECTION ====================-->
    <section class="hero" id="beranda">
        <!-- SVG Graphic Background Layer -->
        <img src="<?= base_url('assets/img/bg-medical-analytics.svg') ?>" alt="Latar Belakang Analitik Polara" class="hero__bg">

        <div class="container hero__container">
            <!-- Left Column: Content & CTA -->
            <div class="hero__content">
                <!-- Flash Alerts -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success" role="alert" style="width: 100%; margin-bottom: 1.5rem;">
                        <i class="ri-checkbox-circle-fill"></i>
                        <div><?= esc(session()->getFlashdata('success')) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert" style="width: 100%; margin-bottom: 1.5rem;">
                        <i class="ri-error-warning-fill"></i>
                        <div><?= esc(session()->getFlashdata('error')) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Badge -->
                <div class="badge-polara hero__badge">
                    <i class="ri-sparkling-fill"></i> Sistem Intelijen Data Rekam Medis
                </div>

                <!-- Headline -->
                <h1 class="hero__title">
                    Analisis Pola Kunjungan Rekam Medis dengan <span class="highlight-coral">Data Mining Terpadu</span>.
                </h1>

                <!-- Description -->
                <p class="hero__description">
                    Platform analitik cerdas untuk mengekstraksi wawasan pola diagnosis, asosiasi penyakit, dan tren kunjungan pasien menggunakan algoritma data mining klinis terpadu.
                </p>

                <!-- Actions -->
                <div class="hero__actions">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <a href="<?= site_url('dashboard') ?>" class="btn-hero-primary" id="hero-dashboard-btn">
                            <i class="ri-dashboard-line"></i>
                            <span>Buka Dashboard Analitik</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('login') ?>" class="btn-hero-primary" id="hero-login-btn">
                            <i class="ri-login-box-line"></i>
                            <span>Buka Form Login</span>
                        </a>
                    <?php endif; ?>
                    <a href="#fitur" class="btn-hero-secondary">
                        <i class="ri-book-read-line"></i>
                        <span>Pelajari Sistem</span>
                    </a>
                </div>
            </div>

            <!-- Right Column: Fox Mascot & Analytics Visual -->
            <div class="hero__visual">
                <div class="hero__visual-container">
                    <!-- Peach Ambient Glow Circle -->
                    <div class="hero__glow-circle"></div>

                    <!-- SVG Trend / Pulse Wave -->
                    <svg class="hero__pulse-svg" viewBox="0 0 400 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 10 70 Q 60 70 90 70 L 120 40 L 140 95 L 165 20 L 190 100 L 210 55 L 230 70 L 390 70" 
                              stroke="url(#heroTrendGrad)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <defs>
                            <linearGradient id="heroTrendGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#F9B097" stop-opacity="0.2" />
                                <stop offset="40%" stop-color="#F04432" stop-opacity="0.8" />
                                <stop offset="80%" stop-color="#B9DDF4" stop-opacity="0.9" />
                                <stop offset="100%" stop-color="#FAF1D6" stop-opacity="0.1" />
                            </linearGradient>
                        </defs>
                        <!-- Node circles -->
                        <circle cx="120" cy="40" r="4" fill="#F04432" />
                        <circle cx="165" cy="20" r="5" fill="#F9B097" />
                        <circle cx="210" cy="55" r="4" fill="#B9DDF4" />
                    </svg>

                    <!-- Polara Fox Mascot Image -->
                    <img src="<?= base_url('assets/images/Maskot rubah Polara.png') ?>" 
                         alt="Maskot Rubah Polara" 
                         class="hero__mascot-img">

                    <!-- Floating Analytics Card (Concept 1 UI) -->
                    <div class="hero__floating-card">
                        <div class="hero__floating-card-icon">
                            <i class="ri-bar-chart-grouped-line"></i>
                        </div>
                        <div>
                            <div class="hero__floating-card-title">Pola Rekam Medis</div>
                            <div class="hero__floating-card-subtitle">Analisis Terpadu</div>
                        </div>
                        <div class="hero__mini-chart">
                            <div class="hero__mini-bar"></div>
                            <div class="hero__mini-bar"></div>
                            <div class="hero__mini-bar"></div>
                            <div class="hero__mini-bar"></div>
                        </div>
                    </div>

                    <!-- Floating Tech Badge -->
                    <div class="hero__floating-badge">
                        <i class="ri-shield-check-fill"></i>
                        <span>Standar Klinis &bull; K-Means, C4.5 &amp; Naive Bayes</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Feature Summary Cards Row -->
        <div class="container">
            <div class="hero__features-row">
                <div class="feature-summary-card">
                    <div class="feature-summary-icon">
                        <i class="ri-node-tree"></i>
                    </div>
                    <div>
                        <h3 class="feature-summary-title">Association Rules</h3>
                        <p class="feature-summary-desc">Ekstraksi korelasi pola komorbiditas dan kunjungan poli.</p>
                    </div>
                </div>

                <div class="feature-summary-card">
                    <div class="feature-summary-icon blue-accent">
                        <i class="ri-database-2-line"></i>
                    </div>
                    <div>
                        <h3 class="feature-summary-title">Dataset Rekam Medis</h3>
                        <p class="feature-summary-desc">Manajemen dataset terstruktur dengan sanitasi otomatis.</p>
                    </div>
                </div>

                <div class="feature-summary-card">
                    <div class="feature-summary-icon">
                        <i class="ri-shield-user-line"></i>
                    </div>
                    <div>
                        <h3 class="feature-summary-title">Role-Based Access</h3>
                        <p class="feature-summary-desc">Akses terproteksi untuk Administrator, Analis, dan Pimpinan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--==================== SECTION 2: FITUR ANALISIS ====================-->
    <section class="section section--white" id="fitur">
        <div class="container">
            <div class="section__header">
                <div class="badge-polara">
                    <i class="ri-cpu-line"></i> Algoritma Data Mining
                </div>
                <h2 class="section__title">Metode Analisis Standar Klinis</h2>
                <p class="section__subtitle">
                    Polara memproses data rekam medis aktual tanpa fabrikasi statistik untuk menghasilkan pola yang dapat dipertanggungjawabkan.
                </p>
            </div>

            <div class="features-grid">
                <!-- Card 1: K-Means Clustering -->
                <div class="algorithm-card">
                    <div class="algorithm-card__header">
                        <div class="algorithm-card__icon blue">
                            <i class="ri-bubble-chart-line"></i>
                        </div>
                        <span class="algorithm-card__tag">Segmentasi Pasien</span>
                    </div>
                    <h3 class="algorithm-card__title">Clustering K-Means</h3>
                    <p class="algorithm-card__desc">
                        Mengelompokkan kunjungan pasien berdasarkan karakteristik demografi, frekuensi kunjungan, dan poli tujuan untuk mengidentifikasi segmen beban layanan rumah sakit.
                    </p>
                    <div class="algorithm-card__list">
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Normalisasi Min-Max otomatis
                        </div>
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Evaluasi Silhouette Coefficient
                        </div>
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Visualisasi sebaran cluster
                        </div>
                    </div>
                </div>

                <!-- Card 2: Klasifikasi C4.5 -->
                <div class="algorithm-card">
                    <div class="algorithm-card__header">
                        <div class="algorithm-card__icon">
                            <i class="ri-git-branch-line"></i>
                        </div>
                        <span class="algorithm-card__tag">Pohon Keputusan</span>
                    </div>
                    <h3 class="algorithm-card__title">Klasifikasi C4.5</h3>
                    <p class="algorithm-card__desc">
                        Mengekstraksi aturan inferensi klinis berupa pohon keputusan (Decision Tree) dari data latih untuk memetakan jalur rujukan dan klasifikasi diagnosis.
                    </p>
                    <div class="algorithm-card__list">
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Perhitungan Gain Ratio otomatis
                        </div>
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Struktur aturan IF-THEN terbaca
                        </div>
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Matriks Confusion &amp; Akurasi
                        </div>
                    </div>
                </div>

                <!-- Card 3: Naive Bayes -->
                <div class="algorithm-card">
                    <div class="algorithm-card__header">
                        <div class="algorithm-card__icon blue">
                            <i class="ri-calculator-line"></i>
                        </div>
                        <span class="algorithm-card__tag">Probabilistik</span>
                    </div>
                    <h3 class="algorithm-card__title">Naive Bayes Classifier</h3>
                    <p class="algorithm-card__desc">
                        Memodelkan probabilitas bersyarat kemunculan komorbiditas atau poli tujuan berdasarkan riwayat kunjungan dan atribut klinis pasien terdahulu.
                    </p>
                    <div class="algorithm-card__list">
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Estimasi Posterior &amp; Prioritas
                        </div>
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Penanganan Laplace Smoothing
                        </div>
                        <div class="algorithm-card__item">
                            <i class="ri-checkbox-circle-fill"></i> Pelaporan skor probabilitas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--==================== SECTION 3: TENTANG POLARA ====================-->
    <section class="section section--cream-tint" id="tentang">
        <div class="container">
            <div class="about-grid">
                <!-- Left Column -->
                <div class="about-text">
                    <div class="badge-polara">
                        <i class="ri-shield-star-line"></i> Tentang Polara
                    </div>
                    <h2 class="about-title">Platform Intelijen Rekam Medis untuk Keputusan Terarah</h2>
                    <p class="about-paragraph">
                        <strong>Polara</strong> adalah platform analitik cerdas yang dirancang untuk membantu tenaga medis, analis kesehatan, dan pimpinan fasilitas pelayanan kesehatan dalam mengekstrak wawasan tersembunyi dari arsip data kunjungan pasien.
                    </p>
                    <p class="about-paragraph">
                        Dengan menghubungkan data historis rekam medis dengan algoritma data mining terpadu, Polara memfasilitasi perencanaan alokasi sumber daya rumah sakit, deteksi dini tren kunjungan penyakit musiman, serta efisiensi antrean poliklinik.
                    </p>
                </div>

                <!-- Right Column: 3 Pillars -->
                <div class="about-cards">
                    <div class="about-pillar-card">
                        <div class="about-pillar-icon">
                            <i class="ri-lock-password-line" style="color: var(--color-coral);"></i>
                        </div>
                        <div>
                            <h3 class="about-pillar-title">Privasi &amp; Keamanan Sesuai Peran</h3>
                            <p class="about-pillar-desc">
                                Memisahkan tanggung jawab Administrator sistem, Analis pengolah dataset, dan Pimpinan pengambil kebijakan manajerial.
                            </p>
                        </div>
                    </div>

                    <div class="about-pillar-card">
                        <div class="about-pillar-icon">
                            <i class="ri-table-line" style="color: #0284C7;"></i>
                        </div>
                        <div>
                            <h3 class="about-pillar-title">Pemetaan Kolom Fleksibel</h3>
                            <p class="about-pillar-desc">
                                Mendukung berbagai format spreadsheet ekspor SIMRS dengan fitur pratinjau, sanitasi, dan konfirmasi pemetaan nama kolom.
                            </p>
                        </div>
                    </div>

                    <div class="about-pillar-card">
                        <div class="about-pillar-icon">
                            <i class="ri-verified-badge-line" style="color: var(--color-coral);"></i>
                        </div>
                        <div>
                            <h3 class="about-pillar-title">Hasil Murni dari Data Aktual</h3>
                            <p class="about-pillar-desc">
                                Memegang teguh prinsip integritas data: hasil analitik dan grafik dihitung murni dari dataset yang benar-benar diimpor.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--==================== SECTION 4: PANDUAN SIMRS ====================-->
    <section class="section section--white" id="panduan">
        <div class="container">
            <div class="section__header">
                <div class="badge-polara">
                    <i class="ri-route-line"></i> Alur Kerja Sistem
                </div>
                <h2 class="section__title">Langkah Mudah Analisis Data</h2>
                <p class="section__subtitle">
                    Dari data mentah SIMRS hingga ringkasan eksekutif siap pakai dalam 4 tahap terstruktur.
                </p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <span class="step-number">01</span>
                    <h3 class="step-title">Ekspor Tabular</h3>
                    <p class="step-desc">
                        Ekspor rekam kunjungan pasien dari SIMRS atau spreadsheet (.xlsx, .csv) yang memuat usia, diagnosa, dan poli tujuan.
                    </p>
                </div>

                <div class="step-card">
                    <span class="step-number">02</span>
                    <h3 class="step-title">Upload &amp; Pemetaan</h3>
                    <p class="step-desc">
                        Unggah file ke sistem Polara. Konfirmasi pratinjau struktur tabel dan petakan nama kolom ke atribut analitik yang sesuai.
                    </p>
                </div>

                <div class="step-card">
                    <span class="step-number">03</span>
                    <h3 class="step-title">Eksekusi Model</h3>
                    <p class="step-desc">
                        Pilih metode yang diinginkan (K-Means, C4.5, atau Naive Bayes) dan atur parameter pengujian sesuai kebutuhan klinis.
                    </p>
                </div>

                <div class="step-card">
                    <span class="step-number">04</span>
                    <h3 class="step-title">Wawasan &amp; Laporan</h3>
                    <p class="step-desc">
                        Tinjau visualisasi cluster, aturan keputusan, serta ringkasan metrik untuk bahan evaluasi pelayanan dan pelaporan manajerial.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!--==================== SECTION 5: CTA BANNER ====================-->
    <section class="container" style="margin-bottom: 5rem;">
        <div class="cta-banner">
            <div class="cta-banner__glow"></div>
            <h2 class="cta-banner__title">Mulai Ekstraksi Pola Rekam Medis Bersama Polara</h2>
            <p class="cta-banner__desc">
                Masuk ke portal analisis untuk mengeksplorasi wawasan data kunjungan pasien Anda dengan antarmuka yang aman dan intuitif.
            </p>
            <?php if (session()->get('isLoggedIn')): ?>
                <a href="<?= site_url('dashboard') ?>" class="btn-hero-primary" style="margin-inline: auto;">
                    <i class="ri-dashboard-line"></i>
                    <span>Buka Dashboard Analitik</span>
                </a>
            <?php else: ?>
                <a href="<?= site_url('login') ?>" class="btn-hero-primary" style="margin-inline: auto;">
                    <i class="ri-login-box-line"></i>
                    <span>Masuk ke Sistem Polara</span>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!--==================== SECTION 6: CONTACT US ====================-->
    <section class="container" id="kontak" style="margin-bottom: 5rem;">
        <div class="contact-section">
            <div class="contact-header">
                <span class="badge-tag" style="background: var(--color-coral-light); color: var(--color-coral);">
                    <i class="ri-customer-service-2-line"></i> Kontak &amp; Dukungan
                </span>
                <h2 class="contact-title">Hubungi Kami</h2>
                <p class="contact-desc">
                    Ada pertanyaan seputar implementasi Polara, kendala teknis, atau kolaborasi pengembangan analitik data kesehatan? Jangan ragu untuk menghubungi pengembang.
                </p>
            </div>

            <div class="contact-grid">
                <!-- Card Email -->
                <a href="mailto:rivaldokun64@gmail.com" class="contact-card" title="Kirim email ke rivaldokun64@gmail.com">
                    <div class="contact-icon contact-icon--email">
                        <i class="ri-mail-send-line"></i>
                    </div>
                    <div class="contact-details">
                        <span class="contact-badge-type">Email Resmi</span>
                        <h3 class="contact-value">rivaldokun64@gmail.com</h3>
                        <p class="contact-hint">Kirim pesan langsung via surat elektronik &rarr;</p>
                    </div>
                </a>

                <!-- Card Instagram -->
                <a href="https://instagram.com/rvallldooo" target="_blank" rel="noopener noreferrer" class="contact-card" title="Buka profil Instagram @rvallldooo">
                    <div class="contact-icon contact-icon--ig">
                        <i class="ri-instagram-line"></i>
                    </div>
                    <div class="contact-details">
                        <span class="contact-badge-type">Instagram Pengembang</span>
                        <h3 class="contact-value">@rvallldooo</h3>
                        <p class="contact-hint">Kirim DM atau ikuti kabar terbaru Polara &rarr;</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <style>
    .contact-section {
        background: #ffffff;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-xl, 20px);
        padding: 3.5rem 2.5rem;
        box-shadow: 0 16px 40px rgba(16, 43, 70, 0.05);
        position: relative;
        overflow: hidden;
    }
    .contact-header {
        text-align: center;
        max-width: 620px;
        margin: 0 auto 2.5rem;
    }
    .contact-title {
        font-family: var(--font-title);
        font-size: 2rem;
        font-weight: 800;
        color: var(--color-navy);
        margin: 0.8rem 0 0.5rem;
    }
    .contact-desc {
        font-size: 0.95rem;
        color: var(--color-muted);
        line-height: 1.6;
        margin: 0;
    }
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        max-width: 820px;
        margin: 0 auto;
    }
    .contact-card {
        background: var(--color-warm-white, #FAF9F6);
        border: 1.5px solid var(--color-border);
        border-radius: var(--radius-lg, 16px);
        padding: 1.6rem;
        display: flex;
        align-items: center;
        gap: 1.2rem;
        text-decoration: none;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .contact-card:hover {
        transform: translateY(-4px);
        border-color: var(--color-peach, #F9B097);
        box-shadow: 0 12px 28px rgba(240, 68, 50, 0.12);
    }
    .contact-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }
    .contact-card:hover .contact-icon {
        transform: scale(1.08);
    }
    .contact-icon--email {
        background: #FFF4ED;
        color: #F04432;
        border: 1px solid #F9B097;
    }
    .contact-icon--ig {
        background: linear-gradient(135deg, #FFF0F5 0%, #FFF5EB 100%);
        color: #E1306C;
        border: 1px solid #FBCFE8;
    }
    .contact-details {
        flex: 1;
        min-width: 0;
    }
    .contact-badge-type {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--color-muted);
        display: block;
        margin-bottom: 2px;
    }
    .contact-value {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--color-navy);
        margin: 0 0 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .contact-hint {
        font-size: 0.8rem;
        color: var(--color-coral);
        font-weight: 600;
        margin: 0;
    }
    </style>

    <!--==================== FOOTER ====================-->
    <footer class="footer">
        <div class="container footer__container">
            <div class="footer__brand">
                <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Logo Polara" style="width: 24px; height: 24px; object-fit: contain;">
                <span>Polara</span> &bull; <span style="font-weight: 400; color: var(--color-muted);">Platform Analitik Data Kesehatan</span>
            </div>
            <div class="footer__links">
                <a href="<?= site_url('/') ?>" class="footer__link">Beranda</a>
                <a href="#tentang" class="footer__link">Tentang</a>
                <a href="#fitur" class="footer__link">Fitur</a>
                <a href="#panduan" class="footer__link">Panduan SIMRS</a>
                <a href="#kontak" class="footer__link">Kontak</a>
                <a href="<?= site_url('login') ?>" class="footer__link">Masuk</a>
            </div>
            <div>
                &copy; <?= date('Y') ?> Polara. Discover the Patterns.
            </div>
        </div>
    </footer>

    <!--=============== MAIN JAVASCRIPT ===============-->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
