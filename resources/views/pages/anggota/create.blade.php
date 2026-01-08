@extends('layout.layout')
@php
    $title = 'Tambah Anggota';
    $subTitle = 'Tambah Anggota';
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
@if(session('error'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('error') }}
        <button class="remove-button text-danger-600 text-2xl"> 
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Tambah Anggota & Akun Login</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('anggota.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div class="grid grid-cols-12 gap-4">

                        {{-- ========== DATA USER / AKUN LOGIN ========== --}}
                        <div class="col-span-12">
                            <h5 class="text-md font-semibold mb-3 text-primary-600">📋 Data Akun Login</h5>
                        </div>

                        {{-- Nama Lengkap --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger-600">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Email <span class="text-danger-600">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Email ini akan digunakan untuk login</small>
                        </div>

                        {{-- Password --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Password <span class="text-danger-600">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Konfirmasi Password <span class="text-danger-600">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        {{-- ========== DATA ANGGOTA ========== --}}
                        <div class="col-span-12 mt-4">
                            <hr>
                            <h5 class="text-md font-semibold my-3 text-primary-600">👤 Data Anggota</h5>
                        </div>

                        {{-- ID Kartu --}}
                        <div class="col-span-12">
                            <label class="form-label">ID Kartu <span class="text-danger-600">*</span></label>
                            <input type="text" name="id_kartu" class="form-control @error('id_kartu') is-invalid @enderror" 
                                value="{{ old('id_kartu') }}" required>
                            @error('id_kartu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- No Telepon --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">No Telepon <span class="text-danger-600">*</span></label>
                            <input type="text" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror" 
                                value="{{ old('no_telp') }}" required>
                            @error('no_telp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Jenis Kelamin <span class="text-danger-600">*</span></label>
                            <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tempat Lahir --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tempat Lahir <span class="text-danger-600">*</span></label>
                            <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" 
                                value="{{ old('tempat_lahir') }}" required>
                            @error('tempat_lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tanggal Lahir <span class="text-danger-600">*</span></label>
                            <input type="date" name="tgl_lahir" class="form-control @error('tgl_lahir') is-invalid @enderror" 
                                value="{{ old('tgl_lahir') }}" required>
                            @error('tgl_lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Daftar --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tanggal Daftar <span class="text-danger-600">*</span></label>
                            <input type="date" name="tgl_daftar" class="form-control @error('tgl_daftar') is-invalid @enderror" 
                                value="{{ old('tgl_daftar', date('Y-m-d')) }}" required>
                            @error('tgl_daftar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Golongan Darah --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Golongan Darah <span class="text-danger-600">*</span></label>
                            <select name="gol_darah" class="form-control @error('gol_darah') is-invalid @enderror" required>
                                <option value="">-- Pilih Golongan Darah --</option>
                                <option value="A" {{ old('gol_darah') == 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ old('gol_darah') == 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ old('gol_darah') == 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ old('gol_darah') == 'O' ? 'selected' : '' }}>O</option>
                            </select>
                            @error('gol_darah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tinggi --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tinggi (cm) <span class="text-danger-600">*</span></label>
                            <input type="number" name="tinggi" class="form-control @error('tinggi') is-invalid @enderror" 
                                value="{{ old('tinggi') }}" required>
                            @error('tinggi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Berat --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Berat (kg) <span class="text-danger-600">*</span></label>
                            <input type="number" name="berat" class="form-control @error('berat') is-invalid @enderror" 
                                value="{{ old('berat') }}" required>
                            @error('berat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="col-span-12">
                            <label class="form-label">Alamat <span class="text-danger-600">*</span></label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                                rows="3" required>{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Riwayat Kesehatan --}}
                        <div class="col-span-12">
                            <label class="form-label">Riwayat Kesehatan</label>
                            <textarea name="riwayat_kesehatan" class="form-control @error('riwayat_kesehatan') is-invalid @enderror" 
                                rows="3">{{ old('riwayat_kesehatan') }}</textarea>
                            @error('riwayat_kesehatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Foto --}}
                        <div class="col-span-12">
                            <label class="form-label">Foto <span class="text-danger-600">*</span></label>
                            <input class="border border-neutral-200 w-full rounded-lg @error('photo') is-invalid @enderror" 
                                type="file" name="photo" accept="image/*" required>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tombol --}}
                        <div class="col-span-12 mt-4">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Simpan Anggota & Akun</button>
                                <a href="{{ route('anggota.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3">Batal</a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection