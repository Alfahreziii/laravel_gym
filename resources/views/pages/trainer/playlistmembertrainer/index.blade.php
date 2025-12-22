@extends('layout.layout')

@php
    $title='Riwayat Gym Member';
    $subTitle = 'Riwayat Gym Member';
@endphp

@section('content')

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

<div class="grid grid-cols-12 gap-6">
    <!-- Info Member -->
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Informasi Member</h6>
                <a href="{{ route('trainer.dashboard') }}" class="text-neutral-600 hover:text-primary-600">
                    <iconify-icon icon="ion:arrow-back" class="text-2xl"></iconify-icon>
                </a>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Nama Member</p>
                        <h6 class="font-semibold text-neutral-900">{{ $memberTrainer->anggota->name }}</h6>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Paket Training</p>
                        <h6 class="font-semibold text-neutral-900">{{ $memberTrainer->paketPersonalTrainer->nama_paket }}</h6>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Progress Sesi</p>
                        <h6 class="font-semibold text-neutral-900">
                            {{ $memberTrainer->sesi }} / {{ $memberTrainer->paketPersonalTrainer->jumlah_sesi }} Sesi
                            <span class="text-sm text-neutral-600">(Sisa: {{ $memberTrainer->sisa_sesi }})</span>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Sesi -->
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header">
                <h6 class="card-title mb-0 text-lg">Riwayat Training per Sesi</h6>
            </div>
            <div class="card-body">
                @if($history->isEmpty())
                    <div class="text-center py-12">
                        <iconify-icon icon="mdi:clipboard-text-off-outline" class="text-6xl text-neutral-400 mb-4"></iconify-icon>
                        <h5 class="text-xl font-semibold text-neutral-700 mb-2">Belum Ada Riwayat Training</h5>
                        <p class="text-neutral-600">Member ini belum memiliki riwayat training yang tercatat.</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($history as $sesiKe => $playlists)
                            <div class="border border-neutral-200 rounded-lg overflow-hidden mb-5">
                                <!-- Header Sesi -->
                                <div class="bg-primary-50 border-b border-primary-200 px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <h6 class="font-semibold text-lg text-neutral-900">
                                            <iconify-icon icon="mdi:weight-lifter" class="text-primary-600 mr-2"></iconify-icon>
                                            Sesi ke-{{ $sesiKe }}
                                        </h6>
                                        <span class="text-sm text-neutral-600">
                                            {{ $playlists->count() }} Latihan
                                        </span>
                                    </div>
                                </div>

                                <!-- Daftar Playlist -->
                                <div class="p-6">
                                    <div class="space-y-4">
                                        @foreach($playlists as $playlist)
                                            <div class="flex items-start gap-4 p-4 rounded-lg border border-neutral-200">

                                                <!-- Content -->
                                                <div class="flex-1">
                                                    <h6 class="font-semibold text-base text-neutral-900 mb-2">
                                                        {{ $playlist->playlistTrainer->latihan }}
                                                    </h6>
                                                    
                                                    @if($playlist->keterangan)
                                                        <div class="bg-white rounded-lg mt-2">
                                                            <p class="text-xs text-neutral-600 mb-1 font-medium">Keterangan:</p>
                                                            <p class="text-sm text-neutral-700">{{ $playlist->keterangan }}</p>
                                                        </div>
                                                    @else
                                                        <p class="text-sm text-neutral-500 italic">Tidak ada keterangan</p>
                                                    @endif
                                                </div>

                                                <!-- Timestamp -->
                                                <div class="flex-shrink-0 text-right">
                                                    <p class="text-xs text-neutral-500">
                                                        {{ $playlist->created_at->format('d M Y') }}
                                                    </p>
                                                    <p class="text-xs text-neutral-500">
                                                        {{ $playlist->created_at->format('H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle alert close button
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