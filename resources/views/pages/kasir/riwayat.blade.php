@extends('layout.layout')
@php
    $title = 'Produk';
    $subTitle = 'Daftar Produk';
    $script = '<script src="' . asset('assets/js/data-table.js') . '"></script>';
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
                <h6 class="card-title mb-0 text-lg">Data Produk</h6>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $item)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">
                                <a class="text-primary-600 cursor-pointer btn-view-detail"
                                data-items='@json($item->items)'>
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
                        </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
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
                                        <th class="border-r border-neutral-200 last:border-r-0">Diskon / Barang</th>
                                        <th class="border-r border-neutral-200 last:border-r-0">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            if (items.length > 0) {
                items.forEach((it, index) => {
                    const subtotal = (it.qty * it.price) - (it.diskon * it.qty);
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${it.product_name}</td>
                            <td>${it.qty}</td>
                            <td>Rp ${parseFloat(it.price).toLocaleString('id-ID')}</td>
                            <td>Rp ${parseFloat(it.diskon ?? 0).toLocaleString('id-ID')}</td>
                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
            } else {
                html += `
                    <tr>
                        <td colspan="6" class="text-center py-3">Tidak ada item dalam transaksi ini.</td>
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
                width: '800px',
            });
        });
    });
});
</script>
@endsection
