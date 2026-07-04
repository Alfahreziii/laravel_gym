@extends('layout.layout')
@php
    $title    = 'Paket Trainer';
    $subTitle = 'Paket Trainer';
    $isAdmin  = (bool) auth()->user()?->hasRole('admin');
    $colCount = $isAdmin ? 7 : 6;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Daftar Paket Trainer"
    subtitle="Kelola paket, sesi, dan biaya personal trainer."
>
    <x-slot:actions>
        @role('admin')
        <a href="{{ route('paket_personal_trainer.create') }}" class="btn btn-primary btn-sm">
            + Tambah Paket
        </a>
        @endrole
    </x-slot:actions>
    <x-data-table
        tableId="paketTrainer"
        :colspan="$colCount"
        placeholder="Cari nama paket, periode...">
        <x-slot:header>
            <tr>
                <th scope="col">S.L</th>
                @role('admin')
                <th scope="col">Aksi</th>
                @endrole
                <th scope="col">Nama Paket</th>
                <th scope="col">Durasi</th>
                <th scope="col">Periode</th>
                <th scope="col">Jumlah Sesi</th>
                <th scope="col">Biaya</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

    function confirmDelete(url) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Data paket Trainer yang dihapus tidak bisa dikembalikan!",
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

    AjaxTable.init('paketTrainer', {
        url: '{{ route('paket_personal_trainer.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            const actionCol = isAdmin
                ? `<td class="whitespace-nowrap">
                       <div class="flex gap-2">
                           <a href="${item.edit_url}" title="Edit Item"
                              class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                               <iconify-icon icon="lucide:edit"></iconify-icon>
                           </a>
                           <button onclick="confirmDelete('${item.delete_url}')" title="Hapus Item" type="button"
                               class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center">
                               <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                           </button>
                       </div>
                   </td>`
                : '';

            return `<tr>
                <td class="whitespace-nowrap">${item.no}</td>
                ${actionCol}
                <td class="whitespace-nowrap">${item.nama_paket}</td>
                <td class="whitespace-nowrap">${item.durasi}</td>
                <td class="whitespace-nowrap">${item.periode}</td>
                <td class="whitespace-nowrap">${item.jumlah_sesi}</td>
                <td class="whitespace-nowrap">${item.biaya}</td>
            </tr>`;
        }
    });
});
</script>
@endsection
