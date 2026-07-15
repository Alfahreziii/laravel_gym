@extends('superadmin.layouts.app')

@section('title', 'Paket Langganan')

@section('content')
<style>
/* ── Paket page ──────────────────────────────────────────── */
.sa-paket-intro {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap;
}
.sa-paket-intro-text {}
.sa-paket-title {
    font-family: var(--sa-ff-display);
    font-size: 20px; font-weight: 800;
    color: var(--sa-text); margin-bottom: .25rem;
}
.sa-paket-subtitle {
    font-size: 13.5px; color: var(--sa-text-2); line-height: 1.5;
}
.sa-readonly-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px;
    background: #F5F6FA; border: 1px solid var(--sa-border);
    border-radius: 999px; font-size: 12px; font-weight: 600;
    color: var(--sa-text-2); white-space: nowrap; flex-shrink: 0;
}
.sa-readonly-badge svg { width: 13px; height: 13px; }

/* Cards grid */
.sa-paket-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    align-items: start;
}

/* Individual card */
.sa-paket-card {
    background: #fff;
    border: 1.5px solid var(--sa-border);
    border-radius: 16px;
    overflow: hidden;
}

/* Card top header */
.sa-paket-card-head {
    padding: 1.25rem 1.375rem 1rem;
    border-bottom: 1px solid var(--sa-border);
    background: #FAFAF9;
}
.sa-paket-pill {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 999px;
    font-size: 11.5px; font-weight: 700;
    letter-spacing: .4px; text-transform: uppercase;
    background: rgba(242,98,46,.1); color: var(--sa-primary);
    margin-bottom: .625rem;
}
.sa-paket-card-name {
    font-family: var(--sa-ff-display);
    font-size: 26px; font-weight: 800;
    color: var(--sa-text); line-height: 1.1;
    margin-bottom: .5rem;
}
.sa-paket-tenant-row {
    display: flex; align-items: center; gap: 5px;
    font-size: 12.5px; color: var(--sa-text-3);
}
.sa-paket-tenant-row svg { width: 13px; height: 13px; flex-shrink: 0; }
.sa-paket-tenant-row strong { color: var(--sa-text-2); }

/* Module sections inside card */
.sa-paket-card-body { padding: .875rem 1.375rem 1.25rem; }

.sa-mod-section { margin-bottom: 1rem; }
.sa-mod-section:last-child { margin-bottom: 0; }

.sa-mod-section-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: .5rem;
}
.sa-mod-section-title {
    font-size: 11px; font-weight: 700;
    letter-spacing: .6px; text-transform: uppercase;
}
.sa-mod-section-tag {
    font-size: 10.5px; font-weight: 600; padding: 2px 7px;
    border-radius: 999px;
}

/* Core section styling */
.sa-mod-section-core .sa-mod-section-title { color: #15803D; }
.sa-mod-section-core .sa-mod-section-tag {
    background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0;
}

/* Toggle section styling */
.sa-mod-section-toggle .sa-mod-section-title { color: #92400E; }
.sa-mod-section-toggle .sa-mod-section-tag {
    background: #FFF7ED; color: #C2410C; border: 1px solid #FED7AA;
}

/* Module item rows */
.sa-mod-list { display: flex; flex-direction: column; gap: 2px; }
.sa-mod-item {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 8px; border-radius: 7px;
    font-size: 13px;
}
.sa-mod-item-on  { color: var(--sa-text); }
.sa-mod-item-off { color: #9CA3AF; }
.sa-mod-item:hover { background: #F9F9F8; }

.sa-mod-icon {
    width: 18px; height: 18px; flex-shrink: 0;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.sa-mod-icon-on  { background: #DCFCE7; }
.sa-mod-icon-off { background: #F3F4F6; }
.sa-mod-icon svg { width: 10px; height: 10px; }
.sa-mod-icon-on  svg { color: #16A34A; }
.sa-mod-icon-off svg { color: #9CA3AF; }

.sa-mod-item-label { flex: 1; line-height: 1; }

/* Divider between core and toggle */
.sa-mod-divider {
    border: none; border-top: 1px dashed var(--sa-border);
    margin: .875rem 0;
}

/* Legend note */
.sa-paket-legend {
    display: flex; gap: 1.25rem; flex-wrap: wrap;
    margin-top: 1.5rem;
    padding: .75rem 1rem;
    background: #F9F9F8; border: 1px solid var(--sa-border);
    border-radius: 10px;
}
.sa-legend-item {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: var(--sa-text-2);
}
.sa-legend-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.sa-legend-dot-core   { background: #22C55E; }
.sa-legend-dot-toggle { background: var(--sa-primary); }
.sa-legend-dot-off    { background: #D1D5DB; }

@media (max-width: 1024px) {
    .sa-paket-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .sa-paket-grid { grid-template-columns: 1fr; }
}

/* ── Dark mode overrides ────────────────────────────── */
html.dark .sa-paket-card         { background: var(--sa-card-bg); }
html.dark .sa-paket-card-head    { background: #28231D; }
html.dark .sa-readonly-badge     { background: rgba(255,255,255,.06); }

html.dark .sa-mod-section-core .sa-mod-section-title { color: #4ADE80; }
html.dark .sa-mod-section-core .sa-mod-section-tag {
    background: rgba(34,197,94,.12); color: #4ADE80; border-color: rgba(34,197,94,.3);
}
html.dark .sa-mod-section-toggle .sa-mod-section-title { color: #FDBA74; }
html.dark .sa-mod-section-toggle .sa-mod-section-tag {
    background: rgba(249,115,22,.12); color: #FDBA74; border-color: rgba(249,115,22,.3);
}

html.dark .sa-mod-item:hover     { background: rgba(255,255,255,.04); }
html.dark .sa-mod-item-off       { color: var(--sa-text-3); }

html.dark .sa-mod-icon-on        { background: rgba(34,197,94,.15); }
html.dark .sa-mod-icon-off       { background: rgba(255,255,255,.06); }
html.dark .sa-mod-icon-on  svg   { color: #4ADE80; }
html.dark .sa-mod-icon-off svg   { color: var(--sa-text-3); }

html.dark .sa-paket-legend       { background: rgba(255,255,255,.03); }
</style>

{{-- ── Intro ─────────────────────────────────────────────── --}}
<div class="sa-paket-intro">
    <div class="sa-paket-intro-text">
        <div class="sa-paket-title">Paket Langganan</div>
        <div class="sa-paket-subtitle">
            Daftar paket &amp; komposisi modul yang didapat tiap tenant.
            Data ini dikelola via <code>PackageSeeder</code> — tidak dapat diubah dari panel.
        </div>
    </div>
    <div class="sa-readonly-badge">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
        </svg>
        Read-only
    </div>
</div>

{{-- ── Paket cards ───────────────────────────────────────── --}}
@php
$coreModules = [
    'Dashboard',
    'Membership',
    'Kehadiran',
    'Alat Gym',
    'Users',
    'Laporan',
];
$toggleModules = [
    'trainer'  => 'Personal Trainer',
    'pos'      => 'POS / Kasir',
    'keuangan' => 'Keuangan & Neraca',
];
@endphp

<div class="sa-paket-grid">
    @forelse ($packages as $pkg)
    <div class="sa-paket-card">

        {{-- Header --}}
        <div class="sa-paket-card-head">
            <div class="sa-paket-pill">{{ $pkg->label }}</div>
            <div class="sa-paket-card-name">{{ $pkg->nama }}</div>
            <div class="sa-paket-tenant-row">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                </svg>
                <strong>{{ $pkg->tenants_count }}</strong>
                gym menggunakan paket ini
            </div>
        </div>

        {{-- Body --}}
        <div class="sa-paket-card-body">

            {{-- CORE modules --}}
            <div class="sa-mod-section sa-mod-section-core">
                <div class="sa-mod-section-head">
                    <span class="sa-mod-section-title">Core</span>
                    <span class="sa-mod-section-tag">Selalu tersedia</span>
                </div>
                <ul class="sa-mod-list">
                    @foreach ($coreModules as $mod)
                    <li class="sa-mod-item sa-mod-item-on">
                        <span class="sa-mod-icon sa-mod-icon-on">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                        </span>
                        <span class="sa-mod-item-label">{{ $mod }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <hr class="sa-mod-divider">

            {{-- TOGGLE modules --}}
            <div class="sa-mod-section sa-mod-section-toggle">
                <div class="sa-mod-section-head">
                    <span class="sa-mod-section-title">Toggle</span>
                    <span class="sa-mod-section-tag">Tergantung paket</span>
                </div>
                <ul class="sa-mod-list">
                    @foreach ($toggleModules as $key => $label)
                    @php $on = (bool) $pkg->{$key}; @endphp
                    <li class="sa-mod-item {{ $on ? 'sa-mod-item-on' : 'sa-mod-item-off' }}">
                        <span class="sa-mod-icon {{ $on ? 'sa-mod-icon-on' : 'sa-mod-icon-off' }}">
                            @if ($on)
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                            @else
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            @endif
                        </span>
                        <span class="sa-mod-item-label">{{ $label }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
    @empty
    <div style="grid-column:1/-1">
        <div class="sa-table-card" style="text-align:center;padding:3rem 1.5rem">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                 style="width:36px;height:36px;margin:0 auto .75rem;display:block;color:var(--sa-text-3)">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
            </svg>
            <p style="color:var(--sa-text-2);font-size:14px;margin-bottom:.5rem">
                Belum ada paket tersedia.
            </p>
            <p style="color:var(--sa-text-3);font-size:12.5px">
                Jalankan <code>php artisan db:seed --class=PackageSeeder --database=mysql_master</code>
            </p>
        </div>
    </div>
    @endforelse
</div>

{{-- ── Legend ──────────────────────────────────────────── --}}
@if ($packages->isNotEmpty())
<div class="sa-paket-legend">
    <div class="sa-legend-item">
        <span class="sa-legend-dot sa-legend-dot-core"></span>
        Core — ada di semua paket, tidak bisa dimatikan
    </div>
    <div class="sa-legend-item">
        <span class="sa-legend-dot sa-legend-dot-toggle"></span>
        Toggle aktif — modul ini tersedia di paket ini
    </div>
    <div class="sa-legend-item">
        <span class="sa-legend-dot sa-legend-dot-off"></span>
        Toggle off — modul tidak tersedia di paket ini
    </div>
</div>
@endif

@endsection
