@props(['pageTitle' => 'Panel'])
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} — Super Admin · HexaGym</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    {{-- Apply dark mode before render to prevent flash --}}
    <script>
        (function () {
            if (localStorage.getItem('color-theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        /* ── Reset ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        /* ── Design tokens (light) ───────────────────────────── */
        :root {
            --sa-sidebar-w:        240px;
            --sa-sidebar-collapsed: 72px;

            --sa-sidebar-bg:       #FAFAF9;
            --sa-sidebar-border:   #E2E0DB;
            --sa-sidebar-text:     #525252;
            --sa-sidebar-hover:    #F0EFEC;
            --sa-sidebar-act-bg:   #FEF3EE;
            --sa-sidebar-act-text: #C0461A;
            --sa-sidebar-act-bar:  #F2622E;

            --sa-page-bg:  #FAF9F5;
            --sa-card-bg:  #ffffff;
            --sa-border:   #E2E0DB;
            --sa-border-2: #EBEAE7;
            --sa-text:     #1A1A18;
            --sa-text-2:   #6E6A63;
            --sa-text-3:   #9C978E;
            --sa-primary:  #F2622E;
            --sa-primary-d:#E04E1B;

            --sa-ff-sans:    'Plus Jakarta Sans', system-ui, sans-serif;
            --sa-ff-display: 'Barlow Condensed', sans-serif;
        }

        /* ── Design tokens (dark) ────────────────────────────── */
        html.dark {
            --sa-sidebar-bg:       #1F1B17;
            --sa-sidebar-border:   #332D26;
            --sa-sidebar-text:     #A8A29A;
            --sa-sidebar-hover:    rgba(255,255,255,.05);
            --sa-sidebar-act-bg:   rgba(242,98,46,.2);
            --sa-sidebar-act-text: #F87343;
            --sa-sidebar-act-bar:  #F2622E;

            --sa-page-bg:  #15120F;
            --sa-card-bg:  #1F1B17;
            --sa-border:   #332D26;
            --sa-border-2: #2A2520;
            --sa-text:     #F4F1EC;
            --sa-text-2:   #A8A29A;
            --sa-text-3:   #6E685F;
        }

        body {
            font-family: var(--sa-ff-sans);
            background: var(--sa-page-bg);
            color: var(--sa-text);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        .sa-sidebar {
            width: var(--sa-sidebar-w);
            min-height: 100vh;
            background: var(--sa-sidebar-bg);
            border-right: 1px solid var(--sa-sidebar-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 50;
            overflow-x: hidden;
            overflow-y: auto;
            transition: width .22s ease, transform .22s ease;
        }

        /* Desktop: collapse to icon-only */
        @media (min-width: 1024px) {
            .sa-sidebar.collapsed {
                width: var(--sa-sidebar-collapsed);
            }
            .sa-sidebar.collapsed .sa-sidebar-logo {
                justify-content: center;
                gap: 0;
                padding: 0;
            }
            .sa-sidebar.collapsed .sa-logo-wordmark,
            .sa-sidebar.collapsed .sa-logo-role,
            .sa-sidebar.collapsed .sa-nav-section {
                display: none;
            }
            .sa-sidebar.collapsed .sa-nav-item {
                justify-content: center;
                padding: .625rem 0;
                margin: 1px 6px;
            }
            .sa-sidebar.collapsed .sa-nav-item span { display: none; }
            .sa-sidebar.collapsed .sa-nav-item.active::before { display: none; }

            /* Hover: expand temporarily (overlay on content, no layout shift) */
            .sa-sidebar.collapsed:hover {
                width: var(--sa-sidebar-w);
                box-shadow: 4px 0 20px rgba(0,0,0,.08);
                z-index: 55;
                overflow-y: auto;
            }
            .sa-sidebar.collapsed:hover .sa-sidebar-logo {
                justify-content: flex-start;
                gap: 10px;
                padding: 0 1.25rem;
            }
            .sa-sidebar.collapsed:hover .sa-logo-wordmark,
            .sa-sidebar.collapsed:hover .sa-logo-role,
            .sa-sidebar.collapsed:hover .sa-nav-section {
                display: block;
            }
            .sa-sidebar.collapsed:hover .sa-nav-item {
                justify-content: flex-start;
                padding: .625rem .75rem;
                margin: 1px .5rem;
            }
            .sa-sidebar.collapsed:hover .sa-nav-item span { display: inline; }
            .sa-sidebar.collapsed:hover .sa-nav-item.active::before { display: block; }
        }

        /* Mobile: sidebar hidden, slide in via body class */
        @media (max-width: 1023px) {
            .sa-sidebar { transform: translateX(-100%); }
            body.sa-sidebar-open .sa-sidebar { transform: translateX(0); }
            body.sa-sidebar-open .sa-sidebar-overlay { display: block; }
        }

        .sa-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 49;
        }

        .sa-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            height: 72px;
            padding: 0 1.25rem;
            border-bottom: 1px solid var(--sa-sidebar-border);
            text-decoration: none;
            flex-shrink: 0;
            transition: gap .18s ease, padding .18s ease, justify-content .18s ease;
            white-space: nowrap;
        }

        .sa-logo-wordmark {
            font-family: var(--sa-ff-display);
            font-size: 20px;
            font-weight: 700;
            color: var(--sa-text);
            letter-spacing: .02em;
            line-height: 1;
        }

        .sa-logo-role {
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: var(--sa-text-3);
            margin-top: 3px;
        }

        .sa-nav { flex: 1; padding: .75rem 0 1.5rem; }

        .sa-nav-section {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--sa-text-3);
            padding: .75rem 1rem .35rem;
            white-space: nowrap;
        }

        .sa-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .625rem .75rem;
            margin: 1px .5rem;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--sa-sidebar-text);
            text-decoration: none;
            transition: background .13s, color .13s;
            border-radius: 10px;
            position: relative;
            white-space: nowrap;
        }

        .sa-nav-item:hover {
            background: var(--sa-sidebar-hover);
            color: var(--sa-text);
        }

        .sa-nav-item.active {
            background: var(--sa-sidebar-act-bg);
            color: var(--sa-sidebar-act-text);
            font-weight: 600;
        }

        .sa-nav-item.active::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 6px;
            bottom: 6px;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: var(--sa-sidebar-act-bar);
        }

        .sa-nav-item svg {
            width: 18px; height: 18px;
            flex-shrink: 0;
            color: var(--sa-text-3);
        }

        .sa-nav-item:hover svg { color: var(--sa-text); }
        .sa-nav-item.active svg { color: var(--sa-sidebar-act-text); }

        .sa-nav-item.disabled {
            opacity: .35;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── Main wrapper ─────────────────────────────────────── */
        .sa-main {
            margin-left: var(--sa-sidebar-w);
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left .22s ease;
        }

        @media (min-width: 1024px) {
            .sa-main.collapsed { margin-left: var(--sa-sidebar-collapsed); }
        }

        @media (max-width: 1023px) {
            .sa-main { margin-left: 0; }
        }

        /* ── Topbar ─────────────────────────────────────────── */
        .sa-topbar {
            background: var(--sa-sidebar-bg);
            border-bottom: 1px solid var(--sa-border);
            padding: 0 1.5rem;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
            flex-shrink: 0;
        }

        .sa-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sa-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .sa-topbar-title {
            font-family: var(--sa-ff-display);
            font-size: 20px;
            font-weight: 700;
            color: var(--sa-text);
            letter-spacing: .02em;
        }

        /* ── Navbar control buttons ──────────────────────────── */
        .sa-navbar-ctrl-btn {
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
            border: 1px solid var(--sa-border);
            background: transparent;
            color: var(--sa-text-2);
            cursor: pointer;
            transition: background .13s, color .13s;
            flex-shrink: 0;
        }

        .sa-navbar-ctrl-btn:hover {
            background: var(--sa-border-2);
            color: var(--sa-text);
        }

        .sa-navbar-ctrl-btn svg { width: 20px; height: 20px; }

        /* ── Navbar profile pill ─────────────────────────────── */
        .sa-navbar-profile-pill {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 4px 10px 4px 4px;
            border: 1px solid var(--sa-border);
            border-radius: 999px;
            background: transparent;
            cursor: pointer;
            transition: background .13s;
        }

        .sa-navbar-profile-pill:hover { background: var(--sa-border-2); }

        .sa-navbar-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #E04E1B;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .sa-navbar-profile-info { text-align: left; }

        .sa-navbar-profile-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--sa-text);
            line-height: 1.3;
        }

        .sa-navbar-profile-role {
            font-size: 11px;
            color: var(--sa-text-3);
            line-height: 1.3;
        }

        /* ── Profile dropdown ────────────────────────────────── */
        .sa-profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--sa-card-bg);
            border: 1px solid var(--sa-border);
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
            min-width: 210px;
            z-index: 100;
            overflow: hidden;
        }

        html.dark .sa-profile-dropdown {
            box-shadow: 0 8px 32px rgba(0,0,0,.4);
        }

        .sa-profile-dropdown.hidden { display: none; }

        .sa-profile-dropdown-header {
            padding: 12px 16px 10px;
            background: var(--sa-sidebar-act-bg);
        }

        .sa-profile-dropdown-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--sa-text);
        }

        .sa-profile-dropdown-role {
            font-size: 11.5px;
            color: var(--sa-text-3);
            margin-top: 1px;
        }

        .sa-profile-dropdown-divider {
            height: 1px;
            background: var(--sa-border-2);
        }

        .sa-profile-dropdown-item {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            padding: 10px 16px;
            font-family: var(--sa-ff-sans);
            font-size: 13px;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
            text-align: left;
            color: var(--sa-text-2);
            transition: background .13s, color .13s;
            text-decoration: none;
        }

        .sa-profile-dropdown-item:hover {
            background: var(--sa-border-2);
            color: var(--sa-text);
        }

        .sa-profile-dropdown-item-danger { color: #DC2626; }
        .sa-profile-dropdown-item-danger:hover { background: #FEF2F2; color: #DC2626; }
        html.dark .sa-profile-dropdown-item-danger:hover { background: rgba(220,38,38,.15); }
        .sa-profile-dropdown-item svg { width: 15px; height: 15px; flex-shrink: 0; }

        /* ── Page content ─────────────────────────────────────── */
        .sa-page-content {
            flex: 1;
            min-width: 0;
            padding: 1.75rem 2rem 2rem;
            max-width: 1200px;
            width: 100%;
        }

        /* ── Footer ──────────────────────────────────────────── */
        .sa-footer {
            background: var(--sa-card-bg);
            border-top: 1px solid var(--sa-border);
            padding: 1.375rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: var(--sa-text-2);
            flex-shrink: 0;
        }

        .sa-footer-credit {
            color: var(--sa-primary);
            font-weight: 600;
        }

        /* ── Flash ───────────────────────────────────────────── */
        .sa-flash {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 16px;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            font-size: 13.5px;
        }

        .sa-flash svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

        .sa-flash-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #15803D; }
        .sa-flash-info    { background: #EFF6FF; border: 1px solid #BFDBFE; color: #1D4ED8; }
        .sa-flash-error   { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; }

        html.dark .sa-flash-success { background: rgba(34,197,94,.12);  border-color: rgba(34,197,94,.3);  color: #4ADE80; }
        html.dark .sa-flash-info    { background: rgba(59,130,246,.12); border-color: rgba(59,130,246,.3); color: #93C5FD; }
        html.dark .sa-flash-error   { background: rgba(220,38,38,.15);  border-color: rgba(220,38,38,.3);  color: #FCA5A5; }

        /* ── Stat grid ───────────────────────────────────────── */
        .sa-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .sa-stat-card {
            background: var(--sa-card-bg);
            border: 1px solid var(--sa-border);
            border-radius: 14px;
            padding: 1.125rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sa-stat-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .sa-stat-icon svg { width: 20px; height: 20px; }

        .sa-icon-primary { background: #FFF3ED; color: #F2622E; }
        .sa-icon-success { background: #F0FDF4; color: #16A34A; }
        .sa-icon-neutral { background: #F5F6FA; color: #6B7280; }

        html.dark .sa-icon-primary { background: rgba(242,98,46,.2);   color: #F87343; }
        html.dark .sa-icon-success { background: rgba(34,197,94,.15);  color: #4ADE80; }
        html.dark .sa-icon-neutral { background: rgba(255,255,255,.08); color: #A8A29A; }

        .sa-stat-label { font-size: 11.5px; font-weight: 500; color: var(--sa-text-3); margin-bottom: 3px; }

        .sa-stat-value {
            font-family: var(--sa-ff-display);
            font-size: 28px;
            font-weight: 700;
            color: var(--sa-text);
            line-height: 1;
        }

        /* ── Table card ─────────────────────────────────────── */
        .sa-table-card {
            background: var(--sa-card-bg);
            border: 1px solid var(--sa-border);
            border-radius: 14px;
            overflow: hidden;
            min-width: 0;
        }

        .sa-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.125rem 1.375rem;
            border-bottom: 1px solid var(--sa-border-2);
        }

        .sa-table-title {
            font-family: var(--sa-ff-display);
            font-size: 17px;
            font-weight: 700;
            color: var(--sa-text);
            letter-spacing: .02em;
        }

        .sa-table-sub { font-size: 12px; color: var(--sa-text-3); margin-top: 1px; }

        /* ── Table ───────────────────────────────────────────── */
        .sa-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }

        .sa-table thead th {
            text-align: left;
            padding: .7rem 1rem;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--sa-text-3);
            background: #FAFAF9;
            border-bottom: 1px solid var(--sa-border-2);
            white-space: nowrap;
        }

        html.dark .sa-table thead th { background: #28231D; }

        .sa-table tbody tr {
            border-bottom: 1px solid var(--sa-border-2);
            transition: background .1s;
        }

        .sa-table tbody tr:last-child { border-bottom: none; }
        .sa-table tbody tr:hover { background: #FAFAF9; }

        html.dark .sa-table tbody tr:hover { background: rgba(255,255,255,.03); }

        .sa-table tbody td { padding: .8rem 1rem; color: var(--sa-text); vertical-align: middle; }

        .sa-td-mono {
            font-family: ui-monospace, 'Cascadia Code', monospace;
            font-size: 12.5px;
            color: var(--sa-text-2);
        }

        .sa-empty td {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--sa-text-3);
            font-size: 13.5px;
        }

        /* ── Badges ─────────────────────────────────────────── */
        .sa-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .sa-badge-dot { width: 6px; height: 6px; border-radius: 50%; }

        .sa-badge-available { background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; }
        .sa-badge-available .sa-badge-dot { background: #22C55E; }

        .sa-badge-used { background: #F5F6FA; color: #4B5563; border: 1px solid #D1D5DB; }
        .sa-badge-used .sa-badge-dot { background: #9CA3AF; }

        html.dark .sa-badge-available { background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.3); color: #4ADE80; }
        html.dark .sa-badge-available .sa-badge-dot { background: #4ADE80; }
        html.dark .sa-badge-used      { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.12); color: #A8A29A; }
        html.dark .sa-badge-used .sa-badge-dot { background: #A8A29A; }

        /* ── Buttons ─────────────────────────────────────────── */
        .sa-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 9px;
            font-family: var(--sa-ff-sans);
            font-weight: 600;
            cursor: pointer;
            transition: opacity .13s, box-shadow .13s;
            border: none;
            outline: none;
            text-decoration: none;
        }

        .sa-btn:hover { opacity: .88; }
        .sa-btn:active { opacity: .78; }

        .sa-btn-primary {
            background: linear-gradient(135deg, #F2622E 0%, #E04E1B 100%);
            color: #fff;
            padding: 9px 16px;
            font-size: 13.5px;
            box-shadow: 0 4px 14px -5px rgba(242,98,46,.5);
        }

        .sa-btn-ghost {
            background: transparent;
            border: 1.5px solid var(--sa-border);
            color: var(--sa-text-2);
            padding: 8px 14px;
            font-size: 13px;
        }

        .sa-btn-ghost:hover { border-color: var(--sa-text-3); color: var(--sa-text); }

        .sa-btn-danger-ghost {
            background: transparent;
            border: 1.5px solid #FECACA;
            color: #DC2626;
            padding: 5px 11px;
            font-size: 12px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-family: var(--sa-ff-sans);
            font-weight: 600;
            transition: background .13s, border-color .13s;
        }

        .sa-btn-danger-ghost:hover { background: #FEF2F2; border-color: #FCA5A5; }
        html.dark .sa-btn-danger-ghost { border-color: rgba(220,38,38,.4); }
        html.dark .sa-btn-danger-ghost:hover { background: rgba(220,38,38,.15); border-color: rgba(220,38,38,.6); }
        .sa-btn-danger-ghost svg { width: 13px; height: 13px; }

        .sa-btn-danger {
            background: #DC2626;
            color: #fff;
            padding: 9px 16px;
            font-size: 13.5px;
            border-radius: 9px;
            box-shadow: 0 4px 12px -4px rgba(220,38,38,.4);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: var(--sa-ff-sans);
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity .13s;
        }

        .sa-btn-danger:hover { opacity: .88; }

        /* ── Modal ───────────────────────────────────────────── */
        .sa-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,12,10,.55);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        html.dark .sa-modal-backdrop { background: rgba(0,0,0,.7); }

        .sa-modal-backdrop.open { display: flex; }

        .sa-modal {
            background: var(--sa-card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15,12,10,.22);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        html.dark .sa-modal { box-shadow: 0 20px 60px rgba(0,0,0,.5); }

        .sa-modal-header {
            padding: 1.25rem 1.5rem 1rem;
            border-bottom: 1px solid var(--sa-border-2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sa-modal-title {
            font-family: var(--sa-ff-display);
            font-size: 18px;
            font-weight: 700;
            color: var(--sa-text);
        }

        .sa-modal-close {
            width: 28px; height: 28px;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: var(--sa-text-3);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            transition: background .13s;
        }

        .sa-modal-close:hover { background: var(--sa-border-2); color: var(--sa-text); }

        .sa-modal-body { padding: 1.25rem 1.5rem; }

        .sa-modal-footer {
            padding: 1rem 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .75rem;
        }

        /* ── Form ────────────────────────────────────────────── */
        .sa-form-group { margin-bottom: 1rem; }
        .sa-form-group:last-child { margin-bottom: 0; }

        .sa-form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--sa-text-2);
            margin-bottom: 5px;
            letter-spacing: .02em;
        }

        .sa-form-input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1.5px solid var(--sa-border);
            border-radius: 9px;
            background: #FAFAF9;
            font-family: var(--sa-ff-sans);
            font-size: 13.5px;
            color: var(--sa-text);
            outline: none;
            transition: border-color .13s, box-shadow .13s;
        }

        html.dark .sa-form-input {
            background: #28231D;
            color: var(--sa-text);
            color-scheme: dark;
        }

        .sa-form-input:focus {
            border-color: var(--sa-primary);
            box-shadow: 0 0 0 3px rgba(242,98,46,.1);
            background: #fff;
        }

        html.dark .sa-form-input:focus { background: #1F1B17; }

        .sa-form-input::placeholder { color: #C4C0BA; }
        html.dark .sa-form-input::placeholder { color: var(--sa-text-3); }

        .sa-form-hint { font-size: 11px; color: var(--sa-text-3); margin-top: 4px; }
        .sa-form-error { font-size: 11.5px; color: #DC2626; margin-top: 4px; }
        .sa-field-error .sa-form-input { border-color: #FCA5A5; }

        /* ── Warning modal ────────────────────────────────────── */
        .sa-warn-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: #FEF2F2;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
        }

        html.dark .sa-warn-icon { background: rgba(220,38,38,.2); }

        .sa-warn-icon svg { width: 24px; height: 24px; color: #DC2626; }

        .sa-warn-title {
            font-family: var(--sa-ff-display);
            font-size: 19px;
            font-weight: 700;
            color: var(--sa-text);
            margin-bottom: 6px;
        }

        .sa-warn-desc { font-size: 13.5px; color: var(--sa-text-2); line-height: 1.55; }

        .sa-warn-db-name {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            background: #F5F6FA;
            border: 1px solid var(--sa-border);
            font-family: ui-monospace, monospace;
            font-size: 13px;
            color: var(--sa-text);
            margin: .5rem 0;
        }

        html.dark .sa-warn-db-name { background: #28231D; }

        /* ══════════════════════════════════════════════════════
           Responsive — konten global sa-* (frame sidebar/topbar
           TIDAK disentuh, sudah responsive bawaan)
           ══════════════════════════════════════════════════════ */
        @media (max-width: 1023px) {
            .sa-stat-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .sa-page-content { padding: 1rem 1rem 1.5rem; }

            .sa-stat-grid { grid-template-columns: 1fr; }
            .sa-stat-card { padding: 1rem; }

            .sa-table-header {
                flex-direction: column;
                align-items: stretch;
                gap: .75rem;
            }
            .sa-table-header .sa-btn { justify-content: center; }

            .sa-modal-backdrop { padding: 1rem; }
            .sa-modal {
                max-width: calc(100% - 2rem);
                max-height: calc(100vh - 2rem);
                display: flex;
                flex-direction: column;
            }
            .sa-modal-header,
            .sa-modal-footer { padding-left: 1.25rem; padding-right: 1.25rem; }
            .sa-modal-body { padding: 1.125rem 1.25rem; overflow-y: auto; flex: 1 1 auto; }
            .sa-modal-footer {
                flex-direction: column;
                align-items: stretch;
                gap: .625rem;
            }
            .sa-modal-footer form { width: 100%; }
            .sa-modal-footer .sa-btn,
            .sa-modal-footer .sa-btn-danger { width: 100%; justify-content: center; }

            .sa-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: .375rem;
                padding: 1.125rem 1.25rem;
            }
        }
    </style>
</head>
