@extends('layout.layout')
@php
    $title='Member Trainer';
    $subTitle = 'Member Trainer';
    $script='<script src="' . asset('assets/js/data-table.js') . '"></script>';
@endphp

@section('content')

@if(session('success'))
    <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{ session('success') }}
        </div>
        <button class="remove-button text-success-600 text-2xl">                                         <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
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
                <h6 class="card-title mb-0 text-lg">Data Member Trainer</h6>
                <a href="{{ route('alat_gym.create') }}" 
                   class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                   + Tambah Data
                </a>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            <th>Barcode</th>
                            <th>Nama Alat Gym</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Tanggal Pembelian</th>
                            <th>Lokasi Alat</th>
                            <th>Kondisi Alat</th>
                            <th>Vendor</th>
                            <th>Kontak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alatGyms as $index => $item)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap"><a class="text-primary-600" href="{{ route('alat_gym.edit', $item->id) }}">{{ $item->barcode }}</a></td>
                            <td class="whitespace-nowrap">{{ $item->nama_alat_gym ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->jumlah ?? '-' }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($item->tgl_pembelian)->format('d M Y') }}</td>
                            <td class="whitespace-nowrap">{{ $item->lokasi_alat ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->kondisi_alat ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->vendor ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->kontak ?? '-' }}</td>
                            <td class="whitespace-nowrap flex gap-2">
                                <a href="{{ route('alat_gym.edit', $item->id) }}" 
                                   class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                <a href="{{ route('alat_gym.edit', $item->id) }}" class="w-8 h-8 bg-primary-50 text-primary-600 rounded-full inline-flex items-center justify-center">
                                <form action="{{ route('alat_gym.destroy', $item->id) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                text: "Data Member Trainer yang dihapus tidak bisa dikembalikan!",
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
});
</script>
@endsection
