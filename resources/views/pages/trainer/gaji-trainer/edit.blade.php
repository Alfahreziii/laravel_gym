@extends('layout.layout')
@php
    $title='Edit Parameter Gaji Trainer';
    $subTitle = 'Edit Parameter Gaji Trainer';
@endphp

@section('content')

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
                <h6 class="card-title mb-0 text-lg">Form Edit Parameter Gaji Trainer</h6>
                <a href="{{ route('gaji_trainer.index') }}" class="text-neutral-600 hover:text-primary-600">
                    <iconify-icon icon="ion:arrow-back" class="text-2xl"></iconify-icon>
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('gaji_trainer.update', $gajiTrainer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Trainer (Disabled) -->
                        <div class="col-span-2 md:col-span-1">
                            <label for="trainer_name" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                Nama Trainer
                            </label>
                            <input type="text" 
                                   id="trainer_name" 
                                   class="form-control rounded-lg bg-neutral-100" 
                                   value="{{ $gajiTrainer->trainer->name }}"
                                   disabled>
                            <p class="text-neutral-500 text-xs mt-1">Trainer tidak dapat diubah</p>
                        </div>

                        <!-- Pilih Level -->
                        <div class="col-span-2 md:col-span-1">
                            <label for="id_level" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                Level Trainer <span class="text-danger-600">*</span>
                            </label>
                            <select id="id_level" name="id_level" class="form-control rounded-lg @error('id_level') border-danger-600 @enderror" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" 
                                            {{ (old('id_level', $gajiTrainer->id_level) == $level->id) ? 'selected' : '' }}>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_level')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Base Rate -->
                        <div class="col-span-2 md:col-span-1">
                            <label for="base_rate" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                Base Rate per Sesi (Rp) <span class="text-danger-600">*</span>
                            </label>
                            <input type="number" 
                                   id="base_rate" 
                                   name="base_rate" 
                                   class="form-control rounded-lg @error('base_rate') border-danger-600 @enderror" 
                                   placeholder="Contoh: 50000"
                                   value="{{ old('base_rate', $gajiTrainer->base_rate) }}"
                                   min="0"
                                   step="1000"
                                   required>
                            @error('base_rate')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                            <p class="text-neutral-500 text-xs mt-1">Tarif per sesi training yang diberikan</p>
                        </div>

                        <!-- Tanggal Gajian -->
                        <div class="col-span-2 md:col-span-1">
                            <label for="tgl_gajian" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                Tanggal Gajian <span class="text-danger-600">*</span>
                            </label>
                            <input type="date" 
                                   id="tgl_gajian" 
                                   name="tgl_gajian" 
                                   class="form-control rounded-lg @error('tgl_gajian') border-danger-600 @enderror" 
                                   value="{{ old('tgl_gajian', $gajiTrainer->tgl_gajian->format('Y-m-d')) }}"
                                   required>
                            @error('tgl_gajian')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                            <p class="text-neutral-500 text-xs mt-1">Tanggal gajian setiap bulan (hari ke-berapa)</p>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-info-50 border border-info-200 rounded-lg p-4 mt-6">
                        <div class="flex items-start gap-3">
                            <iconify-icon icon="mdi:information" class="text-info-600 text-2xl"></iconify-icon>
                            <div>
                                <h6 class="font-semibold text-info-900 mb-1">Informasi</h6>
                                <p class="text-sm text-info-700">
                                    Base rate adalah tarif dasar per sesi training. Total gaji akan dihitung berdasarkan: 
                                    <strong>Base Rate × Jumlah Sesi yang Diselesaikan</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-start gap-3 mt-8 pt-6 border-t border-neutral-200">
                        <a href="{{ route('gaji_trainer.index') }}" 
                           class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                            <iconify-icon icon="material-symbols:save" class="text-xl mr-2"></iconify-icon>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove alert
    document.querySelectorAll('.remove-button').forEach(button => {
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