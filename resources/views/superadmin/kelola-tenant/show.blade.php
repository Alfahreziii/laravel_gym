@extends('superadmin.layouts.app')

@section('title', $tenant->nama_gym)

@section('content')
<style>
/* ── Status badges ──────────────────────────────────── */
.sa-sts {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 11px; border-radius: 999px; font-size: 12px; font-weight: 600;
}
.sa-sts-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
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

/* Archived notice banner */
.kt-archived-notice {
    display: flex; align-items: flex-start; gap: .875rem;
    background: #F9FAFB; border: 1.5px solid #D1D5DB;
    border-radius: 12px; padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.kt-archived-notice svg { width: 18px; height: 18px; color: #6B7280; flex-shrink: 0; margin-top: 1px; }
.kt-archived-notice-title { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 3px; }
.kt-archived-notice-desc  { font-size: 13px; color: #6B7280; }
html.dark .kt-archived-notice {
    background: rgba(255,255,255,.03); border-color: #332D26;
}
html.dark .kt-archived-notice-title { color: var(--sa-text); }
html.dark .kt-archived-notice-desc  { color: var(--sa-text-2); }

/* ── Page header ────────────────────────────────────── */
.kt-page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.75rem; }
.kt-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    height: 34px; padding: 0 14px;
    border: 1.5px solid var(--sa-border); border-radius: 8px;
    color: var(--sa-text-2); font-size: 13px; font-weight: 500;
    font-family: var(--sa-ff-sans); background: none; cursor: pointer;
    text-decoration: none; transition: border-color .13s, color .13s, background .13s;
}
.kt-back-btn:hover { border-color: var(--sa-primary); color: var(--sa-primary); background: #fff5f1; }
.kt-back-btn svg { width: 14px; height: 14px; }
.kt-page-title { font-family: var(--sa-ff-display); font-size: 19px; font-weight: 700; color: var(--sa-text); }
.kt-page-sub   { font-size: 13px; color: var(--sa-text-3); margin-top: 2px; }

/* ── Layout ─────────────────────────────────────────── */
.kt-grid {
    display: grid; grid-template-columns: 1fr 340px;
    gap: 1.25rem; align-items: start;
}
.kt-section-gap { margin-top: 1.25rem; }

/* ── Cards ──────────────────────────────────────────── */
.kt-card {
    background: var(--sa-surface); border: 1px solid var(--sa-border);
    border-radius: 14px; padding: 1.5rem;
}
.kt-card-title {
    font-family: var(--sa-ff-display); font-size: 13px; font-weight: 700;
    color: var(--sa-text-3); text-transform: uppercase; letter-spacing: .06em;
    margin-bottom: 1.25rem;
}
.kt-section-label {
    font-size: 11.5px; font-weight: 700; color: var(--sa-text-3);
    text-transform: uppercase; letter-spacing: .06em;
    margin: 1rem 0 .75rem; padding-top: 1rem;
    border-top: 1px solid var(--sa-border);
}

/* ── Logo ───────────────────────────────────────────── */
.kt-logo-wrap {
    width: 64px; height: 64px; border-radius: 10px;
    border: 1px solid var(--sa-border); overflow: hidden;
    background: #F5F6FA; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.kt-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }
.kt-logo-placeholder { width: 34px; height: 34px; color: var(--sa-text-3); }

/* ── Field rows ─────────────────────────────────────── */
.kt-field-row {
    display: flex; gap: 1rem; margin-bottom: .875rem;
    padding-bottom: .875rem; border-bottom: 1px solid var(--sa-border);
}
.kt-field-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.kt-field-label {
    font-size: 12px; font-weight: 600; color: var(--sa-text-3);
    min-width: 110px; flex-shrink: 0; padding-top: 8px;
}
.kt-field-value       { font-size: 13.5px; color: var(--sa-text); font-weight: 500; padding-top: 8px; }
.kt-field-value-mono  { font-size: 13.5px; color: var(--sa-text); font-weight: 500; font-family: var(--sa-ff-mono); padding-top: 8px; }
.kt-field-muted       { color: var(--sa-text-3); font-style: italic; }
.kt-readonly-badge {
    display: inline-flex; align-items: center;
    padding: 1px 7px; border-radius: 4px; font-size: 10.5px;
    background: #F1F5F9; color: #94A3B8; border: 1px solid #E2E8F0;
    font-weight: 600; margin-left: 6px; vertical-align: middle;
}

/* ── Inputs ─────────────────────────────────────────── */
.kt-input, .kt-select {
    width: 100%; height: 38px; padding: 0 12px;
    border: 1.5px solid var(--sa-border); border-radius: 8px;
    font-family: var(--sa-ff-sans); font-size: 13.5px;
    color: var(--sa-text); background: #FAFAF9;
    outline: none; transition: border-color .13s, box-shadow .13s;
    appearance: none; -webkit-appearance: none;
}
/* input[type=date] butuh appearance native — appearance:none bikin ikon
   kalender & layout dd/mm/yyyy rusak di Chrome/Edge */
input[type="date"].kt-input {
    appearance: auto; -webkit-appearance: auto;
}
.kt-input:focus, .kt-select:focus {
    border-color: var(--sa-primary); background: #fff;
    box-shadow: 0 0 0 3px rgba(242,98,46,.1);
}
.kt-input.is-invalid, .kt-select.is-invalid, .kt-textarea.is-invalid {
    border-color: #EF4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1);
}
.kt-textarea {
    width: 100%; padding: 9px 12px; min-height: 80px;
    border: 1.5px solid var(--sa-border); border-radius: 8px;
    font-family: var(--sa-ff-sans); font-size: 13.5px;
    color: var(--sa-text); background: #FAFAF9;
    outline: none; resize: vertical; transition: border-color .13s, box-shadow .13s;
}
.kt-textarea:focus {
    border-color: var(--sa-primary); background: #fff;
    box-shadow: 0 0 0 3px rgba(242,98,46,.1);
}
.kt-select-wrap { position: relative; }
.kt-select-wrap::after {
    content: ''; position: absolute; right: 11px; top: 50%;
    transform: translateY(-50%);
    width: 0; height: 0;
    border-left: 4px solid transparent; border-right: 4px solid transparent;
    border-top: 5px solid var(--sa-text-3); pointer-events: none;
}
.kt-input-error { font-size: 11.5px; color: #EF4444; margin-top: 4px; display: block; }
.kt-date-row    { display: grid; grid-template-columns: 1fr; gap: .75rem; }
.kt-date-label  { font-size: 11.5px; font-weight: 600; color: var(--sa-text-3); margin-bottom: 4px; }

/* ── Toggle switch ──────────────────────────────────── */
.kt-switch { position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer; flex-shrink: 0; }
.kt-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.kt-switch-rail {
    display: block; width: 44px; height: 24px;
    background: #D1D5DB; border-radius: 24px; transition: background .2s;
}
.kt-switch-rail::after {
    content: ''; position: absolute;
    left: 3px; top: 3px; width: 18px; height: 18px;
    background: #fff; border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.2); transition: transform .2s;
}
.kt-switch input:checked + .kt-switch-rail { background: #22C55E; }
.kt-switch input:checked + .kt-switch-rail::after { transform: translateX(20px); }

/* ── Module rows (Form 2) ───────────────────────────── */
.kt-mod-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .625rem 0; border-bottom: 1px solid var(--sa-border);
}
.kt-mod-row:last-of-type { border-bottom: none; }
.kt-mod-info { display: flex; align-items: center; gap: .625rem; }
.kt-mod-icon {
    width: 28px; height: 28px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kt-mod-icon svg { width: 14px; height: 14px; }
.kt-mod-icon-trainer  { background: #EFF6FF; color: #2563EB; }
.kt-mod-icon-pos      { background: #FFF7ED; color: #C2410C; }
.kt-mod-icon-keuangan { background: #F0FDF4; color: #15803D; }
.kt-mod-name-text { font-size: 13.5px; font-weight: 600; color: var(--sa-text); }

/* ── Package badge ──────────────────────────────────── */
.sa-pkg-badge {
    display: inline-flex; align-items: center; justify-content: center;
    height: 22px; padding: 0 7px;
    background: rgba(242,98,46,.1); color: var(--sa-primary);
    border: 1px solid rgba(242,98,46,.25);
    border-radius: 5px; font-size: 12px; font-weight: 700;
    font-family: var(--sa-ff-display);
}

/* ── Save bars ──────────────────────────────────────── */
.kt-save-bar {
    display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
    margin-top: 1.25rem; padding: 1rem 1.25rem;
    background: var(--sa-surface); border: 1px solid var(--sa-border); border-radius: 12px;
}
.kt-save-bar-inline {
    display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
    margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--sa-border);
}

/* ── Expired date ───────────────────────────────────── */
.kt-date-warn { font-size: 11.5px; color: #E11D48; margin-top: 2px; display: block; }

/* ── Dark mode overrides ────────────────────────────── */
html.dark .kt-card        { background: var(--sa-card-bg); }
html.dark .kt-input,
html.dark .kt-select      { background: #28231D; color: var(--sa-text); color-scheme: dark; }
html.dark .kt-input:focus,
html.dark .kt-select:focus { background: #1F1B17; }
html.dark .kt-textarea    { background: #28231D; color: var(--sa-text); color-scheme: dark; }
html.dark .kt-textarea:focus { background: #1F1B17; }
html.dark .kt-save-bar    { background: rgba(255,255,255,.03); }
html.dark .kt-back-btn:hover { background: rgba(242,98,46,.1); }
html.dark .kt-logo-wrap   { background: #28231D; }

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
</style>

{{-- Page header --}}
<div class="kt-page-header">
    <a href="{{ route('super_admin.kelola_tenant') }}" class="kt-back-btn">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Kelola Tenant
    </a>
    <div>
        <div class="kt-page-title">{{ $tenant->nama_gym }}</div>
        <div class="kt-page-sub">
            <span style="font-family:var(--sa-ff-mono)">{{ $tenant->subdomain }}</span>.{{ env('TENANT_BASE_DOMAIN', 'sistemgate.com') }}
        </div>
    </div>
</div>

@php
    $today      = now()->startOfDay();
    $expired    = $tenant->status === 'aktif' && $tenant->tgl_selesai?->lt($today);
    $mod        = $tenant->module;
    $isArchived = $tenant->status === 'archived';
    // Tidak pakai file_exists(public_path(...)) — di sebagian hosting, PHP
    // (mis. karena open_basedir) gagal traverse symlink public/storage
    // walau webserver bisa serve filenya langsung. Cukup percaya path di DB,
    // sama seperti render foto anggota/trainer di tempat lain.
    $logoExists = (bool) $tenant->logo;
@endphp

{{-- Instruksi pasca-reaktivasi (muncul sekali setelah "Aktifkan Kembali") --}}
@if (session('reaktivasi_info'))
    @php $ri = session('reaktivasi_info'); @endphp
    <div class="kt-archived-notice" style="border-color:#FCD34D;background:#FFFBEB">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:#B45309">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <div>
            <div class="kt-archived-notice-title">Langkah selanjutnya sebelum gym bisa diakses</div>
            <div class="kt-archived-notice-desc">
                Pool «{{ $ri['db_name'] }}» sudah dialokasikan{{ $ri['zip_restored'] ? ' & foto sudah di-restore' : '' }}.
                @if ($ri['zip_warning'])
                    <br>Catatan: ekstrak ZIP foto gagal ({{ $ri['zip_warning'] }}) — bisa di-extract manual nanti.
                @endif
                <br><br>
                <strong>LANGKAH SELANJUTNYA:</strong><br>
                1. Import file SQL arsip ke database «{{ $ri['db_name'] }}» via phpMyAdmin
                @if ($ri['sql_download_url'])
                    (<a href="{{ $ri['sql_download_url'] }}">download SQL</a>)
                @endif
                <br>
                2. Pastikan subdomain {{ $ri['subdomain'] }}.{{ $ri['base_domain'] }} aktif di cPanel + SSL<br>
                3. Setelah SQL ter-import, ubah status gym ke <strong>AKTIF</strong> di halaman ini<br><br>
                Status sekarang: <strong>NON-AKTIF</strong> (gym belum bisa diakses).
            </div>
        </div>
    </div>
@endif

{{-- Archived notice --}}
@if ($isArchived)
<div class="kt-archived-notice">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
    </svg>
    <div>
        <div class="kt-archived-notice-title">Gym ini sudah diarsipkan</div>
        <div class="kt-archived-notice-desc">
            Database sudah di-clear dan pool sudah dilepas. Data di bawah adalah riwayat informasi gym semasa aktif.
            Informasi tidak dapat diedit.
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     FORM 1 — Info, Status, Tanggal  →  PUT /kelola-tenant/{id}
     ══════════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('super_admin.kelola_tenant.update', $tenant) }}">
@csrf
@method('PUT')

<div class="kt-grid">

    {{-- Kolom kiri --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Info Gym --}}
        <div class="kt-card">
            <div class="kt-card-title">Informasi Gym</div>

            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--sa-border)">
                <div class="kt-logo-wrap">
                    @if ($logoExists)
                        <img src="{{ asset('storage/' . $tenant->logo) }}" alt="Logo">
                    @else
                        <svg class="kt-logo-placeholder" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                    @endif
                </div>
                <div style="font-size:12.5px;color:var(--sa-text-3)">ID #{{ $tenant->id }}</div>
            </div>

            <div class="kt-field-row">
                <div class="kt-field-label">Nama Gym</div>
                <div style="flex:1">
                    @if ($isArchived)
                        <div class="kt-field-value">{{ $tenant->nama_gym ?: '—' }}</div>
                    @else
                        <input type="text" name="nama_gym"
                               class="kt-input {{ $errors->has('nama_gym') ? 'is-invalid' : '' }}"
                               value="{{ old('nama_gym', $tenant->nama_gym) }}"
                               placeholder="Nama gym"
                               required>
                        @error('nama_gym') <span class="kt-input-error">{{ $message }}</span> @enderror
                    @endif
                </div>
            </div>

            <div class="kt-field-row">
                <div class="kt-field-label">Email</div>
                <div style="flex:1">
                    @if ($isArchived)
                        <div class="kt-field-value">{{ $tenant->email ?: '—' }}</div>
                    @else
                        <input type="email" name="email"
                               class="kt-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email', $tenant->email) }}"
                               placeholder="email@domain.com"
                               required>
                        @error('email') <span class="kt-input-error">{{ $message }}</span> @enderror
                    @endif
                </div>
            </div>

            <div class="kt-field-row">
                <div class="kt-field-label">No. HP</div>
                <div style="flex:1">
                    @if ($isArchived)
                        <div class="kt-field-value">{{ $tenant->no_hp ?: '—' }}</div>
                    @else
                        <input type="text" name="no_hp"
                               class="kt-input {{ $errors->has('no_hp') ? 'is-invalid' : '' }}"
                               value="{{ old('no_hp', $tenant->no_hp) }}"
                               placeholder="08xx-xxxx-xxxx"
                               required>
                        @error('no_hp') <span class="kt-input-error">{{ $message }}</span> @enderror
                    @endif
                </div>
            </div>

            <div class="kt-field-row">
                <div class="kt-field-label" style="padding-top:10px">Alamat</div>
                <div style="flex:1">
                    @if ($isArchived)
                        <div class="kt-field-value" style="white-space:pre-line">{{ $tenant->alamat ?: '—' }}</div>
                    @else
                        <textarea name="alamat"
                                  class="kt-textarea {{ $errors->has('alamat') ? 'is-invalid' : '' }}"
                                  placeholder="Alamat lengkap gym...">{{ old('alamat', $tenant->alamat) }}</textarea>
                        @error('alamat') <span class="kt-input-error">{{ $message }}</span> @enderror
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Teknis (readonly) --}}
        <div class="kt-card">
            <div class="kt-card-title">Detail Teknis</div>
            <div class="kt-field-row">
                <div class="kt-field-label">Subdomain</div>
                <div>
                    <span class="kt-field-value-mono">{{ $tenant->subdomain }}</span>
                    <span class="kt-readonly-badge">readonly</span>
                </div>
            </div>
            <div class="kt-field-row">
                <div class="kt-field-label">Database Pool</div>
                <div>
                    @if ($tenant->databasePool)
                        <span class="kt-field-value-mono">{{ $tenant->databasePool->db_name }}</span>
                        <span style="font-size:12px;color:var(--sa-text-3);margin-left:4px">({{ $tenant->databasePool->db_host }})</span>
                        <span class="kt-readonly-badge">readonly</span>
                    @else
                        <span class="kt-field-muted" style="padding-top:8px;display:block">—</span>
                    @endif
                </div>
            </div>
            <div class="kt-field-row">
                <div class="kt-field-label">Dibuat</div>
                <div class="kt-field-value" style="font-size:13px;color:var(--sa-text-2)">
                    {{ $tenant->created_at?->format('d M Y, H:i') ?? '—' }}
                </div>
            </div>
        </div>

    </div>

    {{-- Kolom kanan --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Status & Langganan --}}
        <div class="kt-card">
            <div class="kt-card-title">Status & Langganan</div>

            <div class="kt-field-row">
                <div class="kt-field-label">Status</div>
                <div style="flex:1">
                    @if ($isArchived)
                        <span class="sa-sts sa-sts-archived" style="margin-top:6px">
                            <span class="sa-sts-dot"></span>Arsip
                        </span>
                    @else
                        <div class="kt-select-wrap">
                            <select name="status"
                                    class="kt-select {{ $errors->has('status') ? 'is-invalid' : '' }}"
                                    required>
                                @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Non-aktif', 'suspend' => 'Suspend'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('status', $tenant->status) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('status') <span class="kt-input-error">{{ $message }}</span> @enderror
                        <div style="font-size:11.5px;color:var(--sa-text-3);margin-top:5px">
                            Nonaktif/Suspend langsung memblokir akses tenant.
                        </div>
                    @endif
                </div>
            </div>

            <div class="kt-field-row">
                <div class="kt-field-label" style="padding-top:0">Periode</div>
                <div style="flex:1;min-width:0">
                    @if ($isArchived)
                        <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:2px">
                            <div>
                                <div class="kt-date-label">Mulai</div>
                                <div class="kt-field-value">{{ $tenant->tgl_mulai?->format('d M Y') ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="kt-date-label">Selesai</div>
                                <div class="kt-field-value">{{ $tenant->tgl_selesai?->format('d M Y') ?? '—' }}</div>
                            </div>
                        </div>
                    @else
                        <div class="kt-date-row">
                            <div>
                                <div class="kt-date-label">Mulai</div>
                                <input type="date" name="tgl_mulai"
                                       class="kt-input {{ $errors->has('tgl_mulai') ? 'is-invalid' : '' }}"
                                       value="{{ old('tgl_mulai', $tenant->tgl_mulai?->format('Y-m-d')) }}"
                                       required>
                                @error('tgl_mulai') <span class="kt-input-error">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <div class="kt-date-label">Selesai</div>
                                <input type="date" name="tgl_selesai"
                                       class="kt-input {{ $errors->has('tgl_selesai') ? 'is-invalid' : '' }}"
                                       value="{{ old('tgl_selesai', $tenant->tgl_selesai?->format('Y-m-d')) }}"
                                       required>
                                @error('tgl_selesai') <span class="kt-input-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Save bar Form 1 --}}
@if (!$isArchived)
<div class="kt-save-bar">
    <a href="{{ route('super_admin.kelola_tenant') }}" class="sa-btn sa-btn-ghost">Batal</a>
    <button type="submit" class="sa-btn sa-btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" style="width:14px;height:14px">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        Simpan Perubahan
    </button>
</div>
@else
<div style="margin-top:1rem;text-align:right">
    <a href="{{ route('super_admin.kelola_tenant') }}" class="sa-btn sa-btn-ghost">← Kembali</a>
</div>
@endif

</form>

{{-- ══════════════════════════════════════════════════════════
     FORM 2 — Paket & Modul  →  PATCH /kelola-tenant/{id}/modules
     ══════════════════════════════════════════════════════════ --}}
<form method="POST"
      action="{{ route('super_admin.kelola_tenant.update_modules', $tenant) }}"
      class="kt-section-gap">
@csrf
@method('PATCH')

<div class="kt-card">
    <div class="kt-card-title">Paket & Modul</div>

    {{-- Package dropdown --}}
    <div class="kt-field-row">
        <div class="kt-field-label">Paket</div>
        <div style="flex:1">
            @if ($isArchived)
                @php $currentPkg = $packages->firstWhere('id', $tenant->package_id); @endphp
                <div class="kt-field-value">
                    @if ($currentPkg)
                        <span class="kt-readonly-badge" style="margin-right:5px">{{ $currentPkg->label }}</span>{{ $currentPkg->nama }}
                    @else
                        <span style="color:var(--sa-text-3)">—</span>
                    @endif
                </div>
            @else
                <div class="kt-select-wrap">
                    <select name="package_id" id="pkg-select"
                            class="kt-select {{ $errors->has('package_id') ? 'is-invalid' : '' }}">
                        @foreach ($packages as $pkg)
                            <option value="{{ $pkg->id }}"
                                    data-trainer="{{ $pkg->trainer ? '1' : '0' }}"
                                    data-pos="{{ $pkg->pos ? '1' : '0' }}"
                                    data-keuangan="{{ $pkg->keuangan ? '1' : '0' }}"
                                    {{ old('package_id', $tenant->package_id) == $pkg->id ? 'selected' : '' }}>
                                {{ $pkg->label }} — {{ $pkg->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('package_id') <span class="kt-input-error">{{ $message }}</span> @enderror
                <div style="font-size:11.5px;color:var(--sa-text-3);margin-top:5px">
                    Ganti paket akan me-reset toggle modul ke preset paket tersebut.
                </div>
            @endif
        </div>
    </div>

    {{-- Module display (read-only) --}}
    <div class="kt-section-label">Modul Aktif</div>

    {{-- Trainer --}}
    <div class="kt-mod-row">
        <div class="kt-mod-info">
            <div class="kt-mod-icon kt-mod-icon-trainer">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>
            <div>
                <div class="kt-mod-name-text">Personal Trainer</div>
                <div style="font-size:11.5px;color:var(--sa-text-3)">Fitur trainer, jadwal, payroll</div>
            </div>
        </div>
        <label class="kt-switch" title="Toggle Personal Trainer">
            <input type="checkbox" id="mod-trainer"
                   {{ $mod?->trainer ? 'checked' : '' }} disabled>
            <span class="kt-switch-rail"></span>
        </label>
    </div>

    {{-- POS --}}
    <div class="kt-mod-row">
        <div class="kt-mod-info">
            <div class="kt-mod-icon kt-mod-icon-pos">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
            </div>
            <div>
                <div class="kt-mod-name-text">Kasir / POS</div>
                <div style="font-size:11.5px;color:var(--sa-text-3)">Point of sale, produk, transaksi</div>
            </div>
        </div>
        <label class="kt-switch" title="Toggle Kasir / POS">
            <input type="checkbox" id="mod-pos"
                   {{ $mod?->pos ? 'checked' : '' }} disabled>
            <span class="kt-switch-rail"></span>
        </label>
    </div>

    {{-- Keuangan --}}
    <div class="kt-mod-row">
        <div class="kt-mod-info">
            <div class="kt-mod-icon kt-mod-icon-keuangan">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                </svg>
            </div>
            <div>
                <div class="kt-mod-name-text">Keuangan</div>
                <div style="font-size:11.5px;color:var(--sa-text-3)">Neraca, transaksi keuangan</div>
            </div>
        </div>
        <label class="kt-switch" title="Toggle Keuangan">
            <input type="checkbox" id="mod-keuangan"
                   {{ $mod?->keuangan ? 'checked' : '' }} disabled>
            <span class="kt-switch-rail"></span>
        </label>
    </div>

    {{-- Save bar Form 2 --}}
    @if (!$isArchived)
    <div class="kt-save-bar-inline">
        <span style="font-size:12px;color:var(--sa-text-3);margin-right:auto">
            Modul otomatis mengikuti preset paket yang dipilih.
        </span>
        <button type="submit" class="sa-btn sa-btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" style="width:14px;height:14px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            Simpan Paket
        </button>
    </div>
    @endif

</div>

</form>
@endsection

@section('scripts')
<script>
(function () {
    var select   = document.getElementById('pkg-select');
    var trainer  = document.getElementById('mod-trainer');
    var pos      = document.getElementById('mod-pos');
    var keuangan = document.getElementById('mod-keuangan');

    if (!select) return;

    select.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        trainer.checked  = opt.dataset.trainer  === '1';
        pos.checked      = opt.dataset.pos      === '1';
        keuangan.checked = opt.dataset.keuangan === '1';
    });
})();
</script>
@endsection
