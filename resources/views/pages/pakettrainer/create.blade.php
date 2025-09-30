@extends('layout.layout')
@php
    $title = 'Tambah Paket Personal Trainer';
    $subTitle = 'Tambah Paket Personal Trainer';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Tambah Paket Personal Trainer</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('paket_personal_trainer.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-12 gap-4">
                        {{-- Nama Paket --}}
                        <div class="col-span-12">
                            <label class="form-label">Nama Paket</label>
                            <input type="text" name="nama_paket" class="form-control" 
                                value="{{ old('nama_paket') }}" required>
                        </div>

                        {{-- Jumlah Sesi --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Jumlah Sesi</label>
                            <input type="number" name="jumlah_sesi" class="form-control" 
                                value="{{ old('jumlah_sesi') }}" min="1" required>
                        </div>

                        {{-- Durasi --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Durasi</label>
                            <input type="text" name="durasi" class="form-control" 
                                placeholder="Contoh: 60 menit" 
                                value="{{ old('durasi') }}" required>
                        </div>

                        {{-- Biaya --}}
                        <div class="col-span-12">
                            <label class="form-label">Biaya</label>
                            <input type="number" name="biaya" class="form-control" 
                                value="{{ old('biaya') }}" min="0" required>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Simpan Paket</button>
                                <a href="{{ route('paket_personal_trainer.index') }}" 
                                    class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 
                                    border border-danger-600 hover:text-white focus:text-white 
                                    focus:ring-4 focus:outline-none focus:ring-danger-300 
                                    font-medium rounded-lg text-base px-6 py-3 text-center 
                                    inline-flex items-center dark:text-danger-400 
                                    dark:hover:text-white dark:focus:text-white 
                                    dark:focus:ring-danger-800">Batal</a>
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
