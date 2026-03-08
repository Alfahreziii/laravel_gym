@extends('layout.layout')
@php
    $title = 'Anggota';
    $subTitle = 'Anggota';
    $isLaporanMode = request()->routeIs('laporan.anggota');
@endphp

@section('content')
    @if (session('success'))
        <div
            class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-4">
                {{ session('success') }}
            </div>
            <button class="remove-button text-success-600 text-2xl">
                <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div
            class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
            {{ session('error') }}
            <button class="remove-button text-danger-600 text-2xl">
                <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">
                        {{ $isLaporanMode ? 'Laporan Data Anggota GYM' : 'Data Anggota GYM' }}
                    </h6>
                    <div class="flex gap-2">
                        <!-- Tombol Export PDF -->
                        <button type="button" data-modal-target="export-pdf-modal" data-modal-toggle="export-pdf-modal"
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                            <iconify-icon icon="carbon:document-pdf" class="mr-2 text-lg"></iconify-icon>
                            Export PDF
                        </button>

                        {{-- Tombol Tambah Data hanya tampil jika BUKAN mode laporan --}}
                        @if (!$isLaporanMode)
                            @role('admin')
                                <a href="{{ route('anggota.create') }}"
                                    class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                                    + Tambah Data
                                </a>
                            @endrole
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    {{-- Search & per page --}}
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                        <input type="text" id="searchAnggota" placeholder="Search..."
                            class="form-control form-control-sm w-64">
                        <div class="flex items-center gap-2">
                            <select id="perPageAnggota" class="form-select form-select-sm w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-sm text-gray-500">entries per page</span>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="ajax-table border border-neutral-200 rounded-lg border-separate">
                            <thead>
                                <tr>
                                    <th scope="col">S.L</th>
                                    @if (!$isLaporanMode)
                                        @role('admin')
                                            <th scope="col">Aksi</th>
                                        @endrole
                                    @endif
                                    <th scope="col">ID Kartu</th>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Nama</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Tanggal Lahir</th>
                                    <th scope="col">No. Telp</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyAnggota">
                                <tr>
                                    <td colspan="{{ !$isLaporanMode && auth()->user()->hasRole('admin') ? 9 : 8 }}"
                                        class="text-center py-8">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination & info --}}
                    <div class="flex justify-between items-center mt-4 flex-wrap gap-2">
                        <span class="text-sm text-gray-500" id="infoAnggota"></span>
                        <div id="paginationAnggota" class="flex gap-1 flex-wrap"></div>
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
                <h1 class="text-xl font-semibold">Filter Export PDF</h1>
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
                <form action="{{ route('anggota.export_pdf') }}" method="POST" id="export-pdf-form">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Pilih Status Filter -->
                        <div class="col-span-12">
                            <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Pilih Status
                                Keanggotaan:</label>
                            <div class="space-y-2">
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="status_all" name="status_filter" value="all"
                                        class="w-4 h-4 text-primary-600" checked>
                                    <label for="status_all" class="ml-2 text-sm font-medium text-gray-900">Semua
                                        Data</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="status_aktif" name="status_filter" value="aktif"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="status_aktif" class="ml-2 text-sm font-medium text-gray-900">Anggota
                                        Aktif</label>
                                </div>
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="status_tidak_aktif" name="status_filter" value="tidak_aktif"
                                        class="w-4 h-4 text-primary-600">
                                    <label for="status_tidak_aktif" class="ml-2 text-sm font-medium text-gray-900">Anggota
                                        Tidak Aktif</label>
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
        document.addEventListener("DOMContentLoaded", function() {

            // Cek apakah user adalah admin (untuk tampilkan kolom aksi)
            const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
            const isLaporan = {{ $isLaporanMode ? 'true' : 'false' }};
            const colSpan = (isAdmin && !isLaporan) ? 9 : 8;

            // Inisialisasi perPage dari select
            let perPage = 10;
            const perPageSelect = document.getElementById('perPageAnggota');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    perPage = parseInt(this.value);
                    // Refresh table dengan perPage baru
                    if (window._ajaxTables['tbodyAnggota']) {
                        window._ajaxTables['tbodyAnggota'].setPerPage(perPage);
                    }
                });
            }

            AjaxTable.create({
                url: '{{ route('anggota.datatable') }}',
                tbodyId: 'tbodyAnggota',
                paginationId: 'paginationAnggota',
                infoId: 'infoAnggota',
                searchId: 'searchAnggota',
                perPage: 10,
                colSpan: colSpan,
                renderRow: function(item) {
                    const statusBadge = item.status ?
                        `<span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Aktif</span>` :
                        `<span class="bg-warning-100 text-warning-600 px-6 py-1.5 rounded-full font-medium text-sm">Tidak Aktif</span>`;

                    const aksiCol = (isAdmin && !isLaporan) ? `
                <td class="whitespace-nowrap">
                    <a href="${item.edit_url}" title="Edit Item"
                        class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                        <iconify-icon icon="lucide:edit"></iconify-icon>
                    </a>
                    <button onclick="confirmDelete('${item.delete_url}')" title="Hapus Item" type="button"
                        class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center">
                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                    </button>
                </td>` : '';

                    const foto = item.foto ?
                        `<img src="${item.foto}" alt="${item.name}"
                    class="w-10 h-10 rounded-full object-cover cursor-pointer bg-gray-200 anggota-photo"
                    data-photo="${item.foto}"
                    onclick="showPhoto('${item.foto}')"
                    loading="lazy">` :
                        `<span class="text-gray-400 italic">No photo</span>`;

                    return `
                <tr>
                    <td class="whitespace-nowrap">${item.no}</td>
                    ${aksiCol}
                    <td class="whitespace-nowrap">${item.id_kartu}</td>
                    <td class="whitespace-nowrap">${foto}</td>
                    <td class="whitespace-nowrap">${item.name}</td>
                    <td class="whitespace-nowrap">${item.email}</td>
                    <td class="whitespace-nowrap">${item.tgl_lahir}</td>
                    <td class="whitespace-nowrap">${item.no_telp}</td>
                    <td class="whitespace-nowrap">${statusBadge}</td>
                </tr>
            `;
                }
            });

            // Fungsi hapus dengan konfirmasi SweetAlert
            window.confirmDelete = function(url) {
                Swal.fire({
                    title: 'Apakah kamu yakin?',
                    text: "Data anggota dan akun login yang dihapus tidak bisa dikembalikan!",
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
                        form.innerHTML = `
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            };

            // Fungsi popup foto
            window.showPhoto = function(url) {
                Swal.fire({
                    imageUrl: url,
                    imageAlt: 'Foto Anggota',
                    showConfirmButton: false,
                    background: 'transparent',
                    width: 'auto',
                    padding: '0',
                    showCloseButton: true,
                });
            };

            // Alert remove button
            document.querySelectorAll('.remove-button').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.alert')?.remove();
                });
            });

        });
    </script>
@endsection
