@extends('layout.layout')
@php
    $title = 'Tambah Produk';
    $subTitle = 'Tambah Produk';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Tambah Produk</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            @if(session('success'))
                                <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
                                    text-success-600 dark:text-success-400 border-success-50 
                                    px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        {{ session('success') }}
                                        <a href="{{ route('products.index') }}" class="text-success-600 focus:bg-success-600 hover:bg-success-700 border border-success-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-success-300 rounded-lg text-sm px-4 py-1 text-center inline-flex items-center dark:text-success-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-success-800">Kembali</a>
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
                        </div>

                        {{-- Nama Produk --}}
                        <div class="col-span-12">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" placeholder="Masukkan nama produk" value="{{ old('name') }}" required>
                        </div>

                        {{-- Barcode --}}
                        <div class="col-span-12">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" placeholder="Masukkan barcode produk" value="{{ old('barcode') }}" required>
                        </div>

                        {{-- Harga --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" placeholder="Masukkan harga produk" value="{{ old('price') }}" required>
                        </div>

                        {{-- Diskon --}}
                        <div class="col-span-12 md:col-span-3">
                            <label class="form-label">Diskon</label>
                            <input type="number" name="discount" class="form-control" placeholder="Masukkan diskon" value="{{ old('discount') }}">
                        </div>

                        {{-- Jenis Diskon --}}
                        <div class="col-span-12 md:col-span-3">
                            <label class="form-label">Jenis Diskon</label>
                            <select name="discount_type" class="form-control">
                                <option value="">-- Pilih Jenis Diskon --</option>
                                <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                                <option value="nominal" {{ old('discount_type') == 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                            </select>
                        </div>

                        {{-- Stok --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Stok</label>
                            <input type="number" name="quantity" class="form-control" placeholder="Masukkan jumlah stok" value="{{ old('quantity') }}" required>
                        </div>

                        {{-- Kategori Produk --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Kategori Produk</label>
                            <select name="kategori_product_id" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_product_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Reorder --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Reorder</label>
                            <input type="number" name="reorder" class="form-control" placeholder="Masukkan jumlah reorder" value="{{ old('reorder') }}" required>
                        </div>

                        {{-- HPP --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">HPP</label>
                            <input type="number" name="hpp" class="form-control" placeholder="Masukkan jumlah hpp" value="{{ old('hpp') }}" required>
                        </div>
                        
                        {{-- Deskripsi --}}
                        <div class="col-span-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Masukkan deskripsi produk">{{ old('description') }}</textarea>
                        </div>

                        {{-- Status Aktif --}}
                        <div class="col-span-12">
                            <label class="form-label">Status Produk</label>
                            <select name="is_active" class="form-control">
                                <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        {{-- Gambar Produk --}}
                        <div class="col-span-12">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" name="image" class="border border-neutral-200 w-full rounded-lg" accept="image/*">
                            <small class="text-gray-500">Format yang diperbolehkan: jpg, jpeg, png, webp</small>
                        </div>

                        {{-- Tombol --}}
                        <div class="col-span-12 flex justify-end gap-2 mt-4">
                            <button type="submit" class="btn btn-primary-600">Simpan</button>
                            <a href="{{ route('products.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- card end -->
    </div>
</div>
@endsection
