@extends('layout.layout')
@php
    $title='Pembayaran Gaji Trainer';
    $subTitle = 'Pembayaran Gaji Trainer';
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
                <h6 class="card-title mb-0 text-lg">Data Pembayaran Gaji Trainer</h6>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            <th>Aksi</th>
                            <th>Nama Trainer</th>
                            <th>Terakhir Gajian</th>
                            <th>Sesi Belum Dibayar</th>
                            <th>Base Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gajiTrainers as $index => $gaji)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap flex gap-2">
                                <button type="button" title="Bayar Gaji" 
                                    data-modal-target="edit-popup-modal-{{ $gaji['id'] }}" 
                                    data-modal-toggle="edit-popup-modal-{{ $gaji['id'] }}" 
                                    class="w-8 h-8 {{ $gaji['sesi_belum_dibayar'] > 0 ? 'bg-success-100 text-success-600' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }} rounded-full inline-flex items-center justify-center"
                                    {{ $gaji['sesi_belum_dibayar'] <= 0 ? 'disabled' : '' }}>
                                    <iconify-icon icon="hugeicons:money-send-square" class="menu-icon"></iconify-icon>
                                </button>
                            </td>
                            <td class="whitespace-nowrap">{{ $gaji['nama'] }}</td>
                            <td class="whitespace-nowrap">{{ $gaji['terakhir_gajian'] }}</td>
                            <td class="whitespace-nowrap">
                                <span class="{{ $gaji['sesi_belum_dibayar'] > 0 ? 'bg-warning-100 text-warning-600' : 'bg-success-100 text-success-600' }} px-4 py-1.5 rounded-full font-medium text-sm">
                                    {{ $gaji['sesi_belum_dibayar'] }} Sesi
                                </span>
                            </td>
                            <td class="whitespace-nowrap">Rp {{ number_format($gaji['base_rate'], 0, ',', '.') }}</td>
                        </tr>
                        
                        <!-- Modal Pembayaran -->
                        <div id="edit-popup-modal-{{ $gaji['id'] }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="rounded-2xl bg-white max-w-[800px] w-full">
                                <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                                    <h1 class="text-xl">Form Pembayaran Gaji Trainer</h1>
                                    <button data-modal-hide="edit-popup-modal-{{ $gaji['id'] }}" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                <div class="p-6">
                                    <form class="form-gaji-trainer" data-trainer-id="{{ $gaji['id'] }}" data-base-rate="{{ $gaji['base_rate'] }}">
                                        @csrf
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                            {{-- Nama Trainer (readonly) --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Nama Trainer</label>
                                                <input type="text" class="form-control bg-gray-50" value="{{ $gaji['nama'] }}" readonly>
                                            </div>

                                            {{-- Info Base Rate --}}
                                            <div class="col-span-12 md:col-span-6">
                                                <label class="form-label">Base Rate per Sesi</label>
                                                <input type="text" class="form-control bg-gray-50" 
                                                    value="Rp {{ number_format($gaji['base_rate'], 0, ',', '.') }}" readonly>
                                            </div>

                                            {{-- Info Sesi Belum Dibayar --}}
                                            <div class="col-span-12 md:col-span-6">
                                                <label class="form-label">Total Sesi Belum Dibayar</label>
                                                <input type="text" class="form-control bg-gray-50" 
                                                    value="{{ $gaji['sesi_belum_dibayar'] }} Sesi" readonly>
                                            </div>

                                            <div class="col-span-12">
                                                <hr class="my-2">
                                            </div>

                                            {{-- Periode Pembayaran --}}
                                            <div class="col-span-12 md:col-span-6">
                                                <label class="form-label">Tanggal Mulai Periode</label>
                                                <input type="date" name="tgl_mulai" class="form-control modal_tgl_mulai" required>
                                            </div>

                                            <div class="col-span-12 md:col-span-6">
                                                <label class="form-label">Tanggal Selesai Periode</label>
                                                <input type="date" name="tgl_selesai" class="form-control modal_tgl_selesai" required>
                                            </div>

                                            {{-- Info Jumlah Sesi dalam Periode --}}
                                            <div class="col-span-12">
                                                <label class="form-label font-semibold text-primary-600">Jumlah Sesi dalam Periode Ini</label>
                                                <input type="text" class="modal_jumlah_sesi form-control bg-primary-50 border-primary-200 text-primary-600 font-bold text-lg" value="Menunggu input tanggal..." readonly>
                                                <input type="hidden" class="jumlah_sesi_value" value="0">
                                            </div>

                                            <div class="col-span-12">
                                                <hr class="my-2">
                                            </div>

                                            {{-- Tanggal Bayar --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Tanggal Bayar</label>
                                                <input type="date" name="tgl_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>

                                            {{-- Metode Pembayaran --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Metode Pembayaran</label>
                                                <select name="metode_pembayaran" class="form-control" required>
                                                    <option value="">-- Pilih Metode --</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="transfer">Transfer Bank</option>
                                                    <option value="e-wallet">E-Wallet</option>
                                                </select>
                                            </div>

                                            {{-- Bonus --}}
                                            <div class="col-span-12">
                                                <label class="form-label">Bonus (Opsional)</label>
                                                <input type="number" name="bonus" class="modal_bonus form-control" value="0" min="0" step="1000">
                                            </div>

                                            {{-- Total Dibayarkan --}}
                                            <div class="col-span-12">
                                                <label class="form-label font-semibold text-success-600">Total Yang Akan Dibayarkan</label>
                                                <input type="text" class="modal_total_dibayarkan form-control bg-success-50 border-success-200 text-success-600 font-bold text-xl" value="Rp 0" readonly>
                                                <small class="text-muted">Base Rate × Jumlah Sesi + Bonus</small>
                                            </div>

                                            <div class="flex items-center justify-start gap-3 mt-6">
                                                <button type="reset" data-modal-hide="edit-popup-modal-{{ $gaji['id'] }}" class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="btn-submit-gaji btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg">
                                                    Simpan Pembayaran
                                                </button>
                                            </div>
                                        </div>  
                                    </form>
                                </div>
                            </div>
                        </div>
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
document.addEventListener("DOMContentLoaded", function () {
    const modals = document.querySelectorAll('[id^="edit-popup-modal-"]');

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    modals.forEach((modal) => {
        const form = modal.querySelector('.form-gaji-trainer');
        if (!form) return;
        
        const trainerId = form.dataset.trainerId;
        const baseRateFromData = parseFloat(form.dataset.baseRate) || 0;
        
        const tglMulai = modal.querySelector('.modal_tgl_mulai');
        const tglSelesai = modal.querySelector('.modal_tgl_selesai');
        const jumlahSesiDisplay = modal.querySelector('.modal_jumlah_sesi');
        const jumlahSesiValue = modal.querySelector('.jumlah_sesi_value');
        const bonusInput = modal.querySelector('.modal_bonus');
        const totalDibayarkanDisplay = modal.querySelector('.modal_total_dibayarkan');
        const btnSubmit = modal.querySelector('.btn-submit-gaji');

        if (!tglMulai || !tglSelesai || !jumlahSesiDisplay || !totalDibayarkanDisplay) return;

        let baseRate = baseRateFromData;
        let currentJumlahSesi = 0;
        let isFetching = false;

        async function fetchPaymentData() {
            if (isFetching) return;

            const tglMulaiVal = tglMulai.value;
            const tglSelesaiVal = tglSelesai.value;

            if (!tglMulaiVal || !tglSelesaiVal) {
                jumlahSesiDisplay.value = 'Menunggu input tanggal...';
                if (jumlahSesiValue) jumlahSesiValue.value = 0;
                currentJumlahSesi = 0;
                updateTotal();
                return;
            }

            if (new Date(tglSelesaiVal) < new Date(tglMulaiVal)) {
                jumlahSesiDisplay.value = 'Tanggal tidak valid';
                if (jumlahSesiValue) jumlahSesiValue.value = 0;
                currentJumlahSesi = 0;
                updateTotal();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai harus lebih besar atau sama dengan tanggal mulai'
                    });
                }
                return;
            }

            isFetching = true;
            jumlahSesiDisplay.value = 'Mengambil data...';
            if (btnSubmit) btnSubmit.disabled = true;

            try {
                const url = `/riwayat-gaji-trainer/payment-data/${trainerId}?tgl_mulai=${tglMulaiVal}&tgl_selesai=${tglSelesaiVal}`;

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server tidak mengembalikan JSON');
                }

                const result = await response.json();

                if (result.success) {
                    currentJumlahSesi = result.data.jumlah_sesi || 0;
                    baseRate = result.data.base_rate || baseRateFromData;
                    
                    jumlahSesiDisplay.value = `${currentJumlahSesi} Sesi`;
                    if (jumlahSesiValue) jumlahSesiValue.value = currentJumlahSesi;
                    updateTotal();
                    if (btnSubmit) btnSubmit.disabled = false;

                    if (currentJumlahSesi === 0 && typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak Ada Sesi',
                            text: 'Tidak ada sesi yang perlu dibayar dalam periode ini'
                        });
                    }
                } else {
                    jumlahSesiDisplay.value = '0 Sesi';
                    currentJumlahSesi = 0;
                    if (jumlahSesiValue) jumlahSesiValue.value = 0;
                    updateTotal();
                    if (btnSubmit) btnSubmit.disabled = false;
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak Ada Data',
                            text: result.message || 'Tidak ada sesi yang perlu dibayar dalam periode ini'
                        });
                    }
                }
            } catch (error) {
                jumlahSesiDisplay.value = '0 Sesi (Error)';
                currentJumlahSesi = 0;
                if (jumlahSesiValue) jumlahSesiValue.value = 0;
                updateTotal();
                if (btnSubmit) btnSubmit.disabled = false;
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        html: `
                            <p>Gagal mengambil data pembayaran</p>
                            <p class="text-sm text-gray-600 mt-2">${error.message}</p>
                        `
                    });
                }
            } finally {
                isFetching = false;
            }
        }

        function updateTotal() {
            const bonus = bonusInput ? (parseInt(bonusInput.value) || 0) : 0;
            const total = (baseRate * currentJumlahSesi) + bonus;
            
            if (totalDibayarkanDisplay) {
                totalDibayarkanDisplay.value = formatRupiah(total);
            }
        }

        let dateChangeTimeout;
        
        if (tglMulai) {
            tglMulai.addEventListener('change', function() {
                clearTimeout(dateChangeTimeout);
                dateChangeTimeout = setTimeout(() => fetchPaymentData(), 300);
            });
        }
        
        if (tglSelesai) {
            tglSelesai.addEventListener('change', function() {
                clearTimeout(dateChangeTimeout);
                dateChangeTimeout = setTimeout(() => fetchPaymentData(), 300);
            });
        }
        
        if (bonusInput) {
            bonusInput.addEventListener('input', function() {
                updateTotal();
            });
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (currentJumlahSesi <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Tidak Valid',
                        text: 'Tidak ada sesi yang perlu dibayar dalam periode ini'
                    });
                } else {
                    alert('Tidak ada sesi yang perlu dibayar dalam periode ini');
                }
                return;
            }

            const formData = new FormData(form);
            const metodePembayaran = formData.get('metode_pembayaran');
            
            if (!metodePembayaran || metodePembayaran === '') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Lengkap',
                        text: 'Silakan pilih metode pembayaran'
                    });
                } else {
                    alert('Silakan pilih metode pembayaran');
                }
                return;
            }

            const data = {
                id_trainer: trainerId,
                tgl_mulai: formData.get('tgl_mulai'),
                tgl_selesai: formData.get('tgl_selesai'),
                tgl_bayar: formData.get('tgl_bayar'),
                metode_pembayaran: metodePembayaran,
                bonus: formData.get('bonus') || 0
            };

            try {
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.textContent = 'Menyimpan...';
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch('/riwayat-gaji-trainer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: result.message,
                            confirmButtonColor: '#3085d6',
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        alert(result.message);
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: result.message || 'Terjadi kesalahan saat menyimpan'
                        });
                    } else {
                        alert(result.message || 'Terjadi kesalahan saat menyimpan');
                    }
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = 'Simpan Pembayaran';
                    }
                }
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        html: `
                            <p>Gagal menyimpan pembayaran</p>
                            <p class="text-sm text-gray-600 mt-2">${error.message}</p>
                        `
                    });
                } else {
                    alert('Gagal menyimpan pembayaran: ' + error.message);
                }
                
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Simpan Pembayaran';
                }
            }
        });

        const modalTrigger = document.querySelector(`[data-modal-target="edit-popup-modal-${trainerId}"]`);
        
        if (modalTrigger) {
            modalTrigger.addEventListener('click', function(e) {
                form.reset();
                jumlahSesiDisplay.value = 'Menunggu input tanggal...';
                if (jumlahSesiValue) jumlahSesiValue.value = 0;
                currentJumlahSesi = 0;
                totalDibayarkanDisplay.value = formatRupiah(0);
                
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Simpan Pembayaran';
                }
                
                const tglBayarInput = form.querySelector('[name="tgl_bayar"]');
                if (tglBayarInput && !tglBayarInput.value) {
                    const today = new Date().toISOString().split('T')[0];
                    tglBayarInput.value = today;
                }
            });
        }
    });
});
</script>
@endsection