@extends('superadmin.layouts.app')

@section('title', 'Aktifkan Kembali — ' . $tenant->nama_gym)

@section('content')
<style>
/* ── Reaktivasi page (reuse pola dari aktivasi.blade.php) ──────── */
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
/* Note block */
.sa-act-note {
    display: flex; align-items: flex-start; gap: .5rem;
    padding: .75rem 1rem;
    background: #FFF7ED; border: 1px solid #FED7AA;
    border-radius: 9px; font-size: 12.5px; color: #92400E;
    line-height: 1.5;
}
.sa-act-note svg { flex-shrink: 0; margin-top: 1px; }
/* Zip source radio rows */
.sa-zip-option { display: block; cursor: pointer; margin-bottom: .625rem; }
.sa-zip-option:last-child { margin-bottom: 0; }
.sa-zip-option input[type="radio"] { margin-right: .5rem; }
.sa-zip-option-body { margin-top: .5rem; margin-left: 1.5rem; }
/* Readonly tenant info */
.sa-readonly-row { display: flex; gap: 1rem; padding-bottom: .625rem; margin-bottom: .625rem; border-bottom: 1px solid var(--sa-border); }
.sa-readonly-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.sa-readonly-label { font-size: 12px; font-weight: 600; color: var(--sa-text-3); min-width: 110px; flex-shrink: 0; }
.sa-readonly-value { font-size: 13.5px; color: var(--sa-text); font-weight: 500; }
.sa-readonly-value-mono { font-family: var(--sa-ff-mono); }
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
    .sa-form-2col { grid-template-columns: 1fr; }
}

/* ── Dark mode overrides ────────────────────────────── */
html.dark .sa-form-section    { background: var(--sa-card-bg); }
html.dark .sa-form-section-head { background: rgba(255,255,255,.03); }
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

/* ── Responsive (reaktivasi content) ─────────────────── */
@media (max-width: 640px) {
    .sa-readonly-row { flex-direction: column; gap: .25rem; }
    .sa-readonly-label { min-width: 0; }
    .sa-act-actions { padding: 1rem 1.125rem; }
}
</style>

<div style="margin-bottom:1.25rem">
    <a href="{{ route('super_admin.kelola_tenant.show', $tenant) }}" style="font-size:13px;color:var(--sa-text-2);text-decoration:none">
        &larr; Kembali ke {{ $tenant->nama_gym }}
    </a>
</div>

<form method="POST"
      action="{{ route('super_admin.reaktivasi.store', $tenant) }}"
      enctype="multipart/form-data">
@csrf

{{-- Info Tenant (readonly) ─────────────────────────── --}}
<div class="sa-form-section">
    <div class="sa-form-section-head">
        <div class="sa-form-section-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
        </div>
        <div>
            <div class="sa-form-section-title">Aktifkan Kembali Gym</div>
            <div class="sa-form-section-sub">Gym ini sedang berstatus arsip</div>
        </div>
    </div>
    <div class="sa-form-section-body">
        <div class="sa-readonly-row">
            <div class="sa-readonly-label">Nama Gym</div>
            <div class="sa-readonly-value">{{ $tenant->nama_gym }}</div>
        </div>
        <div class="sa-readonly-row">
            <div class="sa-readonly-label">Subdomain</div>
            <div class="sa-readonly-value sa-readonly-value-mono">
                {{ $tenant->subdomain }}.{{ env('TENANT_BASE_DOMAIN', 'sistemgate.com') }}
            </div>
        </div>
    </div>
</div>

{{-- Database Pool ───────────────────────────────────── --}}
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
            <div class="sa-form-section-sub">Alokasi database baru untuk gym ini</div>
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
                <select id="database_pool_id" name="database_pool_id" class="sa-form-input" required>
                    <option value="">— Pilih database pool —</option>
                    @foreach ($pools as $pool)
                        <option value="{{ $pool->id }}" {{ old('database_pool_id') == $pool->id ? 'selected' : '' }}>
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
            </div>
        @endif
    </div>
</div>

{{-- Paket & Langganan ───────────────────────────────── --}}
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
            <div class="sa-form-section-sub">Pilih paket dan masa aktif baru</div>
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
                               {{ old('package_id', $tenant->package_id) == $pkg->id ? 'checked' : '' }}>
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

    </div>
</div>

{{-- Arsip Storage (ZIP) ─────────────────────────────── --}}
<div class="sa-form-section">
    <div class="sa-form-section-head">
        <div class="sa-form-section-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
            </svg>
        </div>
        <div>
            <div class="sa-form-section-title">Arsip Storage (ZIP)</div>
            <div class="sa-form-section-sub">Opsional — restore foto/file gym dari backup lama</div>
        </div>
    </div>
    <div class="sa-form-section-body">

        <label class="sa-zip-option">
            <input type="radio" name="zip_source" value="archive"
                   {{ $archives->isEmpty() ? 'disabled' : '' }}
                   {{ old('zip_source', $archives->isNotEmpty() ? 'archive' : 'skip') === 'archive' ? 'checked' : '' }}
                   onchange="toggleZipSource()">
            Pilih dari arsip server
            <div class="sa-zip-option-body">
                @if ($archives->isEmpty())
                    <div class="sa-act-note">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                        </svg>
                        Tidak ada arsip ZIP milik gym ini yang filenya masih tersedia di server.
                    </div>
                @else
                    <select name="archive_backup_id" class="sa-form-input" id="archive_backup_id">
                        @foreach ($archives as $backup)
                            <option value="{{ $backup->id }}" {{ old('archive_backup_id') == $backup->id ? 'selected' : '' }}>
                                {{ $backup->tgl_backup?->format('d M Y') ?? '—' }} — {{ basename($backup->storage_zip_path) }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
        </label>

        <label class="sa-zip-option">
            <input type="radio" name="zip_source" value="upload"
                   {{ old('zip_source') === 'upload' ? 'checked' : '' }}
                   onchange="toggleZipSource()">
            Upload file ZIP manual
            <div class="sa-zip-option-body">
                <input type="file" name="storage_zip" id="storage_zip" class="sa-form-input" accept=".zip">
            </div>
        </label>

        <label class="sa-zip-option">
            <input type="radio" name="zip_source" value="skip"
                   {{ old('zip_source', $archives->isNotEmpty() ? 'archive' : 'skip') === 'skip' ? 'checked' : '' }}
                   onchange="toggleZipSource()">
            Lewati (gym tidak punya foto / tidak perlu restore)
        </label>

        @error('archive_backup_id')
            <div class="sa-form-error">{{ $message }}</div>
        @enderror
        @error('storage_zip')
            <div class="sa-form-error">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Submit ──────────────────────────────────────────── --}}
<div class="sa-act-actions">
    @php $canSubmit = $pools->isNotEmpty() && $packages->isNotEmpty(); @endphp

    <button type="submit" class="sa-btn-submit" {{ $canSubmit ? '' : 'disabled' }}>
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" style="width:15px;height:15px">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        Aktifkan Kembali
    </button>
    <a href="{{ route('super_admin.kelola_tenant.show', $tenant) }}" class="sa-btn-cancel">Batal</a>

    @if (!$canSubmit)
        <div class="sa-act-note">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
            </svg>
            Tambahkan minimal 1 paket dan 1 database tersedia sebelum mengaktifkan kembali gym ini.
        </div>
    @endif
</div>

</form>
@endsection

@section('scripts')
<script>
function toggleZipSource() {
    var source = document.querySelector('input[name="zip_source"]:checked').value;
    var archiveSelect = document.getElementById('archive_backup_id');
    var uploadInput   = document.getElementById('storage_zip');
    if (archiveSelect) archiveSelect.disabled = source !== 'archive';
    if (uploadInput)   uploadInput.disabled   = source !== 'upload';
}
document.addEventListener('DOMContentLoaded', toggleZipSource);
</script>
@endsection
