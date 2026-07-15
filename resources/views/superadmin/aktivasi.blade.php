@extends('superadmin.layouts.app')

@section('title', 'Aktivasi Gym')

@section('content')
<style>
/* ── Aktivasi page ────────────────────────────────────────── */
.sa-act-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.5rem;
    align-items: start;
}
.sa-form-section {
    background: #fff;
    border: 1px solid var(--sa-border);
    border-radius: 14px;
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.sa-form-section:last-child { margin-bottom: 0; }
.sa-form-section-head {
    padding: 1rem 1.375rem;
    border-bottom: 1px solid var(--sa-border);
    display: flex;
    align-items: center;
    gap: .625rem;
}
.sa-form-section-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: rgba(242,98,46,.1);
    color: var(--sa-primary);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sa-form-section-title {
    font-family: var(--sa-ff-display);
    font-size: 15px; font-weight: 700;
    color: var(--sa-text); line-height: 1.25;
}
.sa-form-section-sub {
    font-size: 12px; color: var(--sa-text-3); margin-top: 1px;
}
.sa-form-section-body {
    padding: 1.25rem 1.375rem;
    display: flex; flex-direction: column; gap: 1rem;
}
.sa-form-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
/* Subdomain input + suffix */
.sa-subdomain-group { display: flex; }
.sa-subdomain-group .sa-form-input {
    border-radius: 9px 0 0 9px;
    border-right: none; flex: 1; min-width: 0;
}
.sa-subdomain-suffix {
    display: flex; align-items: center;
    padding: 0 12px;
    background: #F5F6FA;
    border: 1.5px solid var(--sa-border);
    border-radius: 0 9px 9px 0;
    font-size: 12.5px; color: var(--sa-text-2);
    white-space: nowrap;
    font-family: monospace;
    user-select: none;
}
/* Textarea */
.sa-form-textarea {
    width: 100%; padding: 10px 13px;
    min-height: 76px; resize: vertical;
    border: 1.5px solid var(--sa-border);
    border-radius: 9px; font-size: 14px;
    font-family: var(--sa-ff-body);
    color: var(--sa-text); background: #fff;
    transition: border-color .15s;
    box-sizing: border-box; line-height: 1.55;
}
.sa-form-textarea:focus {
    outline: none; border-color: var(--sa-primary);
    box-shadow: 0 0 0 3px rgba(242,98,46,.1);
}
.sa-field-error .sa-form-textarea { border-color: #F87171; }
/* File input */
.sa-file-wrap { display: flex; align-items: center; gap: .75rem; }
.sa-file-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px;
    border: 1.5px solid var(--sa-border); border-radius: 9px;
    font-size: 13px; font-weight: 500; color: var(--sa-text-2);
    background: #F5F6FA; cursor: pointer;
    transition: border-color .15s, background .15s, color .15s;
    white-space: nowrap;
}
.sa-file-btn:hover { border-color: var(--sa-primary); background: #fff5f1; color: var(--sa-primary); }
.sa-file-input { position: absolute; opacity: 0; width: 0; height: 0; }
.sa-file-name { font-size: 13px; color: var(--sa-text-3); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
/* Package radio cards */
.sa-pkg-radio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .625rem;
}
.sa-pkg-label { display: block; cursor: pointer; }
.sa-pkg-label input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.sa-pkg-card {
    border: 1.5px solid var(--sa-border); border-radius: 12px;
    padding: .875rem 1rem;
    transition: border-color .15s, box-shadow .15s, background .15s;
    position: relative;
}
.sa-pkg-label:has(input:checked) .sa-pkg-card {
    border-color: var(--sa-primary);
    box-shadow: 0 0 0 3px rgba(242,98,46,.1);
    background: #fff8f5;
}
.sa-pkg-check {
    display: none; position: absolute; top: 10px; right: 10px;
    width: 18px; height: 18px;
    background: var(--sa-primary); border-radius: 50%;
    align-items: center; justify-content: center;
    color: #fff; font-size: 10px; font-weight: 700;
}
.sa-pkg-label:has(input:checked) .sa-pkg-check { display: flex; }
.sa-pkg-label-text {
    display: inline-block; font-size: 11px; font-weight: 600;
    letter-spacing: .4px; text-transform: uppercase;
    color: var(--sa-primary); background: rgba(242,98,46,.08);
    padding: 2px 7px; border-radius: 999px; margin-bottom: .5rem;
}
.sa-pkg-name {
    font-family: var(--sa-ff-display); font-size: 18px; font-weight: 700;
    color: var(--sa-text); margin-bottom: .375rem;
}
.sa-pkg-modules { display: flex; flex-wrap: wrap; gap: .375rem; margin-top: .375rem; }
.sa-mod-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 999px;
    font-size: 11px; font-weight: 600; line-height: 1.4;
}
.sa-mod-on  { background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; }
.sa-mod-off { background: #F5F6FA; color: #9CA3AF; border: 1px solid #E5E7EB; }
/* Eye button for password fields */
.sa-eye-btn {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--sa-text-3); display: flex; align-items: center;
    justify-content: center; padding: 0;
}
.sa-eye-btn:hover { color: var(--sa-primary); }
/* Pool count note */
.sa-pool-count { font-size: 12.5px; color: var(--sa-text-3); margin-top: 6px; }
.sa-pool-count strong { color: #15803D; }
/* Note block */
.sa-act-note {
    display: flex; align-items: flex-start; gap: .5rem;
    padding: .75rem 1rem;
    background: #FFF7ED; border: 1px solid #FED7AA;
    border-radius: 9px; font-size: 12.5px; color: #92400E;
    line-height: 1.5;
}
.sa-act-note svg { flex-shrink: 0; margin-top: 1px; }
/* Submit section */
.sa-act-actions {
    background: #fff; border: 1px solid var(--sa-border);
    border-radius: 14px; padding: 1.25rem 1.375rem;
    display: flex; flex-direction: column; gap: .75rem;
}
.sa-btn-submit {
    width: 100%; padding: 11px 18px; font-size: 14px; font-weight: 600;
    display: flex; align-items: center; justify-content: center; gap: 7px;
    background: var(--sa-primary); color: #fff;
    border: none; border-radius: 9px; cursor: pointer;
    font-family: var(--sa-ff-display); letter-spacing: .2px;
    transition: background .15s;
}
.sa-btn-submit:hover:not(:disabled) { background: #e0551f; }
.sa-btn-submit:disabled { background: #d1d5db; cursor: not-allowed; }
.sa-btn-cancel {
    display: block; width: 100%; padding: 9px 18px;
    font-size: 13.5px; font-weight: 500; text-align: center;
    color: var(--sa-text-2); background: none;
    border: 1.5px solid var(--sa-border); border-radius: 9px;
    text-decoration: none; transition: border-color .15s, color .15s;
    font-family: var(--sa-ff-body); box-sizing: border-box;
}
.sa-btn-cancel:hover { border-color: var(--sa-primary); color: var(--sa-primary); }
@media (max-width: 900px) {
    .sa-act-layout { grid-template-columns: 1fr; }
    .sa-form-2col { grid-template-columns: 1fr; }
}

/* ── Dark mode overrides ────────────────────────────── */
html.dark .sa-form-section    { background: var(--sa-card-bg); }
html.dark .sa-form-section-head { background: rgba(255,255,255,.03); }

html.dark .sa-form-textarea {
    background: #28231D; color: var(--sa-text);
    border-color: var(--sa-border); color-scheme: dark;
}
html.dark .sa-form-textarea:focus { background: #1F1B17; }

html.dark .sa-subdomain-suffix { background: #28231D; color: var(--sa-text-2); }

html.dark .sa-file-btn {
    background: #28231D; color: var(--sa-text-2); border-color: var(--sa-border);
}
html.dark .sa-file-btn:hover { background: rgba(242,98,46,.1); }

html.dark .sa-pkg-card { background: var(--sa-card-bg); }
html.dark .sa-pkg-label:has(input:checked) .sa-pkg-card {
    border-color: var(--sa-primary); background: rgba(242,98,46,.08);
}

html.dark .sa-mod-on  { background: rgba(34,197,94,.12);  color: #4ADE80; border-color: rgba(34,197,94,.3); }
html.dark .sa-mod-off { background: rgba(255,255,255,.06); color: #6E685F; border-color: rgba(255,255,255,.1); }

html.dark .sa-act-actions { background: var(--sa-card-bg); }

html.dark .sa-act-note {
    background: rgba(249,115,22,.1); border-color: rgba(249,115,22,.3); color: #FDBA74;
}
</style>

<form method="POST"
      action="{{ route('super_admin.aktivasi.store') }}"
      enctype="multipart/form-data">
@csrf

<div class="sa-act-layout">

{{-- ════════════════ LEFT COLUMN ════════════════ --}}
<div>

    {{-- Informasi Gym ──────────────────────────── --}}
    <div class="sa-form-section">
        <div class="sa-form-section-head">
            <div class="sa-form-section-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                </svg>
            </div>
            <div>
                <div class="sa-form-section-title">Informasi Gym</div>
                <div class="sa-form-section-sub">Data identitas gym yang akan diaktifkan</div>
            </div>
        </div>
        <div class="sa-form-section-body">

            <div class="sa-form-group {{ $errors->has('nama_gym') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label" for="nama_gym">
                    Nama Gym <span style="color:var(--sa-primary)">*</span>
                </label>
                <input type="text" id="nama_gym" name="nama_gym"
                       class="sa-form-input"
                       value="{{ old('nama_gym') }}"
                       placeholder="cth. FitHub Bandung"
                       required>
                @error('nama_gym')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="sa-form-group {{ $errors->has('subdomain') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label" for="subdomain">
                    Subdomain <span style="color:var(--sa-primary)">*</span>
                </label>
                <div class="sa-subdomain-group">
                    <input type="text" id="subdomain" name="subdomain"
                           class="sa-form-input"
                           value="{{ old('subdomain') }}"
                           placeholder="fithub"
                           pattern="[a-z0-9\-]+"
                           required>
                    <div class="sa-subdomain-suffix">.{{ env('TENANT_BASE_DOMAIN', 'sistemgate.com') }}</div>
                </div>
                @error('subdomain')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
                <div class="sa-form-hint">Huruf kecil, angka, dan tanda hubung saja. Harus unik.</div>
            </div>

            <div class="sa-form-group {{ $errors->has('alamat') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label" for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat"
                          class="sa-form-textarea"
                          rows="3"
                          placeholder="Jl. Raya Sudirman No. 123, Bandung">{{ old('alamat') }}</textarea>
                @error('alamat')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="sa-form-group">
                <label class="sa-form-label">
                    Logo Gym
                    <span class="sa-form-hint" style="display:inline;font-weight:400">(opsional)</span>
                </label>
                <div class="sa-file-wrap">
                    <label class="sa-file-btn" for="logo">
                        <input type="file" id="logo" name="logo"
                               class="sa-file-input"
                               accept="image/*"
                               onchange="updateLogoName(this)">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                        </svg>
                        Pilih Gambar
                    </label>
                    <span class="sa-file-name" id="logo-file-name">Belum ada file dipilih</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Paket & Langganan ──────────────────────── --}}
    <div class="sa-form-section">
        <div class="sa-form-section-head">
            <div class="sa-form-section-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                </svg>
            </div>
            <div>
                <div class="sa-form-section-title">Paket & Langganan</div>
                <div class="sa-form-section-sub">Pilih paket fitur dan masa aktif</div>
            </div>
        </div>
        <div class="sa-form-section-body">

            <div class="sa-form-group {{ $errors->has('package_id') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label">
                    Pilih Paket <span style="color:var(--sa-primary)">*</span>
                </label>

                @if ($packages->isEmpty())
                    <div class="sa-act-note">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                        </svg>
                        Belum ada paket tersedia. Tambahkan paket terlebih dahulu di menu Paket.
                    </div>
                @else
                    <div class="sa-pkg-radio-grid">
                        @foreach ($packages as $pkg)
                        <label class="sa-pkg-label" for="pkg_{{ $pkg->id }}">
                            <input type="radio"
                                   id="pkg_{{ $pkg->id }}"
                                   name="package_id"
                                   value="{{ $pkg->id }}"
                                   {{ old('package_id') == $pkg->id ? 'checked' : '' }}>
                            <div class="sa-pkg-card">
                                <div class="sa-pkg-check">✓</div>
                                <div class="sa-pkg-label-text">{{ $pkg->label }}</div>
                                <div class="sa-pkg-name">{{ $pkg->nama }}</div>
                                <div class="sa-pkg-modules">
                                    <span class="sa-mod-badge {{ $pkg->trainer ? 'sa-mod-on' : 'sa-mod-off' }}">
                                        {{ $pkg->trainer ? '✓' : '—' }} Trainer
                                    </span>
                                    <span class="sa-mod-badge {{ $pkg->pos ? 'sa-mod-on' : 'sa-mod-off' }}">
                                        {{ $pkg->pos ? '✓' : '—' }} POS
                                    </span>
                                    <span class="sa-mod-badge {{ $pkg->keuangan ? 'sa-mod-on' : 'sa-mod-off' }}">
                                        {{ $pkg->keuangan ? '✓' : '—' }} Keuangan
                                    </span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                @endif

                @error('package_id')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="sa-form-2col">
                <div class="sa-form-group {{ $errors->has('tgl_mulai') ? 'sa-field-error' : '' }}">
                    <label class="sa-form-label" for="tgl_mulai">
                        Tanggal Mulai <span style="color:var(--sa-primary)">*</span>
                    </label>
                    <input type="date" id="tgl_mulai" name="tgl_mulai"
                           class="sa-form-input"
                           value="{{ old('tgl_mulai', date('Y-m-d')) }}"
                           required>
                    @error('tgl_mulai')
                        <div class="sa-form-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="sa-form-group {{ $errors->has('tgl_selesai') ? 'sa-field-error' : '' }}">
                    <label class="sa-form-label" for="tgl_selesai">
                        Tanggal Selesai <span style="color:var(--sa-primary)">*</span>
                    </label>
                    <input type="date" id="tgl_selesai" name="tgl_selesai"
                           class="sa-form-input"
                           value="{{ old('tgl_selesai', date('Y-m-d', strtotime('+1 year'))) }}"
                           required>
                    @error('tgl_selesai')
                        <div class="sa-form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="sa-form-group {{ $errors->has('status') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label" for="status">Status Awal</label>
                <select id="status" name="status" class="sa-form-input">
                    <option value="aktif"    {{ old('status', 'aktif') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                    <option value="suspend"  {{ old('status') === 'suspend'  ? 'selected' : '' }}>Suspend</option>
                </select>
                @error('status')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>

</div>{{-- /left --}}

{{-- ════════════════ RIGHT COLUMN ════════════════ --}}
<div>

    {{-- Kontak & Akun Admin ────────────────────── --}}
    <div class="sa-form-section">
        <div class="sa-form-section-head">
            <div class="sa-form-section-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>
            <div>
                <div class="sa-form-section-title">Kontak & Akun Admin</div>
                <div class="sa-form-section-sub">Email dipakai untuk login admin gym</div>
            </div>
        </div>
        <div class="sa-form-section-body">

            <div class="sa-form-group {{ $errors->has('email') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label" for="email">
                    Email Admin <span style="color:var(--sa-primary)">*</span>
                </label>
                <input type="email" id="email" name="email"
                       class="sa-form-input"
                       value="{{ old('email') }}"
                       placeholder="admin@fithub.com"
                       required>
                @error('email')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="sa-form-group {{ $errors->has('no_hp') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label" for="no_hp">
                    No. HP <span style="color:var(--sa-primary)">*</span>
                </label>
                <input type="text" id="no_hp" name="no_hp"
                       class="sa-form-input"
                       value="{{ old('no_hp') }}"
                       placeholder="08123456789"
                       required>
                @error('no_hp')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="sa-form-group {{ $errors->has('password') ? 'sa-field-error' : '' }}">
                <label class="sa-form-label" for="password">
                    Password Admin <span style="color:var(--sa-primary)">*</span>
                </label>
                <div style="position:relative">
                    <input type="password" id="password" name="password"
                           class="sa-form-input"
                           placeholder="Min. 8 karakter"
                           autocomplete="new-password"
                           style="padding-right:42px"
                           required>
                    <button type="button" class="sa-eye-btn"
                            onclick="togglePass('password', this)"
                            tabindex="-1">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                             style="width:16px;height:16px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="sa-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="sa-form-group">
                <label class="sa-form-label" for="password_confirmation">
                    Konfirmasi Password <span style="color:var(--sa-primary)">*</span>
                </label>
                <div style="position:relative">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="sa-form-input"
                           placeholder="Ulangi password"
                           autocomplete="new-password"
                           style="padding-right:42px"
                           required>
                    <button type="button" class="sa-eye-btn"
                            onclick="togglePass('password_confirmation', this)"
                            tabindex="-1">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                             style="width:16px;height:16px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Database Pool ──────────────────────────── --}}
    <div class="sa-form-section">
        <div class="sa-form-section-head">
            <div class="sa-form-section-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
                </svg>
            </div>
            <div>
                <div class="sa-form-section-title">Database Pool</div>
                <div class="sa-form-section-sub">Alokasi database untuk tenant ini</div>
            </div>
        </div>
        <div class="sa-form-section-body">

            @if ($pools->isEmpty())
                <div class="sa-act-note">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    Tidak ada database tersedia. Tambahkan pool di halaman Config Database.
                </div>
            @else
                <div class="sa-form-group {{ $errors->has('database_pool_id') ? 'sa-field-error' : '' }}">
                    <label class="sa-form-label" for="database_pool_id">
                        Pilih Database <span style="color:var(--sa-primary)">*</span>
                    </label>
                    <select id="database_pool_id" name="database_pool_id"
                            class="sa-form-input" required>
                        <option value="">— Pilih database pool —</option>
                        @foreach ($pools as $pool)
                            <option value="{{ $pool->id }}"
                                    {{ old('database_pool_id') == $pool->id ? 'selected' : '' }}>
                                {{ $pool->db_name }}
                                @if ($pool->db_host !== '127.0.0.1')
                                    ({{ $pool->db_host }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('database_pool_id')
                        <div class="sa-form-error">{{ $message }}</div>
                    @enderror
                    <div class="sa-pool-count">
                        <strong>{{ $pools->count() }}</strong> database tersedia
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Submit ─────────────────────────────────── --}}
    <div class="sa-act-actions">
        @php $canSubmit = $pools->isNotEmpty() && $packages->isNotEmpty(); @endphp

        <button type="submit" class="sa-btn-submit" {{ $canSubmit ? '' : 'disabled' }}>
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"
                 style="width:15px;height:15px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            Aktifkan Gym
        </button>
        <a href="{{ route('super_admin.dashboard') }}" class="sa-btn-cancel">Batal</a>

        @if (!$canSubmit)
            <div class="sa-act-note">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
                Tambahkan minimal 1 paket dan 1 database tersedia sebelum mengaktifkan gym.
            </div>
        @endif
    </div>

</div>{{-- /right --}}

</div>{{-- /sa-act-layout --}}
</form>
@endsection

@section('scripts')
<script>
function togglePass(fieldId) {
    const inp = document.getElementById(fieldId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

function updateLogoName(input) {
    document.getElementById('logo-file-name').textContent =
        input.files.length ? input.files[0].name : 'Belum ada file dipilih';
}

// Subdomain: force lowercase, strip non-allowed chars
document.getElementById('subdomain').addEventListener('input', function () {
    const clean = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
    if (this.value !== clean) this.value = clean;
});
</script>
@endsection
