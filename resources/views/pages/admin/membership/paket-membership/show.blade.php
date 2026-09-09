@extends('layout.layout')
@php
    $title = 'Detail Paket Membership';
    $subTitle = 'Detail Paket Membership';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Detail Paket Membership</h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        @if(session('success'))
                            <x-alert type="success">
                                {{ session('success') }}
                                <a href="{{ route('paket_membership.index') }}" class="text-success-600 focus:bg-success-600 hover:bg-success-700 border border-success-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-success-300 rounded-lg text-sm px-4 py-1 text-center inline-flex items-center dark:text-success-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-success-800">Kembali</a>
                            </x-alert>
                        @endif
                        @if(session('danger'))
                            <x-alert type="danger">{{ session('danger') }}</x-alert>
                        @endif
                    </div>

                    {{-- Dropdown Kategori --}}
                    <div class="col-span-12">
                        <label class="form-label">Kategori Paket</label>
                        <select name="id_kategori" id="id_kategori" class="form-control" disabled>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori as $item)
                                <option value="{{ $item->id }}"
                                    {{ $paket_membership->id_kategori == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama Paket --}}
                    <div class="col-span-12">
                        <label class="form-label">Nama Paket</label>
                        <input type="text" name="nama_paket" class="form-control" value="{{ $paket_membership->nama_paket }}" readonly>
                    </div>

                    {{-- Durasi & Periode --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Durasi</label>
                        <input type="number" name="durasi" class="form-control" value="{{ $paket_membership->durasi }}" readonly>
                    </div>

                    <div class="col-span-12 md:col-span-6">
                        <label for="periode" class="form-label">Periode</label>
                        <select name="periode" id="periode" class="form-control" disabled>
                            <option value="hari" {{ $paket_membership->periode == 'hari' ? 'selected' : '' }}>Hari</option>
                            <option value="minggu" {{ $paket_membership->periode == 'minggu' ? 'selected' : '' }}>Minggu</option>
                            <option value="bulan" {{ $paket_membership->periode == 'bulan' ? 'selected' : '' }}>Bulan</option>
                            <option value="tahun" {{ $paket_membership->periode == 'tahun' ? 'selected' : '' }}>Tahun</option>
                        </select>
                    </div>

                    {{-- Harga --}}
                    <div class="col-span-12">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" class="form-control" value="{{ $paket_membership->harga }}" readonly>
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-span-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" readonly>{{ $paket_membership->keterangan }}</textarea>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-span-12">
                        <div class="form-group flex items-center justify-end gap-2">
                            <a href="{{ route('paket_membership.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- card end -->
    </div>
</div>
@endsection
