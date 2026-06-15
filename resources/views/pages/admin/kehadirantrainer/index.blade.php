@extends('layout.layout')
@php
    $title = 'Kehadiran Trainer';
    $subTitle = 'Kehadiran Trainer';
    $isLaporanMode = request()->routeIs('laporan.kehadirantrainer');
@endphp

@section('content')
    @if (session('success'))
        <div
            class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-4">{{ session('success') }}</div>
            <button class="remove-button text-success-600 text-2xl">
                <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
            </button>
        </div>
    @endif
    @if (session('danger'))
        <div
            class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
            {{ session('danger') }}
            <button class="remove-button text-danger-600 text-2xl">
                <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
            </button>
        </div>
    @endif

    <!-- Tabel Data Kehadiran -->
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">
                        {{ $isLaporanMode ? 'Laporan Data Kehadiran Trainer' : 'Riwayat Kehadiran Trainer' }}
                    </h6>
                    <div class="flex gap-2">
                        <button type="button" data-modal-target="export-pdf-modal" data-modal-toggle="export-pdf-modal"
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                            <iconify-icon icon="carbon:export" class="mr-2"></iconify-icon>
                            Export Laporan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                        <input type="text" id="searchKehadiran" placeholder="Search..."
                            class="form-control form-control-sm w-64">
                        <div class="flex items-center gap-2">
                            <select id="perPageKehadiran" class="form-select form-select-sm w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-sm text-gray-500">entries per page</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="ajax-table border border-neutral-200 rounded-lg border-separate">
                            <thead>
                                <tr>
                                    <th>S.L</th>
                                    <th>ID Kartu</th>
                                    <th>Foto</th>
                                    <th>Nama Trainer</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    @if (!$isLaporanMode)
                                        @role('admin')
                                            <th>Aksi</th>
                                        @endrole
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="tbodyKehadiranTrainer">
                                <tr>
                                    <td colspan="{{ !$isLaporanMode && auth()->user()->hasRole('admin') ? 7 : 6 }}"
                                        class="text-center py-8">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center mt-4 flex-wrap gap-2">
                        <span class="text-sm text-gray-500" id="infoKehadiranTrainer"></span>
                        <div id="paginationKehadiranTrainer" class="flex gap-1 flex-wrap"></div>
                    </div>
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
                <form action="{{ route('kehadirantrainer.export_pdf') }}" method="POST" id="export-pdf-form">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        <div class="col-span-12">
                            <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Pilih Periode:</label>
                            <div class="space-y-2">
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_all" name="filter_type" value="all"
                                        class="w-4 h-4 text-primary-600" checked>
                                    <label for="filter_all" class="ml-2 text-sm font-medium text-gray-900">Semua
                                        Data</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="filter_range" name="filter_type" value="range"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="filter_range" class="ml-2 text-sm font-medium text-gray-900">Range
                                        Tanggal</label>
                                </div>
                            </div>
                        </div>
                        <div id="range-filter" class="hidden">
                            <div class="space-y-4">
                                <div>
                                    <label for="tanggal_dari"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Dari
                                        Tanggal:</label>
                                    <input type="date" id="tanggal_dari" name="tanggal_dari"
                                        class="form-control rounded-lg">
                                </div>
                                <div>
                                    <label for="tanggal_sampai"
                                        class="inline-block font-semibold text-neutral-600 text-sm mb-2">Sampai
                                        Tanggal:</label>
                                    <input type="date" id="tanggal_sampai" name="tanggal_sampai"
                                        class="form-control rounded-lg">
                                </div>
                            </div>
                        </div>
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
                                <button type="submit" formaction="{{ route('kehadirantrainer.export_excel') }}"
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
        document.addEventListener('DOMContentLoaded', () => {

            const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
            const isLaporan = {{ $isLaporanMode ? 'true' : 'false' }};
            const colSpan = (isAdmin && !isLaporan) ? 7 : 6;

            const perPageSelect = document.getElementById('perPageKehadiran');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    if (window._ajaxTables['tbodyKehadiranTrainer']) {
                        window._ajaxTables['tbodyKehadiranTrainer'].setPerPage(parseInt(this.value));
                    }
                });
            }

            AjaxTable.create({
                url: '{{ route('kehadirantrainer.datatable') }}',
                tbodyId: 'tbodyKehadiranTrainer',
                paginationId: 'paginationKehadiranTrainer',
                infoId: 'infoKehadiranTrainer',
                searchId: 'searchKehadiran',
                perPage: 10,
                colSpan: colSpan,
                renderRow: function(item) {
                    const foto = item.foto ?
                        `<img src="${item.foto}" alt="${item.name}"
                        class="w-10 h-10 rounded-lg object-cover cursor-pointer bg-gray-200"
                        onclick="showPhoto('${item.foto}', 'Foto Absensi')"
                        loading="lazy">` :
                        `<span class="text-gray-400 italic text-xs">No photo</span>`;

                    const statusBadge = item.status === 'in' ?
                        `<span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm">CHECK IN</span>` :
                        `<span class="bg-warning-100 text-warning-600 px-4 py-1.5 rounded-full font-medium text-sm">CHECK OUT</span>`;

                    const aksiCol = (isAdmin && !isLaporan) ? `
                        <td class="whitespace-nowrap">
                            <button onclick="confirmDeleteKehadiran('${item.delete_url}')"
                                class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center">
                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                            </button>
                        </td>` : '';

                    return `
                        <tr>
                            <td class="whitespace-nowrap">${item.no}</td>
                            <td class="whitespace-nowrap">${item.rfid}</td>
                            <td class="whitespace-nowrap">${foto}</td>
                            <td class="whitespace-nowrap">${item.name}</td>
                            <td class="whitespace-nowrap">${statusBadge}</td>
                            <td class="whitespace-nowrap">${item.time}</td>
                            ${aksiCol}
                        </tr>
                    `;
                }
            });

            window.confirmDeleteKehadiran = function(url) {
                Swal.fire({
                    title: 'Apakah kamu yakin?',
                    text: "Data absensi yang dihapus tidak bisa dikembalikan!",
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
            };

            window.showPhoto = function(url, alt = 'Foto') {
                Swal.fire({
                    imageUrl: url,
                    imageAlt: alt,
                    showConfirmButton: false,
                    background: 'transparent',
                    width: 'auto',
                    padding: '0',
                    showCloseButton: true,
                });
            };

            document.querySelectorAll('.remove-button').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.alert')?.remove();
                });
            });

            const filterRadios = document.querySelectorAll('input[name="filter_type"]');
            const rangeFilter = document.getElementById('range-filter');
            filterRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    rangeFilter.classList.toggle('hidden', this.value !== 'range');
                });
            });

            document.getElementById('export-pdf-form').addEventListener('submit', function(e) {
                const filterType = document.querySelector('input[name="filter_type"]:checked').value;
                if (filterType === 'range') {
                    const dari = document.getElementById('tanggal_dari').value;
                    const sampai = document.getElementById('tanggal_sampai').value;
                    if (!dari || !sampai) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Mohon lengkapi range tanggal!'
                        });
                        return false;
                    }
                    if (new Date(dari) > new Date(sampai)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Tanggal dari tidak boleh lebih besar dari tanggal sampai!'
                        });
                        return false;
                    }
                }
            });

        });
    </script>
@endsection
