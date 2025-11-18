@extends('layout.layout')
@php
    $title='Alat Gym';
    $subTitle = 'Alat Gym';
    $script='<script src="' . asset('assets/js/data-table.js') . '"></script>';
    
    // Cek apakah diakses dari menu Laporan
    $isLaporanMode = request()->routeIs('laporan.alat_gym');
@endphp

@section('content')

@if(session('success'))
    <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{ session('success') }}
        </div>
        <button class="remove-button text-success-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif
@if(session('danger'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('danger') }}
        <button class="remove-button text-danger-600 text-2xl"> 
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">
                    {{ $isLaporanMode ? 'Laporan Data Alat Gym' : 'Data Alat Gym' }}
                </h6>
                <div class="flex gap-2">
                    <!-- Tombol Export PDF -->
                    <button type="button" data-modal-target="export-pdf-modal" data-modal-toggle="export-pdf-modal" 
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                        <iconify-icon icon="carbon:document-pdf" class="mr-2 text-lg"></iconify-icon>
                        Export PDF
                    </button>
                    
                    {{-- Tombol Tambah Data hanya tampil jika BUKAN mode laporan --}}
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
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            {{-- Kolom Aksi hanya tampil jika BUKAN mode laporan --}}
                            @if(!$isLaporanMode)
                                @role('admin')
                                <th>Aksi</th>
                                @endrole
                            @endif
                            <th>Barcode</th>
                            <th>Nama Alat Gym</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Tanggal Pembelian</th>
                            <th>Lokasi Alat</th>
                            <th>Kondisi Alat</th>
                            <th>Vendor</th>
                            <th>Kontak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alatGyms as $index => $item)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            {{-- Tombol Aksi hanya tampil jika BUKAN mode laporan --}}
                            @if(!$isLaporanMode)
                                @role('admin')
                                <td class="whitespace-nowrap flex gap-2">
                                    <a href="{{ route('alat_gym.edit', $item->id) }}" title="Edit Item"
                                       class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <form action="{{ route('alat_gym.destroy', $item->id) }}" method="POST" class="inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" title="Hapus Item"
                                                class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                            <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                                @endrole
                            @endif
                            @if(!$isLaporanMode)
                                @role('admin')
                                <td class="whitespace-nowrap"><a class="text-primary-600" href="{{ route('alat_gym.edit', $item->id) }}">{{ $item->barcode }}</a></td>
                                @endrole
                                @role('spv')
                                <td class="whitespace-nowrap">{{ $item->barcode }}</td>
                                @endrole
                            @else
                                <td class="whitespace-nowrap">{{ $item->barcode }}</td>
                            @endif
                            <td class="whitespace-nowrap">{{ $item->nama_alat_gym ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->jumlah ?? '-' }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">{{ $item->tgl_pembelian ? \Carbon\Carbon::parse($item->tgl_pembelian)->format('d M Y') : '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->lokasi_alat ?? '-' }}</td>
                            <td class="whitespace-nowrap">
                                @if($item->kondisi_alat === 'Baik')
                                    <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full font-medium text-xs">Baik</span>
                                @elseif($item->kondisi_alat === 'Rusak')
                                    <span class="bg-danger-100 text-danger-600 px-3 py-1 rounded-full font-medium text-xs">Rusak</span>
                                @elseif($item->kondisi_alat === 'Perlu Perbaikan')
                                    <span class="bg-warning-100 text-warning-600 px-3 py-1 rounded-full font-medium text-xs">Perlu Perbaikan</span>
                                @else
                                    <span class="text-gray-500">{{ $item->kondisi_alat ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $item->vendor ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->kontak ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Export PDF -->
<div id="export-pdf-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[600px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Export Laporan Alat Gym</h1>
            <button data-modal-hide="export-pdf-modal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <div class="p-6">
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-danger-100 rounded-full flex items-center justify-center mb-4">
                    <iconify-icon icon="carbon:document-pdf" class="text-danger-600 text-3xl"></iconify-icon>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Export Data Alat Gym ke PDF?</h3>
                <p class="text-sm text-gray-500">
                    Laporan akan mencakup semua data alat gym yang terdaftar dalam sistem
                </p>
            </div>

            <form action="{{ route('alat_gym.export_pdf') }}" method="POST">
                @csrf
                <div class="flex items-center justify-center gap-3">
                    <button type="button" data-modal-hide="export-pdf-modal" 
                            class="border border-neutral-300 hover:bg-neutral-100 text-neutral-700 text-base px-10 py-2.5 rounded-lg font-medium">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-danger-600 hover:bg-danger-700 text-white text-base px-8 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
                        <iconify-icon icon="carbon:document-pdf"></iconify-icon>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();

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
                    form.submit();
                }
            });
        });
    });

    // Remove alert
    const removeButtons = document.querySelectorAll('.remove-button');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if(alert) {
                alert.remove();
            }
        });
    });
});
</script>
@endsection