{{-- Modal: Backup & Nonaktifkan --}}
<div class="sa-modal-backdrop" id="modal-backup">
    <div class="sa-modal">
        <div class="sa-modal-header">
            <div class="sa-modal-title" style="color:#C2410C">Backup & Nonaktifkan Gym</div>
            <button class="sa-modal-close" onclick="saCloseModal('modal-backup')">&#x2715;</button>
        </div>
        <div class="sa-modal-body">
            <div class="sa-warn-icon" style="background:#FFF7ED">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:#D97706">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                </svg>
            </div>
            <div class="sa-warn-title">Backup & Nonaktifkan?</div>
            <div class="sa-warn-desc">
                Sistem akan membuat backup SQL dari database gym
                <span class="sa-warn-db-name" id="backup-gym-name">—</span>
                lalu mengubah statusnya menjadi <strong>Non-aktif</strong>.
                <br><br>
                Gym tidak akan dapat login setelah proses ini.
                File SQL dapat didownload dari halaman Arsip Backup setelah selesai.
                Proses mungkin memakan beberapa detik tergantung ukuran data.
            </div>
        </div>
        <div class="sa-modal-footer">
            <button type="button" class="sa-btn sa-btn-ghost"
                    onclick="saCloseModal('modal-backup')">Batal</button>
            <form id="backup-form" method="POST" action="">
                @csrf
                <button type="submit" class="sa-btn-danger"
                        style="background:#D97706;box-shadow:0 4px 12px -4px rgba(217,119,6,.4)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                         style="width:14px;height:14px">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                    </svg>
                    Ya, Backup & Nonaktifkan
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Clear Database --}}
<div class="sa-modal-backdrop" id="modal-clear">
    <div class="sa-modal">
        <div class="sa-modal-header">
            <div class="sa-modal-title" style="color:#DC2626">Clear Database Gym</div>
            <button class="sa-modal-close" onclick="saCloseModal('modal-clear')">&#x2715;</button>
        </div>
        <div class="sa-modal-body">
            <div class="sa-warn-icon" style="background:#FEF2F2">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:#DC2626">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
            </div>
            <div class="sa-warn-title" style="color:#DC2626">Clear Database?</div>
            <div class="sa-warn-desc">
                Data gym <span id="clear-gym-name" class="sa-warn-db-name">—</span>
                akan di-<strong>truncate</strong> dari database pool (kecuali tabel sistem:
                roles, permissions, migrations, akun keuangan). Tenant record dihapus dari master.
                File backup SQL <em>tetap ada</em> di server.
                <br><br>
                Tindakan ini <strong>tidak dapat dibatalkan</strong>. Pool akan kembali
                tersedia untuk gym baru.
            </div>
        </div>
        <div class="sa-modal-footer">
            <button type="button" class="sa-btn sa-btn-ghost"
                    onclick="saCloseModal('modal-clear')">Batal</button>
            <form id="clear-form" method="POST" action="">
                @csrf
                <button type="submit" class="sa-btn-danger">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                         style="width:14px;height:14px">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                    Ya, Clear Database
                </button>
            </form>
        </div>
    </div>
</div>
