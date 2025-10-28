@extends('layout.layout')
@php
$title = 'Detail Anggota Membership';
$subTitle = 'Detail Anggota Membership';
$script='<script src="' . asset('assets/js/data-table.js') . '"></script>';
@endphp

@section('content')
<div class="grid grid-cols-12 mb-5">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Riwayat Pembayaran</h6>
                @role('admin')
                <button type="button" data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">+ Tambah Data</button>
                @endrole
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            <th>Tanggal Bayar</th>
                            <th>Jumlah Bayar</th>
                            <th>Metode Pembayaran</th>
                            @role('admin')
                            <th>Aksi</th>
                            @endrole
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anggotaMembership->pembayaranMemberships as $index => $pembayaran)
                            <tr>
                                <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($pembayaran->tgl_bayar)->format('d-m-Y') }}</td>
                                <td class="whitespace-nowrap">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap">{{ $pembayaran->metode_pembayaran }}</td>
                                @role('admin')
                                <td class="whitespace-nowrap flex gap-2">
                                    <form action="{{ route('pembayaran_membership.destroy', $pembayaran->id) }}" method="POST" class="inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                            <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                                @endrole
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada pembayaran</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 ">
    <div class="col-span-12">
        <div class="card border-0">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Detail Anggota Membership</h6>
                <a href="{{ route('anggota_membership.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
            </div>
            <div class="card-body">
                <form action="{{ route('anggota_membership.update', $anggotaMembership->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-12 gap-4">
                        {{-- Kode Transaksi --}}
                        <div class="col-span-12">
                            <label class="form-label">Kode Transaksi</label>
                            <input readonly type="text" id="kode_transaksi" class="form-control" 
                                value="{{ old('kode_transaksi', $anggotaMembership->kode_transaksi) }}" required>
                        </div>

                        {{-- Anggota --}}
                        <!-- <div class="col-span-12">
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
                        </div> -->
                        <div class="col-span-12">
                            <label class="form-label">Anggota</label>
                            <input readonly type="text" id="tgl_mulai" class="form-control" 
                                value="{{ $anggotas->where('id', $anggotaMembership->id_anggota)->first()->name ?? '' }}" required>
                        </div>
    
                        {{-- Paket Membership --}}
                        <!-- <div class="col-span-12">
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
                        </div>  -->
                        <div class="col-span-12">
                            <label class="form-label">Paket Membership</label>
                            <input readonly type="text" id="tgl_mulai" class="form-control" 
                                        value="{{ optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->nama_paket 
                                        ?? '' }} ({{ optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->durasi 
                                        ?? '' }} {{ optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->periode 
                                        ?? '' }}) - Rp {{ number_format(optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->harga ?? 0, 0, ',', '.') }}"  required>
                        </div>       
    
                        {{-- Tanggal Mulai & Selesai --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" 
                                value="{{ old('tgl_mulai', \Carbon\Carbon::parse($anggotaMembership->tgl_mulai)->format('Y-m-d')) }}" readonly required>
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
                                value="{{ old('diskon', $anggotaMembership->diskon) }}" readonly class="form-control">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Total Biaya</label>
                            <input type="number" name="total_biaya" id="total_biaya" 
                                value="{{ old('total_biaya', $anggotaMembership->total_biaya) }}" class="form-control" readonly>
                        </div>

                        {{-- Total Dibayarkan --}}
                        <div class="col-span-12">
                            <label class="form-label">Total Dibayarkan</label>
                            <input type="number" id="total_dibayarkan" class="form-control" 
                                value="{{ $anggotaMembership->pembayaranMemberships->sum('jumlah_bayar') }}" readonly>
                        </div>
                                                
                        {{-- Status Pembayaran --}}
                        <div class="col-span-12">
                            <label class="form-label">Status Pembayaran</label>
                            <input type="text" value="{{ old('status_pembayaran', $anggotaMembership->status_pembayaran) }}" 
                                name="status_pembayaran" id="status_pembayaran" class="form-control" readonly>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <!-- <div class="col-span-12">
                            <div class="form-group flex items-center justify-end gap-2">
                                <button type="submit" class="btn btn-primary-600">Update Membership</button>
                                <a href="{{ route('anggota_membership.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
                            </div>
                        </div> -->
                    </div>
                </form>
            </div>
        </div><!-- card end -->
    </div>
</div>

<!-- Modal Add Riwayat Pembayaran -->
<div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[800px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl">Add New Pembayaran</h1>
            <button data-modal-hide="popup-modal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"></path>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <div class="p-6">
            <form action="{{ route('anggota_membership.tambahPembayaran', $anggotaMembership->id) }}" method="POST">
            @csrf
            @method('POST')
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    {{-- Kode Transaksi (readonly) --}}
                    <div class="col-span-12">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" class="form-control" value="{{ $anggotaMembership->kode_transaksi }}" readonly>
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
                    <div class="col-span-12">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" name="tgl_bayar" class="form-control" value="{{ old('tgl_bayar') }}">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Total Dibayarkan</label>
                        <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" value="0">
                    </div>
                    <div class="flex items-center justify-start gap-3 mt-6">
                        <button type="reset" data-modal-hide="popup-modal" class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                            Save
                        </button>
                    </div>
                </div>  
            </form>
        </div>
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
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data anggota membership yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

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

    // Jalankan perhitungan awal saat halaman Detail dibuka
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