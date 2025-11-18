@extends('layout.layout')
@php
    $title='Pembayaran Trainer';
    $subTitle = 'Pembayaran Trainer';
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
        <button class="remove-button text-success-600 text-2xl"><iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
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
                <h6 class="card-title mb-0 text-lg">Data Pembayaran Trainer</h6>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            <th>Aksi</th>
                            <th>Kode Transaksi</th>
                            <th>Nama Anggota</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Total Biaya</th>
                            <th>Total Dibayarkan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($memberTrainers as $index => $item)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap flex gap-2">
                                @if($item->status_pembayaran === 'Lunas')
                                <a href="{{ route('pembayaran_trainer.notaPDF', $item->id) }}" 
                                class="w-8 h-8 bg-warning-100 text-warning-600 rounded-full inline-flex items-center justify-center"
                                title="Download Nota PDF">
                                    <iconify-icon icon="hugeicons:money-send-square" class="menu-icon"></iconify-icon>
                                </a>
                                @else
                                @role('admin')
                                <button type="button" title="Bayar" data-modal-target="edit-popup-modal-{{ $item->id }}" data-modal-toggle="edit-popup-modal-{{ $item->id }}" 
                                   class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                                </button>
                                @endrole
                                @role('spv')
                                -
                                @endrole
                                @endif
                            </td>
                            <td class="whitespace-nowrap"><a class="text-primary-600 cursor-pointer" href="{{ route('membertrainer.edit', $item->id) }}">{{ $item->kode_transaksi }}</a></td>
                            <td class="whitespace-nowrap">{{ $item->anggota->name ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->paketPersonalTrainer->nama_paket ?? '-' }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->paketPersonalTrainer->biaya, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->diskon, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->pembayaranMemberTrainers->sum('jumlah_bayar'), 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">
                                @if($item->status_pembayaran === 'Lunas')
                                    <span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm">Lunas</span>
                                @else
                                    <span class="bg-warning-100 text-warning-600 px-4 py-1.5 rounded-full font-medium text-sm">Belum Lunas</span>
                                @endif
                            </td>
                        </tr>
                        @role('admin')
                        <div id="edit-popup-modal-{{ $item->id }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="rounded-2xl bg-white max-w-[800px] w-full">
                                <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                                    <h1 class="text-xl">Tambah Pembayaran</h1>
                                    <button data-modal-hide="edit-popup-modal-{{ $item->id }}" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                
                                <div class="p-6">
                                    <form action="{{ route('pembayaran_trainer.tambahPembayaran', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                            </div>
                                            {{-- Kode Transaksi (readonly) --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Kode Transaksi</label>
                                                <input type="text" class="form-control" value="{{ $item->kode_transaksi }}" readonly>
                                            </div>
                                            {{-- Metode Pembayaran --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Metode Pembayaran</label>
                                                <select name="metode_pembayaran" class="form-control" required>
                                                    <option value="">-- Pilih Metode --</option>
                                                    <option value="cash" {{ old('metode_pembayaran') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="transfer" {{ old('metode_pembayaran') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                                    <option value="ewallet" {{ old('metode_pembayaran') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                                                </select>
                                            </div>

                                            {{-- Tanggal Bayar & Total Dibayarkan --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Tanggal Bayar</label>
                                                <input type="date" name="tgl_bayar" class="form-control" value="{{ old('tgl_bayar') }}">
                                            </div>
                                            <div class="col-span-12">
                                                <label class="form-label">Total Dibayarkan</label>
                                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" value="0">
                                            </div>
                                            <div class="col-span-12">
                                                <div class="flex items-center justify-start gap-3 mt-6">
                                                    <button type="button" data-modal-hide="edit-popup-modal-{{ $item->id }}" class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                                        Tambah
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endrole
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
                text: "Data Pembayaran Trainer yang dihapus tidak bisa dikembalikan!",
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
