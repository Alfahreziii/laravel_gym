@extends('layout.layout')
@php
    $title    = 'Kategori Membership';
    $subTitle = 'Kategori Membership';
    $isAdmin  = (bool) auth()->user()?->hasRole('admin');
    $colCount = $isAdmin ? 3 : 2;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Kategori Membership"
    subtitle="Kelola kategori paket dan jenis keanggotaan gym."
>
    <x-slot:actions>
        @if($isAdmin)
        <button type="button" onclick="HexaModal.show('add-kategori-modal')"
            class="btn btn-primary btn-sm">
            + Tambah Data
        </button>
        @endif
    </x-slot:actions>
    <x-data-table
        tableId="kategoriPaket"
        :colspan="$colCount"
        placeholder="Cari nama kategori...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                @if($isAdmin)
                <th scope="col">Aksi</th>
                @endif
                <th scope="col">Nama Kategori</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

{{-- Modal Edit Kategori (shared, di-populate via JS saat tombol edit diklik) --}}
@if($isAdmin)
<x-modal id="edit-kategori-modal" title="Edit Kategori">
    <x-slot:body>
        <form id="editKategoriForm" method="POST">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_nama_kategori" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Kategori :
                </label>
                <input type="text" id="edit_nama_kategori" name="nama_kategori"
                    class="form-control rounded-lg" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="edit-kategori-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="editKategoriForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Update
        </button>
    </x-slot:footer>
</x-modal>

{{-- Modal Add Kategori --}}
<x-modal id="add-kategori-modal" title="Add New Kategori">
    <x-slot:body>
        <form id="addKategoriForm" action="{{ route('kategori_paket_membership.store') }}" method="POST">
            @csrf
            <div>
                <label for="add_nama_kategori" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Kategori :
                </label>
                <input type="text" id="add_nama_kategori" name="nama_kategori"
                    class="form-control rounded-lg" placeholder="Masukkan Nama Kategori" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="add-kategori-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="addKategoriForm"
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

    var editForm      = document.getElementById('editKategoriForm');
    var editNamaInput = document.getElementById('edit_nama_kategori');

    // Escape HTML attribute values untuk embed aman di data-* attrs
    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Event delegation: edit + delete dari baris ajax
    document.addEventListener('click', function (e) {
        // Tombol edit — populate shared modal lalu tampilkan
        var editBtn = e.target.closest('.open-edit-modal');
        if (editBtn) {
            editNamaInput.value = editBtn.dataset.namaKategori;
            editForm.setAttribute('action', editBtn.dataset.action);
            HexaModal.show('edit-kategori-modal');
            return;
        }

        // Tombol delete — SweetAlert konfirmasi, lalu submit form programatik
        var deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            var url = deleteBtn.dataset.action;
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Data kategori yang dihapus tidak bisa dikembalikan!',
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

    AjaxTable.init('kategoriPaket', {
        url: '{{ route('kategori_paket_membership.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            var actionCol = '';
            if (isAdmin) {
                actionCol = '<td class="whitespace-nowrap">'
                    + '<div class="flex gap-2">'
                    + '<button type="button"'
                    + ' class="open-edit-modal btn-action"'
                    + ' title="Edit Kategori"'
                    + ' data-nama-kategori="' + htmlEsc(item.nama_kategori) + '"'
                    + ' data-action="' + item.update_url + '">'
                    + '<iconify-icon icon="lucide:edit"></iconify-icon>'
                    + '</button>'
                    + '<button type="button"'
                    + ' class="delete-btn btn-action btn-action-del"'
                    + ' title="Hapus Kategori"'
                    + ' data-action="' + item.delete_url + '">'
                    + '<iconify-icon icon="mingcute:delete-2-line"></iconify-icon>'
                    + '</button>'
                    + '</div>'
                    + '</td>';
            }

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + actionCol
                + '<td>' + item.nama_kategori + '</td>'
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
