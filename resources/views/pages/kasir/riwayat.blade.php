@extends('layout.layout')
@php
    $title = 'Penjualan';
    $subTitle = 'Riwayat Penjualan';
    $script = '<script src="' . asset('assets/js/data-table.js') . '"></script>';
    $isLaporanMode = request()->routeIs('laporan.penjualan');
@endphp

@section('content')

@if(session('success'))
    <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{ session('success') }}
        </div>
        <button class="remove-button text-success-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

@if(session('danger'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('danger') }}
        <button class="remove-button text-danger-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">{{ $isLaporanMode ? 'Laporan Penjualan Produk' : 'Penjualan Produk' }}</h6>
                <div class="flex gap-2">
                    <!-- Tombol Export PDF -->
                    <button type="button" data-modal-target="export-pdf-modal" data-modal-toggle="export-pdf-modal" 
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                        <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                        Export PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
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
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $item)
                        @php
                            // Hitung total HPP dari semua item di transaksi ini
                            $totalHPP = $item->items->sum(function($transItem) {
                                $product = \App\Models\Product::find($transItem->product_id);
                                return $product ? ($product->hpp * $transItem->qty) : 0;
                            });
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">
                                <a class="text-primary-600 cursor-pointer btn-view-detail"
                                data-items='@json($item->items->map(function($transItem) {
                                    $product = \App\Models\Product::find($transItem->product_id);
                                    return array_merge($transItem->toArray(), [
                                        "hpp" => $product ? $product->hpp : 0
                                    ]);
                                }))'>
                                {{ $item->transaction_code }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($item->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($item->dibayarkan, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($item->kembalian, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $item->metode_pembayaran ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($item->harga_sebelum_diskon, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($item->diskon_barang, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($item->diskon, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($totalHPP, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Export PDF -->
<div id="export-pdf-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[600px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Filter Export PDF</h1>
            <button data-modal-hide="export-pdf-modal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <div class="p-6">
            <form action="{{ route('kasir.export_pdf') }}" method="POST" id="export-pdf-form">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <!-- Pilih Tipe Filter Tanggal -->
                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Filter Periode Transaksi:</label>
                        <div class="space-y-2">
                            <div class="flex items-center mb-2">
                                <input type="radio" id="filter_all" name="filter_type" value="all" class="w-4 h-4 text-primary-600" checked>
                                <label for="filter_all" class="ml-2 text-sm font-medium text-gray-900">Semua Tanggal</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="filter_range" name="filter_type" value="range" class="w-4 h-4 text-primary-600">
                                <label for="filter_range" class="ml-2 text-sm font-medium text-gray-900">Range Tanggal</label>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Range Tanggal -->
                    <div id="range-filter" class="hidden">
                        <div class="space-y-4">
                            <div>
                                <label for="tanggal_mulai" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tanggal Mulai:</label>
                                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control rounded-lg w-full">
                            </div>
                            <div>
                                <label for="tanggal_selesai" class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tanggal Selesai:</label>
                                <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control rounded-lg w-full">
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="col-span-12">
                        <div class="flex items-center justify-start gap-3 mt-6">
                            <button type="button" data-modal-hide="export-pdf-modal" class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
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

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    // View detail transaction
    document.querySelectorAll('.btn-view-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            const items = JSON.parse(btn.dataset.items || "[]");

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
                                        <th class="border-r border-neutral-200 last:border-r-0">Qty</th>
                                        <th class="border-r border-neutral-200 last:border-r-0">Harga</th>
                                        <th class="border-r border-neutral-200 last:border-r-0">HPP</th>
                                        <th class="border-r border-neutral-200 last:border-r-0">Diskon / Barang</th>
                                        <th class="border-r border-neutral-200 last:border-r-0">Subtotal</th>
                                        <th class="border-r border-neutral-200 last:border-r-0">Total HPP</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            if (items.length > 0) {
                items.forEach((it, index) => {
                    const subtotal = (it.qty * it.price) - (it.diskon * it.qty);
                    const totalHPPItem = (it.hpp || 0) * it.qty;
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${it.product_name}</td>
                            <td>${it.qty}</td>
                            <td>Rp ${parseFloat(it.price).toLocaleString('id-ID')}</td>
                            <td>Rp ${parseFloat(it.hpp || 0).toLocaleString('id-ID')}</td>
                            <td>Rp ${parseFloat(it.diskon ?? 0).toLocaleString('id-ID')}</td>
                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                            <td>Rp ${totalHPPItem.toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
            } else {
                html += `
                    <tr>
                        <td colspan="8" class="text-center py-3">Tidak ada item dalam transaksi ini.</td>
                    </tr>
                `;
            }

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            Swal.fire({
                html: html,
                showConfirmButton: true,
                confirmButtonText: 'Tutup',
                width: '900px',
            });
        });
    });

    // Toggle filter sections
    const filterRadios = document.querySelectorAll('input[name="filter_type"]');
    const rangeFilter = document.getElementById('range-filter');

    filterRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            rangeFilter.classList.add('hidden');

            if (this.value === 'range') {
                rangeFilter.classList.remove('hidden');
            }
        });
    });

    // Form validation
    document.getElementById('export-pdf-form').addEventListener('submit', function(e) {
        const filterType = document.querySelector('input[name="filter_type"]:checked').value;

        if (filterType === 'range') {
            const tanggalMulai = document.getElementById('tanggal_mulai').value;
            const tanggalSelesai = document.getElementById('tanggal_selesai').value;

            if (!tanggalMulai || !tanggalSelesai) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Mohon pilih tanggal mulai dan tanggal selesai terlebih dahulu!'
                });
                return false;
            }

            // Validasi tanggal selesai >= tanggal mulai
            if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Tanggal selesai harus lebih besar atau sama dengan tanggal mulai!'
                });
                return false;
            }
        }
    });
});
</script>
@endsection