@extends('layout.layout')
@php
    $title         = 'Penjualan';
    $subTitle      = 'Riwayat Penjualan';
    $isLaporanMode = request()->routeIs('laporan.penjualan');
@endphp

@section('content')
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('danger'))
        <x-alert type="danger">{{ session('danger') }}</x-alert>
    @endif

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">
                        {{ $isLaporanMode ? 'Laporan Penjualan Produk' : 'Penjualan Produk' }}
                    </h6>
                    <div class="flex gap-2">
                        <button type="button" data-modal-target="export-pdf-modal" data-modal-toggle="export-pdf-modal"
                            title="Maks. 300 transaksi. Gunakan Export Excel untuk data lebih banyak."
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                            <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                            Export PDF <span class="ml-1 text-xs opacity-75">(maks. 300)</span>
                        </button>
                        <button type="button" data-modal-target="export-csv-modal" data-modal-toggle="export-csv-modal"
                            class="text-white bg-success-600 hover:bg-success-700 focus:ring-4 focus:outline-none focus:ring-success-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                            <iconify-icon icon="carbon:document-export" class="mr-2"></iconify-icon>
                            Export Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <x-data-table
                        tableId="riwayatKasir"
                        :colspan="12"
                        placeholder="Cari kode, nama pelanggan, metode pembayaran...">
                        <x-slot:header>
                            <tr>
                                <th>No</th>
                                <th>Kode Transaksi</th>
                                <th>Nama Pelanggan</th>
                                <th>Tanggal Transaksi</th>
                                <th>Harga Total</th>
                                <th>Dibayarkan</th>
                                <th>Kembalian</th>
                                <th>Metode Pembayaran</th>
                                <th>Harga Sebelum Diskon</th>
                                <th>Harga Diskon / Barang</th>
                                <th>Harga Diskon Manual</th>
                                <th>Total HPP</th>
                            </tr>
                        </x-slot:header>
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Export PDF -->
    <div id="export-pdf-modal" tabindex="-1"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="rounded-2xl bg-white max-w-[600px] w-full">
            <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                <h1 class="text-xl font-semibold">Filter Export PDF</h1>
                <button data-modal-hide="export-pdf-modal" type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="p-6">
                <form action="{{ route('kasir.export_pdf') }}" method="POST" id="export-pdf-form">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        <div class="col-span-12">
                            <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Filter Periode
                                Transaksi:</label>
                            <div class="space-y-2">
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_all" name="filter_type" value="all"
                                        class="w-4 h-4 text-primary-600" checked>
                                    <label for="filter_all" class="ml-2 text-sm font-medium text-gray-900">Semua
                                        Tanggal</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_range" name="filter_type" value="range"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_range" class="ml-2 text-sm font-medium text-gray-900">Range
                                        Tanggal</label>
                                </div>
                            </div>
                        </div>

                        <div id="range-filter" class="hidden">
                            <div class="space-y-4">
                                <div>
                                    <label for="tanggal_mulai"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tanggal
                                        Mulai:</label>
                                    <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                                        class="form-control rounded-lg w-full">
                                </div>
                                <div>
                                    <label for="tanggal_selesai"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tanggal
                                        Selesai:</label>
                                    <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                                        class="form-control rounded-lg w-full">
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12">
                            <div class="flex items-center justify-start gap-3 mt-6">
                                <button type="button" data-modal-hide="export-pdf-modal"
                                    class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                    <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                                    Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Export Excel/CSV -->
    <div id="export-csv-modal" tabindex="-1"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="rounded-2xl bg-white max-w-[600px] w-full">
            <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                <h1 class="text-xl font-semibold">Filter Export Excel</h1>
                <button data-modal-hide="export-csv-modal" type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form action="{{ route('kasir.export_csv') }}" method="POST" id="export-csv-form">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        <div class="col-span-12">
                            <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Filter Periode Transaksi:</label>
                            <div class="space-y-2">
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="csv_filter_all" name="filter_type" value="all"
                                        class="w-4 h-4 text-primary-600" checked>
                                    <label for="csv_filter_all" class="ml-2 text-sm font-medium text-gray-900">Semua Tanggal</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="csv_filter_range" name="filter_type" value="range"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="csv_filter_range" class="ml-2 text-sm font-medium text-gray-900">Range Tanggal</label>
                                </div>
                            </div>
                        </div>
                        <div id="csv-range-filter" class="col-span-12 hidden">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tanggal Mulai:</label>
                                    <input type="date" id="csv_tanggal_mulai" name="tanggal_mulai" class="form-control rounded-lg w-full">
                                </div>
                                <div>
                                    <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tanggal Selesai:</label>
                                    <input type="date" id="csv_tanggal_selesai" name="tanggal_selesai" class="form-control rounded-lg w-full">
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12">
                            <div class="flex items-center justify-start gap-3 mt-6">
                                <button type="button" data-modal-hide="export-csv-modal"
                                    class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Map transaction ID -> items (populated per renderRow, used by view-detail handler)
    const txItems = {};

    AjaxTable.init('riwayatKasir', {
        url: '{{ route('kasir.riwayat.datatable') }}',
        colSpan: 12,
        renderRow: function (item) {
            txItems[item.id] = item.items_json;
            return `<tr>
                <td class="whitespace-nowrap">${item.no}</td>
                <td class="whitespace-nowrap">
                    <a class="text-primary-600 cursor-pointer btn-view-detail" data-tx-id="${item.id}">
                        ${item.kode_transaksi}
                    </a>
                </td>
                <td class="whitespace-nowrap">${item.customer_name}</td>
                <td class="whitespace-nowrap">${item.tanggal}</td>
                <td class="whitespace-nowrap">${item.total_amount}</td>
                <td class="whitespace-nowrap">${item.dibayarkan}</td>
                <td class="whitespace-nowrap">${item.kembalian}</td>
                <td class="whitespace-nowrap">${item.metode_pembayaran}</td>
                <td class="whitespace-nowrap">${item.harga_sebelum_diskon}</td>
                <td class="whitespace-nowrap">${item.diskon_barang}</td>
                <td class="whitespace-nowrap">${item.diskon}</td>
                <td class="whitespace-nowrap">${item.total_hpp}</td>
            </tr>`;
        }
    });

    // Event delegation untuk btn-view-detail (rows dinamis dari ajax)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-view-detail');
        if (!btn) return;

        const items = txItems[btn.dataset.txId] || [];

        let html = `
            <div class="card rounded-lg border-0 overflow-hidden">
                <div class="card-header">
                    <h5 class="card-title text-lg mb-0">Detail Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table basic-border-table mb-0">
                            <thead>
                                <tr>
                                    <th class="border-r border-neutral-200 last:border-r-0">No</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">Nama Produk</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">Keterangan</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">Qty</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">Harga</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">HPP</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">Diskon / Barang</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">Subtotal</th>
                                    <th class="border-r border-neutral-200 last:border-r-0">Total HPP</th>
                                </tr>
                            </thead>
                            <tbody>`;

        if (items.length > 0) {
            items.forEach((it, index) => {
                const subtotal    = (it.qty * it.price) - (it.diskon * it.qty);
                const totalHPPItem = (it.hpp || 0) * it.qty;
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${it.product_name}</td>
                        <td>${it.keterangan ?? '-'}</td>
                        <td>${it.qty}</td>
                        <td>Rp ${parseFloat(it.price).toLocaleString('id-ID')}</td>
                        <td>Rp ${parseFloat(it.hpp || 0).toLocaleString('id-ID')}</td>
                        <td>Rp ${parseFloat(it.diskon ?? 0).toLocaleString('id-ID')}</td>
                        <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td>Rp ${totalHPPItem.toLocaleString('id-ID')}</td>
                    </tr>`;
            });
        } else {
            html += `<tr><td colspan="8" class="text-center py-3">Tidak ada item dalam transaksi ini.</td></tr>`;
        }

        html += `</tbody></table></div></div></div>`;

        Swal.fire({
            html: html,
            showConfirmButton: true,
            confirmButtonText: 'Tutup',
            width: '900px',
        });
    });

    // PDF modal — toggle range filter
    const filterRadios = document.querySelectorAll('#export-pdf-modal input[name="filter_type"]');
    const rangeFilter  = document.getElementById('range-filter');
    filterRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            rangeFilter.classList.toggle('hidden', this.value !== 'range');
        });
    });

    // PDF form validation
    document.getElementById('export-pdf-form').addEventListener('submit', function (e) {
        const filterType = document.querySelector('#export-pdf-modal input[name="filter_type"]:checked').value;
        if (filterType === 'range') {
            const tanggalMulai   = document.getElementById('tanggal_mulai').value;
            const tanggalSelesai = document.getElementById('tanggal_selesai').value;
            if (!tanggalMulai || !tanggalSelesai) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon pilih tanggal mulai dan tanggal selesai terlebih dahulu!' });
                return false;
            }
            if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Tanggal selesai harus lebih besar atau sama dengan tanggal mulai!' });
                return false;
            }
        }
    });

    // Excel modal — toggle range filter
    const csvRadios      = document.querySelectorAll('#export-csv-modal input[name="filter_type"]');
    const csvRangeFilter = document.getElementById('csv-range-filter');
    csvRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            csvRangeFilter.classList.toggle('hidden', this.value !== 'range');
        });
    });

    // Excel form validation
    document.getElementById('export-csv-form').addEventListener('submit', function (e) {
        const filterType = document.querySelector('#export-csv-modal input[name="filter_type"]:checked').value;
        if (filterType === 'range') {
            const mulai   = document.getElementById('csv_tanggal_mulai').value;
            const selesai = document.getElementById('csv_tanggal_selesai').value;
            if (!mulai || !selesai) {
                e.preventDefault();
                alert('Tanggal mulai dan selesai wajib diisi.');
                return false;
            }
        }
    });
});
</script>
@endsection
