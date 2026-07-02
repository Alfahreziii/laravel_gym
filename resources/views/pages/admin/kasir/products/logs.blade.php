@extends('layout.layout')
@php
    $title    = 'Log Stok Produk';
    $subTitle = 'Log Perubahan Stok ' . $products->name;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Riwayat Log Stok Produk</h6>
                <a href="{{ route('products.index') }}"
                   class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                   ← Kembali ke Produk
                </a>
            </div>
            <div class="card-body">
                <x-data-table
                    tableId="logStok"
                    :colspan="7"
                    placeholder="Cari tipe atau deskripsi...">
                    <x-slot:header>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Produk</th>
                            <th scope="col">Tipe</th>
                            <th scope="col">Jumlah</th>
                            <th scope="col">Stok Sekarang</th>
                            <th scope="col">Deskripsi</th>
                            <th scope="col">Tanggal</th>
                        </tr>
                    </x-slot:header>
                </x-data-table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    AjaxTable.init('logStok', {
        url: '{{ route('products.logs.datatable', $products->id) }}',
        colSpan: 7,
        renderRow: function (item) {
            const typeBadge = item.type === 'in'
                ? AjaxTable.badge('success', 'Masuk')
                : AjaxTable.badge('danger', 'Keluar');
            return `<tr>
                <td class="whitespace-nowrap">${item.no}</td>
                <td class="whitespace-nowrap">${item.product_name}</td>
                <td class="whitespace-nowrap">${typeBadge}</td>
                <td class="whitespace-nowrap text-center">${item.quantity}</td>
                <td class="whitespace-nowrap text-center">${item.current_quantity}</td>
                <td class="whitespace-nowrap">${item.description}</td>
                <td class="whitespace-nowrap">${item.created_at}</td>
            </tr>`;
        }
    });
});
</script>
@endsection
