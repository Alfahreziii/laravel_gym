{{-- Aksi tombol per-baris: Kelola · Backup & Nonaktifkan · Clear DB
     Variables tersedia lewat scope @forelse: $tenant, $canClear, $clearTitle, $poolId --}}
<div class="sa-tbl-acts">

    {{-- Detail / Kelola --}}
    <a href="{{ route('super_admin.kelola_tenant.show', $tenant) }}"
       class="sa-btn-tbl"
       title="Kelola tenant ini">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
        </svg>
    </a>

    {{-- Backup & Nonaktifkan (disabled untuk tenant archived — DB sudah kosong) --}}
    <button type="button"
            class="sa-btn-tbl{{ ($canBackup ?? true) ? '' : ' sa-tbl-off' }}"
            title="{{ ($canBackup ?? true) ? 'Backup & Nonaktifkan' : 'Database sudah di-clear' }}"
            @if ($canBackup ?? true) onclick="openBackupModal({{ $tenant->id }}, '{{ addslashes($tenant->nama_gym) }}')" @endif>
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
        </svg>
    </button>

    {{-- Clear Database: aktif hanya jika nonaktif + ada pool + backup sudah didownload --}}
    <button type="button"
            class="sa-btn-tbl{{ $canClear ? ' sa-btn-tbl-danger' : ' sa-tbl-off' }}"
            title="{{ $clearTitle }}"
            @if ($canClear) onclick="openClearModal({{ $poolId }}, '{{ addslashes($tenant->nama_gym) }}')" @endif>
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
        </svg>
    </button>

    {{-- Aktifkan Kembali: hanya untuk tenant yang sudah diarsipkan --}}
    @if ($tenant->status === 'archived')
    <a href="{{ route('super_admin.reaktivasi', $tenant) }}"
       class="sa-btn-tbl"
       title="Aktifkan Kembali"
       style="color:#15803D">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
        </svg>
    </a>
    @endif

</div>
