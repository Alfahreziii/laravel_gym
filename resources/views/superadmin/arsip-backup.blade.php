@extends('superadmin.layouts.app')

@section('title', 'Arsip Backup')

@section('content')
<style>
/* ── Tenant status badges ───────────────────────────── */
.sa-sts {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;
}
.sa-sts-dot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
.sa-sts-aktif    { background: #F0FDF4; color: #15803D; border: 1px solid #BBF7D0; }
.sa-sts-aktif    .sa-sts-dot { background: #22C55E; }
.sa-sts-nonaktif { background: #F5F6FA; color: #6B7280; border: 1px solid #D1D5DB; }
.sa-sts-nonaktif .sa-sts-dot { background: #9CA3AF; }
.sa-sts-suspend  { background: #FFF7ED; color: #C2410C; border: 1px solid #FED7AA; }
.sa-sts-suspend  .sa-sts-dot { background: #F97316; }
.sa-sts-cleared  { background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1; }
.sa-sts-cleared  .sa-sts-dot { background: #94A3B8; }

/* ── Table header search / action area ─────────────── */
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

/* ── Icon action buttons ────────────────────────────── */
.sa-tbl-acts { display: flex; align-items: center; gap: 4px; }
.sa-btn-tbl {
    width: 30px; height: 28px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 7px; border: 1.5px solid var(--sa-border);
    background: none; cursor: pointer; color: var(--sa-text-2);
    text-decoration: none;
    transition: border-color .13s, background .13s, color .13s;
}
.sa-btn-tbl:hover                { border-color: var(--sa-primary); color: var(--sa-primary); background: #fff5f1; }
.sa-btn-tbl.sa-btn-tbl-danger   { color: #DC2626; border-color: #FECACA; }
.sa-btn-tbl.sa-btn-tbl-danger:hover { border-color: #DC2626; background: #FEF2F2; }
.sa-btn-tbl.sa-tbl-off           { opacity: .35; pointer-events: none; cursor: default; }
.sa-btn-tbl svg                  { width: 13px; height: 13px; }

/* ── Dark mode ──────────────────────────────────────── */
html.dark .sa-btn-tbl:hover             { background: rgba(242,98,46,.12); }
html.dark .sa-btn-tbl.sa-btn-tbl-danger { color: #F87171; border-color: rgba(220,38,38,.4); }
html.dark .sa-btn-tbl.sa-btn-tbl-danger:hover { background: rgba(220,38,38,.15); border-color: rgba(220,38,38,.6); }

html.dark .sa-search-input         { background: #28231D; }
html.dark .sa-search-input:focus   { background: #1F1B17; }

html.dark .sa-sts-aktif    { background: rgba(34,197,94,.12);  color: #4ADE80; border-color: rgba(34,197,94,.3); }
html.dark .sa-sts-aktif    .sa-sts-dot { background: #4ADE80; }
html.dark .sa-sts-nonaktif { background: rgba(255,255,255,.06); color: #A8A29A; border-color: rgba(255,255,255,.12); }
html.dark .sa-sts-nonaktif .sa-sts-dot { background: #A8A29A; }
html.dark .sa-sts-suspend  { background: rgba(249,115,22,.12); color: #FDBA74; border-color: rgba(249,115,22,.3); }
html.dark .sa-sts-suspend  .sa-sts-dot { background: #F97316; }
html.dark .sa-sts-cleared  { background: rgba(255,255,255,.05); color: #A8A29A; border-color: rgba(255,255,255,.1); }
html.dark .sa-sts-cleared  .sa-sts-dot { background: #6E685F; }

html.dark .sa-dl-badge-yes { color: #4ADE80; }

/* ── Download pill button ───────────────────────────── */
.sa-dl-btn {
    display: inline-flex; align-items: center; gap: 4px;
    height: 26px; padding: 0 10px;
    border: 1.5px solid var(--sa-border); border-radius: 6px;
    font-size: 11.5px; font-weight: 600; font-family: var(--sa-ff-sans);
    color: var(--sa-text-2); background: none; text-decoration: none;
    transition: border-color .13s, color .13s, background .13s;
}
.sa-dl-btn:hover            { border-color: var(--sa-primary); color: var(--sa-primary); background: #fff5f1; }
.sa-dl-btn.sa-dl-btn-off    { opacity: .35; pointer-events: none; cursor: default; }
.sa-dl-btn svg              { width: 11px; height: 11px; }

/* ── Downloaded badge ───────────────────────────────── */
.sa-dl-badge-yes { display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;color:#15803D; }
.sa-dl-badge-no  { font-size:11.5px;color:var(--sa-text-3); }

/* ── Search no-results ──────────────────────────────── */
#sa-no-results { display: none; }

/* ── Empty state ────────────────────────────────────── */
.sa-empty-icon { width:36px;height:36px;margin:0 auto .75rem;display:block;color:var(--sa-text-3); }
</style>

<div class="sa-table-card">
    <div class="sa-table-header">
        <div>
            <div class="sa-table-title">Arsip Backup</div>
            <div class="sa-table-sub">
                {{ $backups->count() }} backup tersimpan &mdash; termasuk gym yang sudah di-clear
            </div>
        </div>
        <div class="sa-table-header-r">
            <div class="sa-search-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input type="text" id="arsip-search" class="sa-search-input" placeholder="Cari gym...">
            </div>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table class="sa-table" id="arsip-table">
            <thead>
                <tr>
                    <th style="width:52px">Aksi</th>
                    <th>Nama Gym</th>
                    <th style="width:140px">Subdomain</th>
                    <th style="width:110px">Tgl Backup</th>
                    <th style="width:100px">File SQL</th>
                    <th style="width:80px">Zip</th>
                    <th style="width:96px;text-align:center">Downloaded</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($backups as $backup)
                @php
                    // Nama & subdomain: snapshot dulu, fallback relasi, fallback ID
                    $namaGym   = $backup->nama_gym
                              ?? $backup->tenant?->nama_gym
                              ?? ($backup->tenant_id ? "Gym #{$backup->tenant_id}" : '—');
                    $subdomain = $backup->subdomain
                              ?? $backup->tenant?->subdomain
                              ?? '—';

                    // Status tenant saat ini
                    if ($backup->tenant_id === null) {
                        $stsClass = 'sa-sts-cleared';
                        $stsLabel = 'Dihapus';
                    } else {
                        $stsClass = match($backup->tenant?->status) {
                            'aktif'    => 'sa-sts-aktif',
                            'nonaktif' => 'sa-sts-nonaktif',
                            'suspend'  => 'sa-sts-suspend',
                            default    => 'sa-sts-nonaktif',
                        };
                        $stsLabel = match($backup->tenant?->status) {
                            'aktif'    => 'Aktif',
                            'nonaktif' => 'Non-aktif',
                            'suspend'  => 'Suspend',
                            default    => 'Non-aktif',
                        };
                    }
                @endphp
                <tr data-gym="{{ strtolower($namaGym . ' ' . $subdomain) }}">

                    {{-- Aksi --}}
                    <td>
                        <div class="sa-tbl-acts">
                            <button type="button"
                                    class="sa-btn-tbl sa-btn-tbl-danger"
                                    title="Hapus arsip ini (permanen)"
                                    onclick="openDeleteModal({{ $backup->id }}, '{{ addslashes($namaGym) }}', '{{ $backup->tgl_backup?->format('d M Y') ?? '—' }}')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                            </button>
                        </div>
                    </td>

                    {{-- Nama Gym --}}
                    <td>
                        <div style="font-weight:600;color:var(--sa-text)">{{ $namaGym }}</div>
                        <div style="margin-top:3px">
                            <span class="sa-sts {{ $stsClass }}">
                                <span class="sa-sts-dot"></span>
                                {{ $stsLabel }}
                            </span>
                        </div>
                    </td>

                    {{-- Subdomain --}}
                    <td>
                        @if ($subdomain !== '—')
                            <span class="sa-td-mono">{{ $subdomain }}</span>
                        @else
                            <span style="color:var(--sa-text-3)">—</span>
                        @endif
                    </td>

                    {{-- Tgl Backup --}}
                    <td style="font-size:13px;color:var(--sa-text-2)">
                        {{ $backup->tgl_backup?->format('d M Y') ?? '—' }}
                    </td>

                    {{-- File SQL --}}
                    <td>
                        @if ($backup->sql_path)
                            @if ($backup->sql_exists)
                                <a href="{{ route('super_admin.backup.download', [$backup->id, 'sql']) }}"
                                   class="sa-dl-btn" title="Download {{ basename($backup->sql_path) }}">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                    </svg>
                                    .sql
                                </a>
                            @else
                                <span class="sa-dl-btn sa-dl-btn-off" title="File tidak ditemukan di server">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    .sql
                                </span>
                            @endif
                        @else
                            <span style="color:var(--sa-text-3);font-size:12.5px">—</span>
                        @endif
                    </td>

                    {{-- File Zip --}}
                    <td>
                        @if ($backup->storage_zip_path)
                            @if ($backup->zip_exists)
                                <a href="{{ route('super_admin.backup.download', [$backup->id, 'zip']) }}"
                                   class="sa-dl-btn">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                    </svg>
                                    .zip
                                </a>
                            @else
                                <span class="sa-dl-btn sa-dl-btn-off" title="File tidak ditemukan di server">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    .zip
                                </span>
                            @endif
                        @else
                            <span style="color:var(--sa-text-3);font-size:12.5px">—</span>
                        @endif
                    </td>

                    {{-- Downloaded --}}
                    <td style="text-align:center">
                        @if ($backup->downloaded)
                            <span class="sa-dl-badge-yes">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                                     style="width:13px;height:13px">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                Ya
                            </span>
                        @else
                            <span class="sa-dl-badge-no">—</span>
                        @endif
                    </td>

                </tr>
                @empty
                <tr class="sa-empty">
                    <td colspan="7">
                        <svg class="sa-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                        </svg>
                        Belum ada backup yang pernah dibuat.
                    </td>
                </tr>
                @endforelse

                @if ($backups->isNotEmpty())
                <tr id="sa-no-results" class="sa-empty">
                    <td colspan="7">Tidak ada backup yang cocok dengan pencarian.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- ── Modal: konfirmasi hapus arsip ──────────────────── --}}
@section('modals')
<div class="sa-modal-backdrop" id="modal-delete-arsip">
    <div class="sa-modal">
        <div class="sa-modal-header">
            <div class="sa-modal-title" style="color:#DC2626">Hapus Arsip Backup</div>
            <button class="sa-modal-close" onclick="saCloseModal('modal-delete-arsip')">&#x2715;</button>
        </div>
        <div class="sa-modal-body">
            <div class="sa-warn-icon" style="background:#FEF2F2">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:#DC2626">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
            </div>
            <div class="sa-warn-title" style="color:#DC2626">Hapus Arsip Permanen?</div>
            <div class="sa-warn-desc">
                Backup tanggal <strong id="del-arsip-tgl">—</strong>
                milik gym <span id="del-arsip-gym" class="sa-warn-db-name">—</span>
                akan dihapus permanen.
                <br><br>
                File SQL di server <strong>akan ikut dihapus</strong> — tidak dapat dikembalikan.
                Pastikan file sudah di-download sebelum menghapus arsip ini.
            </div>
        </div>
        <div class="sa-modal-footer">
            <button type="button" class="sa-btn sa-btn-ghost"
                    onclick="saCloseModal('modal-delete-arsip')">Batal</button>
            <form id="del-arsip-form" method="POST" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="sa-btn-danger">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                         style="width:14px;height:14px">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                    Ya, Hapus Permanen
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openDeleteModal(id, gymName, tglBackup) {
    document.getElementById('del-arsip-gym').textContent  = gymName;
    document.getElementById('del-arsip-tgl').textContent  = tglBackup;
    document.getElementById('del-arsip-form').action =
        '{{ url("/arsip-backup") }}/' + id;
    saOpenModal('modal-delete-arsip');
}

(function () {
    var input = document.getElementById('arsip-search');
    var noRes = document.getElementById('sa-no-results');
    if (!input) return;
    input.addEventListener('input', function () {
        var q    = this.value.toLowerCase().trim();
        var rows = document.querySelectorAll('#arsip-table tbody tr[data-gym]');
        var vis  = 0;
        rows.forEach(function (row) {
            var match = !q || row.dataset.gym.indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) vis++;
        });
        if (noRes) noRes.style.display = (vis === 0 && q) ? '' : 'none';
    });
})();
</script>
@endsection
