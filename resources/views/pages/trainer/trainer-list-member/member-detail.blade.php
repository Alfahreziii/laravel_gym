@extends('layout.layout')

@php
    $title    = 'Detail Member';
    $subTitle = $member->name;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger">{{ session('error') }}</x-alert>
@endif

{{-- Statistik --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-blue-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Total Paket</p>
                    <h6>{{ $totalPaket }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-blue-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:package-variant" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-green-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Sesi Aktif</p>
                    <h6>{{ $totalSesiAktif }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-green-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:timer-sand" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-purple-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Sesi Selesai</p>
                    <h6>{{ $totalSesiSelesai }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-purple-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:check-circle" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-orange-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Sesi Kadaluarsa</p>
                    <h6>{{ $totalSesiKadaluarsa }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-orange-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:clock-alert" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Info Member --}}
<div class="grid grid-cols-12 mb-6">
    <div class="col-span-12">
        <div class="card border border-gray-200">
            <div class="card-header">
                <h6 class="card-title mb-0 text-lg">Informasi Member</h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-muted mb-1">Nama</p>
                        <p class="font-semibold">{{ $member->name }}</p>
                    </div>
                    <div>
                        <p class="text-muted mb-1">No. Telepon</p>
                        <p class="font-semibold">{{ $member->no_telp }}</p>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Email</p>
                        <p class="font-semibold">{{ $member->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Status Hari Ini</p>
                        @if($isCheckedIn)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-700 dark:bg-success-600/20 dark:text-success-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-success-500 flex-shrink-0"></span>
                                Sudah Check-in
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-neutral-400 flex-shrink-0"></span>
                                Belum Check-in
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Paket Aktif --}}
<div class="grid grid-cols-12 mb-6">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header">
                <h6 class="card-title mb-0 text-lg">Paket Aktif</h6>
            </div>
            <div class="card-body">
                <x-data-table tableId="paketAktif" :colspan="6" placeholder="Cari paket atau kode transaksi...">
                    <x-slot:header>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Paket</th>
                            <th scope="col">Periode</th>
                            <th scope="col">Sesi Tersisa</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </x-slot:header>
                </x-data-table>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat Semua Paket --}}
<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header">
                <h6 class="card-title mb-0 text-lg">Riwayat Semua Paket</h6>
            </div>
            <div class="card-body">
                <x-data-table tableId="riwayatPaket" :colspan="9" placeholder="Cari kode transaksi atau paket...">
                    <x-slot:header>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Kode Transaksi</th>
                            <th scope="col">Riwayat Gym</th>
                            <th scope="col">Paket</th>
                            <th scope="col">Periode</th>
                            <th scope="col">Total Sesi</th>
                            <th scope="col">Sesi Selesai</th>
                            <th scope="col">Sisa Sesi</th>
                            <th scope="col">Status</th>
                        </tr>
                    </x-slot:header>
                </x-data-table>
            </div>
        </div>
    </div>
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

    AjaxTable.init('paketAktif', {
        url: '{{ route('trainerlistmember.active_packages.datatable', $member->id) }}',
        colSpan: 6,
        renderRow: function (item) {
            var statusHtml = '';
            if (item.is_session_active) {
                statusHtml = AjaxTable.badge('warning', 'Sedang Training')
                    + (item.session_started_at
                        ? '<br><small class="text-neutral-500">Mulai: ' + item.session_started_at + '</small>'
                        : '');
            } else {
                statusHtml = AjaxTable.badge('success', 'Siap Training');
            }

            var actionHtml = '';
            if (item.sesi > 0) {
                if (!item.is_session_active) {
                    if (!item.trainer_is_training) {
                        if (item.is_checked_in) {
                            actionHtml = '<button type="button"'
                                + ' class="open-start-session-modal text-xs font-medium px-3 py-1.5 rounded-lg bg-success-600 text-white hover:bg-success-700 transition-colors"'
                                + ' data-member="' + item.member_name + '"'
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
                + '<td><strong>' + item.paket_nama + '</strong><br><small class="text-neutral-500">' + item.kode_transaksi + '</small></td>'
                + '<td class="whitespace-nowrap">' + item.periode + '</td>'
                + '<td class="whitespace-nowrap">' + AjaxTable.badge(item.sesi > 0 ? 'info' : 'neutral', item.sesi + ' / ' + item.jumlah_sesi) + '</td>'
                + '<td>' + statusHtml + '</td>'
                + '<td class="whitespace-nowrap">' + actionHtml + '</td>'
                + '</tr>';
        }
    });

    AjaxTable.init('riwayatPaket', {
        url: '{{ route('trainerlistmember.history.datatable', $member->id) }}',
        colSpan: 9,
        renderRow: function (item) {
            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td class="whitespace-nowrap">' + item.kode_transaksi + '</td>'
                + '<td class="whitespace-nowrap"><a href="' + item.history_url + '" class="text-primary-600 hover:underline">Lihat Detail</a></td>'
                + '<td>' + item.paket_nama + '</td>'
                + '<td class="whitespace-nowrap">' + item.periode + '</td>'
                + '<td class="whitespace-nowrap">' + item.jumlah_sesi + '</td>'
                + '<td class="whitespace-nowrap">' + item.sesi_selesai + '</td>'
                + '<td class="whitespace-nowrap">' + item.sisa_sesi + '</td>'
                + '<td class="whitespace-nowrap">' + AjaxTable.badge(item.status_type, item.status_label) + '</td>'
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
