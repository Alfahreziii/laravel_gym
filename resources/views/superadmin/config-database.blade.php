@extends('superadmin.layouts.app')

@section('title', 'Config Database')

@section('content')

{{-- ── Stat cards ────────────────────────────────────────────────── --}}
<div class="sa-stat-grid">

    <div class="sa-stat-card">
        <div class="sa-stat-icon sa-icon-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
            </svg>
        </div>
        <div>
            <div class="sa-stat-label">Total Pool DB</div>
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
            <div class="sa-stat-label">Tersedia</div>
            <div class="sa-stat-value" style="color:#15803D">{{ $stats['available'] }}</div>
        </div>
    </div>

    <div class="sa-stat-card">
        <div class="sa-stat-icon sa-icon-neutral">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
            </svg>
        </div>
        <div>
            <div class="sa-stat-label">Digunakan</div>
            <div class="sa-stat-value" style="color:#6B7280">{{ $stats['used'] }}</div>
        </div>
    </div>

</div>

{{-- ── Tabel pool ────────────────────────────────────────────────── --}}
<div class="sa-table-card">
    <div class="sa-table-header">
        <div>
            <div class="sa-table-title">Database Pool</div>
            <div class="sa-table-sub">Daftar database yang dapat dialokasikan ke tenant baru</div>
        </div>
        <button class="sa-btn sa-btn-primary" onclick="saOpenModal('modal-add-pool')">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"
                 style="width:15px;height:15px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Database
        </button>
    </div>

    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th style="width:100px">Aksi</th>
                    <th>Nama Database</th>
                    <th>Host</th>
                    <th>Username</th>
                    <th style="width:120px">Status</th>
                    <th>Dipakai Oleh</th>
                    <th style="width:140px">Tgl Dialokasikan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pools as $pool)
                <tr>
                    <td>
                        @if ($pool->status === 'used' && $pool->tenant)
                            <button class="sa-btn-danger-ghost"
                                    onclick="openClearModal({{ $pool->id }}, '{{ addslashes($pool->db_name) }}')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                                Clear
                            </button>
                        @else
                            <button class="sa-btn-danger-ghost" disabled
                                    style="opacity:.35;cursor:not-allowed"
                                    title="{{ $pool->status === 'available' ? 'Pool kosong — tidak ada yang perlu di-clear' : 'Tidak ada tenant terkait' }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                                Clear
                            </button>
                        @endif
                    </td>
                    <td class="sa-td-mono">{{ $pool->db_name }}</td>
                    <td class="sa-td-mono">{{ $pool->db_host }}</td>
                    <td class="sa-td-mono">{{ $pool->db_username }}</td>
                    <td>
                        @if ($pool->status === 'available')
                            <span class="sa-badge sa-badge-available">
                                <span class="sa-badge-dot"></span>Available
                            </span>
                        @else
                            <span class="sa-badge sa-badge-used">
                                <span class="sa-badge-dot"></span>Used
                            </span>
                        @endif
                    </td>
                    <td>
                        @if ($pool->tenant)
                            <span style="font-weight:600;color:var(--sa-text)">{{ $pool->tenant->nama_gym }}</span>
                            <span style="display:block;font-size:11.5px;color:var(--sa-text-3)">
                                {{ $pool->tenant->subdomain }}.{{ env('TENANT_BASE_DOMAIN') }}
                            </span>
                        @else
                            <span style="color:var(--sa-text-3)">—</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-text-2);font-size:13px">
                        {{ $pool->created_at->format('d M Y') }}
                    </td>
                </tr>
                @empty
                <tr class="sa-empty">
                    <td colspan="7">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                             style="width:32px;height:32px;margin:0 auto .75rem;display:block;color:var(--sa-text-3)">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375"/>
                        </svg>
                        Belum ada database pool yang terdaftar
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODALS                                                         --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@section('modals')

{{-- Modal: Tambah Database Pool --}}
<div class="sa-modal-backdrop" id="modal-add-pool">
    <div class="sa-modal">
        <div class="sa-modal-header">
            <div class="sa-modal-title">Tambah Database Pool</div>
            <button class="sa-modal-close" onclick="saCloseModal('modal-add-pool')">&#x2715;</button>
        </div>
        <form method="POST" action="{{ route('super_admin.config_database.store') }}">
            @csrf
            <div class="sa-modal-body">

                <div class="sa-form-group {{ $errors->has('db_name') ? 'sa-field-error' : '' }}">
                    <label class="sa-form-label" for="db_name">
                        Nama Database <span style="color:var(--sa-primary)">*</span>
                    </label>
                    <input type="text" id="db_name" name="db_name"
                           class="sa-form-input"
                           value="{{ old('db_name') }}"
                           placeholder="gym_namatenant"
                           required>
                    @error('db_name')
                        <div class="sa-form-error">{{ $message }}</div>
                    @enderror
                    <div class="sa-form-hint">Harus unik. Nama database yang sudah ada di MySQL server.</div>
                </div>

                <div class="sa-form-group {{ $errors->has('db_host') ? 'sa-field-error' : '' }}">
                    <label class="sa-form-label" for="db_host">
                        Host <span style="color:var(--sa-primary)">*</span>
                    </label>
                    <input type="text" id="db_host" name="db_host"
                           class="sa-form-input"
                           value="{{ old('db_host', '127.0.0.1') }}"
                           placeholder="127.0.0.1"
                           required>
                    @error('db_host')
                        <div class="sa-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sa-form-group {{ $errors->has('db_username') ? 'sa-field-error' : '' }}">
                    <label class="sa-form-label" for="db_username">
                        Username <span style="color:var(--sa-primary)">*</span>
                    </label>
                    <input type="text" id="db_username" name="db_username"
                           class="sa-form-input"
                           value="{{ old('db_username', 'root') }}"
                           placeholder="root"
                           required>
                    @error('db_username')
                        <div class="sa-form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sa-form-group">
                    <label class="sa-form-label" for="db_password">Password</label>
                    <input type="password" id="db_password" name="db_password"
                           class="sa-form-input"
                           placeholder="Kosongkan jika tanpa password"
                           autocomplete="new-password">
                    <div class="sa-form-hint">Biarkan kosong jika database tidak memerlukan password.</div>
                </div>

            </div>
            <div class="sa-modal-footer">
                <button type="button" class="sa-btn sa-btn-ghost"
                        onclick="saCloseModal('modal-add-pool')">Batal</button>
                <button type="submit" class="sa-btn sa-btn-primary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"
                         style="width:14px;height:14px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah Pool
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Clear Database (konfirmasi) --}}
<div class="sa-modal-backdrop" id="modal-clear-db">
    <div class="sa-modal">
        <div class="sa-modal-header">
            <div class="sa-modal-title" style="color:#DC2626">Clear Database</div>
            <button class="sa-modal-close" onclick="saCloseModal('modal-clear-db')">&#x2715;</button>
        </div>
        <div class="sa-modal-body">
            <div class="sa-warn-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
            </div>
            <div class="sa-warn-title">Yakin ingin clear database ini?</div>
            <div class="sa-warn-desc">
                Data gym di database
                <span class="sa-warn-db-name" id="clear-db-name">—</span>
                akan di-<strong>truncate</strong> (kecuali tabel sistem: roles, permissions,
                migrations, akun keuangan). Tenant record akan dihapus dari master.
                File backup SQL <em>tetap ada</em> di server.
                <br><br>
                Guard: gym harus sudah <strong>Non-aktif</strong> dan file backup
                sudah <strong>didownload</strong>. Tindakan ini <strong>tidak dapat dibatalkan</strong>.
            </div>
        </div>
        <div class="sa-modal-footer">
            <button type="button" class="sa-btn sa-btn-ghost"
                    onclick="saCloseModal('modal-clear-db')">Batal</button>
            <form method="POST" id="clear-db-form" action="">
                @csrf
                <button type="submit" class="sa-btn-danger">Ya, Clear Database</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openClearModal(poolId, dbName) {
    document.getElementById('clear-db-name').textContent = dbName;
    document.getElementById('clear-db-form').action =
        '{{ url("/config-database") }}/' + poolId + '/clear';
    saOpenModal('modal-clear-db');
}

// Buka modal add-pool otomatis saat ada error validasi
@if (session('open_modal') === 'add-pool' || ($errors->any() && old('db_name')))
    document.addEventListener('DOMContentLoaded', function () {
        saOpenModal('modal-add-pool');
    });
@endif
</script>
@endsection
