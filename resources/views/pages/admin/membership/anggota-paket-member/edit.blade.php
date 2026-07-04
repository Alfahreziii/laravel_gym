@extends('layout.layout')
@php
    $title = 'Detail Anggota Membership';
    $subTitle = 'Detail Anggota Membership';
@endphp

@section('content')
    <div class="grid grid-cols-12 mb-5">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">Riwayat Pembayaran</h6>
                    @role('admin')
                        <button type="button" onclick="openPopupModal()"
                            class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                            + Tambah Data
                        </button>
                    @endrole
                </div>
                <div class="card-body">
                    <x-data-table tableId="riwayatPembayaranMembership" :colspan="auth()->user()->hasRole('admin') ? 5 : 4" placeholder="Cari tanggal atau metode...">
                        <x-slot:header>
                            <tr>
                                <th>S.L</th>
                                <th>Tanggal Bayar</th>
                                <th>Jumlah Bayar</th>
                                <th>Metode Pembayaran</th>
                                @role('admin')
                                    <th>Aksi</th>
                                @endrole
                            </tr>
                        </x-slot:header>
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">Detail Anggota Membership</h6>
                    <a href="{{ route('anggota_membership.index') }}"
                        class="text-danger-600 focus:bg-danger-600 hover:bg-danger-700 border border-danger-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-base px-6 py-3 text-center inline-flex items-center dark:text-danger-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-danger-800">
                        Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('anggota_membership.update', $anggotaMembership->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <label class="form-label">Kode Transaksi</label>
                                <input readonly type="text" class="form-control"
                                    value="{{ old('kode_transaksi', $anggotaMembership->kode_transaksi) }}" required>
                            </div>
                            <div class="col-span-12">
                                <label class="form-label">Anggota</label>
                                <input readonly type="text" class="form-control"
                                    value="{{ $anggotas->where('id', $anggotaMembership->id_anggota)->first()->name ?? '' }}" required>
                            </div>
                            <div class="col-span-12">
                                <label class="form-label">Paket Membership</label>
                                <input readonly type="text" class="form-control"
                                    value="{{ optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->nama_paket ?? '' }} ({{ optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->durasi ?? '' }} {{ optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->periode ?? '' }}) - Rp {{ number_format(optional($pakets->where('id', $anggotaMembership->id_paket_membership)->first())->harga ?? 0, 0, ',', '.') }}"
                                    required>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" class="form-control"
                                    value="{{ old('tgl_mulai', \Carbon\Carbon::parse($anggotaMembership->tgl_mulai)->format('Y-m-d')) }}"
                                    readonly required>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai"
                                    value="{{ old('tgl_selesai', \Carbon\Carbon::parse($anggotaMembership->tgl_selesai)->format('Y-m-d')) }}"
                                    class="form-control" readonly>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <label class="form-label">Diskon (Rp)</label>
                                <input type="number" name="diskon"
                                    value="{{ old('diskon', $anggotaMembership->diskon) }}" readonly class="form-control">
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <label class="form-label">Total Biaya</label>
                                <input type="number" name="total_biaya"
                                    value="{{ old('total_biaya', $anggotaMembership->total_biaya) }}" class="form-control" readonly>
                            </div>
                            <div class="col-span-12">
                                <label class="form-label">Total Dibayarkan</label>
                                <input type="number" class="form-control"
                                    value="{{ $anggotaMembership->pembayaranMemberships->sum('jumlah_bayar') }}" readonly>
                            </div>
                            <div class="col-span-12">
                                <label class="form-label">Status Pembayaran</label>
                                <input type="text"
                                    value="{{ old('status_pembayaran', $anggotaMembership->status_pembayaran) }}"
                                    name="status_pembayaran" class="form-control" readonly>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="popup-modal" title="Add New Pembayaran" maxWidth="max-w-[800px]">
        <x-slot:body>
            <form action="{{ route('anggota_membership.tambahPembayaran', $anggotaMembership->id) }}" method="POST"
                id="form-pembayaran">
                @csrf
                @method('POST')
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" class="form-control" value="{{ $anggotaMembership->kode_transaksi }}" readonly>
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Total Biaya</label>
                        <input type="text" id="modal_total_biaya" class="form-control bg-gray-50"
                            value="Rp {{ number_format($anggotaMembership->total_biaya, 0, ',', '.') }}" readonly>
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">Sudah Dibayar</label>
                        <input type="text" id="modal_sudah_dibayar" class="form-control bg-gray-50"
                            value="Rp {{ number_format($anggotaMembership->pembayaranMemberships->sum('jumlah_bayar'), 0, ',', '.') }}"
                            readonly>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-semibold text-danger-600">Sisa Tagihan</label>
                        <input type="text" id="modal_sisa_tagihan"
                            class="form-control bg-danger-50 border-danger-200 text-danger-600 font-bold text-lg"
                            value="Rp {{ number_format($anggotaMembership->total_biaya - $anggotaMembership->pembayaranMemberships->sum('jumlah_bayar'), 0, ',', '.') }}"
                            readonly>
                        <input type="hidden" id="sisa_tagihan_value"
                            value="{{ $anggotaMembership->total_biaya - $anggotaMembership->pembayaranMemberships->sum('jumlah_bayar') }}">
                    </div>
                    <div class="col-span-12"><hr class="my-2"></div>
                    <div class="col-span-12">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-control" required>
                            <option value="">-- Pilih Metode --</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit Card</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" name="tgl_bayar" class="form-control"
                            value="{{ old('tgl_bayar', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Jumlah Dibayarkan</label>
                        <input type="number" name="jumlah_bayar" id="modal_jumlah_bayar" class="form-control"
                            value="0" min="0" required>
                        <small class="text-muted" id="modal_warning_text" style="display: none; color: #dc3545; margin-top: 4px;"></small>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Sisa Setelah Pembayaran Ini</label>
                        <input type="text" id="modal_sisa_setelah"
                            class="form-control bg-success-50 border-success-200 text-success-600 font-semibold" readonly>
                    </div>
                    <div class="flex items-center justify-start gap-3 mt-6">
                        <button type="reset" data-close-modal="popup-modal"
                            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" id="btn-submit-pembayaran"
                            class="btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg">
                            Simpan Pembayaran
                        </button>
                    </div>
                </div>
            </form>
        </x-slot:body>
    </x-modal>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        var isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};

        AjaxTable.init('riwayatPembayaranMembership', {
            url: '{{ route('anggota_membership.datatablePembayaran', $anggotaMembership->id) }}',
            colSpan: isAdmin ? 5 : 4,
            renderRow: function(item) {
                var aksiHtml = '';
                if (isAdmin) {
                    aksiHtml = '<td class="whitespace-nowrap">' +
                        '<button type="button" onclick="deletePembayaran(\'' + item.delete_url + '\')" ' +
                        'class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center">' +
                        '<iconify-icon icon="mingcute:delete-2-line"></iconify-icon>' +
                        '</button></td>';
                }

                return '<tr>' +
                    '<td class="whitespace-nowrap text-center">' + item.no + '</td>' +
                    '<td class="whitespace-nowrap">' + item.tgl_bayar + '</td>' +
                    '<td class="whitespace-nowrap">' + item.jumlah_bayar + '</td>' +
                    '<td class="whitespace-nowrap">' + item.metode_pembayaran + '</td>' +
                    aksiHtml +
                    '</tr>';
            }
        });

        window.deletePembayaran = function(url) {
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data pembayaran yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                        '<input type="hidden" name="_method" value="DELETE">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        };

        // Modal Pembayaran Logic
        document.addEventListener('DOMContentLoaded', function() {
            var modalJumlahBayar    = document.getElementById('modal_jumlah_bayar');
            var modalSisaSetelah    = document.getElementById('modal_sisa_setelah');
            var modalWarningText    = document.getElementById('modal_warning_text');
            var sisaTagihanValue    = document.getElementById('sisa_tagihan_value');
            var formPembayaran      = document.getElementById('form-pembayaran');
            var btnSubmitPembayaran = document.getElementById('btn-submit-pembayaran');

            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
            }

            function updateSisaSetelahPembayaran() {
                var sisaTagihan = parseInt(sisaTagihanValue.value) || 0;
                var jumlahBayar = parseInt(modalJumlahBayar.value) || 0;
                var sisaSetelah = sisaTagihan - jumlahBayar;

                if (sisaSetelah < 0) {
                    modalSisaSetelah.value = formatRupiah(0);
                    modalSisaSetelah.className = 'form-control bg-warning-50 border-warning-200 text-warning-600 font-semibold';
                    btnSubmitPembayaran.disabled = false;
                    btnSubmitPembayaran.textContent = 'Simpan Pembayaran';
                    btnSubmitPembayaran.className = 'btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg';
                } else if (sisaSetelah === 0 && jumlahBayar > 0) {
                    modalSisaSetelah.value = formatRupiah(0) + " (AKAN LUNAS)";
                    modalSisaSetelah.className = 'form-control bg-success-50 border-success-200 text-success-600 font-semibold';
                    btnSubmitPembayaran.disabled = false;
                    btnSubmitPembayaran.textContent = '✓ Simpan & Lunasi';
                    btnSubmitPembayaran.className = 'bg-success-600 border border-success-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg';
                } else {
                    modalSisaSetelah.value = formatRupiah(sisaSetelah);
                    modalSisaSetelah.className = 'form-control bg-success-50 border-success-200 text-success-600 font-semibold';
                    btnSubmitPembayaran.disabled = false;
                    btnSubmitPembayaran.textContent = 'Simpan Pembayaran';
                    btnSubmitPembayaran.className = 'btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg';
                }

                if (jumlahBayar > sisaTagihan && sisaTagihan > 0) {
                    modalJumlahBayar.value = sisaTagihan;
                    modalWarningText.textContent = '⚠️ Pembayaran tidak boleh melebihi sisa tagihan (' + formatRupiah(sisaTagihan) + ')';
                    modalWarningText.style.display = 'block';
                    setTimeout(function() { modalWarningText.style.display = 'none'; }, 3000);
                    updateSisaSetelahPembayaran();
                } else {
                    modalWarningText.style.display = 'none';
                }
            }

            if (modalJumlahBayar) {
                modalJumlahBayar.addEventListener('input', updateSisaSetelahPembayaran);
                modalJumlahBayar.setAttribute('max', sisaTagihanValue.value);
            }

            if (formPembayaran) {
                formPembayaran.addEventListener('submit', function(e) {
                    var sisaTagihan = parseInt(sisaTagihanValue.value) || 0;
                    var jumlahBayar = parseInt(modalJumlahBayar.value) || 0;

                    if (jumlahBayar > sisaTagihan && sisaTagihan > 0) {
                        e.preventDefault();
                        Swal.fire({ icon: 'error', title: 'Pembayaran Melebihi Tagihan!',
                            html: '<p>Jumlah tidak boleh melebihi sisa tagihan.</p><strong>Sisa:</strong> ' + formatRupiah(sisaTagihan),
                            confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                        return false;
                    }
                    if (jumlahBayar <= 0) {
                        e.preventDefault();
                        Swal.fire({ icon: 'error', title: 'Jumlah Tidak Valid!',
                            text: 'Jumlah pembayaran harus lebih dari 0', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                        return false;
                    }
                });
            }

            window.openPopupModal = function() {
                var sisaTagihan = parseInt(sisaTagihanValue.value) || 0;
                if (sisaTagihan === 0) {
                    btnSubmitPembayaran.disabled = true;
                    btnSubmitPembayaran.textContent = '✓ SUDAH LUNAS';
                    btnSubmitPembayaran.className = 'bg-success-600 border border-success-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg cursor-not-allowed opacity-60';
                    modalJumlahBayar.disabled = true;
                    modalJumlahBayar.value = 0;
                    modalSisaSetelah.value = formatRupiah(0) + " (SUDAH LUNAS)";
                    modalSisaSetelah.className = 'form-control bg-success-50 border-success-200 text-success-600 font-semibold';
                } else {
                    modalJumlahBayar.disabled = false;
                    modalJumlahBayar.value = 0;
                    btnSubmitPembayaran.disabled = false;
                    btnSubmitPembayaran.textContent = 'Simpan Pembayaran';
                    btnSubmitPembayaran.className = 'btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg';
                    updateSisaSetelahPembayaran();
                }
                HexaModal.show('popup-modal');
            };
        });
    </script>
@endsection
