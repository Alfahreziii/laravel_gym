@extends('layout.layout')
@php
    $title = 'Edit Trainer';
    $subTitle = 'Edit Trainer';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Edit Trainer</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('trainer.update', $trainer->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-12 gap-4">

                        {{-- RFID --}}
                        <div class="col-span-12">
                            <label class="form-label">RFID</label>
                            <input type="text" name="rfid" class="form-control"
                                value="{{ old('rfid', $trainer->rfid) }}" required>
                        </div>

                        {{-- Nama --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $trainer->name) }}" required>
                        </div>

                        {{-- No Telepon --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">No Telepon</label>
                            <input type="text" name="no_telp" class="form-control"
                                value="{{ old('no_telp', $trainer->no_telp) }}" required>
                        </div>

                        {{-- Spesialisasi --}}
                        <div class="col-span-12">
                            <label class="form-label">Spesialisasi</label>
                            <select name="id_specialisasi" class="form-control" required>
                                <option value="">-- Pilih Spesialisasi --</option>
                                @foreach($specialisasis as $specialisasi)
                                    <option value="{{ $specialisasi->id }}"
                                        {{ old('id_specialisasi', $trainer->id_specialisasi) == $specialisasi->id ? 'selected' : '' }}>
                                        {{ $specialisasi->nama_specialisasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Experience --}}
                        <div class="col-span-12">
                            <label class="form-label">Pengalaman</label>
                            <input type="text" name="experience" class="form-control"
                                value="{{ old('experience', $trainer->experience) }}" required>
                        </div>

                        {{-- Tempat Lahir --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control"
                                value="{{ old('tempat_lahir', $trainer->tempat_lahir) }}" required>
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control"
                                value="{{ old('tgl_lahir', \Carbon\Carbon::parse($trainer->tgl_lahir)->format('Y-m-d')) }}" required>
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $trainer->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $trainer->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        {{-- Alamat --}}
                        <div class="col-span-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat', $trainer->alamat) }}</textarea>
                        </div>

                        {{-- Tanggal Gabung --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Gabung</label>
                            <input type="date" name="tgl_gabung" class="form-control"
                                value="{{ old('tgl_gabung', \Carbon\Carbon::parse($trainer->tgl_gabung)->format('Y-m-d')) }}" required>
                        </div>

                        {{-- Status --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif" {{ old('status', $trainer->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status', $trainer->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-span-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control"
                                value="{{ old('keterangan', $trainer->keterangan) }}">
                        </div>

                        {{-- Foto --}}
                        <div class="col-span-12">
                            <label class="form-label">Foto</label>
                            @if($trainer->photo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$trainer->photo) }}" alt="Foto Trainer" class="h-24 rounded-md">
                                </div>
                            @endif
                            <input class="border border-neutral-200 w-full rounded-lg" type="file" name="photo">
                            <small class="text-gray-500">Kosongkan jika tidak ingin mengganti foto</small>
                        </div>

                        {{-- Jadwal Trainer --}}
                        <hr class="col-span-12">
                        <div class="col-span-12 mb-2">
                            <label class="form-label">Jadwal Trainer :</label>
                            <div id="jadwal-container">
                                @foreach($trainer->schedules as $i => $jadwal)
                                    <div class="jadwal-item grid grid-cols-12 gap-4 mb-2">
                                        <div class="col-span-12 md:col-span-3">
                                            <label class="form-label w-full text-end">Hari</label>
                                            <select name="jadwal[{{ $i }}][day_of_week]" class="form-control" required>
                                                <option value="">-- Pilih Hari --</option>
                                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari)
                                                    <option value="{{ $hari }}" {{ $jadwal->day_of_week == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-12 md:col-span-3">
                                            <label class="form-label w-full text-end">Jam Mulai</label>
                                            <input type="time" name="jadwal[{{ $i }}][start_time]" class="form-control" value="{{ $jadwal->start_time }}" required>
                                        </div>
                                        <div class="col-span-12 md:col-span-3">
                                            <label class="form-label w-full text-end">Jam Selesai</label>
                                            <input type="time" name="jadwal[{{ $i }}][end_time]" class="form-control" value="{{ $jadwal->end_time }}" required>
                                        </div>
                                        <div class="col-span-12 md:col-span-3 flex items-end">
                                            <button type="button" class="btn-remove-jadwal text-danger-600 w-full border border-danger-600 rounded-lg px-6 py-3">Hapus</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="add-jadwal"
                                class="text-primary-600 border border-primary-600 rounded-lg px-5 py-2">Tambah Jadwal</button>
                        </div>

                        {{-- Tombol --}}
                        <div class="col-span-12 mt-4">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Update Trainer</button>
                                <a href="{{ route('trainer.index') }}" class="text-danger-600 border border-danger-600 rounded-lg px-6 py-3">Kembali</a>
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
    let index = {{ $trainer->schedules->count() }};

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

    addBtn.addEventListener('click', function() {
        const html = `
        <div class="jadwal-item grid grid-cols-12 gap-4 mb-2">
            <div class="col-span-12 md:col-span-3">
                <label>Hari</label>
                <select name="jadwal[${index}][day_of_week]" class="form-control" required>
                    ${hariOptions}
                </select>
            </div>
            <div class="col-span-12 md:col-span-3">
                <label>Jam Mulai</label>
                <input type="time" name="jadwal[${index}][start_time]" class="form-control" required>
            </div>
            <div class="col-span-12 md:col-span-3">
                <label>Jam Selesai</label>
                <input type="time" name="jadwal[${index}][end_time]" class="form-control" required>
            </div>
            <div class="col-span-12 md:col-span-3 flex items-end">
                <button type="button" class="btn-remove-jadwal text-danger-600 border w-full border-danger-600 rounded-lg px-6 py-3">Hapus</button>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        index++;
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-jadwal')) {
            e.target.closest('.jadwal-item').remove();
        }
    });
});
</script>
@endsection
