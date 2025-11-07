@extends('layout.layout')
@php
    $title = 'Detail Trainer';
    $subTitle = 'Detail Trainer';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Detail Trainer</h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-12 gap-4">

                    {{-- Foto --}}
                    <div class="col-span-12">
                        <label class="form-label">Foto</label>
                        @if($trainer->user->photo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$trainer->user->photo) }}" alt="Foto Trainer" class="h-24 rounded-md">
                            </div>
                        @else
                            <p>Tidak ada foto</p>
                        @endif
                    </div>

                    {{-- RFID --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">RFID</label>
                        <p class="form-control bg-gray-100">{{ $trainer->rfid }}</p>
                    </div>
                    {{-- Email --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Email</label>
                        <p class="form-control bg-gray-100">{{ $trainer->user->email }}</p>
                    </div>

                    {{-- Nama --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Nama</label>
                        <p class="form-control bg-gray-100">{{ $trainer->name }}</p>
                    </div>

                    {{-- No Telepon --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">No Telepon</label>
                        <p class="form-control bg-gray-100">{{ $trainer->no_telp }}</p>
                    </div>

                    {{-- Spesialisasi --}}
                    <div class="col-span-12">
                        <label class="form-label">Spesialisasi</label>
                        <p class="form-control bg-gray-100">{{ $trainer->specialisasi->nama_specialisasi ?? '-' }}</p>
                    </div>

                    {{-- Pengalaman --}}
                    <div class="col-span-12">
                        <label class="form-label">Pengalaman</label>
                        <p class="form-control bg-gray-100">{{ $trainer->experience }}</p>
                    </div>

                    {{-- Tempat Lahir --}}
                    <div class="col-span-12 md:col-span-4">
                        <label class="form-label">Tempat Lahir</label>
                        <p class="form-control bg-gray-100">{{ $trainer->tempat_lahir }}</p>
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div class="col-span-12 md:col-span-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <p class="form-control bg-gray-100">{{ \Carbon\Carbon::parse($trainer->tgl_lahir)->format('d-m-Y') }}</p>
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="col-span-12 md:col-span-4">
                        <label class="form-label">Jenis Kelamin</label>
                        <p class="form-control bg-gray-100">{{ $trainer->jenis_kelamin }}</p>
                    </div>

                    {{-- Alamat --}}
                    <div class="col-span-12">
                        <label class="form-label">Alamat</label>
                        <p class="form-control bg-gray-100">{{ $trainer->alamat }}</p>
                    </div>

                    {{-- Tanggal Gabung --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Tanggal Gabung</label>
                        <p class="form-control bg-gray-100">{{ \Carbon\Carbon::parse($trainer->tgl_gabung)->format('d-m-Y') }}</p>
                    </div>

                    {{-- Status --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Status</label>
                        <p class="form-control bg-gray-100">{{ ucfirst($trainer->status) }}</p>
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-span-12">
                        <label class="form-label">Keterangan</label>
                        <p class="form-control bg-gray-100">{{ $trainer->keterangan ?? '-' }}</p>
                    </div>

                    {{-- Jadwal Trainer --}}
                    <hr class="col-span-12">
                    <div class="col-span-12 mb-2">
                        <label class="form-label">Jadwal Trainer :</label>
                        <div class="grid grid-cols-12 gap-4">
                            @foreach($trainer->schedules as $jadwal)
                                <div class="col-span-12 md:col-span-4">
                                    <p class="form-control bg-gray-100">{{ $jadwal->day_of_week }} : {{ $jadwal->start_time }} - {{ $jadwal->end_time }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tombol Kembali --}}
                    <div class="col-span-12 mt-4">
                        <a href="{{ route('trainer.index') }}" class="text-danger-600 border border-danger-600 rounded-lg px-6 py-3">Kembali</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
