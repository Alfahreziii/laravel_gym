@extends('layout.layout')
@php
    $title = 'Edit Anggota';
    $subTitle = 'Edit Anggota';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Edit Anggota</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('anggota.update', $anggota->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            @if(session('success'))
                                <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
                                    text-success-600 dark:text-success-400 border-success-50 
                                    px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        {{ session('success') }}
                                        <a href="{{ route('anggota.index') }}" class="text-success-600 focus:bg-success-600 hover:bg-success-700 border border-success-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-success-300 rounded-lg text-sm px-4 py-1 text-center inline-flex items-center dark:text-success-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-success-800">Kembali</a>
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
                        </div>

                        <div class="col-span-12">
                            <label class="form-label">ID Kartu</label>
                            <input type="text" name="id_kartu" class="form-control" value="{{ old('id_kartu', $anggota->id_kartu) }}" required>
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $anggota->name) }}" required>
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Jenis Kelamin</label>
                            <input type="text" name="jenis_kelamin" class="form-control" value="{{ old('jenis_kelamin', $anggota->jenis_kelamin) }}" required>
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $anggota->no_telp) }}">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $anggota->tempat_lahir) }}" required>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control"
                                value="{{ old('tgl_lahir', \Carbon\Carbon::parse($anggota->tgl_lahir)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Tanggal Daftar</label>
                            <input type="date" name="tgl_daftar" class="form-control"
                                value="{{ old('tgl_daftar', \Carbon\Carbon::parse($anggota->tgl_daftar)->format('Y-m-d')) }}">
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <label class="form-label">Gol. Darah</label>
                            <input type="text" name="gol_darah" class="form-control" value="{{ old('gol_darah', $anggota->gol_darah) }}">
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label class="form-label">Tinggi</label>
                            <input type="text" name="tinggi" class="form-control" value="{{ old('tinggi', $anggota->tinggi) }}">
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label class="form-label">Berat</label>
                            <input type="text" name="berat" class="form-control" value="{{ old('berat', $anggota->berat) }}">
                        </div>

                        <div class="col-span-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $anggota->alamat) }}</textarea>
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Riwayat Kesehatan</label>
                            <textarea name="riwayat_kesehatan" class="form-control" rows="3">{{ old('riwayat_kesehatan', $anggota->riwayat_kesehatan) }}</textarea>
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Foto </label>
                            <input class="border border-neutral-200 w-full rounded-lg" type="file" name="photo">
                            @if($anggota->photo)
                                <img src="{{ asset('storage/' . $anggota->photo) }}" alt="Foto Anggota" class="mt-2 w-24 h-24 object-cover rounded">
                            @endif
                        </div>
                        
                        <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Update Anggota</button>
                                <a href="{{ route('anggota.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- card end -->
    </div>
</div>
@endsection
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
