@extends('layout.layout')
@php
    $title = 'List User';
    $subTitle = 'List User Management System';
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Manajemen User"
    subtitle="Kelola role dan akun pengguna sistem."
>
    <x-data-table tableId="users" :colspan="auth()->user()->hasRole('admin') ? 7 : 6" placeholder="Cari nama, email, atau role...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                @role('admin')
                    <th scope="col">Aksi</th>
                @endrole
                <th scope="col">Foto</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

    {{-- Modal Edit Role (shared) --}}
    @role('admin')
    <x-modal id="editUserRoleModal" title="Edit Role User">
        <x-slot:body>
            <form id="formEditRole" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-6">
                    <div class="modal-info-box bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Nama User:</label>
                                <p class="text-base font-semibold text-gray-900" id="modalUserName">-</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Email:</label>
                                <p class="text-base font-semibold text-gray-900" id="modalUserEmail">-</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                            Pilih Role <span class="text-danger-600">*</span>
                        </label>
                        <select id="modalRoleSelect" name="role"
                            class="form-control rounded-lg w-full border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            required>
                            <option value="" disabled>-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="spv">Supervisor (SPV)</option>
                            <option value="guest">Guest</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Role saat ini:
                            <span id="modalCurrentRole" class="font-semibold text-primary-600">-</span>
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" data-close-modal="editUserRoleModal"
                            class="border border-gray-300 hover:bg-gray-100 text-gray-700 text-base px-8 py-2.5 rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="bg-primary-600 hover:bg-primary-700 text-white text-base px-8 py-2.5 rounded-lg transition-colors font-medium">
                            Update Role
                        </button>
                    </div>
                </div>
            </form>
        </x-slot:body>
    </x-modal>
    @endrole

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        var isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};

        AjaxTable.init('users', {
            url: '{{ route('users.datatable') }}',
            colSpan: isAdmin ? 7 : 6,
            renderRow: function(item) {
                var fotoHtml = item.foto
                    ? '<img src="' + item.foto + '" alt="' + item.name + '" class="w-10 h-10 rounded-lg object-cover">'
                    : '<span class="text-gray-400 italic text-xs">No photo</span>';

                var actionHtml = '';
                if (isAdmin) {
                    if (item.can_edit_role) {
                        actionHtml = '<td class="whitespace-nowrap">' +
                            '<button type="button" title="Ubah Role" ' +
                            'data-id="' + item.id + '" ' +
                            'data-name="' + item.name.replace(/"/g, '&quot;') + '" ' +
                            'data-email="' + item.email.replace(/"/g, '&quot;') + '" ' +
                            'data-role="' + (item.current_role || '') + '" ' +
                            'data-url="' + item.update_url + '" ' +
                            'onclick="openEditRoleModal(this)" ' +
                            'class="btn-action">' +
                            '<iconify-icon icon="lucide:edit"></iconify-icon>' +
                            '</button>' +
                            '</td>';
                    } else {
                        actionHtml = '<td class="whitespace-nowrap">' +
                            '<span class="btn-action opacity-50 cursor-not-allowed" title="Role ini tidak dapat diubah">' +
                            '<iconify-icon icon="lucide:lock"></iconify-icon>' +
                            '</span>' +
                            '</td>';
                    }
                }

                return '<tr>' +
                    '<td class="whitespace-nowrap text-center">' + item.no + '</td>' +
                    actionHtml +
                    '<td class="whitespace-nowrap">' + fotoHtml + '</td>' +
                    '<td class="whitespace-nowrap">' + item.name + '</td>' +
                    '<td class="whitespace-nowrap">' + item.email + '</td>' +
                    '<td class="whitespace-nowrap">' + (item.role || '-') + '</td>' +
                    '<td class="whitespace-nowrap"><span class="' + item.status_class + '">' + item.status + ' <span class="text-xs text-gray-500">' + item.time_info + '</span></span></td>' +
                    '</tr>';
            }
        });

        window.openEditRoleModal = function(btn) {
            var name = btn.getAttribute('data-name');
            var email = btn.getAttribute('data-email');
            var role = btn.getAttribute('data-role');
            var url = btn.getAttribute('data-url');

            document.getElementById('modalUserName').textContent = name;
            document.getElementById('modalUserEmail').textContent = email;
            document.getElementById('modalCurrentRole').textContent = role
                ? (role.charAt(0).toUpperCase() + role.slice(1))
                : 'Tidak ada role';
            document.getElementById('modalRoleSelect').value = role || '';
            document.getElementById('formEditRole').action = url;

            HexaModal.show('editUserRoleModal');
        };
    </script>
@endsection
