@extends('layout.layout')
@php
    $title = 'Trainer';
    $subTitle = 'Trainer';
    $isLaporanMode = request()->routeIs('laporan.trainer');
@endphp

@section('content')
@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger">{{ session('error') }}</x-alert>
@endif

<x-page-table
    title="{{ $isLaporanMode ? 'Laporan Data Trainer' : 'Data Trainer' }}"
    subtitle="Kelola data trainer, status keaktifan, dan spesialisasi."
>
    <x-slot:actions>
        <button type="button" onclick="HexaModal.show('export-pdf-modal')" class="btn btn-secondary btn-sm">
            <iconify-icon icon="carbon:export" class="text-base"></iconify-icon>
            Export
        </button>
        @if (!$isLaporanMode)
            @role('admin')
                <a href="{{ route('trainer.create') }}" class="btn btn-primary btn-sm">+ Tambah Data</a>
            @endrole
        @endif
    </x-slot:actions>

    <x-data-table tableId="trainer" :colspan="$isLaporanMode ? 11 : 13" placeholder="Search...">
        <x-slot:header>
            <tr>
                <th>S.L</th>
                @if (!$isLaporanMode)
                    <th>Aksi</th>
                @endif
                <th>Fingerprint</th>
                <th>Foto</th>
                <th>Nama</th>
                <th>No Telp</th>
                <th>Spesialisasi</th>
                <th>Sesi Belum Dijalani</th>
                <th>Sesi Sudah Dijalani</th>
                <th>Experience</th>
                <th>Tanggal Gabung</th>
                <th>Status</th>
                <th>Fingerprint</th>
                @if (!$isLaporanMode)
                    <th></th>
                @endif
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

<x-modal id="export-pdf-modal" title="Filter Export Laporan">
        <x-slot:body>
            <form action="{{ route('trainer.export_pdf') }}" method="POST" id="export-pdf-form">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <!-- Pilih Status Filter -->
                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Pilih Status Trainer:</label>
                        <div class="space-y-2">
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_all" name="status_filter" value="all"
                                    class="w-4 h-4 text-primary-600" checked>
                                <label for="status_all" class="ml-2 text-sm font-medium text-gray-900">Semua Data</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_aktif" name="status_filter" value="aktif"
                                    class="w-4 h-4 text-primary-600">
                                <label for="status_aktif" class="ml-2 text-sm font-medium text-gray-900">Trainer Aktif</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_nonaktif" name="status_filter" value="nonaktif"
                                    class="w-4 h-4 text-primary-600">
                                <label for="status_nonaktif" class="ml-2 text-sm font-medium text-gray-900">Trainer Non-Aktif</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="radio" id="status_pending" name="status_filter" value="pending"
                                    class="w-4 h-4 text-primary-600">
                                <label for="status_pending" class="ml-2 text-sm font-medium text-gray-900">Trainer Pending</label>
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
                            <button type="submit" formaction="{{ route('trainer.export_excel') }}"
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
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
            const isSpv = {{ auth()->user()->hasRole('spv') ? 'true' : 'false' }};
            const isLaporan = {{ $isLaporanMode ? 'true' : 'false' }};
            const colSpan = isLaporan ? 11 : 13;

            AjaxTable.init('trainer', {
                url: '{{ route('trainer.datatable') }}',
                colSpan: colSpan,
                renderRow: function(item) {

                    const foto = item.foto ?
                        `<img src="${item.foto}" alt="${item.name}"
                    class="w-10 h-10 rounded-full object-cover cursor-pointer bg-gray-200"
                    onclick="showPhoto('${item.foto}', 'Foto Trainer')"
                    loading="lazy">` :
                        `<span class="text-gray-400 italic text-xs">No photo</span>`;
                    const fingerBadge = item.status_finger == 0
                        ? AjaxTable.badge('success', 'Enroll')
                        : item.status_finger == 1
                        ? AjaxTable.badge('danger', 'Delete')
                        : AjaxTable.badge('neutral', 'Default');
                    const statusTypeMap = { 'aktif': 'success', 'nonaktif': 'danger', 'pending': 'warning' };
                    const statusBadge = AjaxTable.badge(statusTypeMap[item.status] || 'neutral', item.status_label.text);

                    const aksiCol = !isLaporan ? `
                <td class="whitespace-nowrap">
                    ${(isAdmin || isSpv) ? `
                                                    <a href="${item.show_url}" title="Lihat detail"
                                                        class="btn-action">
                                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                                    </a>` : ''}
                    ${isAdmin ? `
                                                    <a href="${item.edit_url}" title="Edit Item"
                                                        class="btn-action">
                                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                                    </a>
                                                    <button onclick="confirmDeleteTrainer('${item.delete_url}')" title="Hapus Item"
                                                        class="btn-action btn-action-del">
                                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                                    </button>` : ''}
                </td>` : '';

                    const updateStatusCol = !isLaporan ? `
                <td class="whitespace-nowrap">
                    ${(isAdmin || isSpv) ? (
                        item.status !== 'aktif'
                            ? `<button onclick="updateStatus('${item.update_status_url}', 'aktif')"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                       bg-success-50 text-success-700 dark:bg-success-600/20 dark:text-success-400
                                       hover:opacity-80 transition-opacity cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-success-500 flex-shrink-0"></span>Izinkan Akses
                               </button>`
                            : `<button onclick="updateStatus('${item.update_status_url}', 'nonaktif')"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                       bg-danger-50 text-danger-700 dark:bg-danger-600/20 dark:text-danger-400
                                       hover:opacity-80 transition-opacity cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-danger-500 flex-shrink-0"></span>Batasi Akses
                               </button>`
                    ) : ''}
                </td>` : '';

                    return `
                <tr>
                    <td class="whitespace-nowrap">${item.no}</td>
                    ${aksiCol}
                    <td class="whitespace-nowrap">${item.rfid}</td>
                    <td class="whitespace-nowrap">${foto}</td>
                    <td class="whitespace-nowrap">${item.name}</td>
                    <td class="whitespace-nowrap">${item.no_telp}</td>
                    <td class="whitespace-nowrap">${item.specialisasi}</td>
                    <td class="whitespace-nowrap">${item.sesi_belum_dijalani}</td>
                    <td class="whitespace-nowrap">${item.sesi_sudah_dijalani}</td>
                    <td class="whitespace-nowrap">${item.experience}</td>
                    <td class="whitespace-nowrap">${item.tgl_gabung}</td>
                    <td class="whitespace-nowrap">${statusBadge}</td>
                    <td class="whitespace-nowrap">${fingerBadge}</td>
                    ${updateStatusCol}
                </tr>
            `;
                }
            });

            // Fungsi hapus trainer
            window.confirmDeleteTrainer = function(url) {
                Swal.fire({
                    title: 'Apakah kamu yakin?',
                    text: "Data trainer yang dihapus tidak bisa dikembalikan!",
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

            // Fungsi update status trainer
            window.updateStatus = function(url, status) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="status" value="${status}">
        `;
                document.body.appendChild(form);
                form.submit();
            };

            // Fungsi popup foto - reusable
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

            // Alert remove button
            document.querySelectorAll('.remove-button').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.alert')?.remove();
                });
            });

        });
    </script>
@endsection
