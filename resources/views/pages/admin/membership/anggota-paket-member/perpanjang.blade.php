@extends('layout.layout')
@php
    $title = $isPerpanjang ? 'Perpanjang Membership' : 'Buat Membership';
    $subTitle = $title;
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">{{ $title }} — {{ $anggota->name }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('anggota_membership.perpanjang.store', $anggota->id) }}" method="POST">
                    @csrf
                    @method('POST')

                    <div class="grid grid-cols-12 gap-4">
                        {{-- Alert success / danger --}}
                        <div class="col-span-12">
                            @if(session('success'))
                                <x-alert type="success">
                                    {{ session('success') }}
                                    <a href="{{ route('anggota_membership.index') }}" class="text-success-600 focus:bg-success-600 hover:bg-success-700 border border-success-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-success-300 rounded-lg text-sm px-4 py-1">Kembali</a>
                                </x-alert>
                            @endif
                            @if(session('error'))
                                <x-alert type="danger">{{ session('error') }}</x-alert>
                            @endif
                            @if(session('danger'))
                                <x-alert type="danger">{{ session('danger') }}</x-alert>
                            @endif
                            @if($errors->any())
                                <x-alert type="danger">
                                    <ul class="mb-0 ps-4" style="list-style: disc;">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </x-alert>
                            @endif
                        </div>

                        {{-- Anggota (terkunci) --}}
                        <div class="col-span-12">
                            <label class="form-label">Anggota</label>
                            <input type="text" class="form-control" value="{{ $anggota->name }}" readonly>
                        </div>

                        {{-- Paket Membership dengan Searchable Dropdown --}}
                        <div class="col-span-12">
                            <label class="form-label">Pilih Paket Membership</label>
                            <select
                                name="id_paket_membership"
                                id="paket"
                                class="form-control"
                                required
                                data-searchable="true"
                                data-placeholder="-- Pilih Paket --"
                                data-search-placeholder="Cari paket..."
                                data-no-results="Paket tidak ditemukan">
                                <option value="">-- Pilih Paket --</option>
                                @foreach($pakets as $paket)
                                    <option value="{{ $paket->id }}"
                                        data-durasi="{{ $paket->durasi }}"
                                        data-periode="{{ $paket->periode }}"
                                        data-harga="{{ $paket->harga }}"
                                        {{ old('id_paket_membership') == $paket->id ? 'selected' : '' }}>
                                        {{ $paket->nama_paket }} ({{ $paket->durasi }} {{ $paket->periode }}) - Rp {{ number_format($paket->harga,0,',','.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tgl Mulai & Selesai --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control"
                                min="{{ $floorDate->format('Y-m-d') }}" value="{{ old('tgl_mulai', $floorDate->format('Y-m-d')) }}" required>
                            @if ($isPerpanjang)
                                <small class="text-muted">Tidak bisa memilih tanggal sebelum {{ $floorDate->format('d-m-Y') }} karena masa aktif membership sebelumnya masih berjalan.</small>
                            @endif
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" value="{{ old('tgl_selesai') }}" readonly>
                        </div>

                        {{-- Diskon & Total Biaya --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Diskon (Rp)</label>
                            <input type="number" name="diskon" id="diskon" class="form-control" value="{{ old('diskon', 0) }}">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Total Biaya</label>
                            <input type="number" name="total_biaya" id="total_biaya" class="form-control" value="{{ old('total_biaya') }}" readonly>
                        </div>

                        {{-- Metode Pembayaran --}}
                        <div class="col-span-12">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-control" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash" {{ old('metode_pembayaran') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ old('metode_pembayaran') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="qris" {{ old('metode_pembayaran') == 'qris' ? 'selected' : '' }}>QRIS</option>
                                <option value="debit" {{ old('metode_pembayaran') == 'debit' ? 'selected' : '' }}>Debit Card</option>
                                <option value="ewallet" {{ old('metode_pembayaran') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                            </select>
                        </div>

                        {{-- Tanggal Bayar & Total Dibayarkan --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Bayar</label>
                            <input type="date" name="tgl_bayar" class="form-control" value="{{ old('tgl_bayar') }}">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Total Dibayarkan Diawal</label>
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" value="{{ old('jumlah_bayar', 0) }}">
                            <small class="text-muted" id="warning_text" style="display: none; color: #dc3545; margin-top: 4px;"></small>
                        </div>

                        {{-- Status Pembayaran --}}
                        <div class="col-span-12">
                            <label class="form-label">Status Pembayaran</label>
                            <input type="text" name="status_pembayaran" id="status_pembayaran" class="form-control" readonly>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">{{ $isPerpanjang ? 'Simpan Perpanjangan' : 'Simpan Membership' }}</button>
                                <a href="{{ route('anggota_membership.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
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
<!-- Include Searchable Dropdown Component -->
<link rel="stylesheet" href="{{ asset('assets/css/searchable-dropdown.css') }}">
<script src="{{ asset('assets/js/searchable-dropdown.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const paketSelect = document.getElementById('paket');
    const tglMulai = document.getElementById('tgl_mulai');
    const tglSelesai = document.getElementById('tgl_selesai');
    const diskonInput = document.getElementById('diskon');
    const totalBiaya = document.getElementById('total_biaya');
    const totalDibayarkan = document.getElementById('jumlah_bayar');
    const statusPembayaran = document.getElementById('status_pembayaran');
    const warningText = document.getElementById('warning_text');

    let harga = 0;
    let durasi = 0;
    let periode = 'bulan';

    // Hitung tanggal selesai
    function hitungTanggalSelesai() {
        if (tglMulai.value && durasi > 0) {
            let mulai = new Date(tglMulai.value);
            let selesai = new Date(mulai);

            if (periode.toLowerCase() === 'hari') selesai.setDate(mulai.getDate() + durasi);
            if (periode.toLowerCase() === 'minggu') selesai.setDate(mulai.getDate() + (durasi * 7));
            if (periode.toLowerCase() === 'bulan') selesai.setMonth(mulai.getMonth() + durasi);
            if (periode.toLowerCase() === 'tahun') selesai.setFullYear(mulai.getFullYear() + durasi);

            let yyyy = selesai.getFullYear();
            let mm = String(selesai.getMonth() + 1).padStart(2, '0');
            let dd = String(selesai.getDate()).padStart(2, '0');

            tglSelesai.value = `${yyyy}-${mm}-${dd}`;
        }
    }

    // Hitung total biaya
    function hitungTotalBiaya() {
        let diskon = parseInt(diskonInput.value) || 0;
        totalBiaya.value = Math.max(harga - diskon, 0);

        // Update max attribute untuk jumlah_bayar
        totalDibayarkan.setAttribute('max', totalBiaya.value);

        // Validasi ulang jumlah yang dibayar
        validateJumlahBayar();

        hitungStatusPembayaran();
    }

    function validateJumlahBayar() {
        let dibayar = parseInt(totalDibayarkan.value) || 0;
        let maxBiaya = parseInt(totalBiaya.value) || 0;

        if (dibayar > maxBiaya && maxBiaya > 0) {
            // Jika melebihi, set ke nilai maksimal
            totalDibayarkan.value = maxBiaya;

            // Tampilkan peringatan
            warningText.textContent = `⚠️ Pembayaran tidak boleh melebihi total biaya (Rp ${formatRupiah(maxBiaya)})`;
            warningText.style.display = 'block';

            // Sembunyikan peringatan setelah 3 detik
            setTimeout(() => {
                warningText.style.display = 'none';
            }, 3000);
        } else {
            warningText.style.display = 'none';
        }
    }

    // Hitung status pembayaran
    function hitungStatusPembayaran() {
        let dibayar = parseInt(totalDibayarkan.value) || 0;
        let biaya = parseInt(totalBiaya.value) || 0;

        if (dibayar >= biaya && biaya > 0) {
            statusPembayaran.value = "Lunas";
        } else {
            statusPembayaran.value = "Belum Lunas";
        }
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    // Event pilih paket (gunakan native select karena sudah ditransform)
    paketSelect.addEventListener('change', function() {
        let selected = this.options[this.selectedIndex];
        harga = parseInt(selected.dataset.harga || 0);
        durasi = parseInt(selected.dataset.durasi || 0);
        periode = selected.dataset.periode || 'bulan';

        hitungTanggalSelesai();
        hitungTotalBiaya();
    });

    // Event tanggal mulai
    tglMulai.addEventListener('change', hitungTanggalSelesai);

    // Event diskon
    diskonInput.addEventListener('input', hitungTotalBiaya);

    // Event jumlah dibayar dengan validasi real-time
    totalDibayarkan.addEventListener('input' , function() {
        validateJumlahBayar();
        hitungStatusPembayaran();
    });

    // Validasi sebelum submit form
    document.querySelector('form').addEventListener('submit', function(e) {
        let dibayar = parseInt(totalDibayarkan.value) || 0;
        let maxBiaya = parseInt(totalBiaya.value) || 0;

        if (dibayar > maxBiaya && maxBiaya > 0) {
            e.preventDefault();
            alert(`Pembayaran tidak boleh melebihi total biaya!\n\nTotal Biaya: ${formatRupiah(maxBiaya)}\nYang Anda input: ${formatRupiah(dibayar)}`);
            totalDibayarkan.focus();
            return false;
        }
    });
});
</script>
@endsection
