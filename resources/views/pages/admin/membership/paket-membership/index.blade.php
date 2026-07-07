@extends('layout.layout')
@php
    $title    = 'Paket Membership';
    $subTitle = 'Paket Membership';
    $isAdmin  = (bool) auth()->user()?->hasRole('admin');
    $colCount = $isAdmin ? 8 : 7;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Daftar Paket Membership"
    subtitle="Kelola paket dan harga keanggotaan gym."
>
    <x-slot:actions>
        @role('admin')
        <a href="{{ route('paket_membership.create') }}" class="btn btn-primary btn-sm">
            + Tambah Paket
        </a>
        @endrole
    </x-slot:actions>
    <x-data-table
        tableId="paketMembership"
        :colspan="$colCount"
        placeholder="Cari nama paket, kategori, periode...">
        <x-slot:header>
            <tr>
                <th scope="col">S.L</th>
                @role('admin')
                <th scope="col">Aksi</th>
                @endrole
                <th scope="col">Kategori</th>
                <th scope="col">Nama Paket</th>
                <th scope="col">Durasi</th>
                <th scope="col">Periode</th>
                <th scope="col">Harga</th>
                <th scope="col">Keterangan</th>
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

    window.confirmDelete = function(url) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Data paket membership yang dihapus tidak bisa dikembalikan!",
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

    AjaxTable.init('paketMembership', {
        url: '{{ route('paket_membership.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            const actionCol = isAdmin
                ? `<td class="whitespace-nowrap">
                       <div class="flex gap-2">
                           <a href="${item.edit_url}" title="Edit Item"
                              class="btn-action">
                               <iconify-icon icon="lucide:edit"></iconify-icon>
                           </a>
                           <button onclick="confirmDelete('${item.delete_url}')" title="Hapus Item" type="button"
                               class="btn-action btn-action-del">
                               <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                           </button>
                       </div>
                   </td>`
                : '';

            return `<tr>
                <td class="whitespace-nowrap">${item.no}</td>
                ${actionCol}
                <td class="whitespace-nowrap">${item.nama_kategori}</td>
                <td class="whitespace-nowrap">${item.nama_paket}</td>
                <td class="whitespace-nowrap">${item.durasi}</td>
                <td class="whitespace-nowrap">${item.periode}</td>
                <td class="whitespace-nowrap">${item.harga}</td>
                <td class="whitespace-nowrap">${item.keterangan}</td>
            </tr>`;
        }
    });
});
</script>
@endsection
