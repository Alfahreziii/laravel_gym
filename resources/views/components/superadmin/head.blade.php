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
    <style>
        /* ── Reset ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        /* ── Design tokens ───────────────────────────────────── */
        :root {
            --sa-sidebar-w:        240px;
            --sa-sidebar-bg:       #201C18;
            --sa-sidebar-border:   rgba(255,255,255,.08);
            --sa-sidebar-text:     rgba(255,255,255,.6);
            --sa-sidebar-hover:    rgba(255,255,255,.06);
            --sa-sidebar-act-bg:   rgba(242,98,46,.13);
            --sa-sidebar-act-text: #F2622E;
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
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 50;
            overflow-y: auto;
        }

        .sa-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1.375rem 1.25rem 1.25rem;
            border-bottom: 1px solid var(--sa-sidebar-border);
            text-decoration: none;
        }

        .sa-logo-wordmark {
            font-family: var(--sa-ff-display);
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            letter-spacing: .02em;
            line-height: 1;
        }

        .sa-logo-role {
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: rgba(255,255,255,.5);
            margin-top: 3px;
        }

        .sa-nav { flex: 1; padding: .75rem 0; }

        .sa-nav-section {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.28);
            padding: .75rem 1.25rem .35rem;
        }

        .sa-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .6rem 1.25rem;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--sa-sidebar-text);
            text-decoration: none;
            transition: background .13s, color .13s;
            border-left: 2.5px solid transparent;
        }

        .sa-nav-item:hover {
            background: var(--sa-sidebar-hover);
            color: rgba(255,255,255,.85);
        }

        .sa-nav-item.active {
            background: var(--sa-sidebar-act-bg);
            color: var(--sa-sidebar-act-text);
            border-left-color: var(--sa-sidebar-act-bar);
            font-weight: 600;
        }

        .sa-nav-item svg { width: 16px; height: 16px; flex-shrink: 0; opacity: .65; }
        .sa-nav-item.active svg, .sa-nav-item:hover svg { opacity: 1; }

        .sa-nav-item.disabled {
            opacity: .35;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Sidebar footer (user + logout) */
        .sa-sidebar-footer {
            padding: .75rem 1.25rem 1.25rem;
            border-top: 1px solid var(--sa-sidebar-border);
        }

        .sa-user-row {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: .75rem;
        }

        .sa-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: rgba(242,98,46,.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            color: #F2622E;
            flex-shrink: 0;
        }

        .sa-user-name {
            font-size: 12.5px;
            font-weight: 600;
            color: rgba(255,255,255,.75);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sa-user-role { font-size: 10px; color: rgba(255,255,255,.35); }

        .sa-btn-logout {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,.12);
            background: transparent;
            color: rgba(255,255,255,.5);
            font-family: var(--sa-ff-sans);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color .13s, color .13s, background .13s;
        }

        .sa-btn-logout:hover {
            border-color: rgba(242,98,46,.5);
            color: #F2622E;
            background: rgba(242,98,46,.08);
        }

        .sa-btn-logout svg { width: 14px; height: 14px; }

        /* ── Main wrapper ─────────────────────────────────────── */
        .sa-main {
            margin-left: var(--sa-sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ─────────────────────────────────────────── */
        .sa-topbar {
            background: var(--sa-card-bg);
            border-bottom: 1px solid var(--sa-border);
            padding: 0 2rem;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .sa-topbar-title {
            font-family: var(--sa-ff-display);
            font-size: 22px;
            font-weight: 700;
            color: var(--sa-text);
            letter-spacing: .02em;
        }

        .sa-topbar-meta {
            font-size: 13px;
            color: var(--sa-text-3);
        }

        /* ── Page content ─────────────────────────────────────── */
        .sa-page-content {
            flex: 1;
            padding: 1.75rem 2rem 3rem;
            max-width: 1200px;
            width: 100%;
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

        .sa-table tbody tr {
            border-bottom: 1px solid var(--sa-border-2);
            transition: background .1s;
        }

        .sa-table tbody tr:last-child { border-bottom: none; }
        .sa-table tbody tr:hover { background: #FAFAF9; }

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

        .sa-modal-backdrop.open { display: flex; }

        .sa-modal {
            background: var(--sa-card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15,12,10,.22);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

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

        .sa-form-input:focus {
            border-color: var(--sa-primary);
            box-shadow: 0 0 0 3px rgba(242,98,46,.1);
            background: #fff;
        }

        .sa-form-input::placeholder { color: #C4C0BA; }

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
    </style>
</head>
