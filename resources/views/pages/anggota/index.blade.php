@extends('layout.layout')
@php
    $title='Anggota';
    $subTitle = 'Anggota';
    $script='<script src="' . asset('assets/js/data-table.js') . '"></script>';
    $isLaporanMode = request()->routeIs('laporan.anggota');
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
                    {{ $isLaporanMode ? 'Laporan Data Anggota GYM' : 'Data Anggota GYM' }}
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
                        <a href="{{ route('anggota.create') }}" 
                           class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                           + Tambah Data
                        </a>
                        @endrole
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate">
                    <thead>
                        <tr>
                            <th scope="col">S.L</th>
                            <th scope="col">ID Kartu</th>
                            <th scope="col">Photo</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Tanggal Lahir</th>
                            <th scope="col">No. Telp</th>
                            <th scope="col">Status</th>
                            @role('admin')
                            <th scope="col">Aksi</th>
                            @endrole
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anggotas as $index => $anggota)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">{{ $anggota->id_kartu }}</td>
                            <td class="whitespace-nowrap">
                                @if($anggota->photo)
                                    <img src="{{ asset('storage/' . $anggota->photo) }}" 
                                        alt="Photo {{ $anggota->name }}" 
                                        class="w-10 h-10 rounded-full object-cover cursor-pointer anggota-photo"
                                        data-photo="{{ asset('storage/' . $anggota->photo) }}">
                                @else
                                    <span class="text-gray-400 italic">No photo</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $anggota->name }}</td>
                            <td class="whitespace-nowrap">{{ $anggota->tgl_lahir->format('d M Y') }}</td>
                            <td class="whitespace-nowrap">{{ $anggota->no_telp }}</td>
                            <td class="whitespace-nowrap">
                                @if($anggota->status_keanggotaan)
                                    <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Aktif</span>
                                @else
                                    <span class="bg-warning-100 text-warning-600 px-6 py-1.5 rounded-full font-medium text-sm">Tidak Aktif</span>
                                @endif
                            </td>
                            @role('admin')
                            <td class="whitespace-nowrap">
                                <a href="{{ route('anggota.edit', $anggota->id) }}" class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                <form action="{{ route('anggota.destroy', $anggota->id) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </td>
                            @endrole
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
            <h1 class="text-xl font-semibold">Filter Export PDF</h1>
            <button data-modal-hide="export-pdf-modal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <div class="p-6">
            <form action="{{ route('anggota.export_pdf') }}" method="POST" id="export-pdf-form">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <!-- Pilih Status Filter -->
                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Pilih Status Keanggotaan:</label>
                        <div class="space-y-2">
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_all" name="status_filter" value="all" class="w-4 h-4 text-primary-600" checked>
                                <label for="status_all" class="ml-2 text-sm font-medium text-gray-900">Semua Data</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_aktif" name="status_filter" value="aktif" class="w-4 h-4 text-primary-600">
                                <label for="status_aktif" class="ml-2 text-sm font-medium text-gray-900">Anggota Aktif</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_tidak_aktif" name="status_filter" value="tidak_aktif" class="w-4 h-4 text-primary-600">
                                <label for="status_tidak_aktif" class="ml-2 text-sm font-medium text-gray-900">Anggota Tidak Aktif</label>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="col-span-12">
                        <div class="flex items-center justify-start gap-3 mt-6">
                            <button type="button" data-modal-hide="export-pdf-modal" class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                                Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Script untuk konfirmasi hapus
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data anggota yang dihapus tidak bisa dikembalikan!",
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

    // Script untuk menampilkan pop-up foto anggota
    const photos = document.querySelectorAll('.anggota-photo');
    photos.forEach(photo => {
        photo.addEventListener('click', function () {
            const imageUrl = this.getAttribute('data-photo');
            Swal.fire({
                imageUrl: imageUrl,
                imageAlt: 'Foto Anggota',
                showConfirmButton: false,
                background: 'transparent',
                width: 'auto',
                padding: '0',
                showCloseButton: true,
            });
        });
    });
});
</script>
@endsection