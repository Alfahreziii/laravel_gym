@extends('layout.layout')
@php
    $title = 'Profile Saya';
    $subTitle = 'Profile Member';
@endphp

@section('content')

@if(session('success'))
    <div class="alert alert-success bg-success-50 text-success-600 px-6 py-3 mb-4 rounded-lg flex items-center justify-between">
        {{ session('success') }}
        <button class="remove-button text-success-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger bg-danger-100 text-danger-600 px-6 py-3 mb-4 rounded-lg flex items-center justify-between">
        {{ session('error') }}
        <button class="remove-button text-danger-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="grid grid-cols-12 gap-5">
    <!-- Kolom Kiri: Info Profile & Barcode -->
    <div class="col-span-12 lg:col-span-4">
        <!-- Card Profile -->
        <div class="card border-0 mb-5">
            <div class="card-body text-center">
                <!-- Foto Profile -->
                <div class="mb-4">
                    @if($anggota->user && $anggota->user->photo)
                        <img src="{{ asset('storage/' . $anggota->user->photo) }}" 
                             alt="Photo {{ $anggota->name }}" 
                             class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-primary-200 shadow-lg">
                    @else
                        <div class="w-32 h-32 rounded-full bg-primary-100 flex items-center justify-center mx-auto border-4 border-primary-200">
                            <iconify-icon icon="mdi:account" class="text-6xl text-primary-600"></iconify-icon>
                        </div>
                    @endif
                </div>

                <!-- Nama & Status -->
                <h4 class="text-2xl font-bold text-neutral-800 mb-2">{{ $anggota->name }}</h4>
                <p class="text-neutral-600 mb-3">{{ $anggota->user ? $anggota->user->email : '-' }}</p>
                
                <!-- Status Membership -->
                @if($anggota->status_keanggotaan)
                    <span class="bg-success-100 text-success-600 px-6 py-2 rounded-full font-semibold text-sm inline-block mb-4">
                        ✓ Member Aktif
                    </span>
                @else
                    <span class="bg-warning-100 text-warning-600 px-6 py-2 rounded-full font-semibold text-sm inline-block mb-4">
                        ⚠ Membership Tidak Aktif
                    </span>
                @endif

                <!-- Info Membership -->
                @if($anggota->active_membership)
                <div class="bg-primary-50 rounded-lg p-4 mb-4">
                    <div class="text-sm text-neutral-600 mb-1">Paket Aktif</div>
                    <div class="font-bold text-primary-600 text-lg">{{ $anggota->active_membership->paketMembership->nama_paket }}</div>
                    <div class="text-xs text-neutral-500 mt-2">
                        Berlaku s/d: {{ $anggota->active_membership->tgl_selesai->format('d M Y') }}
                    </div>
                </div>
                @endif

                <!-- Statistik Singkat -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-info-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-info-600">{{ $totalKehadiran }}</div>
                        <div class="text-xs text-neutral-600">Total Kunjungan</div>
                    </div>
                    <div class="bg-success-50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-success-600">{{ $kehadiranBulanIni }}</div>
                        <div class="text-xs text-neutral-600">Bulan Ini</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-2">
                    <button type="button" data-modal-target="barcode-modal" data-modal-toggle="barcode-modal"
                            class="btn bg-primary-600 text-white hover:bg-primary-700 w-full">
                        <iconify-icon icon="mdi:barcode-scan" class="text-xl"></iconify-icon>
                        <span class="ml-2">Lihat Barcode Saya</span>
                    </button>
                    <a href="{{ route('member.download-card') }}" 
                       class="btn bg-success-600 text-white hover:bg-success-700 w-full">
                        <iconify-icon icon="mdi:card-account-details" class="text-xl"></iconify-icon>
                        <span class="ml-2">Download Kartu Member</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Detail Info & Riwayat -->
    <div class="col-span-12 lg:col-span-8">
        <!-- Card Data Pribadi -->
        <div class="card border-0 mb-5">
            <div class="card-header bg-primary-600 text-white">
                <h6 class="text-lg font-semibold mb-0">📋 Data Pribadi</h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">ID Kartu</div>
                        <div class="font-semibold text-neutral-800 text-lg">{{ $anggota->id_kartu }}</div>
                    </div>
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">No. Telepon</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->no_telp }}</div>
                    </div>
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Jenis Kelamin</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->jenis_kelamin }}</div>
                    </div>
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Tanggal Lahir</div>
                        <div class="font-semibold text-neutral-800">
                            {{ $anggota->tgl_lahir->format('d M Y') }} ({{ $anggota->age }} tahun)
                        </div>
                    </div>
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Tempat Lahir</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->tempat_lahir }}</div>
                    </div>
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Golongan Darah</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->gol_darah }}</div>
                    </div>
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Tinggi Badan</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->tinggi }} cm</div>
                    </div>
                    <div class="border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Berat Badan</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->berat }} kg</div>
                    </div>
                    <div class="col-span-1 md:col-span-2 border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Alamat</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->alamat }}</div>
                    </div>
                    <div class="col-span-1 md:col-span-2 border-b border-neutral-200 pb-3">
                        <div class="text-sm text-neutral-500 mb-1">Tanggal Daftar</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->tgl_daftar->format('d M Y') }}</div>
                    </div>
                    @if($anggota->riwayat_kesehatan)
                    <div class="col-span-1 md:col-span-2">
                        <div class="text-sm text-neutral-500 mb-1">Riwayat Kesehatan</div>
                        <div class="font-semibold text-neutral-800">{{ $anggota->riwayat_kesehatan }}</div>
                    </div>
                    @endif
                </div>

                @if($anggota->bmi)
                <div class="mt-4 bg-info-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-neutral-600 mb-1">Body Mass Index (BMI)</div>
                            <div class="text-2xl font-bold text-info-600">{{ $anggota->bmi }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-neutral-500">Kategori</div>
                            <div class="font-semibold text-neutral-700">
                                @if($anggota->bmi < 18.5)
                                    Kurus
                                @elseif($anggota->bmi < 25)
                                    Normal
                                @elseif($anggota->bmi < 30)
                                    Gemuk
                                @else
                                    Obesitas
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Card Riwayat Kehadiran -->
        <div class="card border-0">
            <div class="card-header bg-success-600 text-white">
                <h6 class="text-lg font-semibold mb-0">📊 Riwayat Kehadiran Terakhir</h6>
            </div>
            <div class="card-body">
                @if($anggota->kehadirans->count() > 0)
                <div class="space-y-3">
                    @foreach($anggota->kehadirans->take(10) as $kehadiran)
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center gap-3">
                            @if($kehadiran->foto)
                                <img src="{{ asset('storage/' . $kehadiran->foto) }}" 
                                     alt="Foto Absensi" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-300 flex items-center justify-center">
                                    <iconify-icon icon="mdi:account" class="text-gray-600"></iconify-icon>
                                </div>
                            @endif
                            <div>
                                <div class="font-semibold text-neutral-800">
                                    {{ $kehadiran->created_at->format('d M Y') }}
                                </div>
                                <div class="text-sm text-neutral-500">
                                    {{ $kehadiran->created_at->format('H:i:s') }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($kehadiran->status === 'in')
                                <span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full text-sm font-semibold">
                                    CHECK IN
                                </span>
                            @else
                                <span class="bg-warning-100 text-warning-600 px-4 py-1.5 rounded-full text-sm font-semibold">
                                    CHECK OUT
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-neutral-500">
                    <iconify-icon icon="mdi:calendar-remove" class="text-6xl mb-3"></iconify-icon>
                    <p>Belum ada riwayat kehadiran</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Barcode -->
<div id="barcode-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[600px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Barcode Kartu Member Saya</h1>
            <button data-modal-hide="barcode-modal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        <div class="p-6 text-center">
            <!-- Info Member -->
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-neutral-800 mb-2">{{ $anggota->name }}</h3>
                <p class="text-neutral-600 font-semibold text-lg">ID: {{ $anggota->id_kartu }}</p>
            </div>

            <!-- Barcode Display -->
            <div class="bg-white border-4 border-primary-200 rounded-lg p-6 mb-4 inline-block">
                {!! DNS1D::getBarcodeHTML($anggota->id_kartu, 'C128', 3, 150) !!}
                <div class="text-center font-mono font-bold text-xl mt-3 tracking-widest">
                    {{ $anggota->id_kartu }}
                </div>
            </div>

            <!-- Instruksi -->
            <div class="bg-info-50 rounded-lg p-4 text-left">
                <h6 class="font-semibold text-info-800 mb-2">📱 Cara Menggunakan:</h6>
                <ol class="list-decimal list-inside text-sm text-neutral-600 space-y-1">
                    <li>Tunjukkan barcode ini kepada staff GYM</li>
                    <li>Staff akan scan barcode dengan scanner</li>
                    <li>Absensi Anda akan tercatat otomatis</li>
                    <li>Atau download kartu member untuk dicetak</li>
                </ol>
            </div>

            <!-- Tombol Print -->
            <div class="mt-6 flex gap-3 justify-center">
                <button onclick="window.print()" class="btn bg-primary-600 text-white hover:bg-primary-700">
                    <iconify-icon icon="mdi:printer" class="mr-2"></iconify-icon>
                    Print Barcode
                </button>
                <a href="{{ route('member.download-card') }}" class="btn bg-success-600 text-white hover:bg-success-700">
                    <iconify-icon icon="mdi:download" class="mr-2"></iconify-icon>
                    Download Kartu
                </a>
            </div>
        </div>
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

// Print styles untuk barcode
@media print {
    body * {
        visibility: hidden;
    }
    #barcode-modal, #barcode-modal * {
        visibility: visible;
    }
    #barcode-modal {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</script>
@endsection