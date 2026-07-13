@extends('layout.layout')

@php
    $title    = 'Dashboard';
    $subTitle = $trainer->name;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger">{{ session('error') }}</x-alert>
@endif

@php
    $isTraining  = $trainer->isTraining();
    $statusLabel = $isTraining ? 'Training' : 'Available';
    $statusColor = $isTraining ? 'orange' : 'teal';
    $statusSub   = $isTraining && $trainer->active_session
        ? 'Melatih: ' . $trainer->active_session->anggota->name
        : 'Tidak ada sesi aktif';
@endphp

{{-- KPI Row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

    <x-stat-card :color="$statusColor" label="Status Training" icon="personal-trainer"
        :value="$statusLabel" :sub="$statusSub" />

    <x-stat-card color="teal" label="Sesi Selesai" icon="kehadiran"
        value="{{ $trainer->sesi_sudah_dijalani }}" sub="Total sesi yang telah dijalani" />

    <x-stat-card color="blue" label="Sesi Tersisa" icon="paket-member"
        value="{{ $trainer->sesi_belum_dijalani }}" sub="Total sesi belum dijalani" />

    <x-stat-card color="cyan" label="Total Member" icon="member"
        value="{{ $memberTrainers->count() }}" sub="Member aktif saat ini" />

</div>

{{-- Daftar Member --}}
<div class="card border-0 overflow-hidden mt-6">
    <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
        <h6 class="font-display font-semibold text-[20px] leading-snug tracking-[.01em] text-ink dark:text-ink-d">
            Daftar Member Aktif
        </h6>
        <a href="{{ route('trainer.session.logs') }}" class="btn btn-secondary btn-sm inline-flex items-center gap-1.5">
            <iconify-icon icon="lucide:clock" class="text-base"></iconify-icon>
            Riwayat Sesi
        </a>
    </div>
    <x-data-table tableId="dashboardMember" :colspan="9" placeholder="Cari nama member atau paket...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Member</th>
                <th scope="col">Riwayat Gym</th>
                <th scope="col">Paket</th>
                <th scope="col">Sesi Selesai</th>
                <th scope="col">Sisa Sesi</th>
                <th scope="col">Status Kehadiran</th>
                <th scope="col">Status Sesi</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</div>

{{-- Modal Mulai Sesi --}}
<x-modal id="start-session-modal" title="Mulai Sesi Training">
    <x-slot:body>
        <form id="startSessionForm" method="POST">
            @csrf
            <p class="text-neutral-700 dark:text-neutral-300 text-base">
                Apakah Anda yakin ingin memulai sesi training untuk <strong id="sessionMemberName"></strong>?
            </p>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="start-session-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-6 py-2 rounded-lg transition-colors">
            Batal
        </button>
        <button type="submit" form="startSessionForm"
            class="bg-primary-600 hover:bg-primary-700 text-white text-base px-6 py-2 rounded-lg transition-colors">
            Mulai
        </button>
    </x-slot:footer>
</x-modal>

{{-- Modal Selesai Sesi (tidak ada trigger dari tabel ini, dipertahankan untuk konsistensi) --}}
<x-modal id="end-session-modal" title="Selesai Sesi Training">
    <x-slot:body>
        <form id="endSessionForm" method="POST">
            @csrf
            <p class="text-neutral-700 dark:text-neutral-300 text-base">
                Apakah Anda yakin ingin menyelesaikan sesi training untuk <strong id="endSessionMemberName"></strong>?
            </p>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="end-session-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-6 py-2 rounded-lg transition-colors">
            Batal
        </button>
        <button type="submit" form="endSessionForm"
            class="bg-primary-600 hover:bg-primary-700 text-white text-base px-6 py-2 rounded-lg transition-colors">
            Selesai
        </button>
    </x-slot:footer>
</x-modal>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startForm = document.getElementById('startSessionForm');
    const startName = document.getElementById('sessionMemberName');

    // Event delegation untuk tombol di baris ajax yang di-render dinamis.
    // HexaModal.show/hide didefinisikan oleh komponen x-modal di atas.
    document.addEventListener('click', function (e) {
        const startBtn = e.target.closest('.open-start-session-modal');
        if (startBtn) {
            startName.textContent = startBtn.dataset.member;
            startForm.setAttribute('action', startBtn.dataset.action);
            HexaModal.show('start-session-modal');
        }
    });

    AjaxTable.init('dashboardMember', {
        url: '{{ route('trainer.dashboard.datatable') }}',
        colSpan: 9,
        renderRow: function (item) {
            var kehadiranBadge = item.is_checked_in
                ? AjaxTable.badge('success', 'Hadir')
                : AjaxTable.badge('neutral', 'Belum Check-in');

            var statusSesi = '';
            if (item.is_session_active) {
                statusSesi = AjaxTable.badge('warning', 'Sedang Training')
                    + (item.session_started_at
                        ? '<br><small class="text-neutral-500">Mulai: ' + item.session_started_at + '</small>'
                        : '');
            } else {
                statusSesi = AjaxTable.badge('neutral', 'Tidak Aktif');
            }

            var actionHtml = '';
            if (item.sisa_sesi > 0) {
                if (!item.is_session_active) {
                    if (!item.trainer_is_training) {
                        if (item.is_checked_in) {
                            actionHtml = '<button type="button"'
                                + ' class="open-start-session-modal text-xs font-medium px-3 py-1.5 rounded-lg bg-success-600 text-white hover:bg-success-700 transition-colors"'
                                + ' data-member="' + item.anggota_name + '"'
                                + ' data-action="' + item.start_session_url + '">'
                                + 'Mulai Sesi'
                                + '</button>';
                        } else {
                            actionHtml = '<button disabled class="text-xs font-medium px-3 py-1.5 rounded-lg bg-neutral-100 text-neutral-400 cursor-not-allowed">Belum Check-in</button>';
                        }
                    } else {
                        actionHtml = '<button disabled class="text-xs font-medium px-3 py-1.5 rounded-lg bg-neutral-100 text-neutral-400 cursor-not-allowed">Sedang Melatih Lainnya</button>';
                    }
                } else {
                    actionHtml = '<a href="' + item.monitoring_url + '" class="text-xs font-medium px-3 py-1.5 rounded-lg bg-warning-100 text-warning-700 hover:bg-warning-200 transition-colors">Ke Monitoring</a>';
                }
            } else {
                actionHtml = '<span class="text-neutral-400 text-sm">Sesi Habis</span>';
            }

            return '<tr class="' + (item.is_session_active ? 'bg-warning-50 dark:bg-warning-900/20' : '') + '">'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td>'
                    + '<a title="Member Detail" href="' + item.detail_url + '">'
                        + '<strong class="text-primary-600">' + item.anggota_name + '</strong><br>'
                        + '<small class="text-neutral-500">' + item.anggota_no_telp + '</small>'
                    + '</a>'
                + '</td>'
                + '<td class="whitespace-nowrap"><a href="' + item.history_url + '" class="text-primary-600 hover:underline">Lihat Detail</a></td>'
                + '<td>' + item.paket_nama + '</td>'
                + '<td class="whitespace-nowrap">' + item.sesi + ' / ' + item.jumlah_sesi + '</td>'
                + '<td class="whitespace-nowrap">' + AjaxTable.badge(item.sisa_sesi > 0 ? 'info' : 'neutral', item.sisa_sesi + ' sesi') + '</td>'
                + '<td class="whitespace-nowrap">' + kehadiranBadge + '</td>'
                + '<td>' + statusSesi + '</td>'
                + '<td class="whitespace-nowrap">' + actionHtml + '</td>'
                + '</tr>';
        }
    });

    @if(session('success'))
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}', timer: 3000 });
    @endif
    @if(session('error'))
    Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });
    @endif
});
</script>
@endsection
