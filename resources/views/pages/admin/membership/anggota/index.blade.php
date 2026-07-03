@extends('layout.layout')
@php
    $title = 'Anggota';
    $subTitle = 'Anggota';
    $isLaporanMode = request()->routeIs('laporan.anggota');
@endphp

@section('content')
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="danger">{{ session('error') }}</x-alert>
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
                        <button type="button" onclick="HexaModal.show('export-pdf-modal')"
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                            <iconify-icon icon="carbon:export" class="mr-2 text-lg"></iconify-icon>
                            Export Laporan
                        </button>

                        {{-- Tombol Tambah Data hanya tampil jika BUKAN mode laporan --}}
                        @if (!$isLaporanMode)
                            @role('admin|spv')
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
                                    <th scope="col">Fingerprint</th>
                                    <th scope="col">Fingerprint</th>
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

    <x-modal id="export-pdf-modal" title="Filter Export Laporan">
        <x-slot:body>
            <form action="{{ route('anggota.export_pdf') }}" method="POST" id="export-pdf-form">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <!-- Pilih Status Filter -->
                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Pilih Status Keanggotaan:</label>
                        <div class="space-y-2">
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_all" name="status_filter" value="all"
                                    class="w-4 h-4 text-primary-600" checked>
                                <label for="status_all" class="ml-2 text-sm font-medium text-gray-900">Semua Data</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_aktif" name="status_filter" value="aktif"
                                    class="w-4 h-4 text-primary-600">
                                <label for="status_aktif" class="ml-2 text-sm font-medium text-gray-900">Anggota Aktif</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_tidak_aktif" name="status_filter" value="tidak_aktif"
                                    class="w-4 h-4 text-primary-600">
                                <label for="status_tidak_aktif" class="ml-2 text-sm font-medium text-gray-900">Anggota Tidak Aktif</label>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="col-span-12">
                        <div class="flex items-center justify-start gap-3 mt-6">
                            <button type="button" data-close-modal="export-pdf-modal"
                                class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                Cancel
                            </button>
                            <button type="submit"
                                class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                                Export PDF
                            </button>
                            <button type="submit" formaction="{{ route('anggota.export_excel') }}"
                                class="bg-success-600 hover:bg-success-700 text-white text-base px-6 py-3 rounded-lg inline-flex items-center">
                                <iconify-icon icon="carbon:document-export" class="mr-2"></iconify-icon>
                                Export Excel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot:body>
    </x-modal>

    <x-modal id="status-finger-modal" title="Ubah Status Fingerprint" maxWidth="max-w-[600px]">
        <x-slot:body>
            <form id="status-finger-form">
                <div class="grid grid-cols-1 gap-6">
                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Anggota:</label>
                        <p id="statusFingerAnggotaInfo" class="text-neutral-800 font-medium"></p>
                    </div>

                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Status
                            Fingerprint:</label>
                        <select id="statusFingerSelect" class="form-control" required>
                            <option value="2">— Default (Tidak Ada Aksi) —</option>
                            <option value="0">🟢 Enroll Fingerprint</option>
                            <option value="1">🔴 Delete Fingerprint</option>
                        </select>
                        <small class="text-muted">Enroll = daftarkan sidik jari, Delete = hapus sidik jari,
                            Default = tidak ada aksi</small>
                    </div>

                    <div class="col-span-12">
                        <div class="flex items-center justify-start gap-3 mt-6">
                            <button type="button" data-close-modal="status-finger-modal"
                                class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                Cancel
                            </button>
                            <button type="submit" id="statusFingerSubmitBtn"
                                class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                <iconify-icon icon="lucide:save" class="mr-2"></iconify-icon>
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot:body>
    </x-modal>
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
                    const fingerBadgeInner = item.status_finger == 0 ?
                        `<span class="bg-success-100 text-success-600 px-4 py-1.5 rounded-full font-medium text-sm flex items-center gap-1 w-fit">
                                <iconify-icon icon="lucide:scan-line"></iconify-icon> Enroll
                           </span>` :
                        item.status_finger == 1 ?
                        `<span class="bg-danger-100 text-danger-600 px-4 py-1.5 rounded-full font-medium text-sm flex items-center gap-1 w-fit">
                                <iconify-icon icon="lucide:trash-2"></iconify-icon> Delete
                           </span>` :
                        `<span class="bg-neutral-100 text-neutral-500 px-4 py-1.5 rounded-full font-medium text-sm flex items-center gap-1 w-fit">
                                <iconify-icon icon="lucide:minus-circle"></iconify-icon> Default
                           </span>`;

                    const escapeAttr = (val) => String(val ?? '').replace(/&/g, '&amp;').replace(/"/g,
                        '&quot;');

                    const fingerBadge = `
                <button type="button" onclick="openStatusFingerModal(this)"
                    data-url="${item.status_finger_url}"
                    data-status="${item.status_finger}"
                    data-name="${escapeAttr(item.name)}"
                    data-kartu="${escapeAttr(item.id_kartu)}"
                    class="cursor-pointer hover:opacity-80 transition" title="Klik untuk ubah status fingerprint">
                    ${fingerBadgeInner}
                </button>`;

                    const statusBadge = AjaxTable.badge(item.status ? 'success' : 'warning', item.status ? 'Aktif' : 'Tidak Aktif');

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
                    <td class="whitespace-nowrap">${fingerBadge}</td>
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

            // Fungsi popup ubah status fingerprint
            let statusFingerUrl = null;

            window.openStatusFingerModal = function(btn) {
                statusFingerUrl = btn.dataset.url;
                document.getElementById('statusFingerSelect').value = btn.dataset.status;
                document.getElementById('statusFingerAnggotaInfo').textContent =
                    `${btn.dataset.name} (ID Kartu: ${btn.dataset.kartu})`;
                HexaModal.show('status-finger-modal');
            };

            const statusFingerForm = document.getElementById('status-finger-form');
            if (statusFingerForm) {
                statusFingerForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!statusFingerUrl) return;

                    const submitBtn = document.getElementById('statusFingerSubmitBtn');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    submitBtn.disabled = true;

                    fetch(statusFingerUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken.content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                status_finger: document.getElementById('statusFingerSelect')
                                    .value
                            })
                        })
                        .then(res => res.json().then(data => ({
                            ok: res.ok,
                            data
                        })))
                        .then(({
                            ok,
                            data
                        }) => {
                            submitBtn.disabled = false;
                            if (!ok) {
                                Swal.fire('Gagal', data.message ||
                                    'Gagal memperbarui status fingerprint.', 'error');
                                return;
                            }

                            HexaModal.hide('status-finger-modal');

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            if (window._ajaxTables['tbodyAnggota']) {
                                window._ajaxTables['tbodyAnggota'].refresh();
                            }
                        })
                        .catch(() => {
                            submitBtn.disabled = false;
                            Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error');
                        });
                });
            }

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

});
    </script>
@endsection
