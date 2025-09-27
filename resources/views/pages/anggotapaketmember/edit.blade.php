@extends('layout.layout')
@php
    $title = 'Tambah Anggota Membership';
    $subTitle = 'Tambah Anggota Membership';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Tambah Anggota Membership</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('anggota_membership.update', $anggotaMembership->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-12 gap-4">
                        {{-- Anggota --}}
                        <div class="col-span-12">
                            <label class="form-label">Anggota</label>
                            <select name="id_anggota" class="form-control" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach($anggotas as $anggota)
                                    <option value="{{ $anggota->id }}" 
                                        {{ old('id_anggota', $anggotaMembership->id_anggota) == $anggota->id ? 'selected' : '' }}>
                                        {{ $anggota->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
    
                        {{-- Paket Membership --}}
                        <div class="col-span-12">
                            <label class="form-label">Pilih Paket Membership</label>
                            <select name="id_paket_membership" id="paket" class="form-control" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach($pakets as $paket)
                                    <option value="{{ $paket->id }}"
                                        data-durasi="{{ $paket->durasi }}"
                                        data-periode="{{ $paket->periode }}"
                                        data-harga="{{ $paket->harga }}"
                                        {{ old('id_paket_membership', $anggotaMembership->id_paket_membership) == $paket->id ? 'selected' : '' }}>
                                        {{ $paket->nama_paket }} ({{ $paket->durasi }} {{ $paket->periode }}) - Rp {{ number_format($paket->harga,0,',','.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>        
    
                        {{-- Tanggal Mulai & Selesai --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" 
                                value="{{ old('tgl_mulai', \Carbon\Carbon::parse($anggotaMembership->tgl_mulai)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" 
                                value="{{ old('tgl_selesai', \Carbon\Carbon::parse($anggotaMembership->tgl_selesai)->format('Y-m-d')) }}" 
                                class="form-control" readonly>
                        </div>
    
                        {{-- Diskon & Total Biaya --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Diskon (Rp)</label>
                            <input type="number" name="diskon" id="diskon" 
                                value="{{ old('diskon', $anggotaMembership->diskon) }}" class="form-control">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Total Biaya</label>
                            <input type="number" name="total_biaya" id="total_biaya" 
                                value="{{ old('total_biaya', $anggotaMembership->total_biaya) }}" class="form-control" readonly>
                        </div>
    
                        {{-- Metode Pembayaran --}}
                        <div class="col-span-12">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-control" required>
                                <option value="cash" {{ old('metode_pembayaran', $anggotaMembership->metode_pembayaran) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ old('metode_pembayaran', $anggotaMembership->metode_pembayaran) == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="ewallet" {{ old('metode_pembayaran', $anggotaMembership->metode_pembayaran) == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                            </select>
                        </div>
    
                        {{-- Tanggal Bayar & Total Dibayarkan --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Bayar</label>
                            <input type="date" name="tgl_bayar" class="form-control" 
                                value="{{ old('tgl_bayar', $anggotaMembership->tgl_bayar ? \Carbon\Carbon::parse($anggotaMembership->tgl_bayar)->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Total Dibayarkan</label>
                            <input type="number" name="total_dibayarkan" id="total_dibayarkan" 
                                value="{{ old('total_dibayarkan', $anggotaMembership->total_dibayarkan) }}" class="form-control">
                        </div>
                        
                        {{-- Status Pembayaran --}}
                        <div class="col-span-12">
                            <label class="form-label">Status Pembayaran</label>
                            <input type="text" value="{{ old('status_pembayaran', $anggotaMembership->status_pembayaran) }}" 
                                name="status_pembayaran" id="status_pembayaran" class="form-control" readonly>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Update Membership</button>
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
<script>
document.addEventListener("DOMContentLoaded", function() {
    const paketSelect = document.getElementById('paket');
    const tglMulai = document.getElementById('tgl_mulai');
    const tglSelesai = document.getElementById('tgl_selesai');
    const diskonInput = document.getElementById('diskon');
    const totalBiaya = document.getElementById('total_biaya');
    const totalDibayarkan = document.getElementById('total_dibayarkan');
    const statusPembayaran = document.getElementById('status_pembayaran');

    let harga = 0;
    let durasi = 0;
    let periode = 'bulan';

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

    function hitungTotalBiaya() {
        let diskon = parseInt(diskonInput.value) || 0;
        totalBiaya.value = Math.max(harga - diskon, 0);
        hitungStatusPembayaran();
    }

    function hitungStatusPembayaran() {
        let dibayar = parseInt(totalDibayarkan.value) || 0;
        let biaya = parseInt(totalBiaya.value) || 0;

        if (dibayar >= biaya && biaya > 0) {
            statusPembayaran.value = "Lunas";
        } else {
            statusPembayaran.value = "Belum Lunas";
        }
    }

    // Event
    paketSelect.addEventListener('change', function() {
        let selected = this.options[this.selectedIndex];
        harga = parseInt(selected.dataset.harga || 0);
        durasi = parseInt(selected.dataset.durasi || 0);
        periode = selected.dataset.periode || 'bulan';
        hitungTanggalSelesai();
        hitungTotalBiaya();
    });

    tglMulai.addEventListener('change', hitungTanggalSelesai);
    diskonInput.addEventListener('input', hitungTotalBiaya);
    totalDibayarkan.addEventListener('input', hitungStatusPembayaran);

    // Jalankan perhitungan awal saat halaman edit dibuka
    if (paketSelect.value) {
        let selected = paketSelect.options[paketSelect.selectedIndex];
        harga = parseInt(selected.dataset.harga || 0);
        durasi = parseInt(selected.dataset.durasi || 0);
        periode = selected.dataset.periode || 'bulan';
    }
    hitungTanggalSelesai();
    hitungTotalBiaya();
    hitungStatusPembayaran();
});

</script>
@endsection
