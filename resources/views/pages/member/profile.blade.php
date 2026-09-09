@extends('layout.layout')
@php
    $title = 'Profile Saya';
    $subTitle = 'Profile Member';

    // Avatar initials
    $nameParts = explode(' ', trim($anggota->name));
    $initials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) $initials .= strtoupper(substr(end($nameParts), 0, 1));

    // Package duration badge & progress — pakai membership dengan tgl_selesai
    // PALING AKHIR (latest), bukan yang aktif hari ini, supaya perpanjangan
    // yang belum mulai tetap tercermin di "Berlaku s/d" & sisa hari.
    $latestMembership = $anggota->latest_membership;
    $packageBadge  = '';
    $daysRemaining = 0;
    $daysTotal     = 1;
    $progressPct   = 0;
    if ($latestMembership) {
        $dur = $latestMembership->tgl_mulai->diffInDays($latestMembership->tgl_selesai);
        if ($dur >= 330)     $packageBadge = 'TAHUNAN';
        elseif ($dur >= 25)  $packageBadge = 'BULANAN';
        elseif ($dur >= 7)   $packageBadge = 'MINGGUAN';
        else                 $packageBadge = 'HARIAN';
        $daysRemaining = max(0, now()->startOfDay()->diffInDays($latestMembership->tgl_selesai, false));
        $daysTotal     = max(1, $latestMembership->tgl_mulai->diffInDays($latestMembership->tgl_selesai));
        $progressPct   = $latestMembership->tgl_mulai->isFuture()
            ? 0
            : min(100, (int) round(($latestMembership->tgl_mulai->diffInDays(now()) / $daysTotal) * 100));
    }

    // BMI
    $bmiValue    = $anggota->bmi;
    $bmiCategory = null;
    $bmiClass    = 'mp-bmi-success';
    $bmiPosition = 50;
    if ($bmiValue) {
        if ($bmiValue < 18.5)   { $bmiCategory = 'Kurus';    $bmiClass = 'mp-bmi-info'; }
        elseif ($bmiValue < 25) { $bmiCategory = 'Normal';   $bmiClass = 'mp-bmi-success'; }
        elseif ($bmiValue < 30) { $bmiCategory = 'Gemuk';    $bmiClass = 'mp-bmi-warning'; }
        else                    { $bmiCategory = 'Obesitas';  $bmiClass = 'mp-bmi-danger'; }
        $bmiPosition = min(98, max(2, (int) round((($bmiValue - 10) / 30) * 100)));
    }

    $hariId  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulanId = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
@endphp

@section('content')
<style>
/* ============================================================
   Member Profile – CSS variables (scoped)
   ============================================================ */
.mp {
    --s : #FAFAF9;   /* surface */
    --s2: #F1F0ED;   /* surface-2 */
    --bd: #E2E0DB;   /* border */
    --t1: #1A1A18;   /* text */
    --t2: #6E6A63;   /* text-2 */
    --t3: #9C978E;   /* text-3 */
    --ac: #F2622E;   /* accent */
    --at: #BC3E14;   /* accent-text */
    --as: #FFE2D3;   /* accent-soft */
    --bi: #DCDAD4;   /* bar-idle */
    --sh: 0 1px 3px rgba(26,22,18,.07), 0 3px 8px rgba(26,22,18,.08);
    --r : 16px;
    --cs: #15803D;   /* color success */
    --cw: #B45309;   /* color warning */
    --cd: #B91C1C;   /* color danger */
    --ci: #1D4ED8;   /* color info */
    --ab: #fff;      /* avatar border */
}
.dark .mp {
    --s : #1F1B17;
    --s2: #28231D;
    --bd: #332D26;
    --t1: #F4F1EC;
    --t2: #A8A29A;
    --t3: #6E685F;
    --at: #FB7843;
    --as: rgba(242,98,46,.16);
    --bi: #332D26;
    --sh: none;
    --cs: #4ADE80;
    --cw: #FBBF24;
    --cd: #F87171;
    --ci: #60A5FA;
    --ab: #28231D;
}

/* ---- Card ---- */
.mp-card {
    background   : var(--s);
    border       : 1px solid var(--bd);
    border-radius: var(--r);
    box-shadow   : var(--sh);
    overflow     : hidden;
}
.mp-ch {
    display        : flex;
    align-items    : center;
    justify-content: space-between;
    padding        : 16px 20px;
    border-bottom  : 1px solid var(--bd);
}
.mp-ch h3 {
    margin       : 0;
    font-family  : 'Barlow Condensed', Oswald, sans-serif;
    font-size    : 20px;
    font-weight  : 600;
    color        : var(--t1);
    letter-spacing: .01em;
}
.mp-ch-sub { font-size: 12px; color: var(--t2); margin-top: 1px; }

/* ---- Banner & avatar ---- */
.mp-banner {
    height    : 76px;
    background: linear-gradient(120deg,#BC3E14,#F2622E 72%,#FB7843);
}
.mp-avatar-row {
    display        : flex;
    justify-content: center;
    margin-top     : -40px;
    padding        : 0 20px;
    position       : relative;
}
.mp-av, .mp-av-init {
    width        : 80px;
    height       : 80px;
    border-radius: 50%;
    border       : 4px solid var(--ab);
    box-shadow   : 0 2px 8px rgba(0,0,0,.15);
    flex         : none;
}
.mp-av       { object-fit: cover; }
.mp-av-init  {
    background: #0F766E;
    display   : flex;
    align-items: center;
    justify-content: center;
    font-family: 'Barlow Condensed', sans-serif;
    font-size  : 26px;
    font-weight: 700;
    color      : #fff;
}

/* ---- Profile body ---- */
.mp-pinfo { padding: 10px 20px 20px; text-align: center; }
.mp-pname {
    margin    : 0 0 3px;
    font-family: 'Barlow Condensed', Oswald, sans-serif;
    font-size : 24px;
    font-weight: 700;
    color     : var(--t1);
    line-height: 1.1;
}
.mp-pemail { font-size: 13px; color: var(--t2); margin-bottom: 12px; }

/* ---- Status badges ---- */
.mp-badge {
    display     : inline-flex;
    align-items : center;
    gap         : 5px;
    font-size   : 12px;
    font-weight : 600;
    padding     : 5px 14px;
    border-radius: 999px;
    margin-bottom: 14px;
}
.mp-badge-ok  { color: var(--cs); background: rgba(21,128,61,.10); border: 1px solid rgba(21,128,61,.22); }
.dark .mp-badge-ok { background: rgba(74,222,128,.10); border-color: rgba(74,222,128,.22); }
.mp-badge-off { color: var(--cw); background: rgba(180,83,9,.10);  border: 1px solid rgba(180,83,9,.22); }
.dark .mp-badge-off { background: rgba(251,191,36,.10); border-color: rgba(251,191,36,.22); }

/* ---- Package box ---- */
.mp-pkg {
    background   : var(--as);
    border-radius: 12px;
    padding      : 14px 16px;
    text-align   : left;
    margin-bottom: 14px;
}
.mp-pkg-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
.mp-pkg-lbl { font-size: 12px; color: var(--t2); }
.mp-pkg-dur { font-size: 10.5px; font-weight: 700; letter-spacing: .05em; color: var(--at); }
.mp-pkg-name {
    font-family: 'Barlow Condensed', Oswald, sans-serif;
    font-size  : 20px;
    font-weight: 700;
    color      : var(--at);
    line-height: 1.1;
    margin-bottom: 10px;
}
.mp-pkg-dates { display: flex; justify-content: space-between; font-size: 11px; color: var(--t2); margin-bottom: 5px; }
.mp-pkg-days  { font-weight: 600; color: var(--t1); }
.mp-pkgbar    { height: 5px; background: var(--bi); border-radius: 999px; overflow: hidden; }
.mp-pkgbar-f  { height: 100%; background: var(--ac); border-radius: 999px; }

/* ---- Stats ---- */
.mp-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.mp-stat  {
    background   : var(--s2);
    border       : 1px solid var(--bd);
    border-radius: 12px;
    padding      : 12px;
    text-align   : center;
}
.mp-stat-v { font-family: 'Barlow Condensed', sans-serif; font-size: 26px; font-weight: 700; color: var(--t1); line-height: 1; }
.mp-stat-l { font-size: 11px; color: var(--t2); margin-top: 4px; }

/* ---- Digital card ---- */
.mp-dc {
    background   : linear-gradient(135deg,#1a1512,#2d1b0e);
    border-radius: var(--r);
    padding      : 18px;
    position     : relative;
    overflow     : hidden;
}
.mp-dc::before {
    content : '';
    position: absolute;
    inset   : 0;
    background: radial-gradient(ellipse at top right, rgba(242,98,46,.18), transparent 60%);
    pointer-events: none;
}
.mp-dc-hdr {
    display        : flex;
    align-items    : center;
    justify-content: space-between;
    position       : relative;
    margin-bottom  : 16px;
}
.mp-dc-lbl { font-size: 11px; font-weight: 700; letter-spacing: .14em; color: rgba(255,255,255,.55); }
.mp-dc-body { display: flex; align-items: center; gap: 14px; position: relative; margin-bottom: 16px; }
.mp-dc-qr   { background: #fff; border-radius: 12px; padding: 8px; flex: none; line-height: 0; }
.mp-dc-info { min-width: 0; }
.mp-dc-name {
    font-family  : 'Barlow Condensed', Oswald, sans-serif;
    font-size    : 22px;
    font-weight  : 700;
    line-height  : 1.05;
    color        : #fff;
    overflow     : hidden;
    text-overflow: ellipsis;
    white-space  : nowrap;
}
.mp-dc-id {
    font-size     : 12px;
    color         : rgba(255,255,255,.5);
    font-family   : 'JetBrains Mono', ui-monospace, monospace;
    letter-spacing: .03em;
    margin-top    : 2px;
}
.mp-dc-status {
    display     : inline-flex;
    align-items : center;
    gap         : 4px;
    margin-top  : 6px;
    font-size   : 11px;
    font-weight : 700;
    color       : #4ade80;
    background  : rgba(74,222,128,.10);
    border      : 1px solid rgba(74,222,128,.28);
    padding     : 3px 10px;
    border-radius: 999px;
}
.mp-dc-btns { display: flex; gap: 8px; position: relative; }
.mp-dc-btn {
    flex           : 1;
    display        : inline-flex;
    align-items    : center;
    justify-content: center;
    gap            : 6px;
    font-size      : 12px;
    font-weight    : 600;
    padding        : 8px 12px;
    border-radius  : 10px;
    cursor         : pointer;
    text-decoration: none;
    border         : none;
    transition     : opacity .15s;
}
.mp-dc-btn:hover { opacity: .85; }
.mp-dc-ghost   { background: rgba(255,255,255,.12); color: #fff !important; border: 1px solid rgba(255,255,255,.2); }
.mp-dc-primary { background: #F2622E; color: #fff !important; }

/* ---- Data fields (flex rows, no misalignment) ---- */
.mp-fields { padding: 0 20px 4px; }
.mp-row {
    display      : flex;
    gap          : 24px;
    border-bottom: 1px solid var(--bd);
    align-items  : stretch;
}
.mp-row:last-child { border-bottom: none; }
.mp-field     { flex: 1; padding: 12px 0; min-width: 0; }
.mp-field-lbl {
    font-size     : 11px;
    font-weight   : 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    color         : var(--t3);
    margin-bottom : 3px;
}
.mp-field-val { font-weight: 600; color: var(--t1); word-break: break-word; }
.mp-field-val.mono { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 13px; }

/* ---- BMI ---- */
.mp-bmi {
    display    : flex;
    align-items: center;
    gap        : 16px;
    padding    : 14px 20px 18px;
    border-top : 1px solid var(--bd);
}
.mp-bmi-lbl-txt {
    font-size     : 11px;
    font-weight   : 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    color         : var(--t2);
    margin-bottom : 4px;
}
.mp-bmi-num {
    font-family : 'Barlow Condensed', Oswald, sans-serif;
    font-size   : 38px;
    font-weight : 700;
    line-height : 1;
}
.mp-bmi-success { color: var(--cs); }
.mp-bmi-warning { color: var(--cw); }
.mp-bmi-danger  { color: var(--cd); }
.mp-bmi-info    { color: var(--ci); }
.mp-bmi-bar-wrap { flex: 1; }
.mp-bmi-track {
    height       : 8px;
    border-radius: 999px;
    background   : linear-gradient(to right,#93c5fd 0%,#86efac 28%,#fcd34d 55%,#f87171 75%);
    position     : relative;
}
.mp-bmi-thumb {
    position     : absolute;
    top          : 50%;
    transform    : translate(-50%,-50%);
    width        : 16px;
    height       : 16px;
    border-radius: 50%;
    border       : 3px solid var(--s);
    box-shadow   : 0 1px 4px rgba(0,0,0,.25);
}
.mp-bmi-cats {
    display        : flex;
    justify-content: space-between;
    font-size      : 10px;
    color          : var(--t3);
    margin-top     : 6px;
}
.mp-bmi-cat { text-align: right; flex: none; }
.mp-bmi-cat-l { font-size: 11px; color: var(--t3); }
.mp-bmi-cat-v { font-size: 14px; font-weight: 700; margin-top: 2px; }

/* ---- Kehadiran list ---- */
.mp-klist { padding: 8px 12px; }
.mp-kitem {
    display      : flex;
    align-items  : center;
    gap          : 13px;
    padding      : 10px 10px;
    border-radius: 12px;
    transition   : background .12s;
}
.mp-kitem:hover { background: var(--s2); }
.mp-kicon {
    width          : 36px;
    height         : 36px;
    border-radius  : 50%;
    display        : flex;
    align-items    : center;
    justify-content: center;
    flex           : none;
}
.mp-kicon-in  { background: rgba(21,128,61,.10); }
.dark .mp-kicon-in  { background: rgba(74,222,128,.10); }
.mp-kicon-out { background: rgba(180,83,9,.10); }
.dark .mp-kicon-out { background: rgba(251,191,36,.10); }
.mp-kicon-in  svg { stroke: var(--cs); width:18px; height:18px; }
.mp-kicon-out svg { stroke: var(--cw); width:18px; height:18px; }
.mp-kinfo     { flex: 1; min-width: 0; }
.mp-kdate     { font-weight: 600; font-size: 13.5px; color: var(--t1); }
.mp-ktime     { font-size: 12px; color: var(--t3); font-variant-numeric: tabular-nums; margin-top: 1px; }
.mp-kpill {
    font-size    : 11px;
    font-weight  : 700;
    padding      : 4px 12px;
    border-radius: 999px;
    flex         : none;
}
.mp-kpill-in  { color: var(--cs); background: rgba(21,128,61,.10); }
.dark .mp-kpill-in  { background: rgba(74,222,128,.10); }
.mp-kpill-out { color: var(--cw); background: rgba(180,83,9,.10); }
.dark .mp-kpill-out { background: rgba(251,191,36,.10); }

/* ---- Modal ---- */
.mp-qrborder {
    border       : 3px solid #F2622E;
    border-radius: 16px;
    padding      : 20px;
    display      : inline-block;
    background   : #fff;
    line-height  : 0;
}
.mp-instr {
    background   : var(--s2);
    border-radius: 12px;
    padding      : 14px 16px;
    margin-top   : 16px;
    text-align   : left;
    border       : 1px solid var(--bd);
}
.mp-instr-ttl { font-size: 13px; font-weight: 600; color: var(--ci); margin-bottom: 8px; }
.mp-instr ol  { margin: 0; padding-left: 18px; font-size: 13px; color: var(--t2); line-height: 1.7; }
</style>

@if (session('success'))
    <div class="alert alert-success bg-success-50 text-success-600 px-6 py-3 mb-4 rounded-lg flex items-center justify-between">
        {{ session('success') }}
        <button class="remove-button text-success-600 text-2xl"><iconify-icon icon="iconamoon:sign-times-light"></iconify-icon></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger bg-danger-100 text-danger-600 px-6 py-3 mb-4 rounded-lg flex items-center justify-between">
        {{ session('error') }}
        <button class="remove-button text-danger-600 text-2xl"><iconify-icon icon="iconamoon:sign-times-light"></iconify-icon></button>
    </div>
@endif

<div class="mp">
<div class="grid grid-cols-12 gap-5">

    {{-- ============================================================
         LEFT COLUMN (col-4)
    ============================================================ --}}
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-5">

        {{-- PROFILE CARD --}}
        <div class="mp-card">
            <div class="mp-banner"></div>
            <div class="mp-avatar-row">
                @if ($anggota->user && $anggota->user->photo)
                    <img src="{{ asset('storage/' . $anggota->user->photo) }}"
                         alt="{{ $anggota->name }}" class="mp-av">
                @else
                    <div class="mp-av-init">{{ $initials }}</div>
                @endif
            </div>
            <div class="mp-pinfo">
                <h2 class="mp-pname">{{ $anggota->name }}</h2>
                <div class="mp-pemail">{{ $anggota->user ? $anggota->user->email : '-' }}</div>

                @if ($anggota->status_keanggotaan)
                    <div class="mp-badge mp-badge-ok">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:13px;height:13px;flex:none"><path d="M20 6 9 17l-5-5"/></svg>
                        Member Aktif
                    </div>
                @else
                    <div class="mp-badge mp-badge-off">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:13px;height:13px;flex:none"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        Membership Tidak Aktif
                    </div>
                @endif

                @if ($latestMembership)
                    <div class="mp-pkg">
                        <div class="mp-pkg-top">
                            <span class="mp-pkg-lbl">Paket aktif</span>
                            <span class="mp-pkg-dur">{{ $packageBadge }}</span>
                        </div>
                        <div class="mp-pkg-name">
                            {{ optional($latestMembership->paketMembership)->nama_paket ?? $latestMembership->nama_paket }}
                        </div>
                        <div class="mp-pkg-dates">
                            <span>Berlaku s/d {{ $latestMembership->tgl_selesai->format('d M Y') }}</span>
                            <span class="mp-pkg-days">{{ $daysRemaining }} hari</span>
                        </div>
                        <div class="mp-pkgbar"><div class="mp-pkgbar-f" style="width:{{ $progressPct }}%"></div></div>
                    </div>
                @endif

                <div class="mp-stats">
                    <div class="mp-stat">
                        <div class="mp-stat-v">{{ $totalKehadiran }}</div>
                        <div class="mp-stat-l">Total kunjungan</div>
                    </div>
                    <div class="mp-stat">
                        <div class="mp-stat-v">{{ $kehadiranBulanIni }}</div>
                        <div class="mp-stat-l">Kunjungan bln ini</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DIGITAL MEMBER CARD --}}
        <div class="mp-dc">
            <div class="mp-dc-hdr">
                <span class="mp-dc-lbl">KARTU MEMBER</span>
                <svg viewBox="0 0 40 40" fill="none" style="width:26px;height:26px;opacity:.9">
                    <path d="M20 2 35.3 11v18L20 38 4.7 29V11Z" fill="#fff"/>
                    <g stroke="#F2622E" stroke-width="2.6" stroke-linecap="round">
                        <path d="M13 20h14"/><path d="M13 16.5v7M27 16.5v7"/>
                    </g>
                </svg>
            </div>
            <div class="mp-dc-body">
                <div class="mp-dc-qr">
                    {!! DNS2D::getBarcodeHTML($anggota->id_kartu, 'QRCODE', 5, 5) !!}
                </div>
                <div class="mp-dc-info">
                    <div class="mp-dc-name">{{ $anggota->name }}</div>
                    <div class="mp-dc-id">{{ $anggota->id_kartu }}</div>
                    <div class="mp-dc-status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:11px;height:11px;flex:none"><path d="M20 6 9 17l-5-5"/></svg>
                        ACTIVE
                    </div>
                </div>
            </div>
            <div class="mp-dc-btns">
                <button type="button" onclick="HexaModal.show('qr-modal')" class="mp-dc-btn mp-dc-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex:none"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8v8M11 8v8M15 8v8"/></svg>
                    Perbesar QR
                </button>
                <a href="{{ route('member.download-card') }}" class="mp-dc-btn mp-dc-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex:none"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                    Unduh
                </a>
            </div>
        </div>

        {{-- PERSONAL TRAINER CARD --}}
        <div class="mp-card">
            <div class="mp-ch">
                <h3>Personal Trainer</h3>
            </div>
            <div style="padding:16px 20px;">
                @if ($ptMembership)
                    @php
                        $ptIsSessionsCompleted = $ptMembership->isSessionsCompleted();
                        if ($ptMembership->is_active && !$ptIsSessionsCompleted) {
                            $ptStatusLabel = 'Aktif';
                            $ptBadgeClass  = 'mp-badge-ok';
                        } elseif ($ptIsSessionsCompleted) {
                            $ptStatusLabel = 'Sesi habis';
                            $ptBadgeClass  = 'mp-badge-off';
                        } else {
                            $ptStatusLabel = 'Berakhir';
                            $ptBadgeClass  = 'mp-badge-off';
                        }
                    @endphp
                    <div class="mp-badge {{ $ptBadgeClass }}" style="margin-bottom:12px;">
                        @if ($ptBadgeClass === 'mp-badge-ok')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:13px;height:13px;flex:none"><path d="M20 6 9 17l-5-5"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:13px;height:13px;flex:none"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        @endif
                        {{ $ptStatusLabel }}
                    </div>

                    <div class="mp-pkg" style="margin-bottom:0;">
                        <div class="mp-pkg-top">
                            <span class="mp-pkg-lbl">Personal Trainer aktif</span>
                        </div>
                        <div class="mp-pkg-name">
                            {{ optional($ptMembership->paketPersonalTrainer)->nama_paket ?? '-' }}
                        </div>
                        <div style="font-size:12px;color:var(--t2);margin-bottom:10px;">
                            Trainer: {{ optional($ptMembership->trainer)->name ?? '-' }}
                        </div>
                        <div class="mp-pkg-dates">
                            <span>Sisa {{ $ptSisaSesi }} dari {{ $ptTotalSesi }} sesi</span>
                        </div>
                        <div class="mp-pkgbar" style="margin-bottom:10px;"><div class="mp-pkgbar-f" style="width:{{ $ptSessionPct }}%"></div></div>
                        <div class="mp-pkg-dates">
                            <span>Berlaku s/d {{ $ptMembership->tgl_selesai->format('d M Y') }}</span>
                            <span class="mp-pkg-days">{{ $ptDaysRemaining }} hari</span>
                        </div>
                    </div>
                @else
                    <div style="text-align:center;padding:14px 0;color:var(--t3);">
                        <div style="font-size:13px;margin-bottom:10px;">Belum ada paket personal trainer aktif.</div>
                        <a href="{{ route('member.paket') }}" style="font-size:13px;font-weight:600;color:var(--at);text-decoration:none;">Lihat paket &rarr;</a>
                    </div>
                @endif
            </div>
        </div>

    </div>
    {{-- END LEFT --}}

    {{-- ============================================================
         RIGHT COLUMN (col-8)
    ============================================================ --}}
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-5">

        {{-- DATA PRIBADI --}}
        <div class="mp-card">
            <div class="mp-ch">
                <h3>Data Pribadi</h3>
            </div>
            <div class="mp-fields">
                {{-- Baris 1 --}}
                <div class="mp-row">
                    <div class="mp-field">
                        <div class="mp-field-lbl">ID Kartu</div>
                        <div class="mp-field-val mono">{{ $anggota->id_kartu }}</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">No. Telepon</div>
                        <div class="mp-field-val">{{ $anggota->no_telp }}</div>
                    </div>
                </div>
                {{-- Baris 2 --}}
                <div class="mp-row">
                    <div class="mp-field">
                        <div class="mp-field-lbl">Jenis Kelamin</div>
                        <div class="mp-field-val">{{ $anggota->jenis_kelamin }}</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">Tanggal Lahir</div>
                        <div class="mp-field-val">{{ $anggota->tgl_lahir->format('d M Y') }} ({{ $anggota->age }} thn)</div>
                    </div>
                </div>
                {{-- Baris 3 --}}
                <div class="mp-row">
                    <div class="mp-field">
                        <div class="mp-field-lbl">Tempat Lahir</div>
                        <div class="mp-field-val">{{ $anggota->tempat_lahir }}</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">Golongan Darah</div>
                        <div class="mp-field-val">{{ $anggota->gol_darah }}</div>
                    </div>
                </div>
                {{-- Baris 4 --}}
                <div class="mp-row">
                    <div class="mp-field">
                        <div class="mp-field-lbl">Tinggi Badan</div>
                        <div class="mp-field-val">{{ $anggota->tinggi }} cm</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">Berat Badan</div>
                        <div class="mp-field-val">{{ $anggota->berat }} kg</div>
                    </div>
                </div>
                {{-- Baris 5 - Alamat (full) --}}
                <div class="mp-row">
                    <div class="mp-field" style="flex:1">
                        <div class="mp-field-lbl">Alamat</div>
                        <div class="mp-field-val">{{ $anggota->alamat }}</div>
                    </div>
                </div>
                {{-- Baris 6 - Tanggal Daftar (full) --}}
                <div class="mp-row">
                    <div class="mp-field" style="flex:1">
                        <div class="mp-field-lbl">Tanggal Daftar</div>
                        <div class="mp-field-val">{{ $anggota->tgl_daftar->format('d M Y') }}</div>
                    </div>
                </div>
                {{-- Baris 7 - Riwayat Kesehatan (optional, full) --}}
                @if ($anggota->riwayat_kesehatan)
                    <div class="mp-row" style="border-bottom:none;">
                        <div class="mp-field" style="flex:1">
                            <div class="mp-field-lbl">Riwayat Kesehatan</div>
                            <div class="mp-field-val">{{ $anggota->riwayat_kesehatan }}</div>
                        </div>
                    </div>
                @endif
            </div>

            @if ($bmiValue)
                <div class="mp-bmi">
                    <div>
                        <div class="mp-bmi-lbl-txt">Body Mass Index</div>
                        <div class="mp-bmi-num {{ $bmiClass }}">{{ $bmiValue }}</div>
                    </div>
                    <div class="mp-bmi-bar-wrap">
                        <div class="mp-bmi-track">
                            <div class="mp-bmi-thumb {{ $bmiClass }}"
                                 style="left:{{ $bmiPosition }}%;"></div>
                        </div>
                        <div class="mp-bmi-cats">
                            <span>Kurus</span><span>Normal</span><span>Gemuk</span><span>Obesitas</span>
                        </div>
                    </div>
                    <div class="mp-bmi-cat">
                        <div class="mp-bmi-cat-l">Kategori</div>
                        <div class="mp-bmi-cat-v {{ $bmiClass }}">{{ $bmiCategory }}</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- RIWAYAT KEHADIRAN --}}
        <div class="mp-card">
            <div class="mp-ch">
                <div>
                    <h3>Riwayat Kehadiran Terakhir</h3>
                    <div class="mp-ch-sub">10 aktivitas check-in / check-out terakhir</div>
                </div>
            </div>
            <div class="mp-klist">
                @if ($anggota->kehadirans->count() > 0)
                    @foreach ($anggota->kehadirans->take(10) as $kehadiran)
                        @php
                            $isIn        = $kehadiran->status === 'in';
                            $kehadiranTz = to_tenant_tz($kehadiran->created_at);
                            $namaHari    = $hariId[$kehadiranTz->dayOfWeek];
                            $namaBulan   = $bulanId[(int) $kehadiranTz->format('n')];
                            $tglFmt      = $namaHari . ', ' . $kehadiranTz->format('d') . ' ' . $namaBulan . ' ' . $kehadiranTz->format('Y');
                        @endphp
                        <div class="mp-kitem">
                            <div class="mp-kicon {{ $isIn ? 'mp-kicon-in' : 'mp-kicon-out' }}">
                                @if ($isIn)
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M5 12h13"/>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="mp-kinfo">
                                <div class="mp-kdate">{{ $tglFmt }}</div>
                                <div class="mp-ktime">{{ $kehadiranTz->format('H:i') }} {{ tz_label() }} · {{ $namaHari }}</div>
                            </div>
                            <span class="mp-kpill {{ $isIn ? 'mp-kpill-in' : 'mp-kpill-out' }}">
                                {{ $isIn ? 'CHECK IN' : 'CHECK OUT' }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:40px 0;color:var(--t3);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                             style="width:44px;height:44px;margin:0 auto 10px;display:block;opacity:.45">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M8 2v4M16 2v4M3 10h18M10 14l-2 2 2 2M14 14l2 2-2 2"/>
                        </svg>
                        <div style="font-size:13px;">Belum ada riwayat kehadiran</div>
                    </div>
                @endif
            </div>
        </div>

    </div>
    {{-- END RIGHT --}}

</div>

</div>

{{-- QR MODAL --}}
<x-modal id="qr-modal" title="Barcode Kartu Member" maxWidth="max-w-[480px]">
    <x-slot:body>
        <div style="text-align:center;">
            <div style="font-family:'Barlow Condensed',Oswald,sans-serif;font-size:24px;font-weight:700;margin-bottom:4px;color:var(--t1,#1A1A18)">
                {{ $anggota->name }}
            </div>
            <div style="font-size:13px;margin-bottom:20px;color:var(--t2,#6E6A63);font-family:'JetBrains Mono',monospace;">
                {{ $anggota->id_kartu }}
            </div>
            <div class="mp-qrborder">
                {!! DNS2D::getBarcodeHTML($anggota->id_kartu, 'QRCODE', 8, 8) !!}
            </div>
            @php
                $qrGymName = (app()->bound('tenant') ? app('tenant')?->nama_gym : null) ?: 'HexaGym';
            @endphp
            <div class="mp-instr">
                <div class="mp-instr-ttl">Cara menggunakan</div>
                <ol>
                    <li>Tunjukkan barcode ini ke staf {{ $qrGymName }}</li>
                    <li>Staf memindai dengan scanner</li>
                    <li>Absensi tercatat otomatis</li>
                    <li>Atau download kartu member untuk dicetak</li>
                </ol>
            </div>
        </div>
    </x-slot:body>
</x-modal>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.remove-button').forEach(function (btn) {
        btn.addEventListener('click', function () { this.closest('.alert')?.remove(); });
    });
});
</script>
@endsection
