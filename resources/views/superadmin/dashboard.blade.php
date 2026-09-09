@extends('superadmin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
/* ── Dashboard overrides ────────────────────────────────── */
.sa-stat-grid { grid-template-columns: repeat(3, 1fr); }
.sa-icon-info { background: #EFF6FF; color: #2563EB; }
.sa-icon-archive { background: #F3F4F6; color: #4B5563; }

/* Expired tenant banner */
.sa-expired-banner {
    display: flex; gap: .875rem; align-items: flex-start;
    background: #FFF1F2; border: 1px solid #FECDD3;
    border-radius: 12px; padding: 1rem 1.25rem;
    margin-bottom: 1.75rem;
}
.sa-expired-icon {
    width: 36px; height: 36px; flex-shrink: 0;
    background: #FFE4E6; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}
.sa-expired-icon svg { width: 18px; height: 18px; color: #E11D48; }
.sa-expired-title {
    font-family: var(--sa-ff-display);
    font-size: 15px; font-weight: 700; color: #9F1239; margin-bottom: .375rem;
}
.sa-expired-pills { display: flex; flex-wrap: wrap; gap: .5rem; }
.sa-expired-pills span {
    background: rgba(255,255,255,.7); border: 1px solid #FECDD3;
    border-radius: 6px; padding: 2px 10px;
    font-size: 12.5px; color: #BE185D; font-weight: 500;
}

/* Status badges */
.sa-sts {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 999px;
    font-size: 11.5px; font-weight: 600;
}
.sa-sts-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.sa-sts-aktif    { background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; }
.sa-sts-aktif    .sa-sts-dot { background: #22C55E; }
.sa-sts-nonaktif { background: #F5F6FA; color: #6B7280; border: 1px solid #D1D5DB; }
.sa-sts-nonaktif .sa-sts-dot { background: #9CA3AF; }
.sa-sts-suspend  { background: #FFF7ED; color: #C2410C; border: 1px solid #FED7AA; }
.sa-sts-suspend  .sa-sts-dot { background: #F97316; }
.sa-sts-expired  { background: #FFF1F2; color: #E11D48; border: 1px solid #FECDD3; }
.sa-sts-expired  .sa-sts-dot { background: #F43F5E; animation: sa-blink 1.5s ease-in-out infinite; }
.sa-sts-archived { background: #F3F4F6; color: #4B5563; border: 1px solid #D1D5DB; }
.sa-sts-archived .sa-sts-dot { background: #6B7280; }
@keyframes sa-blink { 0%,100%{ opacity:1; } 50%{ opacity:.3; } }

/* Package badge */
.sa-pkg-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 24px; height: 20px; padding: 0 5px;
    background: rgba(242,98,46,.1); color: var(--sa-primary);
    border: 1px solid rgba(242,98,46,.25);
    border-radius: 5px; font-size: 11px; font-weight: 700;
    font-family: var(--sa-ff-display);
}

/* Search */
.sa-table-header-r { display: flex; align-items: center; gap: .75rem; }
.sa-search-wrap { position: relative; }
.sa-search-wrap > svg {
    position: absolute; left: 9px; top: 50%; transform: translateY(-50%);
    width: 14px; height: 14px; color: var(--sa-text-3); pointer-events: none;
}
.sa-search-input {
    height: 36px; padding: 0 12px 0 29px;
    border: 1.5px solid var(--sa-border); border-radius: 8px;
    font-family: var(--sa-ff-sans); font-size: 13px;
    color: var(--sa-text); background: #FAFAF9;
    width: 200px; outline: none; transition: border-color .13s;
}
.sa-search-input:focus {
    border-color: var(--sa-primary); background: #fff;
    box-shadow: 0 0 0 3px rgba(242,98,46,.1);
}
.sa-search-input::placeholder { color: #C4C0BA; }

/* Table action icon buttons */
.sa-tbl-acts { display: flex; align-items: center; gap: 4px; }
.sa-btn-tbl {
    width: 30px; height: 28px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 7px; border: 1.5px solid var(--sa-border);
    background: none; cursor: pointer; color: var(--sa-text-2);
    transition: border-color .13s, background .13s, color .13s;
}
.sa-btn-tbl:hover { border-color: var(--sa-primary); color: var(--sa-primary); background: #fff5f1; }
.sa-btn-tbl.sa-tbl-off { opacity: .3; pointer-events: none; }
.sa-btn-tbl.sa-btn-tbl-danger { color: #DC2626; border-color: #FECACA; }
.sa-btn-tbl.sa-btn-tbl-danger:hover { border-color: #DC2626; color: #DC2626; background: #FEF2F2; }
.sa-btn-tbl svg { width: 13px; height: 13px; }

/* Expired date accent */
.sa-date-expire-sub { display: block; font-size: 11px; color: #E11D48; margin-top: 1px; }

/* Search / filter no-results row */
#sa-no-results { display: none; }

/* Filter status tabs */
.sa-filter-bar {
    display: flex; gap: 4px; flex-wrap: wrap; align-items: center;
    padding: .5rem 1rem;
    border-bottom: 1px solid var(--sa-border-2);
}
.sa-tab {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 11px;
    border-radius: 8px; border: 1.5px solid transparent;
    background: none; cursor: pointer;
    font-family: var(--sa-ff-sans); font-size: 12.5px; font-weight: 500;
    color: var(--sa-text-2);
    transition: background .12s, color .12s, border-color .12s;
    white-space: nowrap;
}
.sa-tab:hover { background: rgba(0,0,0,.04); color: var(--sa-text); }
html.dark .sa-tab:hover { background: rgba(255,255,255,.05); color: var(--sa-text); }
.sa-tab.active {
    background: rgba(242,98,46,.08);
    border-color: rgba(242,98,46,.28);
    color: var(--sa-primary);
}
.sa-tab-count {
    font-size: 11px; font-weight: 600;
    padding: 0 5px; border-radius: 4px; min-width: 18px; text-align: center;
    background: var(--sa-border); opacity: .85;
}
.sa-tab.active .sa-tab-count { background: rgba(242,98,46,.15); opacity: 1; }
.sa-tab-expired { color: #E11D48; }
.sa-tab-expired.active {
    background: rgba(225,29,72,.07);
    border-color: rgba(225,29,72,.25);
    color: #E11D48;
}

/* Backup history inline */
.sa-backup-list { list-style:none; display:flex; flex-direction:column; gap:4px; margin-top:6px; }
.sa-backup-item { display:flex; align-items:center; gap:6px; font-size:11.5px; color:var(--sa-text-2); }
.sa-backup-dl-link {
    display:inline-flex; align-items:center; gap:3px;
    font-size:11px; font-weight:600; color:var(--sa-primary);
    text-decoration:none; padding:1px 7px;
    border:1px solid rgba(242,98,46,.3); border-radius:5px; background:rgba(242,98,46,.05);
}
.sa-backup-dl-link:hover { background:rgba(242,98,46,.12); }

/* ── Dark mode overrides ────────────────────────────── */
html.dark .sa-icon-info    { background: rgba(37,99,235,.2);   color: #93C5FD; }
html.dark .sa-icon-archive { background: rgba(255,255,255,.06); color: #A8A29A; }

html.dark .sa-sts-aktif    { background: rgba(34,197,94,.12);  color: #4ADE80; border-color: rgba(34,197,94,.3); }
html.dark .sa-sts-aktif    .sa-sts-dot { background: #4ADE80; }
html.dark .sa-sts-nonaktif { background: rgba(255,255,255,.06); color: #A8A29A; border-color: rgba(255,255,255,.12); }
html.dark .sa-sts-nonaktif .sa-sts-dot { background: #A8A29A; }
html.dark .sa-sts-suspend  { background: rgba(249,115,22,.12); color: #FDBA74; border-color: rgba(249,115,22,.3); }
html.dark .sa-sts-suspend  .sa-sts-dot { background: #F97316; }
html.dark .sa-sts-expired  { background: rgba(244,63,94,.12);  color: #FB7185; border-color: rgba(244,63,94,.3); }
html.dark .sa-sts-expired  .sa-sts-dot { background: #F43F5E; }
html.dark .sa-sts-archived { background: rgba(255,255,255,.05); color: #A8A29A; border-color: rgba(255,255,255,.1); }
html.dark .sa-sts-archived .sa-sts-dot { background: #6E685F; }

html.dark .sa-expired-banner {
    background: rgba(244,63,94,.1); border-color: rgba(244,63,94,.3);
}
html.dark .sa-expired-icon   { background: rgba(225,29,72,.2); }
html.dark .sa-expired-title  { color: #FB7185; }
html.dark .sa-expired-pills span {
    background: rgba(255,255,255,.06); border-color: rgba(244,63,94,.3); color: #FB7185;
}

html.dark .sa-search-input       { background: #28231D; }
html.dark .sa-search-input:focus { background: #1F1B17; }
html.dark .sa-btn-tbl:hover      { background: rgba(242,98,46,.12); }

/* ── Responsive (dashboard content) ──────────────────── */
@media (max-width: 1023px) {
    .sa-stat-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .sa-stat-grid { grid-template-columns: 1fr; }

    .sa-table-header-r {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
        gap: .5rem;
    }
    .sa-search-wrap { width: 100%; }
    .sa-search-input { width: 100%; }
    .sa-table-header-r .sa-btn { width: 100%; }

    .sa-tbl-acts { flex-wrap: wrap; }

    .sa-expired-banner { padding: .875rem 1rem; }
}
</style>

{{-- ── Stat cards ──────────────────────────────────────────── --}}
<div class="sa-stat-grid">

    <div class="sa-stat-card">
        <div class="sa-stat-icon sa-icon-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
            </svg>
        </div>
        <div>
            <div class="sa-stat-label">Total Tenant</div>
            <div class="sa-stat-value">{{ $stats['total'] }}</div>
        </div>
    </div>

    <div class="sa-stat-card">
        <div class="sa-stat-icon sa-icon-success">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        <div>
            <div class="sa-stat-label">Tenant Aktif</div>
            <div class="sa-stat-value" style="color:#15803D">{{ $stats['aktif'] }}</div>
        </div>
    </div>

    <div class="sa-stat-card">
        <div class="sa-stat-icon sa-icon-neutral">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div>
            <div class="sa-stat-label">Non-aktif / Suspend</div>
            <div class="sa-stat-value" style="color:#6B7280">{{ $stats['nonaktif'] }}</div>
        </div>
    </div>

    <div class="sa-stat-card">
        <div class="sa-stat-icon sa-icon-archive">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20.25 7.5v11.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V7.5M3.75 3h16.5a1.5 1.5 0 0 1 1.5 1.5v1.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-1.5a1.5 1.5 0 0 1 1.5-1.5ZM9.75 12h4.5"/>
            </svg>
        </div>
        <div>
            <div class="sa-stat-label">Arsip</div>
            <div class="sa-stat-value" style="color:#4B5563">{{ $stats['archived'] }}</div>
        </div>
    </div>

    <div class="sa-stat-card">
        <div class="sa-stat-icon sa-icon-info">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75"/>
            </svg>
        </div>
        <div>
            <div class="sa-stat-label">DB Pool Tersisa</div>
            <div class="sa-stat-value" style="color:#2563EB">{{ $stats['db_tersisa'] }}</div>
        </div>
    </div>

</div>

{{-- ── Banner tenant lewat tanggal ────────────────────────── --}}
@if ($lewatTanggal->isNotEmpty())
<div class="sa-expired-banner">
    <div class="sa-expired-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
        </svg>
    </div>
    <div>
        <div class="sa-expired-title">
            {{ $lewatTanggal->count() }} Gym Lewat Tanggal Langganan
        </div>
        <div class="sa-expired-pills">
            @foreach ($lewatTanggal as $t)
                <span>
                    {{ $t->nama_gym }}
                    <span style="opacity:.65">— {{ $t->tgl_selesai->diffForHumans() }}</span>
                </span>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Tabel tenant ─────────────────────────────────────────── --}}
<div class="sa-table-card">
    <div class="sa-table-header">
        <div>
            <div class="sa-table-title">Daftar Gym Terdaftar</div>
            <div class="sa-table-sub">{{ $stats['total'] }} gym dalam sistem</div>
        </div>
        <div class="sa-table-header-r">
            <div class="sa-search-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input type="text" id="tenant-search"
                       class="sa-search-input"
                       placeholder="Cari gym...">
            </div>
            <a href="{{ route('super_admin.aktivasi') }}" class="sa-btn sa-btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"
                     style="width:14px;height:14px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Aktivasi Gym Baru
            </a>
        </div>
    </div>

    {{-- Filter status tabs --}}
    @php
        $fcToday = now()->startOfDay();
        $filterCounts = [
            'all'      => $tenants->count(),
            'aktif'    => $tenants->filter(fn($t) => $t->status === 'aktif' && !$t->tgl_selesai?->lt($fcToday))->count(),
            'expired'  => $tenants->filter(fn($t) => $t->status === 'aktif' && $t->tgl_selesai?->lt($fcToday))->count(),
            'nonaktif' => $tenants->where('status', 'nonaktif')->count(),
            'suspend'  => $tenants->where('status', 'suspend')->count(),
            'archived' => $tenants->where('status', 'archived')->count(),
        ];
    @endphp
    <div class="sa-filter-bar">
        <button class="sa-tab active" data-filter="all">
            Semua <span class="sa-tab-count">{{ $filterCounts['all'] }}</span>
        </button>
        <button class="sa-tab" data-filter="aktif">
            Aktif <span class="sa-tab-count">{{ $filterCounts['aktif'] }}</span>
        </button>
        @if ($filterCounts['expired'] > 0)
        <button class="sa-tab sa-tab-expired" data-filter="expired">
            Lewat Tanggal <span class="sa-tab-count">{{ $filterCounts['expired'] }}</span>
        </button>
        @endif
        <button class="sa-tab" data-filter="nonaktif">
            Nonaktif <span class="sa-tab-count">{{ $filterCounts['nonaktif'] }}</span>
        </button>
        <button class="sa-tab" data-filter="suspend">
            Suspend <span class="sa-tab-count">{{ $filterCounts['suspend'] }}</span>
        </button>
        @if ($filterCounts['archived'] > 0)
        <button class="sa-tab" data-filter="archived">
            Arsip <span class="sa-tab-count">{{ $filterCounts['archived'] }}</span>
        </button>
        @endif
    </div>

    <div style="overflow-x:auto">
        <table class="sa-table" id="tenant-table">
            <thead>
                <tr>
                    <th style="width:100px">Aksi</th>
                    <th>Nama Gym</th>
                    <th>Subdomain</th>
                    <th style="width:76px;text-align:center">Paket</th>
                    <th style="width:148px">Status</th>
                    <th style="width:108px">Tgl Mulai</th>
                    <th style="width:130px">Tgl Selesai</th>
                    <th>Backup Tersedia</th>
                </tr>
            </thead>
            <tbody>

                @forelse ($tenants as $tenant)
                @php
                    $today   = now()->startOfDay();
                    $expired = $tenant->status === 'aktif' && $tenant->tgl_selesai?->lt($today);
                    $stsClass = match(true) {
                        $expired                          => 'sa-sts-expired',
                        $tenant->status === 'aktif'       => 'sa-sts-aktif',
                        $tenant->status === 'nonaktif'    => 'sa-sts-nonaktif',
                        $tenant->status === 'suspend'     => 'sa-sts-suspend',
                        $tenant->status === 'archived'    => 'sa-sts-archived',
                        default                           => 'sa-sts-nonaktif',
                    };
                    $stsLabel = match(true) {
                        $expired                          => 'Lewat Tanggal',
                        $tenant->status === 'aktif'       => 'Aktif',
                        $tenant->status === 'nonaktif'    => 'Non-aktif',
                        $tenant->status === 'suspend'     => 'Suspend',
                        $tenant->status === 'archived'    => 'Arsip',
                        default                           => ucfirst($tenant->status),
                    };

                    // Clear DB: hanya aktif jika nonaktif + punya pool + sudah download backup
                    $poolId         = $tenant->databasePool?->id;
                    $hasDownloaded  = $tenant->backups->where('downloaded', true)->isNotEmpty();
                    $canClear       = $tenant->status === 'nonaktif' && $poolId && $hasDownloaded;
                    $canBackup      = $tenant->status !== 'archived';
                    $clearTitle     = match(true) {
                        $tenant->status === 'archived'                          => 'Database sudah di-clear',
                        $tenant->status === 'aktif'                             => 'Backup & nonaktifkan terlebih dahulu',
                        $tenant->status === 'nonaktif' && ! $poolId            => 'Tidak ada database pool',
                        $tenant->status === 'nonaktif' && ! $hasDownloaded
                            && $tenant->backups->isEmpty()                      => 'Belum ada backup — backup terlebih dahulu',
                        $tenant->status === 'nonaktif' && ! $hasDownloaded     => 'Download backup SQL terlebih dahulu',
                        default                                                 => 'Clear Database (daur ulang pool)',
                    };
                    $rowStatus = $expired ? 'expired' : $tenant->status;
                @endphp
                <tr data-gym="{{ strtolower($tenant->nama_gym . ' ' . $tenant->subdomain) }}"
                    data-status="{{ $rowStatus }}">

                    {{-- Aksi --}}
                    <td>
                        @include('superadmin.partials.tenant-action-buttons')
                    </td>

                    {{-- Nama Gym --}}
                    <td>
                        <div style="font-weight:600;color:var(--sa-text)">{{ $tenant->nama_gym }}</div>
                        @if ($tenant->email)
                            <div style="font-size:11.5px;color:var(--sa-text-3);margin-top:1px">{{ $tenant->email }}</div>
                        @endif
                    </td>

                    {{-- Subdomain --}}
                    <td>
                        <span class="sa-td-mono">{{ $tenant->subdomain }}</span>
                        <span style="font-size:11px;color:var(--sa-text-3)">.{{ env('TENANT_BASE_DOMAIN', 'sistemgate.com') }}</span>
                    </td>

                    {{-- Paket --}}
                    <td style="text-align:center">
                        @if ($tenant->package)
                            <span class="sa-pkg-badge" title="{{ $tenant->package->nama }}">
                                {{ $tenant->package->label }}
                            </span>
                        @else
                            <span style="color:var(--sa-text-3);font-size:13px">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="sa-sts {{ $stsClass }}">
                            <span class="sa-sts-dot"></span>
                            {{ $stsLabel }}
                        </span>
                    </td>

                    {{-- Tgl Mulai --}}
                    <td style="font-size:13px;color:var(--sa-text-2)">
                        {{ $tenant->tgl_mulai?->format('d M Y') ?? '—' }}
                    </td>

                    {{-- Tgl Selesai --}}
                    <td>
                        @if ($tenant->tgl_selesai)
                            <span style="font-size:13px;{{ $expired ? 'color:#E11D48;font-weight:600' : 'color:var(--sa-text-2)' }}">
                                {{ $tenant->tgl_selesai->format('d M Y') }}
                            </span>
                            @if ($expired)
                                <span class="sa-date-expire-sub">{{ $tenant->tgl_selesai->diffForHumans() }}</span>
                            @endif
                        @else
                            <span style="color:var(--sa-text-3)">—</span>
                        @endif
                    </td>

                    {{-- Backup tersedia --}}
                    <td>
                        @if ($tenant->backups->isEmpty())
                            <span style="color:var(--sa-text-3);font-size:12.5px">Belum ada</span>
                        @else
                            <ul class="sa-backup-list">
                                @foreach ($tenant->backups->sortByDesc('tgl_backup')->take(3) as $bk)
                                    <li class="sa-backup-item">
                                        <span style="color:var(--sa-text-3)">{{ $bk->tgl_backup->format('d M Y') }}</span>
                                        @if ($bk->sql_path)
                                            <a href="{{ route('super_admin.backup.download', [$bk->id, 'sql']) }}"
                                               class="sa-backup-dl-link">
                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:10px;height:10px">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                                </svg>
                                                .sql{{ $bk->downloaded ? ' ✓' : '' }}
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>

                </tr>
                @empty
                <tr class="sa-empty">
                    <td colspan="8">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                             style="width:32px;height:32px;margin:0 auto .75rem;display:block;color:var(--sa-text-3)">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                        </svg>
                        Belum ada gym yang diaktifkan.
                        <a href="{{ route('super_admin.aktivasi') }}"
                           style="color:var(--sa-primary);font-weight:600;text-decoration:none">
                            Aktivasi gym pertama →
                        </a>
                    </td>
                </tr>
                @endforelse

                @if ($tenants->isNotEmpty())
                <tr id="sa-no-results" class="sa-empty">
                    <td colspan="8">Tidak ada gym yang cocok dengan pencarian.</td>
                </tr>
                @endif

            </tbody>
        </table>
    </div>
</div>

@endsection

@section('modals')
@include('superadmin.partials.tenant-action-modals')
@endsection

@section('scripts')
@include('superadmin.partials.tenant-action-scripts')
<script>
(function () {
    var currentFilter = 'all';
    var currentSearch = '';

    function applyFilters() {
        var rows  = document.querySelectorAll('#tenant-table tbody tr[data-status]');
        var noRes = document.getElementById('sa-no-results');
        var vis   = 0;

        rows.forEach(function (row) {
            var statusOk = currentFilter === 'all' || row.dataset.status === currentFilter;
            var searchOk = !currentSearch || (row.dataset.gym || '').indexOf(currentSearch) !== -1;
            var show     = statusOk && searchOk;
            row.style.display = show ? '' : 'none';
            if (show) vis++;
        });

        if (noRes) noRes.style.display = (vis === 0 && rows.length > 0) ? '' : 'none';
    }

    document.querySelectorAll('.sa-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.sa-tab').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });

    var searchInput = document.getElementById('tenant-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearch = this.value.toLowerCase().trim();
            applyFilters();
        });
    }
})();
</script>
@endsection
