@extends('layout.layout')
@php
    $title          = 'Produk';
    $subTitle       = 'Daftar Produk';
    $isAdmin        = (bool) auth()->user()?->hasRole('admin');
    $isLaporanMode  = request()->routeIs('laporan.products');
    $colCount       = $isLaporanMode ? 10 : 11;
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

<x-page-table
    title="{{ $isLaporanMode ? 'Laporan Data Produk' : 'Data Produk' }}"
    subtitle="Kelola produk, stok, harga, dan kategori kasir."
>
    <x-slot:actions>
        <button type="button" onclick="HexaModal.show('export-pdf-modal')"
            class="btn btn-secondary btn-sm">
            <iconify-icon icon="carbon:export" class="text-base"></iconify-icon>
            Export
        </button>
        @if(!$isLaporanMode && $isAdmin)
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
            + Tambah Data
        </a>
        @endif
    </x-slot:actions>
    <x-data-table
        tableId="products"
        :colspan="$colCount"
        placeholder="Cari nama produk atau kategori...">
        <x-slot:header>
            <tr>
                <th scope="col">No</th>
                @if(!$isLaporanMode)
                <th scope="col">Aksi</th>
                @endif
                <th scope="col">Foto Produk</th>
                <th scope="col">Nama Produk</th>
                <th scope="col">
                    @if($isAdmin)
                    <span>Stok<br><small>(Klik angka untuk ubah stok)</small></span>
                    @else
                    Stok
                    @endif
                </th>
                <th scope="col">Status</th>
                <th scope="col">Kategori</th>
                <th scope="col">HPP</th>
                <th scope="col">Harga</th>
                <th scope="col">Diskon</th>
                <th scope="col">Reorder</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

<x-modal id="export-pdf-modal" title="Export Laporan Produk">
    <x-slot:body>
        <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-danger-100 rounded-full flex items-center justify-center mb-4">
                <iconify-icon icon="carbon:document-pdf" class="text-danger-600 text-3xl"></iconify-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Export Data Produk?</h3>
            <p class="text-sm text-gray-500">Laporan akan mencakup semua data produk yang terdaftar dalam sistem.</p>
        </div>
        <form id="exportProductForm" action="{{ route('products.export_pdf') }}" method="POST">
            @csrf
            <div class="flex items-center justify-center gap-3">
                <button type="button" data-close-modal="export-pdf-modal"
                    class="border border-neutral-300 hover:bg-neutral-100 text-neutral-700 text-base px-10 py-2.5 rounded-lg font-medium">
                    Batal
                </button>
                <button type="submit"
                    class="bg-danger-600 hover:bg-danger-700 text-white text-base px-8 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
                    <iconify-icon icon="carbon:document-pdf"></iconify-icon>
                    Export PDF
                </button>
                <button type="submit" formaction="{{ route('products.export_excel') }}"
                    class="bg-success-600 hover:bg-success-700 text-white text-base px-8 py-2.5 rounded-lg font-medium inline-flex items-center gap-2">
                    <iconify-icon icon="carbon:document-export"></iconify-icon>
                    Export Excel
                </button>
            </div>
        </form>
    </x-slot:body>
</x-modal>

@if($isAdmin)
<x-modal id="adjust-quantity-modal" title="Edit Quantity Produk">
    <x-slot:body>
        <form id="adjustQuantityForm" method="POST">
            @csrf
            <input type="hidden" id="adjustTypeInput" name="type" value="in">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="col-span-12">
                    <label class="form-label">Produk</label>
                    <input type="text" id="adjustProductName" class="form-control bg-gray-50" readonly>
                </div>
                <div class="col-span-12">
                    <label class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                        Jumlah Perubahan Stok
                    </label>
                    <input type="number" id="adjustQty" name="quantity" class="form-control rounded-lg" required min="1">
                </div>
                <div class="col-span-12">
                    <label class="inline-block font-semibold text-neutral-600 dark:text-neutral-300 text-sm mb-2">
                        Deskripsi
                    </label>
                    <textarea name="description" class="form-control rounded-lg" rows="3" placeholder="Contoh: Restok barang baru..."></textarea>
                </div>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" data-close-modal="adjust-quantity-modal"
            class="border border-neutral-300 hover:bg-neutral-100 text-neutral-700 text-base px-6 py-[11px] rounded-lg transition-colors">
            Batal
        </button>
        <button type="submit" form="adjustQuantityForm"
            onclick="document.getElementById('adjustTypeInput').value='out'"
            class="btn bg-warning-500 hover:bg-warning-600 border border-warning-600 text-white text-base px-6 py-3 rounded-lg">
            Kurangi Stok
        </button>
        <button type="submit" form="adjustQuantityForm"
            onclick="document.getElementById('adjustTypeInput').value='in'"
            class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
            Tambah Stok
        </button>
    </x-slot:footer>
</x-modal>
@endif

@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var isAdmin        = {{ $isAdmin ? 'true' : 'false' }};
    var isLaporanMode  = {{ $isLaporanMode ? 'true' : 'false' }};
    var csrfToken      = '{{ csrf_token() }}';

    function htmlEsc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    var adjustForm = document.getElementById('adjustQuantityForm');

    document.addEventListener('click', function (e) {
        var adjustBtn = e.target.closest('.open-adjust-modal');
        if (adjustBtn) {
            document.getElementById('adjustProductName').value = adjustBtn.dataset.name;
            if (adjustForm) adjustForm.action = adjustBtn.dataset.adjustUrl;
            var qtyEl = document.getElementById('adjustQty');
            if (qtyEl) qtyEl.value = '';
            var typeEl = document.getElementById('adjustTypeInput');
            if (typeEl) typeEl.value = 'in';
            HexaModal.show('adjust-quantity-modal');
            return;
        }

        var deleteBtn = e.target.closest('.delete-product-btn');
        if (deleteBtn) {
            var url = deleteBtn.dataset.action;
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Data produk yang dihapus tidak bisa dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = '<input type="hidden" name="_token" value="' + csrfToken + '">'
                        + '<input type="hidden" name="_method" value="DELETE">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    });

    AjaxTable.init('products', {
        url: '{{ route('products.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            var imgTag = '<img src="' + htmlEsc(item.image_url) + '" alt="' + htmlEsc(item.name) + '" class="w-10 h-10 rounded-full object-cover">';

            var stokCell = '';
            if (isAdmin) {
                stokCell = '<button type="button" class="open-adjust-modal text-primary-600 font-bold" title="Ubah Stok"'
                    + ' data-name="' + htmlEsc(item.name) + '"'
                    + ' data-adjust-url="' + htmlEsc(item.adjust_url) + '">'
                    + item.quantity + '</button>';
            } else {
                stokCell = item.quantity;
            }

            var statusBadge = item.is_active
                ? AjaxTable.badge('success', 'Aktif')
                : AjaxTable.badge('danger', 'Nonaktif');

            var aksiCol = '';
            if (!isLaporanMode) {
                var aksiHtml = '';
                if (isAdmin) {
                    aksiHtml += '<a href="' + htmlEsc(item.edit_url) + '" title="Edit Item" class="btn-action"><iconify-icon icon="lucide:edit"></iconify-icon></a>';
                }
                aksiHtml += '<a href="' + htmlEsc(item.logs_url) + '" title="Riwayat Stok" class="btn-action btn-action-warn"><i class="ri-calendar-schedule-line"></i></a>';
                if (isAdmin) {
                    aksiHtml += '<button type="button" class="delete-product-btn btn-action btn-action-del" title="Hapus Item" data-action="' + htmlEsc(item.delete_url) + '"><iconify-icon icon="mingcute:delete-2-line"></iconify-icon></button>';
                }
                aksiCol = '<td class="whitespace-nowrap"><div class="flex gap-2">' + aksiHtml + '</div></td>';
            }

            return '<tr>'
                + '<td class="whitespace-nowrap">' + item.no + '</td>'
                + aksiCol
                + '<td class="whitespace-nowrap">' + imgTag + '</td>'
                + '<td class="whitespace-nowrap">' + htmlEsc(item.name) + '</td>'
                + '<td class="whitespace-nowrap">' + stokCell + '</td>'
                + '<td class="whitespace-nowrap">' + statusBadge + '</td>'
                + '<td class="whitespace-nowrap">' + htmlEsc(item.kategori_name) + '</td>'
                + '<td class="whitespace-nowrap">Rp ' + Number(item.hpp).toLocaleString('id-ID') + '</td>'
                + '<td class="whitespace-nowrap">Rp ' + Number(item.price).toLocaleString('id-ID') + '</td>'
                + '<td class="whitespace-nowrap">' + htmlEsc(item.diskon) + '</td>'
                + '<td class="whitespace-nowrap">' + item.reorder + '</td>'
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
