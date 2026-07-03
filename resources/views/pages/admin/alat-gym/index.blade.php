@extends('layout.layout')
@php
    $title         = 'Alat Gym';
    $subTitle      = 'Alat Gym';
    $isLaporanMode = request()->routeIs('laporan.alat_gym');
    $isAdmin       = (bool) auth()->user()?->hasRole('admin');
    $colCount      = ($isAdmin && !$isLaporanMode) ? 11 : 10;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger">{{ session('error') }}</x-alert>
@endif

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">
                    {{ $isLaporanMode ? 'Laporan Data Alat Gym' : 'Data Alat Gym' }}
                </h6>
                <div class="flex gap-2">
                    <button type="button" onclick="HexaModal.show('export-pdf-modal')"
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                        <iconify-icon icon="carbon:export" class="mr-2 text-lg"></iconify-icon>
                        Export Laporan
                    </button>
                    @if(!$isLaporanMode)
                        @role('admin')
                        <a href="{{ route('alat_gym.create') }}"
                           class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                           + Tambah Data
                        </a>
                        @endrole
                    @endif
                </div>
            </div>
            <div class="card-body">
                <x-data-table
                    tableId="alatGym"
                    :colspan="$colCount"
                    placeholder="Cari nama, barcode, kondisi...">
                    <x-slot:header>
                        <tr>
                            <th scope="col">S.L</th>
                            @if(!$isLaporanMode)
                                @role('admin')
                                <th scope="col">Aksi</th>
                                @endrole
                            @endif
                            <th scope="col">Barcode</th>
                            <th scope="col">Nama Alat Gym</th>
                            <th scope="col">Jumlah</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Tanggal Pembelian</th>
                            <th scope="col">Lokasi Alat</th>
                            <th scope="col">Kondisi Alat</th>
                            <th scope="col">Vendor</th>
                            <th scope="col">Kontak</th>
                        </tr>
                    </x-slot:header>
                </x-data-table>
            </div>
        </div>
    </div>
</div>

<x-modal id="export-pdf-modal" title="Export Laporan Alat Gym">
    <x-slot:body>
        <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-danger-100 rounded-full flex items-center justify-center mb-4">
                <iconify-icon icon="carbon:document-pdf" class="text-danger-600 text-3xl"></iconify-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Export Data Alat Gym ke PDF?</h3>
            <p class="text-sm text-gray-500">Laporan akan mencakup semua data alat gym yang terdaftar dalam sistem</p>
        </div>
        <form action="{{ route('alat_gym.export_pdf') }}" method="POST">
            @csrf
            <div class="flex items-center justify-center gap-3">
                <button type="button" data-close-modal="export-pdf-modal"
                        class="border border-neutral-300 hover:bg-neutral-100 text-neutral-700 text-base px-10 py-2.5 rounded-lg font-medium">
                    Batal
                </button>
                <button type="submit"
                        class="bg-danger-600 hover:bg-danger-700 text-white text-base px-8 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
                    <iconify-icon icon="carbon:document-pdf"></iconify-icon>
                    Export PDF
                </button>
                <button type="submit" formaction="{{ route('alat_gym.export_excel') }}"
                        class="bg-success-600 hover:bg-success-700 text-white text-base px-8 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
                    <iconify-icon icon="carbon:document-export"></iconify-icon>
                    Export Excel
                </button>
            </div>
        </form>
    </x-slot:body>
</x-modal>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const isAdmin       = {{ $isAdmin ? 'true' : 'false' }};
    const isLaporanMode = {{ $isLaporanMode ? 'true' : 'false' }};

    const kondisiType = { 'Baik': 'success', 'Rusak': 'danger', 'Perlu Perbaikan': 'warning' };

    function confirmDelete(url) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Data Alat Gym yang dihapus tidak bisa dikembalikan!",
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

    AjaxTable.init('alatGym', {
        url: '{{ route('alat_gym.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            const actionCol = isAdmin && !isLaporanMode
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

            const barcodeCol = isAdmin && !isLaporanMode
                ? `<td class="whitespace-nowrap"><a href="${item.edit_url}" class="text-primary-600">${item.barcode}</a></td>`
                : `<td class="whitespace-nowrap">${item.barcode}</td>`;

            return `<tr>
                <td class="whitespace-nowrap">${item.no}</td>
                ${actionCol}
                ${barcodeCol}
                <td class="whitespace-nowrap">${item.nama_alat_gym}</td>
                <td class="whitespace-nowrap">${item.jumlah}</td>
                <td class="whitespace-nowrap">${item.harga}</td>
                <td class="whitespace-nowrap">${item.tgl_pembelian}</td>
                <td class="whitespace-nowrap">${item.lokasi_alat}</td>
                <td class="whitespace-nowrap">${AjaxTable.badge(kondisiType[item.kondisi_alat] || 'neutral', item.kondisi_alat)}</td>
                <td class="whitespace-nowrap">${item.vendor}</td>
                <td class="whitespace-nowrap">${item.kontak}</td>
            </tr>`;
        }
    });
});
</script>
@endsection
