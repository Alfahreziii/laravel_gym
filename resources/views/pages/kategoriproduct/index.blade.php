@extends('layout.layout')

@php
    $title = 'Kategori Produk';
    $subTitle = 'Kategori Produk';
    $script = '<script src="' . asset('assets/js/data-table.js') . '"></script>';
@endphp

@section('content')

{{-- ALERT SECTION --}}
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

{{-- TABLE SECTION --}}
<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Kategori Produk</h6>
                @role('admin')
                <button type="button" data-modal-target="add-kategori-modal" data-modal-toggle="add-kategori-modal"
                    class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 
                    hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 
                    font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center 
                    dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                    + Tambah Data
                </button>
                @endrole
            </div>

            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Kategori</th>
                            <th scope="col">Deskripsi</th>
                            @role('admin')
                            <th scope="col">Aksi</th>
                            @endrole
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kategori_products as $index => $category)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">{{ $category->name }}</td>
                            <td class="whitespace-nowrap">{{ $category->description ?? '-' }}</td>
                            @role('admin')
                            <td class="whitespace-nowrap">
                                {{-- Tombol Edit --}}
                                <button type="button" data-modal-target="edit-kategori-modal-{{ $category->id }}" data-modal-toggle="edit-kategori-modal-{{ $category->id }}"
                                    class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </button>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('kategori_products.destroy', $category->id) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </td>
                            @endrole
                        </tr>

                        @role('admin')
                        {{-- MODAL EDIT KATEGORI --}}
                        <div id="edit-kategori-modal-{{ $category->id }}" tabindex="-1" 
                            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 
                            justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="rounded-2xl bg-white max-w-[800px] w-full">
                                <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                                    <h1 class="text-xl">Edit Kategori</h1>
                                    <button data-modal-hide="edit-kategori-modal-{{ $category->id }}" type="button"
                                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>

                                <div class="p-6">
                                    <form action="{{ route('kategori_products.update', $category->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                            <div class="col-span-12">
                                                <label for="name_{{ $category->id }}" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Nama Kategori:
                                                </label>
                                                <input type="text" id="name_{{ $category->id }}" name="name"
                                                    class="form-control rounded-lg" value="{{ $category->name }}" required>
                                            </div>

                                            <div class="col-span-12">
                                                <label for="description_{{ $category->id }}" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Deskripsi:
                                                </label>
                                                <textarea id="description_{{ $category->id }}" name="description" class="form-control rounded-lg" rows="3">{{ $category->description }}</textarea>
                                            </div>

                                            <div class="col-span-12 mt-4 flex items-center gap-3">
                                                <button type="button" data-modal-hide="edit-kategori-modal-{{ $category->id }}"
                                                    class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                    class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                                    Update
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endrole
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH KATEGORI --}}
<div id="add-kategori-modal" tabindex="-1"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center 
    w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[800px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl">Tambah Kategori Baru</h1>
            <button data-modal-hide="add-kategori-modal" type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <div class="p-6">
            <form action="{{ route('kategori_products.store') }}" method="POST">
                @csrf
                @method('POST')

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="col-span-12">
                        <label for="name" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                            Nama Kategori:
                        </label>
                        <input type="text" id="name" name="name" class="form-control rounded-lg"
                            placeholder="Masukkan nama kategori" required>
                    </div>

                    <div class="col-span-12">
                        <label for="description" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                            Deskripsi:
                        </label>
                        <textarea id="description" name="description" class="form-control rounded-lg" rows="3" placeholder="Deskripsi kategori"></textarea>
                    </div>

                    <div class="col-span-12 mt-4 flex items-center gap-3">
                        <button type="reset" data-modal-hide="add-kategori-modal"
                            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                            Save
                        </button>
                    </div>
                </div>
            </form>
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
                text: "Data kategori produk yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});

// Alert close
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
