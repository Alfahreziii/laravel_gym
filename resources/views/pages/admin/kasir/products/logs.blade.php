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

<x-page-table
    title="Riwayat Log Stok Produk"
    subtitle="Log perubahan stok: {{ $products->name }}"
>
    <x-slot:actions>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
            ← Kembali ke Produk
        </a>
    </x-slot:actions>
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
</x-page-table>

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
