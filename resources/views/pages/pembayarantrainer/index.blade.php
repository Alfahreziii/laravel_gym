@extends('layout.layout')
@php
    $title='Pembayaran Trainer';
    $subTitle = 'Pembayaran Trainer';
    $script='<script src="' . asset('assets/js/data-table.js') . '"></script>';
@endphp

@section('content')

@if(session('success'))
    <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{ session('success') }}
        </div>
        <button class="remove-button text-success-600 text-2xl"><iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif
@if(session('danger'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('danger') }}
        <button class="remove-button text-danger-600 text-2xl"> 
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Data Pembayaran Trainer</h6>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            <th>Aksi</th>
                            <th>Kode Transaksi</th>
                            <th>Nama Anggota</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Total Biaya</th>
                            <th>Total Dibayarkan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($memberTrainers as $index => $item)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap flex gap-2">
                                @if($item->status_pembayaran === 'Lunas')
                                <a href="{{ route('pembayaran_trainer.notaPDF', $item->id) }}" 
                                class="w-8 h-8 bg-warning-100 text-warning-600 rounded-full inline-flex items-center justify-center"
                                title="Download Nota PDF">
                                    <iconify-icon icon="hugeicons:money-send-square" class="menu-icon"></iconify-icon>
                                </a>
                                @else
                                @role('admin')
                                <button type="button" title="Bayar" data-modal-target="edit-popup-modal-{{ $item->id }}" data-modal-toggle="edit-popup-modal-{{ $item->id }}" 
                                   class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                                </button>
                                @endrole
                                @role('spv')
                                -
                                @endrole
                                @endif
                            </td>
                            <td class="whitespace-nowrap"><a class="text-primary-600 cursor-pointer" href="{{ route('membertrainer.edit', $item->id) }}">{{ $item->kode_transaksi }}</a></td>
                            <td class="whitespace-nowrap">{{ $item->anggota->name ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $item->paketPersonalTrainer->nama_paket ?? '-' }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->paketPersonalTrainer->biaya, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->diskon, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">Rp {{ number_format($item->pembayaranMemberTrainers->sum('jumlah_bayar'), 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap">
                                @if($item->status_pembayaran === 'Lunas')
                                    <span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm">Lunas</span>
                                @else
                                    <span class="bg-warning-100 text-warning-600 px-4 py-1.5 rounded-full font-medium text-sm">Belum Lunas</span>
                                @endif
                            </td>
                        </tr>
                        @role('admin')
                        <div id="edit-popup-modal-{{ $item->id }}" tabindex="-1" class="hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0">
                            <div class="rounded-2xl bg-white max-w-[800px] w-full h-modal overflow-y-auto overflow-x-hidden">
                                <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                                    <h1 class="text-xl">Tambah Pembayaran</h1>
                                    <button data-modal-hide="edit-popup-modal-{{ $item->id }}" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"></path>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                <div class="p-6">
                                    <form action="{{ route('pembayaran_trainer.tambahPembayaran', $item->id) }}" method="POST" class="form-pembayaran" data-item-id="{{ $item->id }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                            {{-- Kode Transaksi (readonly) --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Kode Transaksi</label>
                                                <input type="text" class="form-control" value="{{ $item->kode_transaksi }}" readonly>
                                            </div>

                                            {{-- Info Total Biaya --}}
                                            <div class="col-span-12 md:col-span-6">
                                                <label class="form-label">Total Biaya</label>
                                                <input type="text" class="form-control bg-gray-50" 
                                                    value="Rp {{ number_format($item->total_biaya, 0, ',', '.') }}" readonly>
                                            </div>

                                            {{-- Info Total Sudah Dibayar --}}
                                            <div class="col-span-12 md:col-span-6">
                                                <label class="form-label">Sudah Dibayar</label>
                                                <input type="text" class="form-control bg-gray-50" 
                                                    value="Rp {{ number_format($item->pembayaranMemberTrainers->sum('jumlah_bayar'), 0, ',', '.') }}" readonly>
                                            </div>

                                            {{-- Info Sisa Tagihan --}}
                                            <div class="col-span-12">
                                                <label class="form-label font-semibold text-danger-600">Sisa Tagihan</label>
                                                <input type="text" class="modal_sisa_tagihan form-control bg-danger-50 border-danger-200 text-danger-600 font-bold text-lg" 
                                                    value="Rp {{ number_format($item->total_biaya - $item->pembayaranMemberTrainers->sum('jumlah_bayar'), 0, ',', '.') }}" readonly>
                                                <input type="hidden" id="sisa_tagihan_value_{{ $item->id }}"  class="sisa_tagihan_value" value="{{ $item->total_biaya - $item->pembayaranMemberTrainers->sum('jumlah_bayar') }}">
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
                                                <input type="number" name="jumlah_bayar" id="jumlah_bayar_{{ $item->id }}"  class="modal_jumlah_bayar form-control" value="0" min="0" required>
                                                <small class="text-muted modal_warning_text" style="display: none; color: #dc3545; margin-top: 4px;"></small>
                                            </div>

                                            {{-- Sisa Setelah Pembayaran Ini --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Sisa Setelah Pembayaran Ini</label>
                                                <input type="text" id="sisa_setelah_{{ $item->id }}"  class="modal_sisa_setelah form-control bg-success-50 border-success-200 text-success-600 font-semibold" readonly>
                                            </div>

                                            <div class="flex items-center justify-start gap-3 mt-6">
                                                <button type="reset" data-modal-hide="edit-popup-modal-{{ $item->id }}" class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                                    Cancel
                                                </button>
                                                <button type="submit" id="btn-submit-pembayaran_{{ $item->id }}" class=" btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg">
                                                    Simpan Pembayaran
                                                </button>
                                            </div>
                                        </div>  
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endrole
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                text: "Data Pembayaran Trainer yang dihapus tidak bisa dikembalikan!",
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

    // Modal Pembayaran Logic untuk setiap form
    const forms = document.querySelectorAll('.form-pembayaran');

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    forms.forEach(form => {
        const itemId = form.dataset.itemId; 

        const modalJumlahBayar = document.getElementById(`jumlah_bayar_${itemId}`);
        const modalSisaSetelah = document.getElementById(`sisa_setelah_${itemId}`);
        const sisaTagihanValue = document.getElementById(`sisa_tagihan_value_${itemId}`);
        const btnSubmitPembayaran = document.getElementById(`btn-submit-pembayaran_${itemId}`);

        const modalWarningText = form.querySelector('.modal_warning_text');

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
        form.addEventListener('submit', function(e) {
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
        const modalTrigger = document.querySelector(`[data-modal-target="edit-popup-modal-${itemId}"]`);
        if (modalTrigger) {
            modalTrigger.addEventListener('click', function() {
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
        }
    });
});
</script>
@endsection
