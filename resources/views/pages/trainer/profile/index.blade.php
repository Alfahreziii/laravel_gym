@extends('layout.layout')
@php
    $title = 'Profil Saya';
    $subTitle = 'Profile Trainer';

    // Avatar initials
    $nameParts = explode(' ', trim($trainer->name));
    $initials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) $initials .= strtoupper(substr(end($nameParts), 0, 1));

    $isAktif = $trainer->status === \App\Models\Trainer::STATUS_AKTIF;
@endphp

@section('content')
<style>
/* ============================================================
   Trainer Profile – CSS variables (scoped)
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
    background: linear-gradient(120deg,#171717,#404040 72%,#525252);
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

/* ---- Info box (specialisasi) ---- */
.mp-pkg {
    background   : var(--as);
    border-radius: 12px;
    padding      : 14px 16px;
    text-align   : left;
    margin-bottom: 14px;
}
.mp-pkg-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
.mp-pkg-lbl { font-size: 12px; color: var(--t2); }
.mp-pkg-name {
    font-family: 'Barlow Condensed', Oswald, sans-serif;
    font-size  : 20px;
    font-weight: 700;
    color      : var(--at);
    line-height: 1.1;
}
.mp-pkg-sub { font-size: 11px; color: var(--t2); margin-top: 5px; }

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
    background   : linear-gradient(135deg,#171717,#2d2d2d);
    border-radius: var(--r);
    padding      : 18px;
    position     : relative;
    overflow     : hidden;
}
.mp-dc::before {
    content : '';
    position: absolute;
    inset   : 0;
    background: radial-gradient(ellipse at top right, rgba(64,64,64,.35), transparent 60%);
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
    padding     : 3px 10px;
    border-radius: 999px;
}
.mp-dc-status-on  { color: #4ade80; background: rgba(74,222,128,.10); border: 1px solid rgba(74,222,128,.28); }
.mp-dc-status-off { color: #fbbf24; background: rgba(251,191,36,.10); border: 1px solid rgba(251,191,36,.28); }
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
.mp-dc-primary { background: #404040; color: #fff !important; }

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
    border       : 3px solid #404040;
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
                @if ($trainer->user && $trainer->user->photo)
                    <img src="{{ asset('storage/' . $trainer->user->photo) }}"
                         alt="{{ $trainer->name }}" class="mp-av">
                @else
                    <div class="mp-av-init">{{ $initials }}</div>
                @endif
            </div>
            <div class="mp-pinfo">
                <h2 class="mp-pname">{{ $trainer->name }}</h2>
                <div class="mp-pemail">{{ $trainer->user ? $trainer->user->email : '-' }}</div>

                @if ($isAktif)
                    <div class="mp-badge mp-badge-ok">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:13px;height:13px;flex:none"><path d="M20 6 9 17l-5-5"/></svg>
                        Trainer Aktif
                    </div>
                @else
                    <div class="mp-badge mp-badge-off">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:13px;height:13px;flex:none"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        {{ $trainer->status_label['text'] }}
                    </div>
                @endif

                @if ($trainer->specialisasi)
                    <div class="mp-pkg">
                        <div class="mp-pkg-top">
                            <span class="mp-pkg-lbl">Spesialisasi</span>
                        </div>
                        <div class="mp-pkg-name">{{ $trainer->specialisasi->nama_specialisasi }}</div>
                        @if ($trainer->experience)
                            <div class="mp-pkg-sub">{{ $trainer->experience }}</div>
                        @endif
                    </div>
                @endif

                <div class="mp-stats">
                    <div class="mp-stat">
                        <div class="mp-stat-v">{{ $totalKehadiran }}</div>
                        <div class="mp-stat-l">Total absensi</div>
                    </div>
                    <div class="mp-stat">
                        <div class="mp-stat-v">{{ $kehadiranBulanIni }}</div>
                        <div class="mp-stat-l">Absensi bln ini</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DIGITAL TRAINER CARD --}}
        <div class="mp-dc">
            <div class="mp-dc-hdr">
                <span class="mp-dc-lbl">KARTU TRAINER</span>
                <svg viewBox="0 0 40 40" fill="none" style="width:26px;height:26px;opacity:.9">
                    <path d="M20 2 35.3 11v18L20 38 4.7 29V11Z" fill="#fff"/>
                    <g stroke="#404040" stroke-width="2.6" stroke-linecap="round">
                        <path d="M13 20h14"/><path d="M13 16.5v7M27 16.5v7"/>
                    </g>
                </svg>
            </div>
            <div class="mp-dc-body">
                <div class="mp-dc-qr">
                    {!! DNS2D::getBarcodeHTML($trainer->rfid, 'QRCODE', 5, 5) !!}
                </div>
                <div class="mp-dc-info">
                    <div class="mp-dc-name">{{ $trainer->name }}</div>
                    <div class="mp-dc-id">{{ $trainer->rfid }}</div>
                    <div class="mp-dc-status {{ $isAktif ? 'mp-dc-status-on' : 'mp-dc-status-off' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="width:11px;height:11px;flex:none"><path d="M20 6 9 17l-5-5"/></svg>
                        {{ $isAktif ? 'ACTIVE' : strtoupper($trainer->status) }}
                    </div>
                </div>
            </div>
            <div class="mp-dc-btns">
                <button type="button" onclick="HexaModal.show('qr-modal')" class="mp-dc-btn mp-dc-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex:none"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8v8M11 8v8M15 8v8"/></svg>
                    Perbesar QR
                </button>
                <a href="{{ route('trainer.profile.download-card') }}" class="mp-dc-btn mp-dc-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex:none"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                    Unduh
                </a>
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
                        <div class="mp-field-lbl">ID Kartu / RFID</div>
                        <div class="mp-field-val mono">{{ $trainer->rfid }}</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">No. Telepon</div>
                        <div class="mp-field-val">{{ $trainer->no_telp }}</div>
                    </div>
                </div>
                {{-- Baris 2 --}}
                <div class="mp-row">
                    <div class="mp-field">
                        <div class="mp-field-lbl">Jenis Kelamin</div>
                        <div class="mp-field-val">{{ $trainer->jenis_kelamin }}</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">Tanggal Lahir</div>
                        <div class="mp-field-val">
                            {{ $trainer->tgl_lahir ? $trainer->tgl_lahir->format('d M Y') . ' (' . $trainer->tgl_lahir->age . ' thn)' : '-' }}
                        </div>
                    </div>
                </div>
                {{-- Baris 3 --}}
                <div class="mp-row">
                    <div class="mp-field">
                        <div class="mp-field-lbl">Tempat Lahir</div>
                        <div class="mp-field-val">{{ $trainer->tempat_lahir }}</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">Tanggal Gabung</div>
                        <div class="mp-field-val">{{ $trainer->tgl_gabung ? $trainer->tgl_gabung->format('d M Y') : '-' }}</div>
                    </div>
                </div>
                {{-- Baris 4 --}}
                <div class="mp-row">
                    <div class="mp-field">
                        <div class="mp-field-lbl">Sesi Sudah Dijalani</div>
                        <div class="mp-field-val">{{ $trainer->sesi_sudah_dijalani }} sesi</div>
                    </div>
                    <div class="mp-field">
                        <div class="mp-field-lbl">Sesi Belum Dijalani</div>
                        <div class="mp-field-val">{{ $trainer->sesi_belum_dijalani }} sesi</div>
                    </div>
                </div>
                {{-- Baris 5 - Alamat (full) --}}
                <div class="mp-row">
                    <div class="mp-field" style="flex:1">
                        <div class="mp-field-lbl">Alamat</div>
                        <div class="mp-field-val">{{ $trainer->alamat }}</div>
                    </div>
                </div>
                {{-- Baris 6 - Keterangan (optional, full) --}}
                @if ($trainer->keterangan)
                    <div class="mp-row" style="border-bottom:none;">
                        <div class="mp-field" style="flex:1">
                            <div class="mp-field-lbl">Keterangan</div>
                            <div class="mp-field-val">{{ $trainer->keterangan }}</div>
                        </div>
                    </div>
                @endif
            </div>
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
                @if ($trainer->kehadiranTrainers->count() > 0)
                    @foreach ($trainer->kehadiranTrainers->take(10) as $kehadiran)
                        @php
                            $hariId  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                            $bulanId = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                            $isIn      = $kehadiran->status === 'in';
                            $namaHari  = $hariId[$kehadiran->created_at->dayOfWeek];
                            $namaBulan = $bulanId[(int) $kehadiran->created_at->format('n')];
                            $tglFmt    = $namaHari . ', ' . $kehadiran->created_at->format('d') . ' ' . $namaBulan . ' ' . $kehadiran->created_at->format('Y');
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
                                <div class="mp-ktime">{{ $kehadiran->created_at->format('H:i') }} WIB · {{ $namaHari }}</div>
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
<x-modal id="qr-modal" title="Barcode Kartu Trainer" maxWidth="max-w-[480px]">
    <x-slot:body>
        <div style="text-align:center;">
            <div style="font-family:'Barlow Condensed',Oswald,sans-serif;font-size:24px;font-weight:700;margin-bottom:4px;color:var(--t1,#1A1A18)">
                {{ $trainer->name }}
            </div>
            <div style="font-size:13px;margin-bottom:20px;color:var(--t2,#6E6A63);font-family:'JetBrains Mono',monospace;">
                {{ $trainer->rfid }}
            </div>
            <div class="mp-qrborder">
                {!! DNS2D::getBarcodeHTML($trainer->rfid, 'QRCODE', 8, 8) !!}
            </div>
            <div class="mp-instr">
                <div class="mp-instr-ttl">Cara menggunakan</div>
                <ol>
                    <li>Tunjukkan barcode ini ke staf HexaGym</li>
                    <li>Staf memindai dengan scanner</li>
                    <li>Absensi tercatat otomatis</li>
                    <li>Atau download kartu trainer untuk dicetak</li>
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
