@extends('layout.layout')
@php
    $title          = 'Member Trainer';
    $subTitle       = 'Member Trainer';
    $isLaporanMode  = request()->routeIs('laporan.membertrainer');
    $isAdmin        = (bool) auth()->user()?->hasRole('admin');
    $colCount       = ($isAdmin && !$isLaporanMode) ? 11 : 10;
@endphp

@section('content')
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('danger'))
        <x-alert type="danger">{{ session('danger') }}</x-alert>
    @endif

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">
                        {{ $isLaporanMode ? 'Laporan Data Member Trainer' : 'Data Member Trainer' }}
                    </h6>
                    <div class="flex gap-2">
                        <!-- Tombol Export PDF -->
                        <button type="button" data-modal-target="export-pdf-modal" data-modal-toggle="export-pdf-modal"
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                            <iconify-icon icon="carbon:export" class="mr-2 text-lg"></iconify-icon>
                            Export Laporan
                        </button>

                        {{-- Tombol Tambah Data hanya tampil jika BUKAN mode laporan --}}
                        @if (!$isLaporanMode)
                            @role('admin|spv')
                                <a href="{{ route('membertrainer.create') }}"
                                    class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                                    + Tambah Data
                                </a>
                            @endrole
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <x-data-table
                        tableId="memberTrainer"
                        :colspan="$colCount"
                        placeholder="Cari kode, anggota, trainer, paket...">
                        <x-slot:header>
                            <tr>
                                <th>S.L</th>
                                @if (!$isLaporanMode)
                                    @role('admin')
                                        <th>Aksi</th>
                                    @endrole
                                @endif
                                <th>Kode Transaksi</th>
                                <th>Nama Anggota</th>
                                <th>Nama Trainer</th>
                                <th>Sesi</th>
                                <th>Paket</th>
                                <th>Tgl Bayar Awal</th>
                                <th>Metode Pembayaran</th>
                                <th>Status Pembayaran</th>
                                <th>Total Biaya</th>
                            </tr>
                        </x-slot:header>
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Export PDF -->
    <div id="export-pdf-modal" tabindex="-1"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="rounded-2xl bg-white max-w-[600px] w-full">
            <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                <h1 class="text-xl font-semibold">Filter Export Laporan</h1>
                <button data-modal-hide="export-pdf-modal" type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="p-6">
                <form action="{{ route('membertrainer.export_pdf') }}" method="POST" id="export-pdf-form">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Filter Status Pembayaran -->
                        <div class="col-span-12">
                            <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Pilih Status
                                Pembayaran:</label>
                            <div class="space-y-2">
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="status_all" name="status_filter" value="all"
                                        class="w-4 h-4 text-primary-600" checked>
                                    <label for="status_all" class="ml-2 text-sm font-medium text-gray-900">Semua
                                        Data</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="status_lunas" name="status_filter" value="lunas"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="status_lunas" class="ml-2 text-sm font-medium text-gray-900">Lunas</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="status_belum_lunas" name="status_filter" value="belum_lunas"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="status_belum_lunas" class="ml-2 text-sm font-medium text-gray-900">Belum
                                        Lunas</label>
                                </div>
                            </div>
                        </div>

                        <!-- Pilih Tipe Filter Tanggal -->
                        <div class="col-span-12">
                            <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Filter Tanggal
                                (Berdasarkan tanggal mulai):</label>
                            <div class="space-y-2">
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_all" name="filter_type" value="all"
                                        class="w-4 h-4 text-primary-600" checked>
                                    <label for="filter_all" class="ml-2 text-sm font-medium text-gray-900">Semua
                                        Tanggal</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_single" name="filter_type" value="single"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_single" class="ml-2 text-sm font-medium text-gray-900">Bulan &
                                        Tahun Tertentu</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_range" name="filter_type" value="range"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_range" class="ml-2 text-sm font-medium text-gray-900">Range
                                        Bulan</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_daily" name="filter_type" value="daily"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_daily" class="ml-2 text-sm font-medium text-gray-900">Range
                                        Harian</label>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Single Month -->
                        <div id="single-filter" class="hidden">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="bulan"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Bulan:</label>
                                    <select id="bulan" name="bulan" class="form-control rounded-lg">
                                        <option value="">Pilih Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="tahun"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tahun:</label>
                                    <input type="number" id="tahun" name="tahun" class="form-control rounded-lg"
                                        placeholder="2024" min="2000" max="2100">
                                </div>
                            </div>
                        </div>

                        <!-- Filter Range Bulan -->
                        <div id="range-filter" class="hidden">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="bulan_dari"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Dari
                                            Bulan:</label>
                                        <select id="bulan_dari" name="bulan_dari" class="form-control rounded-lg">
                                            <option value="">Pilih Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tahun_dari"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tahun:</label>
                                        <input type="number" id="tahun_dari" name="tahun_dari"
                                            class="form-control rounded-lg" placeholder="2024" min="2000"
                                            max="2100">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="bulan_sampai"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Sampai
                                            Bulan:</label>
                                        <select id="bulan_sampai" name="bulan_sampai" class="form-control rounded-lg">
                                            <option value="">Pilih Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tahun_sampai"
                                            class="inline-block font-semibold text-neutral-600 text-sm mb-2">Tahun:</label>
                                        <input type="number" id="tahun_sampai" name="tahun_sampai"
                                            class="form-control rounded-lg" placeholder="2024" min="2000"
                                            max="2100">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Range Harian -->
                        <div id="daily-filter" class="hidden">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="tgl_dari"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Dari
                                        Tanggal:</label>
                                    <input type="date" id="tgl_dari" name="tgl_dari"
                                        class="form-control rounded-lg">
                                </div>
                                <div>
                                    <label for="tgl_sampai"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Sampai
                                        Tanggal:</label>
                                    <input type="date" id="tgl_sampai" name="tgl_sampai"
                                        class="form-control rounded-lg">
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-span-12">
                            <div class="flex items-center justify-start gap-3 mt-6">
                                <button type="button" data-modal-hide="export-pdf-modal"
                                    class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                    <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                                    Export PDF
                                </button>
                                <button type="submit" formaction="{{ route('membertrainer.export_excel') }}"
                                    class="bg-success-600 hover:bg-success-700 text-white text-base px-6 py-3 rounded-lg inline-flex items-center">
                                    <iconify-icon icon="carbon:document-export" class="mr-2"></iconify-icon>
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/ajax-table.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const isAdmin       = {{ $isAdmin ? 'true' : 'false' }};
    const isLaporanMode = {{ $isLaporanMode ? 'true' : 'false' }};

    function confirmDelete(url) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Data member trainer yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `@csrf<input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    AjaxTable.init('memberTrainer', {
        url: '{{ route('membertrainer.datatable') }}',
        colSpan: {{ $colCount }},
        renderRow: function (item) {
            const actionCol = (isAdmin && !isLaporanMode)
                ? `<td class="whitespace-nowrap">
                       <div class="flex gap-2">
                           <a href="${item.edit_url}" title="Edit Item"
                              class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                               <iconify-icon icon="lucide:edit"></iconify-icon>
                           </a>
                           <button onclick="confirmDelete('${item.delete_url}')" title="Hapus Item" type="button"
                               class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center">
                               <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                           </button>
                       </div>
                   </td>`
                : '';

            return `<tr>
                <td class="whitespace-nowrap">${item.no}</td>
                ${actionCol}
                <td class="whitespace-nowrap"><a class="text-primary-600" href="${item.edit_url}">${item.kode_transaksi}</a></td>
                <td class="whitespace-nowrap">${item.nama_anggota}</td>
                <td class="whitespace-nowrap">${item.nama_trainer}</td>
                <td class="whitespace-nowrap">${item.sesi_text}</td>
                <td class="whitespace-nowrap">${item.nama_paket}</td>
                <td class="whitespace-nowrap">${item.tgl_bayar_awal}</td>
                <td class="whitespace-nowrap">${item.metode_pembayaran}</td>
                <td class="whitespace-nowrap">${AjaxTable.badge(item.status_pembayaran === 'Lunas' ? 'success' : 'warning', item.status_pembayaran)}</td>
                <td class="whitespace-nowrap">${item.total_biaya}</td>
            </tr>`;
        }
    });

    // Toggle filter sections
    const filterRadios = document.querySelectorAll('input[name="filter_type"]');
    const singleFilter = document.getElementById('single-filter');
    const rangeFilter  = document.getElementById('range-filter');
    const dailyFilter  = document.getElementById('daily-filter');

    filterRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            singleFilter.classList.add('hidden');
            rangeFilter.classList.add('hidden');
            dailyFilter.classList.add('hidden');

            if (this.value === 'single') singleFilter.classList.remove('hidden');
            else if (this.value === 'range') rangeFilter.classList.remove('hidden');
            else if (this.value === 'daily') dailyFilter.classList.remove('hidden');
        });
    });

    // Form validation
    document.getElementById('export-pdf-form').addEventListener('submit', function (e) {
        const filterType = document.querySelector('input[name="filter_type"]:checked').value;

        if (filterType === 'single') {
            const bulan = document.getElementById('bulan').value;
            const tahun = document.getElementById('tahun').value;
            if (!bulan || !tahun) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon pilih bulan dan tahun terlebih dahulu!' });
                return false;
            }
        } else if (filterType === 'range') {
            const bulanDari   = document.getElementById('bulan_dari').value;
            const tahunDari   = document.getElementById('tahun_dari').value;
            const bulanSampai = document.getElementById('bulan_sampai').value;
            const tahunSampai = document.getElementById('tahun_sampai').value;
            if (!bulanDari || !tahunDari || !bulanSampai || !tahunSampai) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon lengkapi range bulan dan tahun!' });
                return false;
            }
        } else if (filterType === 'daily') {
            const tglDari   = document.getElementById('tgl_dari').value;
            const tglSampai = document.getElementById('tgl_sampai').value;
            if (!tglDari || !tglSampai) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon lengkapi tanggal dari dan sampai!' });
                return false;
            }
            if (tglDari > tglSampai) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Tanggal "dari" tidak boleh lebih besar dari tanggal "sampai"!' });
                return false;
            }
        }
    });
});
</script>
@endsection
