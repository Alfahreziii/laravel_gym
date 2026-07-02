@extends('layout.layout')
@php
    $title    = 'Specialisasi Trainer';
    $subTitle = 'Specialisasi Trainer';
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

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Specialisasi Trainer</h6>
                @if($isAdmin)
                <button type="button" onclick="HexaModal.show('add-specialisasi-modal')"
                    class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                    + Tambah Data
                </button>
                @endif
            </div>
            <div class="card-body">
                <x-data-table
                    tableId="specialisasi"
                    :colspan="$colCount"
                    placeholder="Cari nama specialisasi...">
                    <x-slot:header>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Specialisasi</th>
                            @if($isAdmin)
                            <th scope="col">Aksi</th>
                            @endif
                        </tr>
                    </x-slot:header>
                </x-data-table>
            </div>
        </div>
    </div>
</div>

@if($isAdmin)
<x-modal id="edit-specialisasi-modal" title="Edit Specialisasi">
    <x-slot:body>
        <form id="editSpecialisasiForm" method="POST">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_nama_specialisasi" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Specialisasi :
                </label>
                <input type="text" id="edit_nama_specialisasi" name="nama_specialisasi"
                    class="form-control rounded-lg" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="edit-specialisasi-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="editSpecialisasiForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Update
        </button>
    </x-slot:footer>
</x-modal>

<x-modal id="add-specialisasi-modal" title="Add New Specialisasi">
    <x-slot:body>
        <form id="addSpecialisasiForm" action="{{ route('specialisasi.store') }}" method="POST">
            @csrf
            <div>
                <label for="add_nama_specialisasi" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Specialisasi :
                </label>
                <input type="text" id="add_nama_specialisasi" name="nama_specialisasi"
                    class="form-control rounded-lg" placeholder="Masukkan Nama Specialisasi" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="add-specialisasi-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="addSpecialisasiForm"
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

    var editForm      = document.getElementById('editSpecialisasiForm');
    var editNamaInput = document.getElementById('edit_nama_specialisasi');

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.open-edit-modal');
        if (editBtn) {
            editNamaInput.value = editBtn.dataset.namaSpecialisasi;
            editForm.setAttribute('action', editBtn.dataset.action);
            HexaModal.show('edit-specialisasi-modal');
            return;
        }

        var deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            var url = deleteBtn.dataset.action;
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Data specialisasi yang dihapus tidak bisa dikembalikan!',
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

    AjaxTable.init('specialisasi', {
        url: '{{ route('specialisasi.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            var actionCol = '';
            if (isAdmin) {
                actionCol = '<td class="whitespace-nowrap">'
                    + '<div class="flex gap-2">'
                    + '<button type="button"'
                    + ' class="open-edit-modal w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center"'
                    + ' title="Edit Specialisasi"'
                    + ' data-nama-specialisasi="' + htmlEsc(item.nama_specialisasi) + '"'
                    + ' data-action="' + item.update_url + '">'
                    + '<iconify-icon icon="lucide:edit"></iconify-icon>'
                    + '</button>'
                    + '<button type="button"'
                    + ' class="delete-btn w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center"'
                    + ' title="Hapus Specialisasi"'
                    + ' data-action="' + item.delete_url + '">'
                    + '<iconify-icon icon="mingcute:delete-2-line"></iconify-icon>'
                    + '</button>'
                    + '</div>'
                    + '</td>';
            }

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td>' + item.nama_specialisasi + '</td>'
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
