@extends('layout.layout')
@php
    $title    = 'Pembayaran Trainer';
    $subTitle = 'Pembayaran Trainer';
    $isAdmin  = (bool) auth()->user()?->hasRole('admin');
    $colCount = 10;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Data Pembayaran Trainer"
    subtitle="Riwayat dan status tagihan personal trainer."
>
    <x-data-table
        tableId="pembayaranTrainer"
        :colspan="$colCount"
        placeholder="Cari kode transaksi atau nama anggota...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Aksi</th>
                <th scope="col">Kode Transaksi</th>
                <th scope="col">Nama Anggota</th>
                <th scope="col">Paket</th>
                <th scope="col">Harga</th>
                <th scope="col">Diskon</th>
                <th scope="col">Total Biaya</th>
                <th scope="col">Total Dibayarkan</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

@if($isAdmin)
<x-modal id="bayar-trainer-modal" title="Tambah Pembayaran Trainer">
    <x-slot:body>
        <form id="bayarTrainerForm" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="form-label">Kode Transaksi</label>
                    <input type="text" id="bayarTrainerKodeTrans" class="form-control bg-gray-50" readonly>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Total Biaya</label>
                    <input type="text" id="bayarTrainerTotalBiaya" class="form-control bg-gray-50" readonly>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Sudah Dibayar</label>
                    <input type="text" id="bayarTrainerSudahDibayar" class="form-control bg-gray-50" readonly>
                </div>
                <div class="col-span-12">
                    <label class="form-label font-semibold text-danger-600">Sisa Tagihan</label>
                    <input type="text" id="bayarTrainerSisaDisplay" class="form-control bg-danger-50 border-danger-200 text-danger-600 font-bold text-lg" readonly>
                    <input type="hidden" id="bayarTrainerSisaValue" value="0">
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
                    <input type="date" name="tgl_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-span-12">
                    <label class="form-label">Jumlah Dibayarkan</label>
                    <input type="number" id="bayarTrainerJumlahBayar" name="jumlah_bayar" class="form-control" value="0" min="0" required>
                    <small id="bayarTrainerWarning" style="display:none; color:#dc3545; margin-top:4px;"></small>
                </div>
                <div class="col-span-12">
                    <label class="form-label">Sisa Setelah Pembayaran Ini</label>
                    <input type="text" id="bayarTrainerSisaSetelah" class="form-control bg-success-50 border-success-200 text-success-600 font-semibold" readonly>
                </div>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="bayar-trainer-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button id="bayarTrainerSubmitBtn" type="submit" form="bayarTrainerForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg">
            Simpan Pembayaran
        </button>
    </x-slot:footer>
</x-modal>
@endif

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var isAdmin   = {{ $isAdmin ? 'true' : 'false' }};
    var csrfToken = '{{ csrf_token() }}';

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    var bayarForm     = document.getElementById('bayarTrainerForm');
    var jumlahBayarEl = document.getElementById('bayarTrainerJumlahBayar');
    var sisaValueEl   = document.getElementById('bayarTrainerSisaValue');
    var sisaSetelahEl = document.getElementById('bayarTrainerSisaSetelah');
    var submitBtnEl   = document.getElementById('bayarTrainerSubmitBtn');
    var warningEl     = document.getElementById('bayarTrainerWarning');

    function resetSubmitBtn() {
        if (!submitBtnEl) return;
        submitBtnEl.disabled = false;
        submitBtnEl.textContent = 'Simpan Pembayaran';
        submitBtnEl.className = 'btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg';
    }

    function updateSisaSetelahPembayaran() {
        var sisaTagihan = parseInt(sisaValueEl ? sisaValueEl.value : 0) || 0;
        var jumlahBayar = parseInt(jumlahBayarEl ? jumlahBayarEl.value : 0) || 0;

        if (jumlahBayar > sisaTagihan && sisaTagihan > 0) {
            if (jumlahBayarEl) jumlahBayarEl.value = sisaTagihan;
            if (warningEl) {
                warningEl.textContent = '⚠️ Pembayaran tidak boleh melebihi sisa tagihan (' + formatRupiah(sisaTagihan) + ')';
                warningEl.style.display = 'block';
                setTimeout(function () { warningEl.style.display = 'none'; }, 3000);
            }
            updateSisaSetelahPembayaran();
            return;
        }
        if (warningEl) warningEl.style.display = 'none';

        var sisaSetelah = sisaTagihan - jumlahBayar;

        if (sisaSetelah < 0) {
            if (sisaSetelahEl) {
                sisaSetelahEl.value = formatRupiah(0);
                sisaSetelahEl.className = 'form-control bg-warning-50 border-warning-200 text-warning-600 font-semibold';
            }
            resetSubmitBtn();
        } else if (sisaSetelah === 0 && jumlahBayar > 0) {
            if (sisaSetelahEl) {
                sisaSetelahEl.value = formatRupiah(0) + ' (AKAN LUNAS)';
                sisaSetelahEl.className = 'form-control bg-success-50 border-success-200 text-success-600 font-semibold';
            }
            if (submitBtnEl) {
                submitBtnEl.disabled = false;
                submitBtnEl.textContent = '✓ Simpan & Lunasi';
                submitBtnEl.className = 'bg-success-600 border border-success-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg';
            }
        } else {
            if (sisaSetelahEl) {
                sisaSetelahEl.value = formatRupiah(sisaSetelah);
                sisaSetelahEl.className = 'form-control bg-success-50 border-success-200 text-success-600 font-semibold';
            }
            resetSubmitBtn();
        }
    }

    if (jumlahBayarEl) jumlahBayarEl.addEventListener('input', updateSisaSetelahPembayaran);

    if (bayarForm) {
        bayarForm.addEventListener('submit', function (e) {
            var sisaTagihan = parseInt(sisaValueEl ? sisaValueEl.value : 0) || 0;
            var jumlahBayar = parseInt(jumlahBayarEl ? jumlahBayarEl.value : 0) || 0;
            if (jumlahBayar > sisaTagihan && sisaTagihan > 0) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Pembayaran Melebihi Tagihan!', html: '<p>Jumlah pembayaran tidak boleh melebihi sisa tagihan.</p><br><strong>Sisa Tagihan:</strong> ' + formatRupiah(sisaTagihan) + '<br><strong>Yang Anda Input:</strong> ' + formatRupiah(jumlahBayar), confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                if (jumlahBayarEl) jumlahBayarEl.focus();
                return false;
            }
            if (jumlahBayar <= 0) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Jumlah Tidak Valid!', text: 'Jumlah pembayaran harus lebih dari 0', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                if (jumlahBayarEl) jumlahBayarEl.focus();
                return false;
            }
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.open-bayar-trainer-modal');
        if (btn) {
            var sisa    = parseFloat(btn.dataset.sisa) || 0;
            var total   = parseFloat(btn.dataset.total) || 0;
            var dibayar = parseFloat(btn.dataset.dibayar) || 0;
            document.getElementById('bayarTrainerKodeTrans').value    = btn.dataset.kode;
            document.getElementById('bayarTrainerTotalBiaya').value   = formatRupiah(total);
            document.getElementById('bayarTrainerSudahDibayar').value = formatRupiah(dibayar);
            document.getElementById('bayarTrainerSisaDisplay').value  = formatRupiah(sisa);
            if (sisaValueEl) sisaValueEl.value = sisa;
            if (jumlahBayarEl) { jumlahBayarEl.value = 0; jumlahBayarEl.disabled = false; }
            if (sisaSetelahEl) { sisaSetelahEl.value = ''; sisaSetelahEl.className = 'form-control bg-success-50 border-success-200 text-success-600 font-semibold'; }
            if (warningEl) warningEl.style.display = 'none';
            if (bayarForm) bayarForm.action = btn.dataset.action;
            resetSubmitBtn();
            HexaModal.show('bayar-trainer-modal');
        }
    });

    AjaxTable.init('pembayaranTrainer', {
        url: '{{ route('pembayaran_trainer.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            var aksi = '';
            if (item.is_lunas) {
                aksi = '<a href="' + htmlEsc(item.nota_url) + '" class="btn-action btn-action-warn" title="Download Nota PDF"><iconify-icon icon="hugeicons:money-send-square"></iconify-icon></a>';
            } else if (isAdmin) {
                aksi = '<button type="button" class="open-bayar-trainer-modal btn-action" title="Bayar"'
                    + ' data-action="' + htmlEsc(item.bayar_url) + '"'
                    + ' data-kode="' + htmlEsc(item.kode_transaksi) + '"'
                    + ' data-total="' + item.total_biaya + '"'
                    + ' data-dibayar="' + item.total_dibayarkan + '"'
                    + ' data-sisa="' + item.sisa_tagihan + '">'
                    + '<iconify-icon icon="hugeicons:invoice-03"></iconify-icon></button>';
            } else {
                aksi = '-';
            }

            var statusBadge = item.is_lunas
                ? AjaxTable.badge('success', 'Lunas')
                : AjaxTable.badge('warning', 'Belum Lunas');

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td class="whitespace-nowrap">' + aksi + '</td>'
                + '<td class="whitespace-nowrap"><a class="text-primary-600" href="' + htmlEsc(item.edit_url) + '">' + htmlEsc(item.kode_transaksi) + '</a></td>'
                + '<td class="whitespace-nowrap">' + htmlEsc(item.anggota_name) + '</td>'
                + '<td class="whitespace-nowrap">' + htmlEsc(item.paket_nama) + '</td>'
                + '<td class="whitespace-nowrap">Rp ' + Number(item.harga).toLocaleString('id-ID') + '</td>'
                + '<td class="whitespace-nowrap">Rp ' + Number(item.diskon).toLocaleString('id-ID') + '</td>'
                + '<td class="whitespace-nowrap">Rp ' + Number(item.total_biaya).toLocaleString('id-ID') + '</td>'
                + '<td class="whitespace-nowrap">Rp ' + Number(item.total_dibayarkan).toLocaleString('id-ID') + '</td>'
                + '<td class="whitespace-nowrap">' + statusBadge + '</td>'
                + '</tr>';
        }
    });

    @if(session('success'))
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}', timer: 3000 });
    @endif
    @if(session('danger'))
    Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('danger') }}' });
    @endif
});
</script>
@endsection
