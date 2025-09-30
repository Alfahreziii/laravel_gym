@extends('layout.layout')
@php
    $title = 'Tambah Member Trainer';
    $subTitle = 'Tambah Member Trainer';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-5">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header">
                <h6 class="text-lg font-semibold mb-0">Form Tambah Member Trainer</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('membertrainer.store') }}" method="POST">
                    @csrf
                    @method('POST')
                    
                    <div class="grid grid-cols-12 gap-4">
                        {{-- Alert success / danger --}}
                        <div class="col-span-12">
                            @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                    <a href="{{ route('membertrainer.index') }}" class="btn btn-success">Kembali</a>
                                </div>
                            @endif

                            @if(session('danger'))
                                <div class="alert alert-danger">
                                    {{ session('danger') }}
                                </div>
                            @endif
                        </div>

                        {{-- Anggota --}}
                        <div class="col-span-12">
                            <label class="form-label">Anggota</label>
                            <select name="id_anggota" class="form-control" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach($anggotas as $anggota)
                                    <option value="{{ $anggota->id }}" {{ old('id_anggota') == $anggota->id ? 'selected' : '' }}>
                                        {{ $anggota->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Trainer --}}
                        <div class="col-span-12">
                            <label class="form-label">Trainer</label>
                            <select name="id_trainer" class="form-control" required>
                                <option value="">-- Pilih Trainer --</option>
                                @foreach($trainers as $trainer)
                                    <option value="{{ $trainer->id }}" {{ old('id_trainer') == $trainer->id ? 'selected' : '' }}>
                                        {{ $trainer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Paket Personal Trainer --}}
                        <div class="col-span-12">
                            <label class="form-label">Pilih Paket Personal Trainer</label>
                            <select name="id_paket_personal_trainer" id="paket" class="form-control" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach($pakets as $paket)
                                    <option value="{{ $paket->id }}"
                                        data-durasi="{{ $paket->durasi }}"
                                        data-periode="{{ $paket->periode }}"
                                        data-biaya="{{ $paket->biaya }}">
                                        {{ $paket->nama_paket }} ({{ $paket->durasi }} {{ $paket->periode }}) - Rp {{ number_format($paket->biaya,0,',','.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Diskon & Total Biaya --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Diskon (Rp)</label>
                            <input type="number" name="diskon" id="diskon" class="form-control" value="0">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Total Biaya</label>
                            <input type="number" name="total_biaya" id="total_biaya" class="form-control" readonly>
                        </div>

                        {{-- Metode Pembayaran --}}
                        <div class="col-span-12">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-control" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash" {{ old('metode_pembayaran') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ old('metode_pembayaran') == 'transfer' ? 'selected' : '' }}>Transfer</option>
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
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" value="0">
                        </div>
                        
                        {{-- Status Pembayaran (readonly) --}}
                        <div class="col-span-12">
                            <label class="form-label">Status Pembayaran</label>
                            <input type="text" id="status_pembayaran" class="form-control" readonly>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Tambah Member Trainer</button>
                                <a href="{{ route('membertrainer.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
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
    const diskonInput = document.getElementById('diskon');
    const totalBiaya = document.getElementById('total_biaya');
    const totalDibayarkan = document.getElementById('jumlah_bayar');
    const statusPembayaran = document.getElementById('status_pembayaran');

    let biaya = 0;

    function hitungTotalBiaya() {
        let diskon = parseInt(diskonInput.value) || 0;
        totalBiaya.value = Math.max(biaya - diskon, 0);
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

    paketSelect.addEventListener('change', function() {
        let selected = this.options[this.selectedIndex];
        biaya = parseInt(selected.dataset.biaya || 0);
        hitungTotalBiaya();
    });

    diskonInput.addEventListener('input', hitungTotalBiaya);
    totalDibayarkan.addEventListener('input', hitungStatusPembayaran);
});
</script>
@endsection
