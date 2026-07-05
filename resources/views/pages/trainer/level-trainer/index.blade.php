@extends('layout.layout')
@php
    $title    = 'Level Trainer';
    $subTitle = 'Level Trainer';
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
    title="Level Trainer"
    subtitle="Kelola level jenjang karier trainer (Junior, Senior, Expert, dst.)."
>
    <x-slot:actions>
        @if($isAdmin)
        <button type="button" onclick="HexaModal.show('add-level-modal')" class="btn btn-primary btn-sm">
            + Tambah Data
        </button>
        @endif
    </x-slot:actions>

    <x-data-table
        tableId="levelTrainer"
        :colspan="$colCount"
        placeholder="Cari nama level...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Nama Level</th>
                @if($isAdmin)
                <th scope="col">Aksi</th>
                @endif
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

@if($isAdmin)
<x-modal id="edit-level-modal" title="Edit Level Trainer">
    <x-slot:body>
        <form id="editLevelForm" method="POST">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_name" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Level :
                </label>
                <input type="text" id="edit_name" name="name"
                    class="form-control rounded-lg" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="edit-level-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="editLevelForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Update
        </button>
    </x-slot:footer>
</x-modal>

<x-modal id="add-level-modal" title="Add New Level Trainer">
    <x-slot:body>
        <form id="addLevelForm" action="{{ route('level_trainer.store') }}" method="POST">
            @csrf
            <div>
                <label for="add_name" class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                    Nama Level :
                </label>
                <input type="text" id="add_name" name="name"
                    class="form-control rounded-lg" placeholder="Masukkan Nama Level (contoh: Junior, Senior, Expert)" required>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="add-level-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button type="submit" form="addLevelForm"
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

    var editForm      = document.getElementById('editLevelForm');
    var editNameInput = document.getElementById('edit_name');

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.open-edit-modal');
        if (editBtn) {
            editNameInput.value = editBtn.dataset.name;
            editForm.setAttribute('action', editBtn.dataset.action);
            HexaModal.show('edit-level-modal');
            return;
        }

        var deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            var url = deleteBtn.dataset.action;
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Data level trainer yang dihapus tidak bisa dikembalikan!',
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

    AjaxTable.init('levelTrainer', {
        url: '{{ route('level_trainer.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            var actionCol = '';
            if (isAdmin) {
                actionCol = '<td class="whitespace-nowrap">'
                    + '<div class="flex gap-2">'
                    + '<button type="button"'
                    + ' class="open-edit-modal w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center"'
                    + ' title="Edit Level"'
                    + ' data-name="' + htmlEsc(item.name) + '"'
                    + ' data-action="' + item.update_url + '">'
                    + '<iconify-icon icon="lucide:edit"></iconify-icon>'
                    + '</button>'
                    + '<button type="button"'
                    + ' class="delete-btn w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center"'
                    + ' title="Hapus Level"'
                    + ' data-action="' + item.delete_url + '">'
                    + '<iconify-icon icon="mingcute:delete-2-line"></iconify-icon>'
                    + '</button>'
                    + '</div>'
                    + '</td>';
            }

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td>' + item.name + '</td>'
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
