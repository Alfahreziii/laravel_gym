@extends('layout.layout')
@php
    $title    = 'Pembayaran Gaji Trainer';
    $subTitle = 'Pembayaran Gaji Trainer';
    $colCount = 6;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="Data Pembayaran Gaji Trainer"
    subtitle="Proses pembayaran gaji trainer berdasarkan sesi yang sudah dijalani."
>
    <x-data-table
        tableId="gajiTrainer"
        :colspan="$colCount"
        placeholder="Cari nama trainer...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Aksi</th>
                <th scope="col">Nama Trainer</th>
                <th scope="col">Terakhir Gajian</th>
                <th scope="col">Sesi Belum Dibayar</th>
                <th scope="col">Base Rate</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

<x-modal id="bayar-gaji-modal" title="Form Pembayaran Gaji Trainer" maxWidth="max-w-2xl">
    <x-slot:body>
        <form id="bayarGajiForm">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="form-label">Nama Trainer</label>
                    <input type="text" id="gajiNamaTrainer" class="form-control bg-gray-50" readonly>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Base Rate per Sesi</label>
                    <input type="text" id="gajiBaseRateDisplay" class="form-control bg-gray-50" readonly>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Total Sesi Belum Dibayar</label>
                    <input type="text" id="gajiSesiDisplay" class="form-control bg-gray-50" readonly>
                </div>
                <div class="col-span-12"><hr class="my-2"></div>
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Tanggal Mulai Periode</label>
                    <input type="date" name="tgl_mulai" id="gajiTglMulai" class="form-control" required>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Tanggal Selesai Periode</label>
                    <input type="date" name="tgl_selesai" id="gajiTglSelesai" class="form-control" required>
                </div>
                <div class="col-span-12">
                    <label class="form-label font-semibold text-primary-600">Jumlah Sesi dalam Periode Ini</label>
                    <input type="text" id="gajiJumlahSesiDisplay" class="form-control bg-primary-50 border-primary-200 text-primary-600 font-bold text-lg" value="Menunggu input tanggal..." readonly>
                </div>
                <div class="col-span-12"><hr class="my-2"></div>
                <div class="col-span-12">
                    <label class="form-label">Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar" id="gajiTglBayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-span-12">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="gajiMetode" class="form-control" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="e-wallet">E-Wallet</option>
                    </select>
                </div>
                <div class="col-span-12">
                    <label class="form-label">Bonus (Opsional)</label>
                    <input type="number" name="bonus" id="gajiBonus" class="form-control" value="0" min="0" step="1000">
                </div>
                <div class="col-span-12">
                    <label class="form-label font-semibold text-success-600">Total Yang Akan Dibayarkan</label>
                    <input type="text" id="gajiTotalDibayarkan" class="form-control bg-success-50 border-success-200 text-success-600 font-bold text-xl" value="Rp 0" readonly>
                    <small class="text-muted">Base Rate × Jumlah Sesi + Bonus</small>
                </div>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="bayar-gaji-modal"
            class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg transition-colors">
            Cancel
        </button>
        <button id="gajiSubmitBtn" type="submit" form="bayarGajiForm"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 whitespace-nowrap text-white rounded-lg">
            Simpan Pembayaran
        </button>
    </x-slot:footer>
</x-modal>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var currentTrainerId = null;
    var currentBaseRate  = 0;
    var currentJumlahSesi = 0;
    var isFetching = false;

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    var gajiJumlahSesiDisplay = document.getElementById('gajiJumlahSesiDisplay');
    var gajiBonus             = document.getElementById('gajiBonus');
    var gajiTotalDibayarkan   = document.getElementById('gajiTotalDibayarkan');
    var gajiTglMulai          = document.getElementById('gajiTglMulai');
    var gajiTglSelesai        = document.getElementById('gajiTglSelesai');
    var gajiSubmitBtn         = document.getElementById('gajiSubmitBtn');

    function updateTotal() {
        var bonus = gajiBonus ? (parseInt(gajiBonus.value) || 0) : 0;
        var total = (currentBaseRate * currentJumlahSesi) + bonus;
        if (gajiTotalDibayarkan) gajiTotalDibayarkan.value = formatRupiah(total);
    }

    async function fetchPaymentData() {
        if (isFetching || !currentTrainerId) return;

        var tglMulaiVal   = gajiTglMulai ? gajiTglMulai.value : '';
        var tglSelesaiVal = gajiTglSelesai ? gajiTglSelesai.value : '';

        if (!tglMulaiVal || !tglSelesaiVal) {
            if (gajiJumlahSesiDisplay) gajiJumlahSesiDisplay.value = 'Menunggu input tanggal...';
            currentJumlahSesi = 0;
            updateTotal();
            return;
        }

        if (new Date(tglSelesaiVal) < new Date(tglMulaiVal)) {
            if (gajiJumlahSesiDisplay) gajiJumlahSesiDisplay.value = 'Tanggal tidak valid';
            currentJumlahSesi = 0;
            updateTotal();
            Swal.fire({ icon: 'warning', title: 'Tanggal Tidak Valid', text: 'Tanggal selesai harus lebih besar atau sama dengan tanggal mulai' });
            return;
        }

        isFetching = true;
        if (gajiJumlahSesiDisplay) gajiJumlahSesiDisplay.value = 'Mengambil data...';
        if (gajiSubmitBtn) gajiSubmitBtn.disabled = true;

        try {
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var url = '/riwayat-gaji-trainer/payment-data/' + currentTrainerId + '?tgl_mulai=' + tglMulaiVal + '&tgl_selesai=' + tglSelesaiVal;
            var response = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '' }
            });

            if (!response.ok) throw new Error('HTTP error ' + response.status);

            var result = await response.json();

            if (result.success) {
                currentJumlahSesi = result.data.jumlah_sesi || 0;
                currentBaseRate   = result.data.base_rate   || currentBaseRate;
                if (gajiJumlahSesiDisplay) gajiJumlahSesiDisplay.value = currentJumlahSesi + ' Sesi';
                updateTotal();
                if (gajiSubmitBtn) gajiSubmitBtn.disabled = false;
                if (currentJumlahSesi === 0) {
                    Swal.fire({ icon: 'info', title: 'Tidak Ada Sesi', text: 'Tidak ada sesi yang perlu dibayar dalam periode ini' });
                }
            } else {
                currentJumlahSesi = 0;
                if (gajiJumlahSesiDisplay) gajiJumlahSesiDisplay.value = '0 Sesi';
                updateTotal();
                if (gajiSubmitBtn) gajiSubmitBtn.disabled = false;
                Swal.fire({ icon: 'info', title: 'Tidak Ada Data', text: result.message || 'Tidak ada sesi yang perlu dibayar dalam periode ini' });
            }
        } catch (error) {
            currentJumlahSesi = 0;
            if (gajiJumlahSesiDisplay) gajiJumlahSesiDisplay.value = '0 Sesi (Error)';
            updateTotal();
            if (gajiSubmitBtn) gajiSubmitBtn.disabled = false;
            Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', html: '<p>Gagal mengambil data pembayaran</p><p class="text-sm text-gray-600 mt-2">' + error.message + '</p>' });
        } finally {
            isFetching = false;
        }
    }

    var dateTimeout;
    if (gajiTglMulai) gajiTglMulai.addEventListener('change', function () { clearTimeout(dateTimeout); dateTimeout = setTimeout(fetchPaymentData, 300); });
    if (gajiTglSelesai) gajiTglSelesai.addEventListener('change', function () { clearTimeout(dateTimeout); dateTimeout = setTimeout(fetchPaymentData, 300); });
    if (gajiBonus) gajiBonus.addEventListener('input', updateTotal);

    var bayarGajiForm = document.getElementById('bayarGajiForm');
    if (bayarGajiForm) {
        bayarGajiForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (currentJumlahSesi <= 0) {
                Swal.fire({ icon: 'error', title: 'Data Tidak Valid', text: 'Tidak ada sesi yang perlu dibayar dalam periode ini' });
                return;
            }

            var metode = document.getElementById('gajiMetode') ? document.getElementById('gajiMetode').value : '';
            if (!metode) {
                Swal.fire({ icon: 'warning', title: 'Data Tidak Lengkap', text: 'Silakan pilih metode pembayaran' });
                return;
            }

            var data = {
                id_trainer:         currentTrainerId,
                tgl_mulai:          gajiTglMulai ? gajiTglMulai.value : '',
                tgl_selesai:        gajiTglSelesai ? gajiTglSelesai.value : '',
                tgl_bayar:          document.getElementById('gajiTglBayar') ? document.getElementById('gajiTglBayar').value : '',
                metode_pembayaran:  metode,
                bonus:              gajiBonus ? (gajiBonus.value || 0) : 0
            };

            try {
                if (gajiSubmitBtn) { gajiSubmitBtn.disabled = true; gajiSubmitBtn.textContent = 'Menyimpan...'; }
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                var response = await fetch('/riwayat-gaji-trainer', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '' },
                    body: JSON.stringify(data)
                });
                var result = await response.json();

                if (response.ok && result.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, confirmButtonColor: '#3085d6' }).then(function () { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Terjadi kesalahan saat menyimpan' });
                    if (gajiSubmitBtn) { gajiSubmitBtn.disabled = false; gajiSubmitBtn.textContent = 'Simpan Pembayaran'; }
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', html: '<p>Gagal menyimpan pembayaran</p><p class="text-sm text-gray-600 mt-2">' + error.message + '</p>' });
                if (gajiSubmitBtn) { gajiSubmitBtn.disabled = false; gajiSubmitBtn.textContent = 'Simpan Pembayaran'; }
            }
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.open-bayar-gaji');
        if (btn) {
            currentTrainerId  = btn.dataset.trainerId;
            currentBaseRate   = parseFloat(btn.dataset.baseRate) || 0;
            currentJumlahSesi = 0;

            document.getElementById('gajiNamaTrainer').value    = btn.dataset.nama;
            document.getElementById('gajiBaseRateDisplay').value = formatRupiah(currentBaseRate);
            document.getElementById('gajiSesiDisplay').value    = btn.dataset.sesi + ' Sesi';
            if (gajiJumlahSesiDisplay) gajiJumlahSesiDisplay.value = 'Menunggu input tanggal...';
            if (gajiTglMulai) gajiTglMulai.value = '';
            if (gajiTglSelesai) gajiTglSelesai.value = '';
            if (gajiBonus) gajiBonus.value = 0;
            if (gajiTotalDibayarkan) gajiTotalDibayarkan.value = formatRupiah(0);
            if (gajiSubmitBtn) { gajiSubmitBtn.disabled = false; gajiSubmitBtn.textContent = 'Simpan Pembayaran'; }
            var tglBayarEl = document.getElementById('gajiTglBayar');
            if (tglBayarEl) tglBayarEl.value = new Date().toISOString().split('T')[0];
            var metodeEl = document.getElementById('gajiMetode');
            if (metodeEl) metodeEl.value = '';
            HexaModal.show('bayar-gaji-modal');
        }
    });

    AjaxTable.init('gajiTrainer', {
        url: '{{ route('riwayat_gaji_trainer.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            var sesi          = item.sesi_belum_dibayar;
            var sesilBadge    = sesi > 0
                ? AjaxTable.badge('warning', sesi + ' Sesi')
                : AjaxTable.badge('success', '0 Sesi');

            var bayarBtn = '<button type="button"'
                + ' class="open-bayar-gaji btn-action' + (sesi <= 0 ? ' opacity-50 cursor-not-allowed pointer-events-none' : '') + '"'
                + ' title="Bayar Gaji"'
                + ' data-trainer-id="' + item.id + '"'
                + ' data-base-rate="' + item.base_rate + '"'
                + ' data-sesi="' + sesi + '"'
                + ' data-nama="' + htmlEsc(item.nama) + '"'
                + (sesi <= 0 ? ' disabled' : '') + '>'
                + '<iconify-icon icon="hugeicons:money-send-square"></iconify-icon></button>';

            var historyBtn = '<a href="' + htmlEsc(item.history_url) + '" title="Lihat History"'
                + ' class="btn-action">'
                + '<iconify-icon icon="solar:clipboard-list-bold"></iconify-icon></a>';

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + '<td class="whitespace-nowrap"><div class="flex gap-2">' + bayarBtn + historyBtn + '</div></td>'
                + '<td class="whitespace-nowrap">' + htmlEsc(item.nama) + '</td>'
                + '<td class="whitespace-nowrap">' + htmlEsc(item.terakhir_gajian) + '</td>'
                + '<td class="whitespace-nowrap">' + sesilBadge + '</td>'
                + '<td class="whitespace-nowrap">Rp ' + Number(item.base_rate).toLocaleString('id-ID') + '</td>'
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
