@extends('layout.layout')
@php
    $title = 'Riwayat Sesi';
    $subTitle = 'Riwayat Sesi Training';
@endphp

@section('content')

    <x-page-table title="Riwayat Sesi Training" subtitle="Trainer: {{ $trainer->name }}">
        <x-slot:actions>
            <button type="button" onclick="HexaModal.show('export-pdf-modal')"
                class="btn btn-danger btn-sm inline-flex items-center gap-1.5">
                <iconify-icon icon="lucide:file-down" class="text-base"></iconify-icon>
                Export Laporan
            </button>
            <a href="{{ route('trainer.dashboard') }}"
                class="btn btn-secondary btn-sm inline-flex items-center gap-1.5">
                <iconify-icon icon="lucide:arrow-left" class="text-base"></iconify-icon>
                Kembali
            </a>
        </x-slot:actions>

        <x-data-table tableId="sessionLogs" :colspan="6" placeholder="Cari keterangan sesi...">
            <x-slot:header>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Tanggal &amp; Waktu</th>
                    <th scope="col">Tipe</th>
                    <th scope="col">Sesi</th>
                    <th scope="col">Total Sesi</th>
                    <th scope="col">Keterangan</th>
                </tr>
            </x-slot:header>
        </x-data-table>

    </x-page-table>

    <x-modal id="export-pdf-modal" title="Filter Export Laporan">
        <x-slot:body>
            <form action="{{ route('trainer.session.logs.export_pdf') }}" method="POST" id="export-pdf-form">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Pilih Tipe Filter Tanggal -->
                        <div class="col-span-12">
                            <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Filter Tanggal
                                (Berdasarkan tanggal sesi):</label>
                            <div class="space-y-2">
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_all" name="filter_type" value="all"
                                        class="w-4 h-4 text-primary-600" checked>
                                    <label for="filter_all" class="ml-2 text-sm font-medium text-gray-900">Semua
                                        Tanggal</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_single" name="filter_type" value="single"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_single" class="ml-2 text-sm font-medium text-gray-900">Bulan &
                                        Tahun Tertentu</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_range" name="filter_type" value="range"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_range" class="ml-2 text-sm font-medium text-gray-900">Range
                                        Bulan</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_daily" name="filter_type" value="daily"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_daily" class="ml-2 text-sm font-medium text-gray-900">Range
                                        Harian</label>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Single Month -->
                        <div id="single-filter" class="hidden">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="bulan"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Bulan:</label>
                                    <select id="bulan" name="bulan" class="form-control rounded-lg">
                                        <option value="">Pilih Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="tahun"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tahun:</label>
                                    <input type="number" id="tahun" name="tahun" class="form-control rounded-lg"
                                        placeholder="2024" min="2000" max="2100">
                                </div>
                            </div>
                        </div>

                        <!-- Filter Range Bulan -->
                        <div id="range-filter" class="hidden">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="bulan_dari"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Dari
                                            Bulan:</label>
                                        <select id="bulan_dari" name="bulan_dari" class="form-control rounded-lg">
                                            <option value="">Pilih Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tahun_dari"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tahun:</label>
                                        <input type="number" id="tahun_dari" name="tahun_dari"
                                            class="form-control rounded-lg" placeholder="2024" min="2000"
                                            max="2100">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="bulan_sampai"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Sampai
                                            Bulan:</label>
                                        <select id="bulan_sampai" name="bulan_sampai" class="form-control rounded-lg">
                                            <option value="">Pilih Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tahun_sampai"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tahun:</label>
                                        <input type="number" id="tahun_sampai" name="tahun_sampai"
                                            class="form-control rounded-lg" placeholder="2024" min="2000"
                                            max="2100">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Range Harian -->
                        <div id="daily-filter" class="hidden">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="tgl_dari"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Dari
                                        Tanggal:</label>
                                    <input type="date" id="tgl_dari" name="tgl_dari"
                                        class="form-control rounded-lg">
                                </div>
                                <div>
                                    <label for="tgl_sampai"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Sampai
                                        Tanggal:</label>
                                    <input type="date" id="tgl_sampai" name="tgl_sampai"
                                        class="form-control rounded-lg">
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-span-12">
                            <div class="flex items-center justify-start gap-3 mt-6">
                                <button type="button" data-close-modal="export-pdf-modal"
                                    class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                    <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                                    Export PDF
                                </button>
                                <button type="submit" formaction="{{ route('trainer.session.logs.export_excel') }}"
                                    class="bg-success-600 hover:bg-success-700 text-white text-base px-6 py-3 rounded-lg inline-flex items-center">
                                    <iconify-icon icon="carbon:document-export" class="mr-2"></iconify-icon>
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
        </x-slot:body>
    </x-modal>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    AjaxTable.init('sessionLogs', {
        url: '{{ route('trainer.session.logs.datatable') }}',
        colSpan: 6,
        renderRow: function (item) {
            var typeBadge = item.type === 'in'
                ? '<span style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:rgba(34,197,94,.13);color:#15803D"><i style="width:6px;height:6px;border-radius:50%;background:#22C55E;flex:none"></i>Masuk</span>'
                : '<span style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:rgba(239,68,68,.13);color:#B91C1C"><i style="width:6px;height:6px;border-radius:50%;background:#EF4444;flex:none"></i>Selesai</span>';

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td class="whitespace-nowrap tabular-nums">' + item.created_at + '</td>'
                + '<td class="whitespace-nowrap">' + typeBadge + '</td>'
                + '<td class="whitespace-nowrap tabular-nums">' + item.sesi + '</td>'
                + '<td class="whitespace-nowrap tabular-nums">' + item.current_sesi + '</td>'
                + '<td><span class="cell-ellipsis" title="' + htmlEsc(item.description) + '">' + item.description + '</span></td>'
                + '</tr>';
        }
    });

    // Export filter modal
    const filterRadios = document.querySelectorAll('input[name="filter_type"]');
    const singleFilter = document.getElementById('single-filter');
    const rangeFilter  = document.getElementById('range-filter');
    const dailyFilter  = document.getElementById('daily-filter');

    filterRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            singleFilter.classList.add('hidden');
            rangeFilter.classList.add('hidden');
            dailyFilter.classList.add('hidden');

            if (this.value === 'single') {
                singleFilter.classList.remove('hidden');
            } else if (this.value === 'range') {
                rangeFilter.classList.remove('hidden');
            } else if (this.value === 'daily') {
                dailyFilter.classList.remove('hidden');
            }
        });
    });

    document.getElementById('export-pdf-form').addEventListener('submit', function(e) {
        const filterType = document.querySelector('input[name="filter_type"]:checked').value;

        if (filterType === 'single') {
            const bulan = document.getElementById('bulan').value;
            const tahun = document.getElementById('tahun').value;
            if (!bulan || !tahun) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon pilih bulan dan tahun terlebih dahulu!' });
                return false;
            }
        } else if (filterType === 'range') {
            const bulanDari   = document.getElementById('bulan_dari').value;
            const tahunDari   = document.getElementById('tahun_dari').value;
            const bulanSampai = document.getElementById('bulan_sampai').value;
            const tahunSampai = document.getElementById('tahun_sampai').value;
            if (!bulanDari || !tahunDari || !bulanSampai || !tahunSampai) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon lengkapi range bulan dan tahun!' });
                return false;
            }
        } else if (filterType === 'daily') {
            const tglDari   = document.getElementById('tgl_dari').value;
            const tglSampai = document.getElementById('tgl_sampai').value;
            if (!tglDari || !tglSampai) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon lengkapi tanggal dari dan sampai!' });
                return false;
            }
            if (tglDari > tglSampai) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Tanggal "dari" tidak boleh lebih besar dari tanggal "sampai"!' });
                return false;
            }
        }
    });
});
</script>
@endsection
