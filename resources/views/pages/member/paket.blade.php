@extends('layout.layout')
@php
    $title = 'Paket & Harga';
    $subTitle = 'Katalog Paket';
@endphp

@section('content')
<style>
/* ============================================================
   Member Paket – CSS variables (scoped)
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

/* ---- Paket katalog (membership & trainer) ---- */
.mp-pkg-grid { padding: 20px; display: grid; gap: 16px; }
.mp-pkgc {
    background   : var(--s2);
    border       : 1px solid var(--bd);
    border-radius: 14px;
    padding      : 18px;
    display      : flex;
    flex-direction: column;
    gap          : 10px;
}
.mp-pkgc-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.mp-pkgc-name {
    font-family: 'Barlow Condensed', Oswald, sans-serif;
    font-size  : 19px;
    font-weight: 700;
    color      : var(--t1);
    line-height: 1.15;
}
.mp-pkgc-cat {
    font-size    : 10.5px;
    font-weight  : 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color        : var(--at);
    background   : var(--as);
    padding      : 3px 10px;
    border-radius: 999px;
    white-space  : nowrap;
    flex         : none;
}
.mp-pkgc-meta { font-size: 12.5px; color: var(--t2); }
.mp-pkgc-price {
    font-family: 'Barlow Condensed', Oswald, sans-serif;
    font-size  : 24px;
    font-weight: 700;
    color      : var(--ac);
}
.mp-pkgc-desc { font-size: 12.5px; color: var(--t2); line-height: 1.5; }
.mp-pkgc-wa {
    margin-top     : 4px;
    display        : inline-flex;
    align-items    : center;
    justify-content: center;
    gap            : 7px;
    font-size      : 13px;
    font-weight    : 600;
    padding        : 10px 14px;
    border-radius  : 10px;
    background     : #25D366;
    color          : #fff !important;
    text-decoration: none;
    border         : none;
    cursor         : pointer;
    transition     : opacity .15s;
}
.mp-pkgc-wa:hover { opacity: .88; }
.mp-pkgc-wa:disabled,
.mp-pkgc-wa[aria-disabled="true"] {
    background: var(--bi);
    color     : var(--t3) !important;
    cursor    : not-allowed;
    opacity   : 1;
}
.mp-pkg-empty {
    text-align: center;
    padding   : 30px 20px;
    color     : var(--t3);
    font-size : 13px;
}
</style>

<div class="mp">

{{-- ============================================================
     PAKET MEMBERSHIP
============================================================ --}}
<div class="mp-card">
    <div class="mp-ch">
        <div>
            <h3>Paket Membership</h3>
            <div class="mp-ch-sub">Tertarik upgrade atau perpanjang? Hubungi admin lewat WhatsApp.</div>
        </div>
    </div>

    @if ($paketMemberships->isEmpty())
        <div class="mp-pkg-empty">Belum ada paket tersedia.</div>
    @else
        <div class="mp-pkg-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($paketMemberships as $paket)
                @php
                    $pesanWa = "Halo {$tenant->nama_gym}, saya {$anggota->name} tertarik dengan paket membership *{$paket->nama_paket}* ({$paket->durasi} {$paket->periode}) seharga Rp" . number_format($paket->harga, 0, ',', '.') . '. Apakah masih tersedia?';
                    $waDigits = wa_number($tenant->no_hp ?? null);
                @endphp
                <div class="mp-pkgc">
                    <div class="mp-pkgc-top">
                        <div class="mp-pkgc-name">{{ $paket->nama_paket }}</div>
                        @if ($paket->kategori)
                            <span class="mp-pkgc-cat">{{ $paket->kategori->nama_kategori }}</span>
                        @endif
                    </div>
                    <div class="mp-pkgc-meta">{{ $paket->durasi }} {{ $paket->periode }}</div>
                    <div class="mp-pkgc-price">Rp{{ number_format($paket->harga, 0, ',', '.') }}</div>
                    @if ($paket->keterangan)
                        <div class="mp-pkgc-desc">{{ $paket->keterangan }}</div>
                    @endif

                    @if ($waDigits)
                        <a href="https://wa.me/{{ $waDigits }}?text={{ rawurlencode($pesanWa) }}"
                           target="_blank" rel="noopener" class="mp-pkgc-wa">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.13 1.01-2.42.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.65.5.24.58.82 2 .89 2.14.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.17-.29.37-.42.5-.14.14-.28.29-.12.57.16.28.71 1.17 1.52 1.9 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.28.37-.23.62-.14.25.1 1.6.75 1.87.89.27.14.45.21.52.32.07.12.07.66-.17 1.34Z"/></svg>
                            Tertarik / Beli via WhatsApp
                        </a>
                    @else
                        <button type="button" class="mp-pkgc-wa" disabled aria-disabled="true"
                                title="Nomor WhatsApp gym belum diatur">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.13 1.01-2.42.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.65.5.24.58.82 2 .89 2.14.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.17-.29.37-.42.5-.14.14-.28.29-.12.57.16.28.71 1.17 1.52 1.9 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.28.37-.23.62-.14.25.1 1.6.75 1.87.89.27.14.45.21.52.32.07.12.07.66-.17 1.34Z"/></svg>
                            Tertarik / Beli via WhatsApp
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ============================================================
     PAKET PERSONAL TRAINER
============================================================ --}}
<div class="mp-card" style="margin-top:20px;">
    <div class="mp-ch">
        <div>
            <h3>Paket Personal Trainer</h3>
            <div class="mp-ch-sub">Latihan lebih terarah bersama trainer. Hubungi admin lewat WhatsApp.</div>
        </div>
    </div>

    @if ($paketTrainers->isEmpty())
        <div class="mp-pkg-empty">Belum ada paket tersedia.</div>
    @else
        <div class="mp-pkg-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($paketTrainers as $paket)
                @php
                    $pesanWaTrainer = "Halo {$tenant->nama_gym}, saya {$anggota->name} tertarik dengan paket personal trainer *{$paket->nama_paket}* ({$paket->jumlah_sesi} sesi) seharga Rp" . number_format($paket->biaya, 0, ',', '.') . '. Apakah masih tersedia?';
                    $waDigitsTrainer = wa_number($tenant->no_hp ?? null);
                @endphp
                <div class="mp-pkgc">
                    <div class="mp-pkgc-top">
                        <div class="mp-pkgc-name">{{ $paket->nama_paket }}</div>
                    </div>
                    <div class="mp-pkgc-meta">{{ $paket->jumlah_sesi }} sesi &middot; {{ $paket->durasi }} {{ $paket->periode }}</div>
                    <div class="mp-pkgc-price">Rp{{ number_format($paket->biaya, 0, ',', '.') }}</div>

                    @if ($waDigitsTrainer)
                        <a href="https://wa.me/{{ $waDigitsTrainer }}?text={{ rawurlencode($pesanWaTrainer) }}"
                           target="_blank" rel="noopener" class="mp-pkgc-wa">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.13 1.01-2.42.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.65.5.24.58.82 2 .89 2.14.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.17-.29.37-.42.5-.14.14-.28.29-.12.57.16.28.71 1.17 1.52 1.9 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.28.37-.23.62-.14.25.1 1.6.75 1.87.89.27.14.45.21.52.32.07.12.07.66-.17 1.34Z"/></svg>
                            Tertarik / Beli via WhatsApp
                        </a>
                    @else
                        <button type="button" class="mp-pkgc-wa" disabled aria-disabled="true"
                                title="Nomor WhatsApp gym belum diatur">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.95-.31-1.64-.6-2.89-1.25-4.78-4.15-4.92-4.34-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.13 1.01-2.42.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.65.5.24.58.82 2 .89 2.14.07.14.12.31.02.5-.09.19-.14.31-.28.48-.14.17-.29.37-.42.5-.14.14-.28.29-.12.57.16.28.71 1.17 1.52 1.9 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.16-.19.68-.79.87-1.06.18-.28.37-.23.62-.14.25.1 1.6.75 1.87.89.27.14.45.21.52.32.07.12.07.66-.17 1.34Z"/></svg>
                            Tertarik / Beli via WhatsApp
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

</div>

@endsection
