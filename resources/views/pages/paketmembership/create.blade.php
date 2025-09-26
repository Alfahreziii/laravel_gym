@extends('layout.layout')
@php
    $title = 'Tambah Paket Membership';
    $subTitle = 'Tambah Paket Membership';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Tambah Paket Membership</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('paket_membership.store') }}" method="POST">
                    @csrf
                    @method('POST')
                    
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            {{-- Success Message --}}
                            @if(session('success'))
                                <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
                                    text-success-600 dark:text-success-400 border-success-50 
                                    px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        {{ session('success') }}
                                        <a href="{{ route('paket_membership.index') }}" class="text-success-600 focus:bg-success-600 hover:bg-success-700 border border-success-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-success-300 rounded-lg text-sm px-4 py-1 text-center inline-flex items-center dark:text-success-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-success-800">Kembali</a>
                                    </div>
                                    <button class="remove-button text-success-600 text-2xl"> 
                                        <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
                                    </button>
                                </div>
                            @endif

                            {{-- Error Message --}}
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

                        {{-- Dropdown Kategori --}}
                        <div class="col-span-12">
                            <label class="form-label">Kategori Paket</label>
                            <select name="id_kategori" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriPaket as $kategori)
                                    <option value="{{ $kategori->id }}" 
                                        {{ old('id_kategori') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nama Paket --}}
                        <div class="col-span-12">
                            <label class="form-label">Nama Paket</label>
                            <input type="text" name="nama_paket" class="form-control" value="{{ old('nama_paket') }}" required>
                        </div>

                        {{-- Durasi & Periode --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Durasi</label>
                            <input type="number" name="durasi" class="form-control" value="{{ old('durasi') }}" required>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Periode</label>
                            <select name="periode" class="form-control" required>
                                <option value="">-- Pilih Periode --</option>
                                <option value="hari" {{ old('periode') == 'hari' ? 'selected' : '' }}>Hari</option>
                                <option value="minggu" {{ old('periode') == 'minggu' ? 'selected' : '' }}>Minggu</option>
                                <option value="bulan" {{ old('periode') == 'bulan' ? 'selected' : '' }}>Bulan</option>
                                <option value="tahun" {{ old('periode') == 'tahun' ? 'selected' : '' }}>Tahun</option>
                            </select>
                        </div>

                        {{-- Harga --}}
                        <div class="col-span-12">
                            <label class="form-label">Harga</label>
                            <input type="number" name="harga" class="form-control" value="{{ old('harga') }}" required>
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-span-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Simpan Paket</button>
                                <a href="{{ route('paket_membership.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Batal</a>
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
