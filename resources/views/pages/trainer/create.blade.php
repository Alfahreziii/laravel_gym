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
                <h6 class="text-lg font-semibold mb-0">Form Tambah Trainer</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('trainer.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div class="grid grid-cols-12 gap-4">

                        {{-- RFID --}}
                        <div class="col-span-12">
                            <label class="form-label">RFID</label>
                            <input type="text" name="rfid" class="form-control" value="{{ old('rfid') }}" required>
                        </div>

                        {{-- Nama --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        {{-- No Telepon --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">No Telepon</label>
                            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp') }}" required>
                        </div>

                        {{-- Spesialisasi --}}
                        <div class="col-span-12">
                            <label class="form-label">Spesialisasi</label>
                            <select name="id_specialisasi" class="form-control" required>
                                <option value="">-- Pilih Spesialisasi --</option>
                                @foreach($specialisasis as $specialisasi)
                                    <option value="{{ $specialisasi->id }}" {{ old('id_specialisasi') == $specialisasi->id ? 'selected' : '' }}>
                                        {{ $specialisasi->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Experience --}}
                        <div class="col-span-12">
                            <label class="form-label">Pengalaman</label>
                            <input type="text" name="experience" class="form-control" value="{{ old('experience') }}" required>
                        </div>

                        {{-- Tempat Lahir --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}" required>
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" value="{{ old('tgl_lahir') }}" required>
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="col-span-12 md:col-span-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        {{-- Alamat --}}
                        <div class="col-span-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat') }}</textarea>
                        </div>

                        {{-- Tanggal Gabung --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Gabung</label>
                            <input type="date" name="tgl_gabung" class="form-control" value="{{ old('tgl_gabung') }}" required>
                        </div>

                        {{-- Status --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-span-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}">
                        </div>

                        {{-- Foto --}}
                        <div class="col-span-12">
                            <label class="form-label">Foto </label>
                            <input class="border border-neutral-200 w-full rounded-lg" type="file" name="photo" required>
                        </div>

                        {{-- Jadwal Trainer --}}
                        <hr class="col-span-12">
                        <div class="col-span-12 mb-2">
                            <label class="form-label">Jadwal Trainer :</label>
                            <div id="jadwal-container">
                                <div class="jadwal-item grid grid-cols-12 gap-4 mb-2">
                                    <div class="col-span-12 md:col-span-3">
                                        <label>Hari</label>
                                        <select name="jadwal[0][hari]" class="form-control" required>
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
                                        <label>Jam Mulai</label>
                                        <input type="time" name="jadwal[0][jam_mulai]" class="form-control" required>
                                    </div>
                                    <div class="col-span-12 md:col-span-3">
                                        <label>Jam Selesai</label>
                                        <input type="time" name="jadwal[0][jam_selesai]" class="form-control" required>
                                    </div>
                                    <div class="col-span-12 md:col-span-3 flex items-end">
                                        <button type="button" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Hapus</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-jadwal" class="btn btn-primary-600 mt-2">Tambah Jadwal</button>
                        </div>

                        {{-- Tombol --}}
                        <div class="col-span-12 mt-4">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Simpan Trainer</button>
                                <a href="{{ route('trainer.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Batal</a>
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
            <div class="col-span-12 md:col-span-4">
                <label>Hari</label>
                <select name="jadwal[${index}][hari]" class="form-control" required>
                    ${hariOptions}
                </select>
            </div>
            <div class="col-span-12 md:col-span-3">
                <label>Jam Mulai</label>
                <input type="time" name="jadwal[${index}][jam_mulai]" class="form-control" required>
            </div>
            <div class="col-span-12 md:col-span-3">
                <label>Jam Selesai</label>
                <input type="time" name="jadwal[${index}][jam_selesai]" class="form-control" required>
            </div>
            <div class="col-span-12 md:col-span-2 flex items-end">
                <button type="button" class="btn btn-danger btn-remove-jadwal w-full">Hapus</button>
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
