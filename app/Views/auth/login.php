<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Polara — Platform Analitik Data Kesehatan. Discover the Patterns.">
    <title><?= esc($title ?? 'Login — Polara Platform Analitik Data Kesehatan') ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>">

    <!--=============== REMIXICONS ===============-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css">

    <!--=============== CUSTOM CSS ===============-->
    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>">
</head>
<body class="login-page">
    <!-- SVG Graphic Background Layer -->
    <img src="<?= base_url('assets/img/bg-medical-analytics.svg') ?>" alt="Latar Belakang Analitik Polara" class="login-page__bg">

    <!-- Top Navigation Header -->
    <header class="login-nav">
        <a href="<?= site_url('/') ?>" class="nav__logo" aria-label="Polara Beranda">
            <div class="nav__logo-icon">
                <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Maskot Rubah Polara" style="width: 26px; height: 26px; object-fit: contain;">
            </div>
            <div class="nav__logo-text">
                polara<span>.</span>
            </div>
        </a>

        <a href="<?= site_url('/') ?>" class="btn-back-home" title="Kembali ke Beranda">
            <i class="ri-arrow-left-line"></i>
            <span>Kembali ke Beranda</span>
        </a>
    </header>

    <!-- Centered Login Card Container -->
    <main class="login-page__container">
        <div class="login-card">
            <!-- Fox Mascot Avatar -->
            <div class="login-card__avatar">
                <img src="<?= base_url('assets/images/Maskot rubah Polara.png') ?>" alt="Maskot Rubah Polara">
            </div>

            <!-- Header Badge & Titles -->
            <span class="login-card__badge">
                <i class="ri-shield-keyhole-line"></i> Portal Autentikasi
            </span>
            <h1 class="login-card__title">Masuk ke Polara</h1>
            <p class="login-card__subtitle">Platform Analitik Data Kesehatan</p>

            <!-- Server-side CI4 Flash Alerts -->
            <div class="login__alerts">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="ri-checkbox-circle-fill"></i>
                        <div><?= esc(session()->getFlashdata('success')) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-error-warning-fill"></i>
                        <div><?= esc(session()->getFlashdata('error')) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-close-circle-fill"></i>
                        <div>
                            <strong>Terjadi kesalahan input:</strong>
                            <ul class="alert-errors-list">
                                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Login Form -->
            <form action="<?= site_url('login') ?>" method="post" autocomplete="off" id="form-login">
                <?= csrf_field() ?>

                <div class="form-group-custom">
                    <!-- Username Input -->
                    <div>
                        <label for="username" class="form-label-custom">Username Pengguna</label>
                        <div class="input-box-custom">
                            <input type="text"
                                   name="username"
                                   id="username"
                                   class="input-field-custom <?= (isset(session()->getFlashdata('errors')['username'])) ? 'is-invalid' : '' ?>"
                                   placeholder="Masukkan username Anda"
                                   value="<?= esc(old('username')) ?>"
                                   required
                                   autofocus>
                            <i class="ri-user-3-line input-icon-custom"></i>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="form-label-custom">Kata Sandi</label>
                        <div class="input-box-custom">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="input-field-custom <?= (isset(session()->getFlashdata('errors')['password'])) ? 'is-invalid' : '' ?>"
                                   placeholder="Masukkan kata sandi"
                                   required>
                            <i class="ri-lock-2-line input-icon-custom"></i>
                            <button type="button" class="toggle-password" id="btnTogglePassword" title="Tampilkan atau sembunyikan kata sandi" aria-label="Toggle password visibility">
                                <i class="ri-eye-line" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Helper Options -->
                <div class="login-options">
                    <a href="javascript:void(0)" onclick="openForgotModal()" class="login-forgot-link">
                        Lupa kata sandi?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login-submit" id="btnLogin">
                    <i class="ri-login-circle-line"></i>
                    <span>Masuk ke Sistem</span>
                </button>

                <!-- Back Link Divider -->
                <div class="login-card__back-divider">
                    <a href="<?= site_url('/') ?>" class="login-card__back-link">
                        <i class="ri-arrow-left-line"></i>
                        <span>Kembali ke Beranda Utama</span>
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- Custom Modal Lupa Sandi -->
    <div id="modalLupaSandi" class="custom-modal-backdrop" style="display:none;" onclick="if(event.target===this) closeForgotModal();">
        <div class="custom-modal-box" role="dialog" aria-modal="true" aria-labelledby="modalForgotTitle">
            <div class="custom-modal-icon">
                <i class="ri-shield-user-line"></i>
            </div>
            <h3 class="custom-modal-title" id="modalForgotTitle">Bantuan Kata Sandi</h3>
            <p class="custom-modal-desc">
                Untuk menjaga keamanan data rekam medis, pengaturan ulang kata sandi dikelola secara terpusat. Silakan hubungi <strong>Administrator Sistem</strong> untuk melakukan reset kata sandi akun Anda.
            </p>
            <button type="button" class="btn-custom-modal" onclick="closeForgotModal()">
                Mengerti
            </button>
        </div>
    </div>

    <style>
    .custom-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(16, 43, 70, 0.45);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 16px;
        animation: modalFadeIn 0.2s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .custom-modal-box {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 28px 24px;
        max-width: 380px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 45px rgba(16, 43, 70, 0.18);
        border: 1px solid rgba(240, 68, 50, 0.15);
        transform: translateY(0);
        animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalPop {
        from { transform: scale(0.92) translateY(12px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }
    .custom-modal-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #FFF4ED;
        color: #F04432;
        border: 1.5px solid #F9B097;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin: 0 auto 16px;
    }
    .custom-modal-title {
        font-size: 17px;
        font-weight: 700;
        color: #102B46;
        margin: 0 0 10px;
        font-family: inherit;
    }
    .custom-modal-desc {
        font-size: 13.5px;
        color: #5A7184;
        line-height: 1.55;
        margin: 0 0 20px;
    }
    .custom-modal-desc strong {
        color: #102B46;
    }
    .btn-custom-modal {
        background: #F04432;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 9px 24px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, transform 0.15s;
        box-shadow: 0 4px 14px rgba(240, 68, 50, 0.3);
        width: 100%;
    }
    .btn-custom-modal:hover {
        background: #D93827;
        transform: translateY(-1px);
    }
    </style>

    <script>
    function openForgotModal() {
        document.getElementById('modalLupaSandi').style.display = 'flex';
    }
    function closeForgotModal() {
        document.getElementById('modalLupaSandi').style.display = 'none';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeForgotModal();
    });
    </script>

    <!--=============== MAIN JAVASCRIPT ===============-->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
