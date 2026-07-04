@extends('layout.layout')
@php
    $title    = 'Kategori Produk';
    $subTitle = 'Kategori Produk';
    $isAdmin  = (bool) auth()->user()?->hasRole('admin');
    $colCount = $isAdmin ? 4 : 3;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Kategori Produk"
    subtitle="Kelola kategori produk yang tersedia di kasir."
>
    <x-slot:actions>
        @if($isAdmin)
        <button type="button" onclick="HexaModal.show('add-kategori-product-modal')"
            class="btn btn-primary btn-sm">
            + Tambah Data
        </button>
        @endif
    </x-slot:actions>
    <x-data-table
        tableId="kategoriProduct"
        :colspan="$colCount"
        placeholder="Cari nama atau deskripsi kategori...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Nama Kategori</th>
                <th scope="col">Deskripsi</th>
                @if($isAdmin)
                <th scope="col">Aksi</th>
                @endif
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

@if($isAdmin)
<x-modal id="edit-kategori-product-modal" title="Edit Kategori">
    <x-slot:body>
        <form id="editKategoriProductForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="edit_product_name" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Kategori :
                </label>
                <input type="text" id="edit_product_name" name="name"
                    class="form-control rounded-lg" required>
            </div>
            <div>
                <label for="edit_product_description" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Deskripsi :
                </label>
                <textarea id="edit_product_description" name="description"
                    class="form-control rounded-lg" rows="3"></textarea>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="edit-kategori-product-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="editKategoriProductForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Update
        </button>
    </x-slot:footer>
</x-modal>

<x-modal id="add-kategori-product-modal" title="Tambah Kategori Baru">
    <x-slot:body>
        <form id="addKategoriProductForm" action="{{ route('kategori_products.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="add_product_name" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Kategori :
                </label>
                <input type="text" id="add_product_name" name="name"
                    class="form-control rounded-lg" placeholder="Masukkan nama kategori" required>
            </div>
            <div>
                <label for="add_product_description" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Deskripsi :
                </label>
                <textarea id="add_product_description" name="description"
                    class="form-control rounded-lg" rows="3" placeholder="Deskripsi kategori"></textarea>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="add-kategori-product-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="addKategoriProductForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Save
        </button>
    </x-slot:footer>
</x-modal>
@endif

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var isAdmin   = {{ $isAdmin ? 'true' : 'false' }};
    var csrfToken = '{{ csrf_token() }}';

    var editForm        = document.getElementById('editKategoriProductForm');
    var editNameInput   = document.getElementById('edit_product_name');
    var editDescInput   = document.getElementById('edit_product_description');

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.open-edit-modal');
        if (editBtn) {
            editNameInput.value = editBtn.dataset.name;
            editDescInput.value = editBtn.dataset.description;
            editForm.setAttribute('action', editBtn.dataset.action);
            HexaModal.show('edit-kategori-product-modal');
            return;
        }

        var deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            var url = deleteBtn.dataset.action;
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Data kategori produk yang dihapus tidak bisa dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = '<input type="hidden" name="_token" value="' + csrfToken + '">'
                        + '<input type="hidden" name="_method" value="DELETE">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    });

    AjaxTable.init('kategoriProduct', {
        url: '{{ route('kategori_products.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            var actionCol = '';
            if (isAdmin) {
                actionCol = '<td class="whitespace-nowrap">'
                    + '<div class="flex gap-2">'
                    + '<button type="button"'
                    + ' class="open-edit-modal w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center"'
                    + ' title="Edit Kategori"'
                    + ' data-name="' + htmlEsc(item.name) + '"'
                    + ' data-description="' + htmlEsc(item.description) + '"'
                    + ' data-action="' + item.update_url + '">'
                    + '<iconify-icon icon="lucide:edit"></iconify-icon>'
                    + '</button>'
                    + '<button type="button"'
                    + ' class="delete-btn w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center"'
                    + ' title="Hapus Kategori"'
                    + ' data-action="' + item.delete_url + '">'
                    + '<iconify-icon icon="mingcute:delete-2-line"></iconify-icon>'
                    + '</button>'
                    + '</div>'
                    + '</td>';
            }

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td>' + item.name + '</td>'
                + '<td>' + (item.description || '-') + '</td>'
                + actionCol
                + '</tr>';
        }
    });

    @if(session('success'))
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}', timer: 3000 });
    @endif
    @if(session('danger'))
    Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('danger') }}' });
    @endif
});
</script>
@endsection
