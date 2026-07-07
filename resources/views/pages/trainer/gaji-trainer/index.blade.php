@extends('layout.layout')
@php
    $title    = 'Parameter Gaji Trainer';
    $subTitle = 'Parameter Gaji Trainer';
    $isAdmin  = (bool) auth()->user()?->hasRole('admin');
    $colCount = $isAdmin ? 6 : 5;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Data Parameter Gaji Trainer"
    subtitle="Atur parameter base rate dan jadwal gajian per trainer."
>
    <x-slot:actions>
        @role('admin')
        <a href="{{ route('gaji_trainer.create') }}" class="btn btn-primary btn-sm">+ Tambah Data</a>
        @endrole
    </x-slot:actions>

    <x-data-table
        tableId="gajiTrainer"
        :colspan="$colCount"
        placeholder="Cari nama trainer atau level...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                @role('admin')
                <th scope="col">Aksi</th>
                @endrole
                <th scope="col">Nama Trainer</th>
                <th scope="col">Level</th>
                <th scope="col">Base Rate per Sesi</th>
                <th scope="col">Tanggal Gajian</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

    window.confirmDelete = function(url) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Setting gaji trainer yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `@csrf<input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    AjaxTable.init('gajiTrainer', {
        url: '{{ route('gaji_trainer.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            const actionCol = isAdmin
                ? `<td class="whitespace-nowrap">
                       <div class="flex gap-2">
                           <a href="${item.edit_url}" title="Edit Setting Gaji"
                              class="btn-action">
                               <iconify-icon icon="lucide:edit"></iconify-icon>
                           </a>
                           <button onclick="confirmDelete('${item.delete_url}')" title="Hapus Setting Gaji" type="button"
                               class="btn-action btn-action-del">
                               <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                           </button>
                       </div>
                   </td>`
                : '';

            return `<tr>
                <td class="whitespace-nowrap">${item.no}</td>
                ${actionCol}
                <td class="whitespace-nowrap">
                    <a class="text-primary-600 font-semibold" href="${item.edit_url}">${item.trainer_name}</a>
                </td>
                <td class="whitespace-nowrap">${AjaxTable.badge('info', item.level_name)}</td>
                <td class="whitespace-nowrap font-semibold text-success-600">${item.base_rate}</td>
                <td class="whitespace-nowrap">${item.tgl_gajian}</td>
            </tr>`;
        }
    });
});
</script>
@endsection
