@extends('layout.layout')
@php
    $title = 'Tambah Trainer';
    $subTitle = 'Tambah Trainer';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Tambah Trainer & Akun Login</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('trainer.store') }}" method="POST" enctype="multipart/form-data">
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

                        {{-- ========== DATA TRAINER ========== --}}
                        <div class="col-span-12 mt-4">
                            <hr>
                            <h5 class="text-md font-semibold my-3 text-primary-600">👤 Data Trainer</h5>
                        </div>

                        {{-- RFID --}}
                        <div class="col-span-12">
                            <label class="form-label">RFID <span class="text-danger-600">*</span></label>
                            <input type="text" name="rfid" class="form-control @error('rfid') is-invalid @enderror" 
                                value="{{ old('rfid') }}" required>
                            @error('rfid')
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

                        {{-- Spesialisasi --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Spesialisasi <span class="text-danger-600">*</span></label>
                            <select name="id_specialisasi" class="form-control @error('id_specialisasi') is-invalid @enderror" required>
                                <option value="">-- Pilih Spesialisasi --</option>
                                @foreach($specialisasis as $specialisasi)
                                    <option value="{{ $specialisasi->id }}" {{ old('id_specialisasi') == $specialisasi->id ? 'selected' : '' }}>
                                        {{ $specialisasi->nama_specialisasi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_specialisasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Experience --}}
                        <div class="col-span-12">
                            <label class="form-label">Pengalaman <span class="text-danger-600">*</span></label>
                            <input type="text" name="experience" class="form-control @error('experience') is-invalid @enderror" 
                                value="{{ old('experience') }}" required placeholder="contoh: 5 Tahun">
                            @error('experience')
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

                        {{-- Jenis Kelamin --}}
                        <div class="col-span-12 md:col-span-4">
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

                        {{-- Alamat --}}
                        <div class="col-span-12">
                            <label class="form-label">Alamat <span class="text-danger-600">*</span></label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                                rows="3" required>{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Gabung --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Gabung <span class="text-danger-600">*</span></label>
                            <input type="date" name="tgl_gabung" class="form-control @error('tgl_gabung') is-invalid @enderror" 
                                value="{{ old('tgl_gabung', date('Y-m-d')) }}" required>
                            @error('tgl_gabung')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-span-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                value="{{ old('keterangan') }}">
                            @error('keterangan')
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

                        {{-- ========== JADWAL TRAINER ========== --}}
                        <div class="col-span-12 mt-4">
                            <hr>
                            <h5 class="text-md font-semibold my-3 text-primary-600">📅 Jadwal Trainer</h5>
                        </div>

                        <div class="col-span-12 mb-2">
                            <label class="form-label">Jadwal Trainer:</label>
                            <div id="jadwal-container">
                                <div class="jadwal-item grid grid-cols-12 gap-4 mb-2">
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="form-label">Hari</label>
                                        <select name="jadwal[0][day_of_week]" class="form-control" required>
                                            <option value="">-- Pilih Hari --</option>
                                            <option value="Senin">Senin</option>
                                            <option value="Selasa">Selasa</option>
                                            <option value="Rabu">Rabu</option>
                                            <option value="Kamis">Kamis</option>
                                            <option value="Jumat">Jumat</option>
                                            <option value="Sabtu">Sabtu</option>
                                            <option value="Minggu">Minggu</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="form-label">Jam Mulai</label>
                                        <input type="time" name="jadwal[0][start_time]" class="form-control" required>
                                    </div>
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" name="jadwal[0][end_time]" class="form-control" required>
                                    </div>
                                    <div class="col-span-12 md:col-span-3 flex items-end">
                                        <button type="button" class="btn-remove-jadwal text-danger-600 w-full focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3">Hapus</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-jadwal" class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                                Tambah Jadwal
                            </button>
                        </div>

                        {{-- Tombol --}}
                        <div class="col-span-12 mt-4">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Simpan Trainer & Akun</button>
                                <a href="{{ route('trainer.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3">Batal</a>
                            </div>
                        </div>

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
    let index = 1;

    const hariOptions = `
        <option value="">-- Pilih Hari --</option>
        <option value="Senin">Senin</option>
        <option value="Selasa">Selasa</option>
        <option value="Rabu">Rabu</option>
        <option value="Kamis">Kamis</option>
        <option value="Jumat">Jumat</option>
        <option value="Sabtu">Sabtu</option>
        <option value="Minggu">Minggu</option>
    `;

    const addBtn = document.getElementById('add-jadwal');
    const container = document.getElementById('jadwal-container');

    // Tambah jadwal
    addBtn.addEventListener('click', function() {
        const html = `
        <div class="jadwal-item grid grid-cols-12 gap-4 mb-2">
            <div class="col-span-12 md:col-span-3">
                <label class="form-label">Hari</label>
                <select name="jadwal[${index}][day_of_week]" class="form-control" required>
                    ${hariOptions}
                </select>
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="form-label">Jam Mulai</label>
                <input type="time" name="jadwal[${index}][start_time]" class="form-control" required>
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="form-label">Jam Selesai</label>
                <input type="time" name="jadwal[${index}][end_time]" class="form-control" required>
            </div>
            <div class="col-span-12 md:col-span-3 flex items-end">
                <button type="button" class="btn-remove-jadwal text-danger-600 w-full focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3">Hapus</button>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        index++;
    });

    // Hapus jadwal (event delegation)
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-jadwal')) {
            e.target.closest('.jadwal-item').remove();
        }
    });
});
</script>
@endsection