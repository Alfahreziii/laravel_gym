@extends('layout.layout')
@php
    $title = 'Transaksi Keuangan';
    $subTitle = 'Detail Transaksi Keuangan';
@endphp

@section('content')
    <div class="grid grid-cols-12 gap-5 mb-5">

        {{-- Card Total Debit --}}
        <div class="col-span-12 md:col-span-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-success-100 flex items-center justify-center flex-shrink-0">
                        <iconify-icon icon="mingcute:arrow-up-fill" class="text-success-600 text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500 mb-1">Total Debit</p>
                        <h6 class="text-lg font-bold text-success-600" id="summaryDebit">Rp 0</h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Total Kredit --}}
        <div class="col-span-12 md:col-span-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-danger-100 flex items-center justify-center flex-shrink-0">
                        <iconify-icon icon="mingcute:arrow-down-fill" class="text-danger-600 text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500 mb-1">Total Kredit</p>
                        <h6 class="text-lg font-bold text-danger-600" id="summaryKredit">Rp 0</h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Selisih --}}
        <div class="col-span-12 md:col-span-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                        <iconify-icon icon="mingcute:balance-fill" class="text-primary-600 text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500 mb-1">Selisih (Debit - Kredit)</p>
                        <h6 class="text-lg font-bold text-primary-600" id="summarySelisih">Rp 0</h6>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0">
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <h6 class="text-lg font-semibold mb-0">Detail Transaksi Keuangan</h6>
            <button id="btnExportPdf" class="btn btn-danger-600 flex items-center gap-2 text-sm px-4 py-2">
                <iconify-icon icon="mingcute:pdf-line"></iconify-icon> Export PDF
            </button>
        </div>

        <div class="card-body">

            {{-- Filter --}}
            <div class="grid grid-cols-12 gap-3 mb-4">

                {{-- Tanggal Mulai --}}
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label text-xs text-neutral-500">Tanggal Mulai</label>
                    <input type="date" id="filterTglMulai" class="form-control form-control-sm">
                </div>

                {{-- Tanggal Selesai --}}
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label text-xs text-neutral-500">Tanggal Selesai</label>
                    <input type="date" id="filterTglSelesai" class="form-control form-control-sm">
                </div>

                {{-- Filter Akun --}}
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label text-xs text-neutral-500">Akun</label>
                    <select id="filterAkun" class="form-control form-control-sm">
                        <option value="">-- Semua Akun --</option>
                        @foreach ($akuns as $akun)
                            <option value="{{ $akun->id }}">
                                {{ $akun->kode }} - {{ $akun->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Referensi --}}
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label text-xs text-neutral-500">Sumber Transaksi</label>
                    <select id="filterReferensi" class="form-control form-control-sm">
                        <option value="">-- Semua Sumber --</option>
                        <option value="anggota_memberships">Membership</option>
                        <option value="products">Produk</option>
                        <option value="transaksi_spas">Spa</option>
                        <option value="personal_trainers">Personal Trainer</option>
                    </select>
                </div>

                {{-- Search + Per Page --}}
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label text-xs text-neutral-500">Cari Deskripsi / Akun</label>
                    <input type="text" id="searchTransaksi" placeholder="Ketik untuk mencari..."
                        class="form-control form-control-sm">
                </div>

                <div class="col-span-12 md:col-span-3">
                    <label class="form-label text-xs text-neutral-500">Tampilkan</label>
                    <select id="perPageTransaksi" class="form-control form-control-sm">
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-span-12 md:col-span-3 flex items-end">
                    <button id="btnResetFilter"
                        class="w-full border border-neutral-300 text-neutral-600 hover:bg-neutral-100 rounded-lg text-sm px-4 py-2">
                        Reset Filter
                    </button>
                </div>

            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="ajax-table border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">No</th>
                            <th class="whitespace-nowrap">Tanggal</th>
                            <th class="whitespace-nowrap">Kode Akun</th>
                            <th class="whitespace-nowrap">Nama Akun</th>
                            <th class="whitespace-nowrap">Kategori</th>
                            <th class="whitespace-nowrap">Deskripsi</th>
                            <th class="whitespace-nowrap">Sumber</th>
                            <th class="whitespace-nowrap text-success-600">Debit (Rp)</th>
                            <th class="whitespace-nowrap text-danger-600">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTransaksi">
                        <tr>
                            <td colspan="9" class="text-center py-8">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex justify-between items-center mt-4 flex-wrap gap-2">
                <span class="text-sm text-gray-500" id="infoTransaksi"></span>
                <div id="paginationTransaksi" class="flex gap-1 flex-wrap"></div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function formatRp(n) {
                return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
            }

            // Label sumber transaksi
            var referensiLabel = {
                'anggota_memberships': 'Membership',
                'products': 'Produk',
                'transaksi_spas': 'Spa',
                'personal_trainers': 'Personal Trainer',
            };

            // Badge warna per kategori akun
            function kategoriBadge(nama) {
                var map = {
                    'Aset': 'bg-primary-100 text-primary-700',
                    'Kewajiban': 'bg-warning-100 text-warning-700',
                    'Modal': 'bg-success-100 text-success-700',
                    'Beban': 'bg-danger-100 text-danger-700',
                };
                var cls = map[nama] || 'bg-neutral-100 text-neutral-600';
                return '<span class="px-2 py-0.5 rounded-full text-xs font-medium ' + cls + '">' + nama + '</span>';
            }

            // ── AjaxTable ─────────────────────────────────────────────
            var table = AjaxTable.create({
                url: '{{ route('keuangan.transaksi.datatable') }}',
                tbodyId: 'tbodyTransaksi',
                paginationId: 'paginationTransaksi',
                infoId: 'infoTransaksi',
                searchId: 'searchTransaksi',
                perPage: 15,
                colSpan: 9,
                extraParams: function() {
                    return {
                        akun_id: document.getElementById('filterAkun').value,
                        referensi: document.getElementById('filterReferensi').value,
                        tgl_mulai: document.getElementById('filterTglMulai').value,
                        tgl_selesai: document.getElementById('filterTglSelesai').value,
                    };
                },
                renderRow: function(item) {
                    var sumberLabel = referensiLabel[item.referensi_tabel] || item.referensi_tabel;
                    var sumberBadge = item.referensi_tabel ?
                        '<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-600">' +
                        sumberLabel + ' #' + item.referensi_id + '</span>' :
                        '-';

                    var debitCell = item.debit > 0 ?
                        '<span class="font-semibold text-success-600">' + Number(item.debit)
                        .toLocaleString('id-ID') + '</span>' :
                        '<span class="text-neutral-300">-</span>';
                    var kreditCell = item.kredit > 0 ?
                        '<span class="font-semibold text-danger-600">' + Number(item.kredit)
                        .toLocaleString('id-ID') + '</span>' :
                        '<span class="text-neutral-300">-</span>';

                    return '<tr>' +
                        '<td class="whitespace-nowrap text-center">' + item.no + '</td>' +
                        '<td class="whitespace-nowrap">' + item.tanggal + '</td>' +
                        '<td class="whitespace-nowrap"><span class="font-mono text-xs bg-neutral-100 px-2 py-0.5 rounded">' +
                        item.kode_akun + '</span></td>' +
                        '<td class="whitespace-nowrap">' + item.nama_akun + '</td>' +
                        '<td class="whitespace-nowrap">' + kategoriBadge(item.kategori_akun) + '</td>' +
                        '<td class="max-w-xs truncate" title="' + item.deskripsi + '">' + item
                        .deskripsi + '</td>' +
                        '<td class="whitespace-nowrap">' + sumberBadge + '</td>' +
                        '<td class="whitespace-nowrap text-right">' + debitCell + '</td>' +
                        '<td class="whitespace-nowrap text-right">' + kreditCell + '</td>' +
                        '</tr>';
                }
            });

            // ── Fetch Summary (debit/kredit/selisih) ─────────────────
            function fetchSummary() {
                var params = new URLSearchParams({
                    akun_id: document.getElementById('filterAkun').value,
                    referensi: document.getElementById('filterReferensi').value,
                    tgl_mulai: document.getElementById('filterTglMulai').value,
                    tgl_selesai: document.getElementById('filterTglSelesai').value,
                    search: document.getElementById('searchTransaksi').value,
                    perPage: 99999,
                    page: 1,
                });

                fetch('{{ route('keuangan.transaksi.datatable') }}?' + params.toString())
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(json) {
                        var debit = parseFloat(json.total_debit) || 0;
                        var kredit = parseFloat(json.total_kredit) || 0;
                        var selisih = debit - kredit;

                        document.getElementById('summaryDebit').innerText = formatRp(debit);
                        document.getElementById('summaryKredit').innerText = formatRp(kredit);
                        document.getElementById('summarySelisih').innerText = formatRp(Math.abs(selisih));
                        document.getElementById('summarySelisih').className =
                            'text-lg font-bold ' + (selisih >= 0 ? 'text-primary-600' : 'text-danger-600');
                    })
                    .catch(function() {});
            }

            // Panggil saat halaman pertama load
            fetchSummary();

            // ── Filter listeners ──────────────────────────────────────
            ['filterAkun', 'filterReferensi', 'filterTglMulai', 'filterTglSelesai'].forEach(function(id) {
                document.getElementById(id).addEventListener('change', function() {
                    fetchSummary();
                    if (window._ajaxTables && window._ajaxTables['tbodyTransaksi']) {
                        window._ajaxTables['tbodyTransaksi'].reload();
                    }
                });
            });

            document.getElementById('perPageTransaksi').addEventListener('change', function() {
                if (window._ajaxTables && window._ajaxTables['tbodyTransaksi']) {
                    window._ajaxTables['tbodyTransaksi'].setPerPage(parseInt(this.value));
                }
            });

            // ── Reset filter ──────────────────────────────────────────
            document.getElementById('btnResetFilter').addEventListener('click', function() {
                document.getElementById('filterAkun').value = '';
                document.getElementById('filterReferensi').value = '';
                document.getElementById('filterTglMulai').value = '';
                document.getElementById('filterTglSelesai').value = '';
                document.getElementById('searchTransaksi').value = '';
                fetchSummary();
                if (window._ajaxTables && window._ajaxTables['tbodyTransaksi']) {
                    window._ajaxTables['tbodyTransaksi'].reload();
                }
            });

            // ── Export PDF ────────────────────────────────────────────
            document.getElementById('btnExportPdf').addEventListener('click', function() {
                var params = new URLSearchParams({
                    akun_id: document.getElementById('filterAkun').value,
                    referensi: document.getElementById('filterReferensi').value,
                    tgl_mulai: document.getElementById('filterTglMulai').value,
                    tgl_selesai: document.getElementById('filterTglSelesai').value,
                });
                window.open('{{ route('keuangan.transaksi.exportPdf') }}?' + params.toString(), '_blank');
            });

        });
    </script>
@endsection
