@extends('layout.layout')

@php
    $title    = 'Detail Member';
    $subTitle = $member->name;

    $nameParts = explode(' ', trim($member->name));
    $initials  = count($nameParts) >= 2
        ? strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1))
        : strtoupper(mb_substr($member->name, 0, 2));
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger">{{ session('error') }}</x-alert>
@endif

{{-- ==================== HERO CARD ==================== --}}
<div class="card overflow-hidden border border-neutral-200 dark:border-neutral-700">

    {{-- Banner --}}
    <div class="h-20 relative flex-none" style="background: linear-gradient(120deg, #BC3E14, #F2622E 70%, #FB7843)">
        <div class="absolute inset-0" style="background: radial-gradient(300px 160px at 88% 0%, rgba(255,255,255,0.18), transparent)"></div>
    </div>

    {{-- Profile row --}}
    <div class="px-6 pb-6 flex flex-wrap items-end gap-5 relative" style="margin-top: -42px">

        {{-- Avatar initials --}}
        <div class="w-[84px] h-[84px] flex-none flex items-center justify-center rounded-[18px] bg-teal-600 border-4 border-white dark:border-neutral-800 shadow font-display font-bold text-[28px] leading-none text-white" style="z-index:1">
            {{ $initials }}
        </div>

        {{-- Name + meta --}}
        <div class="flex-1 min-w-0 pb-1">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="font-display font-bold text-2xl leading-none text-ink dark:text-ink-d">{{ $member->name }}</h1>
                @if($isCheckedIn)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-700 dark:bg-success-600/20 dark:text-success-400">
                        <i class="w-1.5 h-1.5 rounded-full bg-success-500 flex-none"></i>Sudah Check-in
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400">
                        <i class="w-1.5 h-1.5 rounded-full bg-neutral-400 flex-none"></i>Belum Check-in
                    </span>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2 text-sm text-ink-2 dark:text-ink-d2">
                <span class="inline-flex items-center gap-1.5">
                    <iconify-icon icon="mage:email" class="text-neutral-400 text-base flex-none"></iconify-icon>
                    {{ $member->email ?? '-' }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <iconify-icon icon="lucide:phone" class="text-neutral-400 text-base flex-none"></iconify-icon>
                    {{ $member->no_telp }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <iconify-icon icon="lucide:credit-card" class="text-neutral-400 text-base flex-none"></iconify-icon>
                    <span class="tabular-nums">{{ $member->id_kartu }}</span>
                </span>
            </div>
        </div>

        {{-- Stat counters --}}
        <div class="flex items-center gap-5 pb-1 flex-none">
            <div class="text-center">
                <div class="font-display font-bold text-[22px] leading-none tabular-nums text-ink dark:text-ink-d">{{ $totalPaket }}</div>
                <div class="text-[11px] text-ink-3 dark:text-ink-d3 mt-1.5">Total Paket</div>
            </div>
            <div class="w-px h-8 bg-neutral-200 dark:bg-neutral-700 flex-none"></div>
            <div class="text-center">
                <div class="font-display font-bold text-[22px] leading-none tabular-nums text-ink dark:text-ink-d">{{ $totalSesiAktif }}</div>
                <div class="text-[11px] text-ink-3 dark:text-ink-d3 mt-1.5">Sesi Aktif</div>
            </div>
            <div class="w-px h-8 bg-neutral-200 dark:bg-neutral-700 flex-none"></div>
            <div class="text-center">
                <div class="font-display font-bold text-[22px] leading-none tabular-nums text-ink dark:text-ink-d">{{ $totalSesiSelesai }}</div>
                <div class="text-[11px] text-ink-3 dark:text-ink-d3 mt-1.5">Sesi Selesai</div>
            </div>
            <div class="w-px h-8 bg-neutral-200 dark:bg-neutral-700 flex-none"></div>
            <div class="text-center">
                <div class="font-display font-bold text-[22px] leading-none tabular-nums text-ink dark:text-ink-d">{{ $totalSesiKadaluarsa }}</div>
                <div class="text-[11px] text-ink-3 dark:text-ink-d3 mt-1.5">Sesi Kadaluarsa</div>
            </div>
        </div>

    </div>
</div>

{{-- ==================== MAIN (LEFT + RIGHT) ==================== --}}
<div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-5 mt-5 items-start">

    {{-- ─── LEFT PANEL ─────────────────────────────── --}}
    <div class="flex flex-col gap-5">

        {{-- Status Hari Ini --}}
        <div class="card overflow-hidden border border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center gap-3 px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
                <div class="w-1.5 h-5 rounded-full bg-primary-500 flex-none"></div>
                <h3 class="font-display font-semibold text-lg text-ink dark:text-ink-d">Status Hari Ini</h3>
            </div>
            <div class="px-5 py-5 flex flex-col gap-5">

                {{-- Check-in --}}
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3 mb-2">Kehadiran</div>
                    @if($isCheckedIn)
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-success-50 dark:bg-success-600/10 border border-success-200 dark:border-success-600/30">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center bg-success-100 dark:bg-success-600/20 flex-none">
                                <iconify-icon icon="lucide:check-circle" class="text-success-600 dark:text-success-400 text-xl"></iconify-icon>
                            </div>
                            <div>
                                <div class="font-semibold text-sm text-success-700 dark:text-success-400">Sudah Check-in</div>
                                <div class="text-xs text-success-600/80 dark:text-success-500 mt-0.5">Member hadir hari ini</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-neutral-50 dark:bg-neutral-700/30 border border-neutral-200 dark:border-neutral-700">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center bg-neutral-100 dark:bg-neutral-700 flex-none">
                                <iconify-icon icon="lucide:clock" class="text-neutral-400 text-xl"></iconify-icon>
                            </div>
                            <div>
                                <div class="font-semibold text-sm text-ink dark:text-ink-d">Belum Check-in</div>
                                <div class="text-xs text-ink-3 dark:text-ink-d3 mt-0.5">Member belum hadir hari ini</div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sesi PT aktif --}}
                @if($activeSession)
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3 mb-2">Sesi PT Aktif</div>
                    <div class="px-4 py-3 rounded-xl bg-warning-50 dark:bg-warning-600/10 border border-warning-200 dark:border-warning-600/30">
                        <div class="flex items-center gap-2 mb-1.5">
                            <iconify-icon icon="lucide:dumbbell" class="text-warning-600 dark:text-warning-400 text-base flex-none"></iconify-icon>
                            <span class="font-semibold text-sm text-warning-700 dark:text-warning-400">Sedang Training</span>
                        </div>
                        <div class="text-xs text-warning-600/80 dark:text-warning-500">
                            {{ $activeSession->paketPersonalTrainer->nama_paket ?? '-' }}
                        </div>
                        <div class="text-xs text-warning-600/80 dark:text-warning-500 mt-0.5 tabular-nums">
                            {{ $activeSession->sesi }} sesi tersisa
                        </div>
                    </div>
                </div>
                @endif

                {{-- Trainer --}}
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3 mb-2">Trainer</div>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center bg-primary-50 dark:bg-primary-600/10 border border-primary-200 dark:border-primary-600/30 flex-none">
                            <iconify-icon icon="lucide:user" class="text-primary-600 dark:text-primary-400 text-sm"></iconify-icon>
                        </div>
                        <span class="text-sm font-semibold text-ink dark:text-ink-d">{{ $trainer->name ?? '-' }}</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Data Anggota --}}
        <div class="card overflow-hidden border border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center gap-3 px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
                <div class="w-1.5 h-5 rounded-full bg-violet-500 flex-none"></div>
                <h3 class="font-display font-semibold text-lg text-ink dark:text-ink-d">Data Anggota</h3>
            </div>
            <div class="px-5 py-5 flex flex-col gap-4 text-sm">

                @if($member->jenis_kelamin)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Jenis Kelamin</div>
                    <div class="font-semibold text-ink dark:text-ink-d mt-1">{{ $member->jenis_kelamin }}</div>
                </div>
                @endif

                @if($member->tgl_lahir)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Tanggal Lahir</div>
                    <div class="font-semibold text-ink dark:text-ink-d mt-1 tabular-nums">
                        {{ $member->tgl_lahir->format('d M Y') }}
                        @if($member->age)<span class="font-normal text-ink-3 dark:text-ink-d3"> · {{ $member->age }} th</span>@endif
                    </div>
                </div>
                @endif

                @if($member->tempat_lahir)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Tempat Lahir</div>
                    <div class="font-semibold text-ink dark:text-ink-d mt-1">{{ $member->tempat_lahir }}</div>
                </div>
                @endif

                @if($member->alamat)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Alamat</div>
                    <div class="font-semibold text-ink dark:text-ink-d mt-1 leading-relaxed">{{ $member->alamat }}</div>
                </div>
                @endif

                @if($member->tgl_daftar)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Tanggal Daftar</div>
                    <div class="font-semibold text-ink dark:text-ink-d mt-1 tabular-nums">{{ $member->tgl_daftar->format('d M Y') }}</div>
                </div>
                @endif

                @if($member->tinggi || $member->berat)
                <div class="grid grid-cols-3 gap-3 pt-2 border-t border-neutral-200 dark:border-neutral-700">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Tinggi</div>
                        <div class="font-semibold text-ink dark:text-ink-d mt-1">{{ $member->tinggi ? $member->tinggi . ' cm' : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Berat</div>
                        <div class="font-semibold text-ink dark:text-ink-d mt-1">{{ $member->berat ? $member->berat . ' kg' : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">BMI</div>
                        <div class="font-semibold text-ink dark:text-ink-d mt-1">{{ $member->bmi ?? '—' }}</div>
                    </div>
                </div>
                @endif

                @if($member->gol_darah)
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-ink-3 dark:text-ink-d3">Gol. Darah</div>
                    <div class="font-semibold text-ink dark:text-ink-d mt-1">{{ $member->gol_darah }}</div>
                </div>
                @endif

            </div>
        </div>

    </div>

    {{-- ─── RIGHT PANEL: tabbed datatables ─────────── --}}
    <div class="card border-0 overflow-hidden">

        {{-- Tab buttons --}}
        <div class="flex items-center gap-1 px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
            <button onclick="mdTab('aktif')" id="tab-btn-aktif"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-colors bg-primary-50 dark:bg-primary-600/15 text-primary-600 dark:text-primary-400">
                Paket Aktif
                <span id="badge-aktif" class="text-xs font-bold px-1.5 py-0.5 rounded-full tabular-nums bg-primary-100 dark:bg-primary-600/20 text-primary-700 dark:text-primary-300">{{ $activePackages->count() }}</span>
            </button>
            <button onclick="mdTab('riwayat')" id="tab-btn-riwayat"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-colors text-ink-2 dark:text-ink-d2 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                Riwayat Semua Paket
                <span id="badge-riwayat" class="text-xs font-bold px-1.5 py-0.5 rounded-full tabular-nums bg-neutral-100 dark:bg-neutral-700 text-ink-3 dark:text-ink-d3">{{ $memberTrainers->count() }}</span>
            </button>
        </div>

        {{-- Panel: Paket Aktif --}}
        <div id="tab-panel-aktif">
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

        {{-- Panel: Riwayat Paket --}}
        <div id="tab-panel-riwayat" style="display:none">
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

<script>
(function () {
    var BTN_ACTIVE   = 'inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-colors bg-primary-50 dark:bg-primary-600/15 text-primary-600 dark:text-primary-400';
    var BTN_INACTIVE = 'inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-colors text-ink-2 dark:text-ink-d2 hover:bg-neutral-100 dark:hover:bg-neutral-700';
    var BADGE_ACTIVE   = 'text-xs font-bold px-1.5 py-0.5 rounded-full tabular-nums bg-primary-100 dark:bg-primary-600/20 text-primary-700 dark:text-primary-300';
    var BADGE_INACTIVE = 'text-xs font-bold px-1.5 py-0.5 rounded-full tabular-nums bg-neutral-100 dark:bg-neutral-700 text-ink-3 dark:text-ink-d3';

    window.mdTab = function (t) {
        ['aktif', 'riwayat'].forEach(function (id) {
            var isActive = id === t;
            document.getElementById('tab-panel-' + id).style.display = isActive ? '' : 'none';
            document.getElementById('tab-btn-' + id).className        = isActive ? BTN_ACTIVE : BTN_INACTIVE;
            document.getElementById('badge-' + id).className          = isActive ? BADGE_ACTIVE : BADGE_INACTIVE;
        });
    };
}());
</script>

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
