@extends('layout.layout')
@php
    $title = 'Log Stok Produk';
    $subTitle = 'Log Perubahan Stok ' . $products->name;
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
                <h6 class="card-title mb-0 text-lg">Riwayat Log Stok Produk</h6>
                <a href="{{ route('products.index') }}" 
                   class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                   ← Kembali ke Produk
                </a>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Stok Sekarang</th>
                            <th>Deskripsi</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $index => $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">
                                {{ $log->product->name ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap">
                                @if($log->type == 'in')
                                    <span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm">Masuk</span>
                                @else
                                    <span class="bg-danger-100 text-danger-600 px-4 py-1.5 rounded-full font-medium text-sm">Keluar</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-center">
                                {{ $log->quantity }}
                            </td>
                            <td class="whitespace-nowrap text-center">
                                {{ $log->current_quantity }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $log->description ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, H:i') }}
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
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-button');

    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if (alert) alert.remove();
        });
    });
});
</script>
@endsection
