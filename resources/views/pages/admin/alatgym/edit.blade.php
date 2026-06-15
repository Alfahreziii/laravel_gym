@extends('layout.layout')
@php
    $title = 'Edit Alat Gym';
    $subTitle = 'Edit Alat Gym';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Edit Alat Gym</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('alat_gym.update', $alatgym->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-12 gap-4">
                        {{-- Alert success / danger --}}
                        <div class="col-span-12">
                            @if(session('success'))
                                <div class="alert alert-success ...">
                                    <div class="flex items-center gap-4">
                                        {{ session('success') }}
                                        <a href="{{ route('anggota.index') }}" class="...">Kembali</a>
                                    </div>
                                    <button class="remove-button text-success-600 text-2xl"> 
                                        <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
                                    </button>
                                </div>
                            @endif

                            @if(session('danger'))
                                <div class="alert alert-danger ...">
                                    {{ session('danger') }}
                                    <button class="remove-button text-danger-600 text-2xl"> 
                                        <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Barcode --}}
                        <div class="col-span-12">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $alatgym->barcode) }}" required>
                            @error('barcode')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Nama Alat Gym --}}
                        <div class="col-span-12">
                            <label class="form-label">Nama Alat Gym</label>
                            <input type="text" name="nama_alat_gym" class="form-control" value="{{ old('nama_alat_gym', $alatgym->nama_alat_gym) }}" required>
                            @error('nama_alat_gym')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Jumlah --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="{{ old('jumlah', $alatgym->jumlah) }}" min="0" required>
                            @error('jumlah')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Harga --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Harga</label>
                            <input type="number" name="harga" class="form-control" value="{{ old('harga', $alatgym->harga) }}" min="0" required>
                            @error('harga')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Tanggal Pembelian --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Pembelian</label>
                            <input type="date" name="tgl_pembelian" class="form-control" value="{{ old('tgl_pembelian', \Carbon\Carbon::parse($alatgym->tgl_pembelian)->format('Y-m-d')) }}">
                            @error('tgl_pembelian')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Kondisi Alat --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Kondisi Alat</label>
                            <select name="kondisi_alat" class="form-control">
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="Baik" {{ old('kondisi_alat', $alatgym->kondisi_alat) == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Rusak" {{ old('kondisi_alat', $alatgym->kondisi_alat) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="Perbaikan" {{ old('kondisi_alat', $alatgym->kondisi_alat) == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                            </select>
                            @error('kondisi_alat')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Kontak --}}
                        <div class="col-span-12">
                            <label class="form-label">Kontak</label>
                            <input type="text" name="kontak" class="form-control" value="{{ old('kontak', $alatgym->kontak) }}">
                            @error('kontak')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Lokasi Alat --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Lokasi Alat</label>
                            <input type="text" name="lokasi_alat" class="form-control" value="{{ old('lokasi_alat', $alatgym->lokasi_alat) }}">
                            @error('lokasi_alat')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Vendor --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Vendor</label>
                            <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $alatgym->vendor) }}">
                            @error('vendor')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-span-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $alatgym->keterangan) }}</textarea>
                            @error('keterangan')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Edit Alat Gym</button>
                                <a href="{{ route('alat_gym.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- card end -->
    </div>
</div>
@endsection
