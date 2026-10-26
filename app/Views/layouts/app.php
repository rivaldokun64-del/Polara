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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* =============================================
           POLARA DESIGN SYSTEM — Soft Modern UI
           Inspired by: Metis, GitHub, Linear
        ============================================= */
        :root {
            /* Polara Brand Palette */
            --coral:         #F04432;
            --coral-dark:    #D93525;
            --coral-light:   #FEF0EE;
            --coral-glow:    rgba(240, 68, 50, 0.18);
            --peach:         #F9B097;
            --peach-light:   #FDF2ED;
            --cream:         #FAF1D6;
            --cream-light:   #FDFAF0;
            --navy:          #102B46;
            --navy-60:       rgba(16, 43, 70, 0.6);
            --navy-12:       rgba(16, 43, 70, 0.12);
            --navy-06:       rgba(16, 43, 70, 0.06);
            --warm-white:    #FFFCF5;
            --blue-soft:     #E0F0FD;
            --blue-mid:      #0EA5E9;
            --green-soft:    #DCFCE7;
            --green-mid:     #16A34A;
            --amber-soft:    #FEF3C7;
            --amber-mid:     #D97706;
            --muted:         #6B7280;
            --border:        #E9EAEC;

            /* Layout */
            --sidebar-w:     248px;
            --topbar-h:      60px;
            --radius-sm:     8px;
            --radius-md:     12px;
            --radius-lg:     16px;
            --radius-xl:     20px;

            /* Typography */
            --font-body:     'Inter', 'Plus Jakarta Sans', sans-serif;
            --font-brand:    'Outfit', sans-serif;

            /* Shadows */
            --shadow-xs:     0 1px 3px rgba(16,43,70,0.06), 0 1px 2px rgba(16,43,70,0.04);
            --shadow-sm:     0 2px 8px rgba(16,43,70,0.07), 0 1px 3px rgba(16,43,70,0.05);
            --shadow-md:     0 4px 16px rgba(16,43,70,0.08), 0 2px 6px rgba(16,43,70,0.05);
            --shadow-coral:  0 4px 14px var(--coral-glow);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.6;
            background: var(--warm-white);
            color: var(--navy);
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── App Shell ── */
        #app-shell {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
            position: relative;
        }

        /* =============================================
           SIDEBAR
        ============================================= */
        #sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: #ffffff;
            border-right: 1px solid var(--border);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
            transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1), left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-track { background: transparent; }
        #sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        /* Brand / Logo */
        .sb-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 20px 16px;
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            flex-shrink: 0;
            transition: padding 0.2s ease;
        }
        .sb-brand-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--coral-light);
            border: 1.5px solid var(--peach);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 3px 8px rgba(240,68,50,0.14);
            transition: transform 0.2s;
        }
        .sb-brand:hover .sb-brand-icon { transform: scale(1.08) rotate(-4deg); }
        .sb-brand-icon img { width: 24px; height: 24px; object-fit: contain; }
        .sb-brand-text { transition: opacity 0.2s; }
        .sb-brand-name {
            font-family: var(--font-brand);
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -0.3px;
            line-height: 1;
        }
        .sb-brand-name span { color: var(--coral); }
        .sb-brand-tagline {
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1;
            margin-top: 2px;
        }

        /* Navigation */
        .sb-nav {
            flex: 1;
            padding: 12px 10px 8px;
        }
        .sb-section {
            margin-bottom: 6px;
        }
        .sb-section-title {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 10px 10px 4px;
            display: block;
        }
        .sb-menu { list-style: none; padding: 0; margin: 0; }
        .sb-item { margin-bottom: 3px; }
        .sb-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: var(--radius-md);
            color: var(--navy-60);
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.18s ease;
            position: relative;
        }
        .sb-link i {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: rgba(16, 43, 70, 0.04);
            color: var(--navy);
            flex-shrink: 0;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sb-link:hover {
            background: var(--navy-06);
            color: var(--navy);
        }
        .sb-link:hover i {
            background: var(--coral-light);
            color: var(--coral);
            transform: scale(1.08);
        }
        .sb-item.active .sb-link {
            background: var(--coral-light);
            color: var(--coral);
            font-weight: 600;
        }
        .sb-item.active .sb-link i {
            background: var(--coral);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(240, 68, 50, 0.32);
            transform: scale(1.05);
        }
        .sb-item.active .sb-link::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 20px;
            background: var(--coral);
            border-radius: 0 3px 3px 0;
        }

        /* Logout button in sidebar */
        .sb-logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 8px 10px;
            border-radius: var(--radius-md);
            color: #DC2626;
            font-weight: 500;
            font-size: 13px;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: all 0.18s ease;
        }
        .sb-logout-btn i {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: rgba(220, 38, 38, 0.06);
            color: #DC2626;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .sb-logout-btn:hover { background: #FEF2F2; color: #B91C1C; }
        .sb-logout-btn:hover i {
            background: #DC2626;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
        }

        .sb-footer {
            padding: 12px 10px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        /* Backdrop mobile */
        .sb-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(16, 43, 70, 0.45);
            backdrop-filter: blur(3px);
            z-index: 1030;
        }
        .sb-backdrop.show { display: block; }

        /* =============================================
           MAIN CONTENT AREA
        ============================================= */
        #main-wrap {
            margin-left: var(--sidebar-w);
            width: calc(100% - var(--sidebar-w));
            max-width: calc(100% - var(--sidebar-w));
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
            position: relative;
            transition: margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Desktop Sidebar Collapse (Icon Mode) */
        @media (min-width: 1024px) {
            body.sidebar-collapsed #sidebar {
                width: 68px !important;
            }
            body.sidebar-collapsed #sidebar .sb-brand {
                padding: 16px 8px !important;
                justify-content: center !important;
            }
            body.sidebar-collapsed #sidebar .sb-brand-text {
                display: none !important;
            }
            body.sidebar-collapsed #sidebar .sb-section-title {
                display: none !important;
            }
            body.sidebar-collapsed #sidebar .sb-link {
                padding: 8px 0 !important;
                justify-content: center !important;
            }
            body.sidebar-collapsed #sidebar .sb-link span {
                display: none !important;
            }
            body.sidebar-collapsed #sidebar .sb-link::before {
                display: none !important;
            }
            body.sidebar-collapsed #sidebar .sb-logout-btn {
                padding: 8px 0 !important;
                justify-content: center !important;
            }
            body.sidebar-collapsed #sidebar .sb-logout-btn span {
                display: none !important;
            }
            body.sidebar-collapsed #main-wrap {
                margin-left: 68px !important;
                width: calc(100% - 68px) !important;
                max-width: calc(100% - 68px) !important;
            }
        }

        /* ── Topbar ── */
        #topbar {
            height: var(--topbar-h);
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 1020;
            gap: 16px;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        /* Mobile burger */
        .topbar-burger {
            display: none;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 6px 10px;
            color: var(--navy);
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
        }

        /* Desktop sidebar collapse button */
        .topbar-collapse-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px; height: 34px;
            border-radius: var(--radius-sm);
            background: #fff;
            border: 1px solid var(--border);
            color: var(--navy);
            cursor: pointer;
            font-size: 16px;
            transition: all 0.15s ease;
        }
        .topbar-collapse-btn:hover {
            background: var(--coral-light);
            border-color: var(--peach);
            color: var(--coral);
        }
        @media (max-width: 1023px) {
            .topbar-collapse-btn { display: none !important; }
        }

        /* Breadcrumb / page label in topbar */
        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            min-width: 0;
        }
        .topbar-breadcrumb .tb-page-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--navy);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Notification bell button & dropdown in topbar */
        .topbar-notif-btn {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid var(--border);
            color: var(--navy);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-xs);
        }
        .topbar-notif-btn:hover {
            background: var(--coral-light);
            border-color: var(--peach);
            color: var(--coral);
            transform: scale(1.05);
        }
        .notif-badge-dot {
            position: absolute;
            top: 4px; right: 4px;
            width: 8px; height: 8px;
            background: var(--coral);
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 6px rgba(240,68,50,0.6);
            transition: transform 0.2s;
        }
        .notif-badge-dot.hidden { display: none; }
        .notif-dropdown-menu {
            width: 330px;
            max-width: calc(100vw - 32px);
            padding: 0;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: 0 16px 40px rgba(16,43,70,0.12);
            overflow: hidden;
            margin-top: 8px !important;
            animation: ddSlideIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes ddSlideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .notif-dd-header {
            padding: 12px 16px;
            background: #fff;
            border-bottom: 1px solid var(--border);
        }
        .notif-dd-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--navy);
        }
        .notif-clear-btn {
            background: transparent;
            border: none;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--coral);
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background 0.15s;
        }
        .notif-clear-btn:hover { background: var(--coral-light); }
        .notif-dd-body {
            max-height: 300px;
            overflow-y: auto;
            background: #fff;
        }
        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 11px 16px;
            border-bottom: 1px solid rgba(16,43,70,0.05);
            transition: background 0.15s;
            cursor: pointer;
            text-decoration: none;
        }
        .notif-item:hover { background: #FAFBFC; }
        .notif-item.unread {
            background: var(--coral-light);
            border-left: 3px solid var(--coral);
        }
        .notif-item-icon {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }
        .notif-icon-coral { background: #fff; border: 1px solid var(--peach); color: var(--coral); }
        .notif-icon-blue  { background: var(--blue-soft); border: 1px solid #BAE6FD; color: #0284C7; }
        .notif-icon-green { background: var(--green-soft); border: 1px solid #BBF7D0; color: #16A34A; }
        .notif-item-content { flex: 1; min-width: 0; }
        .notif-item-title {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.3;
            margin-bottom: 2px;
        }
        .notif-item-desc {
            font-size: 11.5px;
            color: var(--muted);
            line-height: 1.35;
            margin-bottom: 3px;
        }
        .notif-item-time {
            font-size: 10.5px;
            color: var(--muted-light);
            display: flex; align-items: center; gap: 4px;
        }
        .notif-dd-footer {
            padding: 8px 16px;
            background: var(--warm-white);
            border-top: 1px solid var(--border);
            text-align: center;
        }

        /* User profile area — right side of topbar */
        .topbar-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .topbar-mascot {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--coral-light);
            border: 1.5px solid var(--peach);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .topbar-mascot img { width: 22px; height: 22px; object-fit: contain; }
        .topbar-user-info { line-height: 1.2; }
        .topbar-user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--navy);
            display: block;
        }
        .topbar-user-role {
            font-size: 11px;
            font-weight: 600;
            display: block;
        }

        /* Role badges */
        .badge-role {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .badge-role-admin    { background: var(--coral-light); color: var(--coral); border: 1px solid rgba(240,68,50,0.2); }
        .badge-role-analis   { background: var(--blue-soft); color: #0369A1; border: 1px solid #BAE6FD; }
        .badge-role-pimpinan { background: var(--cream); color: #92400E; border: 1px solid rgba(249,176,151,0.5); }

        /* ── Main Content ── */
        #page-content {
            flex: 1 1 auto;
            padding: 24px 28px 40px;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        /* Page title block */
        .page-header { margin-bottom: 24px; width: 100%; min-width: 0; }
        .page-header-inner {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            width: 100%;
            min-width: 0;
        }
        .page-title-text {
            font-family: var(--font-brand);
            font-size: 22px;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -0.3px;
            margin: 0;
            line-height: 1.2;
        }
        .page-subtitle-text {
            font-size: 13px;
            color: var(--muted);
            margin: 4px 0 0;
        }

        /* Breadcrumb */
        .polara-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            font-size: 12px;
            color: var(--muted);
        }
        .polara-breadcrumb a { color: var(--muted); text-decoration: none; }
        .polara-breadcrumb a:hover { color: var(--coral); }
        .polara-breadcrumb .sep { opacity: 0.4; }
        .polara-breadcrumb .current { color: var(--navy); font-weight: 500; }

        /* =============================================
           CARDS
        ============================================= */
        .card-p, .card-polara {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xs);
            overflow: hidden;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }
        .card-p-header, .card-polara .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            gap: 12px;
            background: #fff;
            min-width: 0;
        }
        .card-p-title, .card-polara .card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--navy);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .card-p-title i, .card-polara .card-title i { color: var(--coral); font-size: 16px; }
        .card-p-body, .card-polara .card-body { padding: 20px; min-width: 0; }
        .card-p-body-flush, .card-polara .card-body.p-0 { padding: 0; min-width: 0; }

        /* Stat Card */
        .stat-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-xs);
            transition: box-shadow 0.2s, border-color 0.2s, transform 0.2s;
            cursor: default;
        }
        .stat-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--peach);
            transform: translateY(-2px);
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .stat-icon-coral  { background: var(--coral-light); color: var(--coral); }
        .stat-icon-blue   { background: var(--blue-soft); color: var(--blue-mid); }
        .stat-icon-cream  { background: var(--cream); color: var(--amber-mid); }
        .stat-icon-navy   { background: #EFF1F5; color: var(--navy); }
        .stat-icon-green  { background: var(--green-soft); color: var(--green-mid); }
        .stat-num {
            font-family: var(--font-brand);
            font-size: 26px;
            font-weight: 800;
            color: var(--navy);
            line-height: 1;
        }
        .stat-label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
            margin-top: 3px;
        }

        /* =============================================
           BUTTONS
        ============================================= */
        .btn-coral {
            background: var(--coral);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            padding: 8px 18px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: background 0.15s, box-shadow 0.15s, transform 0.15s;
            box-shadow: var(--shadow-coral);
            line-height: 1.5;
        }
        .btn-coral:hover {
            background: var(--coral-dark);
            color: #fff;
            box-shadow: 0 6px 18px rgba(240,68,50,0.32);
            transform: translateY(-1px);
        }
        .btn-coral:active { transform: translateY(0); }

        .btn-outline-coral {
            background: transparent;
            color: var(--coral);
            border: 1.5px solid var(--coral);
            border-radius: var(--radius-sm);
            padding: 7px 16px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
        }
        .btn-outline-coral:hover { background: var(--coral-light); color: var(--coral); }

        .btn-ghost {
            background: transparent;
            color: var(--navy-60);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }
        .btn-ghost:hover { background: var(--navy-06); color: var(--navy); border-color: var(--navy-12); }

        /* =============================================
           TABLES & RESPONSIVE CONTAINERS
        ============================================= */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #FAFBFC;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--peach);
        }

        .table-polara { width: 100%; border-collapse: collapse; min-width: 600px; }
        .table-polara thead tr {
            border-bottom: 1px solid var(--border);
        }
        .table-polara thead th {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            padding: 10px 14px;
            background: #FAFBFC;
            white-space: nowrap;
        }
        .table-polara tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }
        .table-polara tbody tr:last-child { border-bottom: none; }
        .table-polara tbody tr:hover { background: var(--navy-06); }
        .table-polara tbody td { padding: 12px 14px; font-size: 13.5px; vertical-align: middle; }

        /* Status Badges */
        .badge-status {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }
        .badge-siap     { background: var(--green-soft); color: var(--green-mid); }
        .badge-uploaded { background: var(--blue-soft); color: #0369A1; }
        .badge-diproses { background: var(--amber-soft); color: var(--amber-mid); }
        .badge-error    { background: #FEF2F2; color: #DC2626; }

        /* =============================================
           EMPTY STATE
        ============================================= */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
            text-align: center;
            color: var(--muted);
        }
        .empty-state-icon {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: var(--coral-light);
            border: 1px solid var(--peach);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }
        .empty-state-icon img { width: 42px; height: 42px; object-fit: contain; }
        .empty-state h5 {
            font-size: 16px;
            font-weight: 700;
            color: var(--navy);
            margin: 0 0 8px;
        }
        .empty-state p { font-size: 13px; margin: 0; max-width: 360px; }

        /* =============================================
           FLASH ALERTS
        ============================================= */
        .flash-wrap { margin-bottom: 20px; }
        .flash-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-radius: var(--radius-md);
            border: 1px solid transparent;
            font-size: 13.5px;
            line-height: 1.5;
            position: relative;
        }
        .flash-alert-success {
            background: var(--green-soft);
            border-color: #BBF7D0;
            color: #166534;
        }
        .flash-alert-danger {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #991B1B;
        }
        .flash-alert-close {
            position: absolute;
            right: 12px; top: 12px;
            background: transparent; border: none;
            cursor: pointer; opacity: 0.5; font-size: 16px;
            line-height: 1; padding: 0; color: inherit;
        }
        .flash-alert-close:hover { opacity: 1; }

        /* =============================================
           TOAST NOTIFICATIONS (TOP-CENTER)
        ============================================= */
        #polara-toasts {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            pointer-events: none;
            width: auto;
            max-width: 90vw;
        }
        .polara-toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            background: #fff;
            border-radius: var(--radius-lg);
            border: 1.5px solid var(--peach);
            box-shadow: 0 16px 36px rgba(16, 43, 70, 0.12), 0 2px 8px rgba(0,0,0,0.04);
            pointer-events: all;
            transform: translateY(-20px);
            opacity: 0;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
            min-width: 280px;
            max-width: 480px;
        }
        .polara-toast.toast-in {
            transform: translateY(0);
            opacity: 1;
        }
        .polara-toast.toast-out {
            transform: translateY(-16px);
            opacity: 0;
        }
        .toast-icon {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #FFF4ED;
            border: 1.5px solid #F9B097;
            color: #F04432;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
            animation: checkPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes checkPop {
            0%   { transform: scale(0.5); opacity: 0; }
            70%  { transform: scale(1.18); }
            100% { transform: scale(1); opacity: 1; }
        }
        .toast-body { flex: 1; min-width: 0; }
        .toast-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.3;
        }
        .toast-msg {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
            line-height: 1.35;
        }
        .toast-close-btn {
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 16px;
            cursor: pointer;
            padding: 0 2px;
            line-height: 1;
            transition: color 0.15s;
        }
        .toast-close-btn:hover { color: var(--navy); }

        /* =============================================
           MODALS — Polara Confirm Dialog
        ============================================= */
        .modal-content { border-radius: var(--radius-xl); border: none; }
        .modal-header { border-bottom: 1px solid var(--border); padding: 20px 24px 16px; }
        .modal-footer { border-top: 1px solid var(--border); padding: 16px 24px 20px; }
        .modal-body { padding: 20px 24px; }

        .confirm-icon-wrap {
            width: 56px; height: 56px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 26px;
        }
        .confirm-icon-danger  { background: #FEF2F2; color: #DC2626; }
        .confirm-icon-warning { background: var(--amber-soft); color: var(--amber-mid); }
        .confirm-icon-info    { background: var(--blue-soft); color: var(--blue-mid); }
        .confirm-icon-coral   { background: var(--coral-light); color: var(--coral); }

        /* =============================================
           FOOTER
        ============================================= */
        #page-footer {
            border-top: 1px solid var(--border);
            padding: 14px 28px;
            font-size: 12.5px;
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
        }

        /* =============================================
           FORM ELEMENTS
        ============================================= */
        .form-control, .form-select {
            font-size: 13.5px;
            border-color: var(--border);
            border-radius: var(--radius-sm);
            color: var(--navy);
            background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--coral);
            box-shadow: 0 0 0 3px var(--coral-glow);
            outline: none;
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 6px;
        }

        /* =============================================
           RESPONSIVE
        ============================================= */
        @media (max-width: 1023px) {
            #sidebar {
                left: calc(-1 * var(--sidebar-w));
            }
            #sidebar.open {
                left: 0;
                box-shadow: 0 0 40px rgba(16,43,70,0.2);
            }
            #main-wrap {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
            }
            .topbar-burger { display: inline-flex !important; }
            #page-content { padding: 20px 16px 32px; }
            #page-footer { padding: 12px 16px; }
        }

        @media (max-width: 575px) {
            .topbar-user-info { display: none; }
            #polara-toasts { bottom: 16px; right: 16px; left: 16px; max-width: none; }
            #page-content { padding: 16px 12px 28px; }
        }

        /* ── Utils ── */
        .text-coral  { color: var(--coral) !important; }
        .bg-coral    { background: var(--coral) !important; }
        .divider { border: none; border-top: 1px solid var(--border); margin: 16px 0; }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>

<!-- Toast Container -->
<div id="polara-toasts" aria-live="polite" aria-atomic="false"></div>

<!-- Flash data passed to JS -->
<?php
$flashSuccess = session()->getFlashdata('success');
$flashError   = session()->getFlashdata('error');
$flashErrors  = session()->getFlashdata('errors');
$flashImport  = session()->getFlashdata('import_success');
$flashExport  = session()->getFlashdata('export_success');
$flashDelete  = session()->getFlashdata('delete_success');
$flashAnalysis= session()->getFlashdata('analysis_success');
$flashLogin   = session()->getFlashdata('login_success');
?>

<div id="app-shell">
    <!-- Sidebar Backdrop -->
    <div class="sb-backdrop" id="sbBackdrop"></div>

    <!-- ═══════════════ SIDEBAR ═══════════════ -->
    <aside id="sidebar" role="navigation" aria-label="Menu navigasi utama">

        <!-- Brand -->
        <a href="<?= site_url('dashboard') ?>" class="sb-brand">
            <div class="sb-brand-icon">
                <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Maskot Polara">
            </div>
            <div class="sb-brand-text">
                <div class="sb-brand-name">polara<span>.</span></div>
                <div class="sb-brand-tagline">Data Kesehatan</div>
            </div>
        </a>

        <!-- Nav Items Sesuai Referensi Figma -->
        <nav class="sb-nav">
            <div class="sb-section">
                <span class="sb-section-title" style="letter-spacing:0.8px;">RUANG ANALITIK</span>
                <ul class="sb-menu">
                    <li class="sb-item <?= url_is('dashboard') ? 'active' : '' ?>">
                        <a href="<?= site_url('dashboard') ?>" class="sb-link">
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sb-item <?= url_is('datasets*') ? 'active' : '' ?>">
                        <a href="<?= site_url('datasets') ?>" class="sb-link">
                            <i class="bi bi-server"></i>
                            <span>Dataset</span>
                        </a>
                    </li>
                    <li class="sb-item <?= (url_is('laporan*')) ? 'active' : '' ?>">
                        <a href="<?= site_url('laporan') ?>" class="sb-link">
                            <i class="bi bi-bar-chart-line"></i>
                            <span>Visualisasi data</span>
                        </a>
                    </li>
                    <li class="sb-item <?= url_is('analisis/baru*') ? 'active' : '' ?>">
                        <a href="<?= site_url('analisis/baru') ?>" class="sb-link">
                            <i class="bi bi-bar-chart-steps"></i>
                            <span>Analisis Data Mining</span>
                        </a>
                    </li>
                    <li class="sb-item <?= (url_is('analisis') || url_is('analisis/detail*')) ? 'active' : '' ?>">
                        <a href="<?= site_url('analisis') ?>" class="sb-link">
                            <i class="bi bi-diagram-3"></i>
                            <span>Klasterisasi</span>
                        </a>
                    </li>
                    <li class="sb-item">
                        <a href="<?= site_url('analisis') ?>" class="sb-link">
                            <i class="bi bi-check-circle"></i>
                            <span>Evaluasi model</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sb-section" style="margin-top:16px;">
                <ul class="sb-menu">
                    <?php if (session()->get('role') === 'admin'): ?>
                    <li class="sb-item <?= url_is('users*') ? 'active' : '' ?>">
                        <a href="<?= site_url('users') ?>" class="sb-link">
                            <i class="bi bi-sliders"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="sb-item">
                        <a href="javascript:void(0)" onclick="if(window.showNotification) window.showNotification('Pusat Bantuan Polara v2.4 siap melayani.', 'info')" class="sb-link">
                            <i class="bi bi-question-circle"></i>
                            <span>Pusat bantuan</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar Footer: Avatar & User Account Switcher / Logout Sesuai Figma -->
        <div class="sb-footer" style="padding:10px 14px;border-top:1px solid var(--border);">
            <div class="d-flex align-items-center justify-content-between" style="width:100%;">
                <div class="d-flex align-items-center gap-2 min-width:0;">
                    <div style="width:34px;height:34px;border-radius:50%;background:#E8EDF2;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--navy);flex-shrink:0;">
                        <?= strtoupper(substr(session()->get('nama') ?? 'AD', 0, 2)) ?>
                    </div>
                    <div style="line-height:1.15;min-width:0;overflow:hidden;">
                        <div style="font-size:12px;font-weight:700;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= esc(session()->get('nama') ?? 'Administrator') ?>
                        </div>
                        <div style="font-size:10px;color:var(--muted);white-space:nowrap;">
                            Ruang kerja Polara
                        </div>
                    </div>
                </div>
                <button type="button" class="btn p-0 border-0 text-muted" id="btnLogoutSidebar" title="Keluar / Opsi Pengguna" style="font-size:14px;">
                    <i class="bi bi-chevron-expand"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- ═══════════════ MAIN ═══════════════ -->
    <div id="main-wrap">

        <!-- Topbar -->
        <header id="topbar">
            <div style="display:flex; align-items:center; gap:12px; min-width:0; flex:1;">
                <button class="topbar-burger" id="btnSidebarToggle" aria-label="Toggle menu">
                    <i class="bi bi-list"></i>
                </button>
                <button class="topbar-collapse-btn" id="btnSidebarCollapse" title="Sembunyikan / Tampilkan Sidebar" aria-label="Toggle sidebar collapse">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </button>
                <div class="topbar-breadcrumb" style="display:flex;align-items:center;gap:8px;font-size:13px;">
                    <span style="color:var(--muted);"><i class="bi bi-columns-gap me-1.5 opacity-75"></i> Ruang analitik</span>
                    <i class="bi bi-chevron-right" style="font-size:10px;color:#9CA3AF;"></i>
                    <span class="tb-page-title" style="font-size:13px;font-weight:600;color:var(--navy);"><?= esc($title ?? 'Dashboard') ?></span>
                </div>
            </div>

            <!-- Right: Jam + Notifikasi + User Profile -->
            <div style="display:flex; align-items:center; gap:14px; flex-shrink:0;">
                <!-- Realtime Clock Widget -->
                <div id="topbarClock" class="d-none d-sm-flex" style="flex-direction:column;align-items:flex-end;line-height:1.15;flex-shrink:0;" title="Waktu real-time">
                    <span id="clockTime" style="font-size:13px;font-weight:700;color:var(--navy);font-family:var(--font-brand);letter-spacing:-0.3px;"></span>
                    <span id="clockDate" style="font-size:10.5px;color:var(--muted);font-weight:500;"></span>
                </div>
                <!-- Notification Bell Dropdown -->
                <div class="dropdown" id="notifDropdownWrap">
                    <button type="button" class="topbar-notif-btn" id="btnNotification" data-bs-toggle="dropdown" aria-expanded="false" title="Pemberitahuan Sistem">
                        <i class="bi bi-bell"></i>
                        <span class="notif-badge-dot" id="notifBadge"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown-menu" aria-labelledby="btnNotification">
                        <div class="notif-dd-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="notif-dd-title"><i class="bi bi-bell-fill text-coral me-1"></i> Notifikasi</span>
                                    <span class="badge-status badge-siap" id="notifBadgeCount" style="font-size:10px; padding:2px 8px;">3 Baru</span>
                                </div>
                                <button type="button" class="notif-clear-btn" id="btnMarkAllRead" title="Tandai semua sudah dibaca">
                                    <i class="bi bi-check2-all"></i> Baca Semua
                                </button>
                            </div>
                        </div>
                        <div class="notif-dd-body" id="notifListContainer">
                            <div class="notif-item unread" onclick="this.classList.remove('unread'); updateNotifCount();">
                                <div class="notif-item-icon notif-icon-coral">
                                    <i class="bi bi-check2-circle"></i>
                                </div>
                                <div class="notif-item-content">
                                    <div class="notif-item-title">Dataset Siap Dianalisis</div>
                                    <div class="notif-item-desc">Dataset rekam medis SIMRS telah terverifikasi dan siap dikonfirmasi.</div>
                                    <div class="notif-item-time"><i class="bi bi-clock"></i> Baru saja</div>
                                </div>
                            </div>

                            <div class="notif-item unread" onclick="this.classList.remove('unread'); updateNotifCount();">
                                <div class="notif-item-icon notif-icon-blue">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </div>
                                <div class="notif-item-content">
                                    <div class="notif-item-title">Model Data Mining Tersedia</div>
                                    <div class="notif-item-desc">Algoritma K-Means, C4.5, dan Naive Bayes siap dieksekusi dengan parameter klinis.</div>
                                    <div class="notif-item-time"><i class="bi bi-clock"></i> 15 menit lalu</div>
                                </div>
                            </div>

                            <div class="notif-item" onclick="this.classList.remove('unread'); updateNotifCount();">
                                <div class="notif-item-icon notif-icon-green">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="notif-item-content">
                                    <div class="notif-item-title">Sesi Akun Aktif</div>
                                    <div class="notif-item-desc">Autentikasi role-based terproteksi session guard aktif untuk akun Anda.</div>
                                    <div class="notif-item-time"><i class="bi bi-clock"></i> Hari ini</div>
                                </div>
                            </div>
                        </div>
                        <div class="notif-dd-footer">
                            <span class="text-muted" style="font-size:11px;">Polara Analytics Notification System</span>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="topbar-profile">
                    <div class="topbar-mascot" title="Maskot Polara">
                        <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Polara">
                    </div>
                    <div class="topbar-user-info">
                        <span class="topbar-user-name"><?= esc(session()->get('nama') ?? 'Pengguna') ?></span>
                        <span class="badge-role badge-role-<?= esc(session()->get('role') ?? 'analis') ?>"><?= esc(session()->get('role') ?? 'analis') ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main id="page-content" role="main">

            <!-- Flash Alerts (non-toast, fallback) -->
            <div class="flash-wrap" id="flash-wrap-server" style="<?= ($flashError || $flashErrors) ? '' : 'display:none' ?>">
                <?php if ($flashError): ?>
                    <div class="flash-alert flash-alert-danger">
                        <i class="bi bi-exclamation-circle-fill" style="font-size:18px; flex-shrink:0; margin-top:1px;"></i>
                        <div><?= esc($flashError) ?></div>
                        <button class="flash-alert-close" onclick="this.parentElement.remove()">&#x2715;</button>
                    </div>
                <?php endif; ?>
                <?php if ($flashErrors): ?>
                    <div class="flash-alert flash-alert-danger">
                        <i class="bi bi-exclamation-circle-fill" style="font-size:18px; flex-shrink:0; margin-top:1px;"></i>
                        <div>
                            <strong>Terdapat beberapa kesalahan:</strong>
                            <ul class="mb-0 ps-3 mt-1 small">
                                <?php foreach ((array)$flashErrors as $err): ?>
                                    <li><?= esc($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <button class="flash-alert-close" onclick="this.parentElement.remove()">&#x2715;</button>
                    </div>
                <?php endif; ?>
            </div>

            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <footer id="page-footer">
            <div>&copy; <?= date('Y') ?> <strong>Polara</strong> — Platform Analitik Data Kesehatan</div>
            <div>CodeIgniter 4 &bull; Discover the Patterns.</div>
        </footer>
    </div>
</div>

<!-- ═══════════════ LOGOUT CONFIRM MODAL ═══════════════ -->
<div class="modal fade" id="modalLogoutConfirm" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center px-4 pt-5 pb-4">
                <div class="confirm-icon-wrap confirm-icon-warning mx-auto mb-3">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <img src="<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>" alt="Polara" width="44" height="44" class="mb-3 d-block mx-auto" style="opacity:0.85;">
                <h6 class="fw-bold" style="color:var(--navy); font-size:15px;">Yakin ingin keluar?</h6>
                <p class="text-muted mb-0" style="font-size:13px;">Sesi Polara Anda akan diakhiri dan Anda perlu login kembali untuk mengakses dashboard.</p>
            </div>
            <div class="modal-footer justify-content-center gap-2 pb-4 border-0 pt-0">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Tetap di sini</button>
                <form action="<?= site_url('logout') ?>" method="get" id="formLogout" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-coral" style="background:#DC2626; box-shadow: 0 4px 12px rgba(220,38,38,0.2);">
                        <i class="bi bi-box-arrow-right"></i> Ya, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ GENERIC DELETE CONFIRM MODAL ═══════════════ -->
<div class="modal fade" id="modalDeleteConfirm" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center px-4 pt-5 pb-3">
                <div class="confirm-icon-wrap confirm-icon-danger mx-auto mb-3">
                    <i class="bi bi-trash3"></i>
                </div>
                <h6 class="fw-bold" style="color:var(--navy); font-size:15px;" id="deleteConfirmTitle">Hapus Item?</h6>
                <p class="text-muted mb-0" style="font-size:13px;" id="deleteConfirmBody">
                    Tindakan ini tidak dapat dibatalkan. Item akan dihapus secara permanen dari sistem.
                </p>
            </div>
            <div class="modal-footer justify-content-center gap-2 pb-4 border-0">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="deleteConfirmBtn" class="btn-coral" style="background:#DC2626; box-shadow: 0 4px 12px rgba(220,38,38,0.2);">
                    <i class="bi bi-trash3"></i> Ya, Hapus
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* =============================================
   POLARA UI SCRIPT
============================================= */
(function() {
'use strict';

/* ── Sidebar Toggle Mobile ── */
const sidebar    = document.getElementById('sidebar');
const backdrop   = document.getElementById('sbBackdrop');
const btnToggle  = document.getElementById('btnSidebarToggle');

function openSidebar()  { sidebar.classList.add('open');  backdrop.classList.add('show'); }
function closeSidebar() { sidebar.classList.remove('open'); backdrop.classList.remove('show'); }

if (btnToggle) btnToggle.addEventListener('click', openSidebar);
if (backdrop)  backdrop.addEventListener('click', closeSidebar);

/* ── Desktop Sidebar Collapse (Mini / Icon Mode) ── */
const btnCollapse = document.getElementById('btnSidebarCollapse');
if (btnCollapse) {
    if (localStorage.getItem('polara_sidebar_collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
    btnCollapse.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
        localStorage.setItem('polara_sidebar_collapsed', isCollapsed ? 'true' : 'false');
    });
}

/* ── Realtime Clock Widget ── */
(function() {
    const clockTimeEl = document.getElementById('clockTime');
    const clockDateEl = document.getElementById('clockDate');
    if (!clockTimeEl || !clockDateEl) return;

    const DAYS_ID   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const MONTHS_ID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    function tick() {
        const now = new Date();
        const h   = String(now.getHours()).padStart(2,'0');
        const m   = String(now.getMinutes()).padStart(2,'0');
        const s   = String(now.getSeconds()).padStart(2,'0');
        clockTimeEl.textContent = `${h}:${m}:${s}`;
        const day = DAYS_ID[now.getDay()];
        const date= now.getDate();
        const mon = MONTHS_ID[now.getMonth()];
        const yr  = now.getFullYear();
        clockDateEl.textContent = `${day}, ${date} ${mon} ${yr}`;
    }
    tick();
    setInterval(tick, 1000);
})();

/* ── Logout Confirm ── */
const btnLogoutSb  = document.getElementById('btnLogoutSidebar');
const modalLogout  = new bootstrap.Modal(document.getElementById('modalLogoutConfirm'));
if (btnLogoutSb) btnLogoutSb.addEventListener('click', () => modalLogout.show());

/* ── Delete Confirm ── */
function confirmDelete(url, title, body) {
    document.getElementById('deleteConfirmTitle').textContent = title || 'Hapus Item?';
    document.getElementById('deleteConfirmBody').textContent  = body  || 'Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('deleteConfirmBtn').href = url;
    new bootstrap.Modal(document.getElementById('modalDeleteConfirm')).show();
}
window.confirmDelete = confirmDelete;

/* ══════════════════════════════════════
   POLARA TOAST & NOTIFICATION SYSTEM
══════════════════════════════════════ */
const toastContainer = document.getElementById('polara-toasts');
const foxMascotUrl   = "<?= base_url('assets/favicon/Maskot rubah Polara.png') ?>";

function updateNotifCount() {
    const unread = document.querySelectorAll('#notifListContainer .notif-item.unread').length;
    const badge = document.getElementById('notifBadge');
    const badgeCount = document.getElementById('notifBadgeCount');
    if (badge) {
        if (unread > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    if (badgeCount) {
        badgeCount.textContent = unread > 0 ? (unread + ' Baru') : 'Semua Dibaca';
        badgeCount.className = unread > 0 ? 'badge-status badge-siap' : 'badge bg-light text-muted border rounded-pill px-2 py-1';
    }
}
window.updateNotifCount = updateNotifCount;

function addNotificationItem(title, desc) {
    const list = document.getElementById('notifListContainer');
    if (!list) return;
    const item = document.createElement('div');
    item.className = 'notif-item unread';
    item.onclick = function() { this.classList.remove('unread'); updateNotifCount(); };
    item.innerHTML =
        '<div class="notif-item-icon notif-icon-coral">' +
            '<i class="bi bi-bell-fill"></i>' +
        '</div>' +
        '<div class="notif-item-content">' +
            '<div class="notif-item-title">' + title + '</div>' +
            '<div class="notif-item-desc">' + desc + '</div>' +
            '<div class="notif-item-time"><i class="bi bi-clock"></i> Baru saja</div>' +
        '</div>';
    list.prepend(item);
    updateNotifCount();
}

const btnMarkAll = document.getElementById('btnMarkAllRead');
if (btnMarkAll) {
    btnMarkAll.addEventListener('click', () => {
        document.querySelectorAll('#notifListContainer .notif-item.unread').forEach(el => el.classList.remove('unread'));
        updateNotifCount();
    });
}

function showToast(type, title, message, duration) {
    duration = duration || 4500;
    const el = document.createElement('div');
    el.className = 'polara-toast toast-' + type;

    const iconHtml = (type === 'error' || type === 'danger')
        ? '<i class="bi bi-exclamation-lg"></i>'
        : (type === 'info' ? '<i class="bi bi-info-lg"></i>' : '<i class="bi bi-check-lg"></i>');

    el.innerHTML =
        '<div class="toast-icon">' + iconHtml + '</div>' +
        '<div class="toast-body">' +
            '<div class="toast-title">' + title + '</div>' +
            (message ? '<div class="toast-msg">' + message + '</div>' : '') +
        '</div>' +
        '<button type="button" class="toast-close-btn" onclick="this.closest(\'.polara-toast\').remove();" aria-label="Tutup">&times;</button>';

    toastContainer.appendChild(el);

    // Tambahkan juga ke daftar notifikasi dropdown
    addNotificationItem(title, message || 'Pemberitahuan sistem.');

    // Animasi masuk (turun dari atas tengah)
    requestAnimationFrame(() => {
        requestAnimationFrame(() => el.classList.add('toast-in'));
    });

    // Otomatis hilang
    setTimeout(() => {
        el.classList.remove('toast-in');
        el.classList.add('toast-out');
        setTimeout(() => el.remove(), 280);
    }, duration);

    return el;
}
window.showToast = showToast;

/* ── showNotification: alias for showToast with simplified signature ── */
function showNotification(message, type) {
    type = type || 'success';
    const titleMap = {
        'success': 'Berhasil',
        'error':   'Terjadi Kesalahan',
        'warning': 'Perhatian',
        'info':    'Informasi',
    };
    showToast(type, titleMap[type] || 'Pemberitahuan', message, 4000);
}
window.showNotification = showNotification;

/* ── Flash data from PHP → Toasts ── */
const flashData = <?= json_encode([
    'success'  => $flashSuccess,
    'import'   => $flashImport,
    'export'   => $flashExport,
    'delete'   => $flashDelete,
    'analysis' => $flashAnalysis,
    'login'    => $flashLogin,
    'user'     => session()->get('nama') ?? 'Pengguna',
    'error'    => null,
]) ?>;

document.addEventListener('DOMContentLoaded', function() {
    if (flashData.login) {
        showToast('success', 'Selamat Datang', flashData.login, 5000);
    } else if (flashData.success) {
        showToast('success', 'Berhasil', flashData.success, 4500);
    }
    if (flashData.import) {
        showToast('success', 'Dataset Berhasil Diunggah', flashData.import, 5000);
    }
    if (flashData.export) {
        showToast('success', 'Ekspor Data Berhasil', flashData.export, 4500);
    }
    if (flashData.delete) {
        showToast('info', 'Data Berhasil Dihapus', flashData.delete, 4000);
    }
    if (flashData.analysis) {
        showToast('success', 'Analisis Data Selesai', flashData.analysis, 5000);
    }
});

})();
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>
