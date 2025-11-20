@extends('layout.layout')
@php
$title = 'Detail Member Trainer';
$subTitle = 'Detail Member Trainer';
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
                        @forelse($memberTrainer->pembayaranMemberTrainers as $index => $pembayaran)
                            <tr>
                                <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($pembayaran->tgl_bayar)->format('d-m-Y') }}</td>
                                <td class="whitespace-nowrap">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap">{{ ucfirst($pembayaran->metode_pembayaran) }}</td>
                                @role('admin')
                                <td class="whitespace-nowrap flex gap-2">
                                    <form action="{{ route('pembayaran_trainer.destroy', $pembayaran->id) }}" method="POST" class="inline-block delete-form">
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
                <h6 class="card-title mb-0 text-lg">Detail Member Trainer</h6>
                <a href="{{ route('membertrainer.index') }}" class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">Kembali</a>
            </div>
            <div class="card-body">
                <form action="{{ route('membertrainer.update', $memberTrainer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-12 gap-4">
                        {{-- Kode Transaksi --}}
                        <div class="col-span-12">
                            <label class="form-label">Kode Transaksi</label>
                            <input readonly type="text" id="kode_transaksi" class="form-control" 
                                value="{{ old('kode_transaksi', $memberTrainer->kode_transaksi) }}" required>
                        </div>

                        <div class="col-span-12">
                            <label class="form-label">Anggota</label>
                            <input readonly type="text" id="anggota" class="form-control" 
                                value="{{ $anggotas->where('id', $memberTrainer->id_anggota)->first()->name ?? '' }}" required>
                        </div>

                        <div class="col-span-12">
                            <label class="form-label">Trainer</label>
                            <input readonly type="text" id="trainer" class="form-control" 
                                value="{{ $trainers->where('id', $memberTrainer->id_trainer)->first()->name ?? '' }}" required>
                        </div>
    
                        <div class="col-span-12">
                            <label class="form-label">Paket Personal Trainer</label>
                            <input readonly type="text" class="form-control" 
                                        value="{{ optional($pakets->where('id', $memberTrainer->id_paket_personal_trainer)->first())->nama_paket 
                                        ?? '' }} ({{ optional($pakets->where('id', $memberTrainer->id_paket_personal_trainer)->first())->durasi 
                                        ?? '' }} {{ optional($pakets->where('id', $memberTrainer->id_paket_personal_trainer)->first())->periode 
                                        ?? '' }}) - Rp {{ number_format(optional($pakets->where('id', $memberTrainer->id_paket_personal_trainer)->first())->biaya ?? 0, 0, ',', '.') }}"  required>
                        </div>       
    
                        {{-- Diskon & Total Biaya --}}
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Diskon (Rp)</label>
                            <input type="number" name="diskon" id="diskon" 
                                value="{{ old('diskon', $memberTrainer->diskon) }}" readonly class="form-control">
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Total Biaya</label>
                            <input type="number" name="total_biaya" id="total_biaya" 
                                value="{{ old('total_biaya', $memberTrainer->total_biaya) }}" class="form-control" readonly>
                        </div>

                        {{-- Total Dibayarkan --}}
                        <div class="col-span-12">
                            <label class="form-label">Total Dibayarkan</label>
                            <input type="number" id="total_dibayarkan" class="form-control" 
                                value="{{ $memberTrainer->pembayaranMemberTrainers->sum('jumlah_bayar') }}" readonly>
                        </div>

                        {{-- Sisa Tagihan --}}
                        <div class="col-span-12">
                            <label class="form-label">Sisa Tagihan</label>
                            <input type="number" id="sisa_tagihan" class="form-control" 
                                value="{{ $memberTrainer->total_biaya - $memberTrainer->pembayaranMemberTrainers->sum('jumlah_bayar') }}" readonly>
                        </div>
                                                
                        {{-- Status Pembayaran --}}
                        <div class="col-span-12">
                            <label class="form-label">Status Pembayaran</label>
                            <input type="text" value="{{ old('status_pembayaran', $memberTrainer->status_pembayaran) }}" 
                                name="status_pembayaran" id="status_pembayaran" class="form-control" readonly>
                        </div>
                        
                    </div>
                </form>
            </div>
        </div><!-- card end -->
    </div>
</div>

<!-- Modal Add Riwayat Pembayaran -->
<div id="popup-modal" tabindex="-1" class="hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0">
    <div class="rounded-2xl bg-white max-w-[800px] w-full h-modal overflow-y-auto overflow-x-hidden">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl">Tambah Pembayaran</h1>
            <button data-modal-hide="popup-modal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"></path>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <div class="p-6">
            <form action="{{ route('membertrainer.tambahPembayaran', $memberTrainer->id) }}" method="POST" id="form-pembayaran">
            @csrf
            @method('POST')
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    {{-- Kode Transaksi (readonly) --}}
                    <div class="col-span-12">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" class="form-control" value="{{ $memberTrainer->kode_transaksi }}" readonly>
                    </div>

                    {{-- Info Total Biaya --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Total Biaya</label>
                        <input type="text" id="modal_total_biaya" class="form-control bg-gray-50" 
                            value="Rp {{ number_format($memberTrainer->total_biaya, 0, ',', '.') }}" readonly>
                    </div>

                    {{-- Info Total Sudah Dibayar --}}
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Sudah Dibayar</label>
                        <input type="text" id="modal_sudah_dibayar" class="form-control bg-gray-50" 
                            value="Rp {{ number_format($memberTrainer->pembayaranMemberTrainers->sum('jumlah_bayar'), 0, ',', '.') }}" readonly>
                    </div>

                    {{-- Info Sisa Tagihan --}}
                    <div class="col-span-12">
                        <label class="form-label font-semibold text-danger-600">Sisa Tagihan</label>
                        <input type="text" id="modal_sisa_tagihan" class="form-control bg-danger-50 border-danger-200 text-danger-600 font-bold text-lg" 
                            value="Rp {{ number_format($memberTrainer->total_biaya - $memberTrainer->pembayaranMemberTrainers->sum('jumlah_bayar'), 0, ',', '.') }}" readonly>
                        <input type="hidden" id="sisa_tagihan_value" value="{{ $memberTrainer->total_biaya - $memberTrainer->pembayaranMemberTrainers->sum('jumlah_bayar') }}">
                    </div>

                    <div class="col-span-12">
                        <hr class="my-2">
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

                    {{-- Tanggal Bayar --}}
                    <div class="col-span-12">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" name="tgl_bayar" class="form-control" value="{{ old('tgl_bayar', date('Y-m-d')) }}" required>
                    </div>

                    {{-- Jumlah Bayar --}}
                    <div class="col-span-12">
                        <label class="form-label">Jumlah Dibayarkan</label>
                        <input type="number" name="jumlah_bayar" id="modal_jumlah_bayar" class="form-control" value="0" min="0" required>
                        <small class="text-muted" id="modal_warning_text" style="display: none; color: #dc3545; margin-top: 4px;"></small>
                    </div>

                    {{-- Sisa Setelah Pembayaran Ini --}}
                    <div class="col-span-12">
                        <label class="form-label">Sisa Setelah Pembayaran Ini</label>
                        <input type="text" id="modal_sisa_setelah" class="form-control bg-success-50 border-success-200 text-success-600 font-semibold" readonly>
                    </div>

                    <div class="flex items-center justify-start gap-3 mt-6">
                        <button type="reset" data-modal-hide="popup-modal" class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" id="btn-submit-pembayaran" class="btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg">
                            Simpan Pembayaran
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
    const deleteForms = document.querySelectorAll('.delete-form');

    // Delete confirmation
    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data pembayaran yang dihapus tidak bisa dikembalikan!",
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

    // Modal Pembayaran Logic
    const modalJumlahBayar = document.getElementById('modal_jumlah_bayar');
    const modalSisaSetelah = document.getElementById('modal_sisa_setelah');
    const modalWarningText = document.getElementById('modal_warning_text');
    const sisaTagihanValue = document.getElementById('sisa_tagihan_value');
    const formPembayaran = document.getElementById('form-pembayaran');
    const btnSubmitPembayaran = document.getElementById('btn-submit-pembayaran');

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    function updateSisaSetelahPembayaran() {
        let sisaTagihan = parseInt(sisaTagihanValue.value) || 0;
        let jumlahBayar = parseInt(modalJumlahBayar.value) || 0;
        let sisaSetelah = sisaTagihan - jumlahBayar;

        // Update display sisa setelah pembayaran
        if (sisaSetelah < 0) {
            modalSisaSetelah.value = formatRupiah(0);
            modalSisaSetelah.classList.remove('bg-success-50', 'border-success-200', 'text-success-600');
            modalSisaSetelah.classList.add('bg-warning-50', 'border-warning-200', 'text-warning-600');
            
            // Tombol tetap normal
            btnSubmitPembayaran.disabled = false;
            btnSubmitPembayaran.textContent = 'Simpan Pembayaran';
            btnSubmitPembayaran.classList.remove('bg-success-600', 'border-success-600', 'cursor-not-allowed', 'opacity-60');
            btnSubmitPembayaran.classList.add('btn-primary', 'border-primary-600');
        } else if (sisaSetelah === 0 && jumlahBayar > 0) {
            // User memasukkan nominal yang pas untuk melunasi
            modalSisaSetelah.value = formatRupiah(0) + " (AKAN LUNAS)";
            modalSisaSetelah.classList.remove('bg-warning-50', 'border-warning-200', 'text-warning-600');
            modalSisaSetelah.classList.add('bg-success-50', 'border-success-200', 'text-success-600');
            
            // Tombol TETAP AKTIF agar bisa submit pembayaran pelunasan
            btnSubmitPembayaran.disabled = false;
            btnSubmitPembayaran.textContent = '✓ Simpan & Lunasi';
            btnSubmitPembayaran.classList.remove('cursor-not-allowed', 'opacity-60');
            btnSubmitPembayaran.classList.remove('btn-primary', 'border-primary-600');
            btnSubmitPembayaran.classList.add('bg-success-600', 'border-success-600');
        } else {
            modalSisaSetelah.value = formatRupiah(sisaSetelah);
            modalSisaSetelah.classList.remove('bg-warning-50', 'border-warning-200', 'text-warning-600');
            modalSisaSetelah.classList.add('bg-success-50', 'border-success-200', 'text-success-600');
            
            // Tombol normal
            btnSubmitPembayaran.disabled = false;
            btnSubmitPembayaran.textContent = 'Simpan Pembayaran';
            btnSubmitPembayaran.classList.remove('bg-success-600', 'border-success-600', 'cursor-not-allowed', 'opacity-60');
            btnSubmitPembayaran.classList.add('btn-primary', 'border-primary-600');
        }

        // Validasi jika melebihi sisa tagihan
        if (jumlahBayar > sisaTagihan && sisaTagihan > 0) {
            modalJumlahBayar.value = sisaTagihan;
            modalWarningText.textContent = `⚠️ Pembayaran tidak boleh melebihi sisa tagihan (${formatRupiah(sisaTagihan)})`;
            modalWarningText.style.display = 'block';
            
            setTimeout(() => {
                modalWarningText.style.display = 'none';
            }, 3000);
            
            // Trigger update lagi untuk cek status lunas
            updateSisaSetelahPembayaran();
        } else {
            modalWarningText.style.display = 'none';
        }
    }

    // Event listener untuk input jumlah bayar
    modalJumlahBayar.addEventListener('input', updateSisaSetelahPembayaran);

    // Validasi sebelum submit
    formPembayaran.addEventListener('submit', function(e) {
        let sisaTagihan = parseInt(sisaTagihanValue.value) || 0;
        let jumlahBayar = parseInt(modalJumlahBayar.value) || 0;

        if (jumlahBayar > sisaTagihan && sisaTagihan > 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Melebihi Tagihan!',
                html: `
                    <p>Jumlah pembayaran tidak boleh melebihi sisa tagihan.</p>
                    <br>
                    <strong>Sisa Tagihan:</strong> ${formatRupiah(sisaTagihan)}<br>
                    <strong>Yang Anda Input:</strong> ${formatRupiah(jumlahBayar)}
                `,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            modalJumlahBayar.focus();
            return false;
        }

        if (jumlahBayar <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Jumlah Tidak Valid!',
                text: 'Jumlah pembayaran harus lebih dari 0',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            modalJumlahBayar.focus();
            return false;
        }
    });

    // Set max attribute pada input
    modalJumlahBayar.setAttribute('max', sisaTagihanValue.value);

    // Reset form saat modal dibuka
    document.querySelector('[data-modal-target="popup-modal"]')?.addEventListener('click', function() {
        // Cek apakah sudah lunas dari awal
        let sisaTagihan = parseInt(sisaTagihanValue.value) || 0;
        
        if (sisaTagihan === 0) {
            // Jika sudah lunas, disable tombol dan tampilkan pesan
            btnSubmitPembayaran.disabled = true;
            btnSubmitPembayaran.textContent = '✓ SUDAH LUNAS';
            btnSubmitPembayaran.classList.remove('btn-primary', 'border-primary-600');
            btnSubmitPembayaran.classList.add('bg-success-600', 'border-success-600', 'cursor-not-allowed', 'opacity-60');
            
            modalJumlahBayar.disabled = true;
            modalJumlahBayar.value = 0;
            modalSisaSetelah.value = formatRupiah(0) + " (SUDAH LUNAS)";
            modalSisaSetelah.classList.add('bg-success-50', 'border-success-200', 'text-success-600');
        } else {
            // Reset normal
            modalJumlahBayar.disabled = false;
            modalJumlahBayar.value = 0;
            btnSubmitPembayaran.disabled = false;
            btnSubmitPembayaran.textContent = 'Simpan Pembayaran';
            btnSubmitPembayaran.classList.remove('bg-success-600', 'border-success-600', 'cursor-not-allowed', 'opacity-60');
            btnSubmitPembayaran.classList.add('btn-primary', 'border-primary-600');
            updateSisaSetelahPembayaran();
        }
    });
});
</script>
@endsection