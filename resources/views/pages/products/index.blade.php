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
                <a href="{{ route('products.create') }}" 
                   class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                   + Tambah Produk
                </a>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto Produk</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $index => $product)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                        alt="image {{ $product->name }}" 
                                        class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <img src="{{ asset('assets/images/kasir/product-placeholder.png') }}" 
                                        alt="image {{ $product->name }}" 
                                        class="w-10 h-10 rounded-full object-cover">
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $product->name }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $product->kategori->name ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap">
                                @if($product->discount > 0)
                                    {{ $product->discount }}
                                    {{ $product->discount_type == 'percent' ? '%' : 'Rp' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <button type="button" class="text-primary-600" data-modal-target="edit-quantity-modal-{{ $product->id }}" data-modal-toggle="edit-quantity-modal-{{ $product->id }}">
                                    {{ $product->quantity }}
                                </button>

                            </td>
                            <td class="whitespace-nowrap">
                                @if($product->is_active)
                                    <span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm">Aktif</span>
                                @else
                                    <span class="bg-danger-100 text-danger-600 px-4 py-1.5 rounded-full font-medium text-sm">Nonaktif</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap flex gap-2">
                                <a href="{{ route('products.edit', $product->id) }}" 
                                   class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                <a href="{{ route('products.logs', $product->id) }}" 
                                   class="w-8 h-8 bg-warning-100 text-warning-600 rounded-full inline-flex items-center justify-center">
                                    <i class="ri-calendar-schedule-line"></i>
                                </a>

                                <form action="{{ route('products.destroy', $product->id) }}" 
                                      method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- MODAL EDIT Quantity --}}
                        <div id="edit-quantity-modal-{{ $product->id }}" tabindex="-1" 
                            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 
                            justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="rounded-2xl bg-white max-w-[800px] w-full">
                                <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                                    <h1 class="text-xl">Edit Quantity</h1>
                                    <button data-modal-hide="edit-quantity-modal-{{ $product->id }}" type="button"
                                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>

                                <div class="p-6">
                                    <form action="{{ route('products.adjust', $product->id) }}" method="POST">
                                        @csrf

                                        <input type="hidden" name="type" id="type_{{ $product->id }}" value="in">

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                            <div class="col-span-12">
                                                <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Jumlah Perubahan Stok
                                                </label>
                                                <input type="number" id="quantity_{{ $product->id }}" name="quantity"
                                                    class="form-control rounded-lg" required min="1">
                                            </div>

                                            <div class="col-span-12">
                                                <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Deskripsi
                                                </label>
                                                <textarea name="description" class="form-control rounded-lg" rows="3" placeholder="Contoh: Restok barang baru..."></textarea>
                                            </div>

                                            <div class="col-span-12 mt-4 flex items-center gap-3">
                                                <!-- Tombol untuk kurangi stok -->
                                                <button type="submit" 
                                                        onclick="document.getElementById('type_{{ $product->id }}').value='out'" 
                                                        class="btn bg-warning-500 hover:bg-warning-600 border border-warning-600 text-white text-base px-6 py-3 rounded-lg">
                                                    Kurangi Stok
                                                </button>

                                                <!-- Tombol untuk tambah stok -->
                                                <button type="submit" 
                                                        onclick="document.getElementById('type_{{ $product->id }}').value='in'" 
                                                        class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                                    Tambah Stok
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
document.addEventListener("DOMContentLoaded", function () {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data produk yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>

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
