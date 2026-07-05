@extends('layout.layout')
@php
    $title    = 'PlayList Trainer';
    $subTitle = 'PlayList Trainer';
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Playlist Trainer"
    subtitle="Kelola daftar latihan untuk program training member."
>
    <x-slot:actions>
        <button type="button" onclick="HexaModal.show('add-playlist-modal')" class="btn btn-primary btn-sm">
            + Tambah Data
        </button>
    </x-slot:actions>

    <x-data-table
        tableId="playlistTrainer"
        :colspan="3"
        placeholder="Cari nama latihan...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Nama Latihan</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

<x-modal id="edit-playlist-modal" title="Edit Playlist">
    <x-slot:body>
        <form id="editPlaylistForm" method="POST">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_latihan" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Latihan :
                </label>
                <input type="text" id="edit_latihan" name="latihan"
                    class="form-control rounded-lg" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="edit-playlist-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="editPlaylistForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Update
        </button>
    </x-slot:footer>
</x-modal>

<x-modal id="add-playlist-modal" title="Add New Playlist">
    <x-slot:body>
        <form id="addPlaylistForm" action="{{ route('trainerplaylist.store') }}" method="POST">
            @csrf
            <div>
                <label for="add_latihan" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Latihan :
                </label>
                <input type="text" id="add_latihan" name="latihan"
                    class="form-control rounded-lg" placeholder="Masukkan Nama Latihan" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="add-playlist-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="addPlaylistForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Save
        </button>
    </x-slot:footer>
</x-modal>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = '{{ csrf_token() }}';

    var editForm        = document.getElementById('editPlaylistForm');
    var editLatihanInput = document.getElementById('edit_latihan');

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.open-edit-modal');
        if (editBtn) {
            editLatihanInput.value = editBtn.dataset.latihan;
            editForm.setAttribute('action', editBtn.dataset.action);
            HexaModal.show('edit-playlist-modal');
            return;
        }

        var deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            var url = deleteBtn.dataset.action;
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Data playlist yang dihapus tidak bisa dikembalikan!',
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

    AjaxTable.init('playlistTrainer', {
        url: '{{ route('trainerplaylist.datatable') }}',
        colSpan: 3,
        renderRow: function (item) {
            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td>' + item.latihan + '</td>'
                + '<td class="whitespace-nowrap">'
                + '<div class="flex gap-2">'
                + '<button type="button"'
                + ' class="open-edit-modal w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center"'
                + ' title="Edit Playlist"'
                + ' data-latihan="' + htmlEsc(item.latihan) + '"'
                + ' data-action="' + item.update_url + '">'
                + '<iconify-icon icon="lucide:edit"></iconify-icon>'
                + '</button>'
                + '<button type="button"'
                + ' class="delete-btn w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center"'
                + ' title="Hapus Playlist"'
                + ' data-action="' + item.delete_url + '">'
                + '<iconify-icon icon="mingcute:delete-2-line"></iconify-icon>'
                + '</button>'
                + '</div>'
                + '</td>'
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
