@extends('layout.layout')
@php
    $title = 'Kehadiran Member';
    $subTitle = 'Kehadiran Member';
    $isLaporanMode = request()->routeIs('laporan.kehadiran');
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

    {{-- Card Scan Barcode - HANYA TAMPIL jika BUKAN mode laporan --}}
    @if (!$isLaporanMode)
        <div class="grid grid-cols-12 gap-5 mb-5">
            <div class="col-span-12 lg:col-span-6">
                <div class="card border-0">
                    <div class="card-header bg-primary-600 text-white">
                        <h6 class="text-lg font-semibold mb-0">📷 Scan Barcode untuk Absensi</h6>
                    </div>
                    <div class="card-body">
                        <form id="barcodeForm" action="{{ route('kehadiranmember.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-4">
                                <!-- Input Barcode -->
                                <div>
                                    <label for="barcode_input" class="form-label text-lg font-semibold">
                                        Scan Kartu Barcode Disini
                                        <span class="text-danger-600">*</span>
                                    </label>
                                    <input type="text" id="barcode_input" name="rfid"
                                        class="form-control form-control-lg text-center text-2xl font-bold tracking-wider"
                                        placeholder="Arahkan scanner ke sini..." autocomplete="off" autofocus required>
                                    <small class="text-muted">Klik di sini atau scan kartu dengan barcode scanner</small>
                                </div>

                                <!-- Live Webcam Preview -->
                                <div
                                    class="flex flex-col items-center border-2 border-dashed border-primary-300 rounded-lg p-4">
                                    <label class="font-semibold text-neutral-600 text-sm mb-2">Kamera Absensi</label>
                                    <video id="webcam" autoplay playsinline
                                        class="rounded-lg border border-neutral-300 w-full max-w-md"></video>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <p class="text-sm text-muted mt-2">Foto akan diambil otomatis saat scan barcode</p>
                                </div>

                                <!-- Status Indicator -->
                                <div id="scan-status" class="hidden">
                                    <div
                                        class="bg-info-50 border border-info-200 text-info-700 px-4 py-3 rounded-lg flex items-center">
                                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-info-700 mr-3">
                                        </div>
                                        <span>Memproses absensi...</span>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Instruksi Penggunaan -->
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h6 class="font-semibold mb-2 text-neutral-700">📝 Cara Menggunakan:</h6>
                            <ol class="list-decimal list-inside space-y-1 text-sm text-neutral-600">
                                <li>Pastikan cursor berada di kotak input (klik jika perlu)</li>
                                <li>Arahkan barcode scanner ke kartu member</li>
                                <li>Scanner akan otomatis membaca dan submit</li>
                                <li>Foto akan diambil secara otomatis dari webcam</li>
                                <li>Status IN/OUT ditentukan otomatis oleh sistem</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Info Absensi Terakhir -->
            <div class="col-span-12 lg:col-span-6">
                <div class="card border-0">
                    <div class="card-header bg-success-600 text-white">
                        <h6 class="text-lg font-semibold mb-0">📊 Absensi Hari Ini</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $today = now()->toDateString();
                            $todayAttendance = $kehadiranmembers->filter(function ($item) use ($today) {
                                return $item->created_at->toDateString() === $today;
                            });
                            $totalToday = $todayAttendance->count();
                            $totalIn = $todayAttendance->where('status', 'in')->count();
                            $totalOut = $todayAttendance->where('status', 'out')->count();
                            $uniqueMembers = $todayAttendance->unique('rfid')->count();
                        @endphp

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-primary-50 p-4 rounded-lg text-center">
                                <div class="text-3xl font-bold text-primary-600">{{ $totalIn }}</div>
                                <div class="text-sm text-neutral-600">Check IN</div>
                            </div>
                            <div class="bg-warning-50 p-4 rounded-lg text-center">
                                <div class="text-3xl font-bold text-warning-600">{{ $totalOut }}</div>
                                <div class="text-sm text-neutral-600">Check OUT</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-success-50 p-4 rounded-lg text-center">
                                <div class="text-3xl font-bold text-success-600">{{ $uniqueMembers }}</div>
                                <div class="text-sm text-neutral-600">Member Unik</div>
                            </div>
                            <div class="bg-info-50 p-4 rounded-lg text-center">
                                <div class="text-3xl font-bold text-info-600">{{ $totalToday }}</div>
                                <div class="text-sm text-neutral-600">Total Absensi</div>
                            </div>
                        </div>

                        <!-- 5 Absensi Terakhir Hari Ini -->
                        <div class="mt-6">
                            <h6 class="font-semibold mb-3 text-neutral-700">Absensi Terakhir:</h6>
                            <div class="space-y-2">
                                @forelse($todayAttendance->take(5) as $item)
                                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            @if ($item->foto)
                                                {{-- Lazy Loading untuk foto di recent attendance --}}
                                                <img data-src="{{ asset('storage/' . $item->foto) }}"
                                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%23e5e7eb'/%3E%3C/svg%3E"
                                                    alt="Photo"
                                                    class="lazy w-10 h-10 rounded-full object-cover bg-gray-200">
                                            @else
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <iconify-icon icon="mdi:account" class="text-gray-600"></iconify-icon>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-semibold text-sm">{{ $item->anggota->user->name }}</div>
                                                <div class="text-xs text-neutral-500">
                                                    {{ $item->created_at->format('H:i:s') }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            @if ($item->status === 'in')
                                                <span
                                                    class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-xs font-semibold">IN</span>
                                            @else
                                                <span
                                                    class="bg-warning-100 text-warning-600 px-3 py-1 rounded-full text-xs font-semibold">OUT</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-neutral-500 py-4">
                                        Belum ada absensi hari ini
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabel Data Kehadiran -->
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">
                        {{ $isLaporanMode ? 'Laporan Data Kehadiran Member' : 'Riwayat Kehadiran Member' }}</h6>
                    <div class="flex gap-2">
                        <!-- Tombol Export PDF -->
                        <button type="button" data-modal-target="export-pdf-modal" data-modal-toggle="export-pdf-modal"
                            class="text-white bg-danger-600 hover:bg-danger-700 focus:ring-4 focus:outline-none focus:ring-danger-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center">
                            <iconify-icon icon="carbon:document-pdf" class="mr-2"></iconify-icon>
                            Export PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Search & per page --}}
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

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="ajax-table border border-neutral-200 rounded-lg border-separate">
                            <thead>
                                <tr>
                                    <th>S.L</th>
                                    <th>ID Kartu</th>
                                    <th>Foto</th>
                                    <th>Nama Member</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    @if (!$isLaporanMode)
                                        @role('admin')
                                            <th>Aksi</th>
                                        @endrole
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="tbodyKehadiranMember">
                                <tr>
                                    <td colspan="{{ !$isLaporanMode && auth()->user()->hasRole('admin') ? 7 : 6 }}"
                                        class="text-center py-8">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination & info --}}
                    <div class="flex justify-between items-center mt-4 flex-wrap gap-2">
                        <span class="text-sm text-gray-500" id="infoKehadiranMember"></span>
                        <div id="paginationKehadiranMember" class="flex gap-1 flex-wrap"></div>
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
                <form action="{{ route('kehadiranmember.export_pdf') }}" method="POST" id="export-pdf-form">
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

            @if (!$isLaporanMode)
                // ==================== BARCODE SCANNER & WEBCAM ====================
                const video = document.getElementById('webcam');
                const canvas = document.getElementById('canvas');
                const form = document.getElementById('barcodeForm');
                const barcodeInput = document.getElementById('barcode_input');
                const scanStatus = document.getElementById('scan-status');

                let isProcessing = false;
                let scanTimeout = null;

                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        }
                    })
                    .then(stream => {
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        console.error('Tidak bisa mengakses kamera:', err);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kamera Tidak Tersedia',
                            text: 'Absensi akan tetap berjalan tanpa foto',
                            confirmButtonText: 'OK'
                        });
                    });

                barcodeInput.focus();

                barcodeInput.addEventListener('input', function() {
                    clearTimeout(scanTimeout);
                    scanTimeout = setTimeout(() => {
                        if (barcodeInput.value.length > 0) submitWithPhoto();
                    }, 100);
                });

                barcodeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(scanTimeout);
                        submitWithPhoto();
                    }
                });

                function submitWithPhoto() {
                    if (isProcessing || barcodeInput.value.trim() === '') return;
                    isProcessing = true;
                    scanStatus.classList.remove('hidden');

                    const context = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    if (video.readyState === video.HAVE_ENOUGH_DATA) {
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob((blob) => {
                            if (blob) {
                                const file = new File([blob], "absensi_" + Date.now() + ".png", {
                                    type: "image/png"
                                });
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                let fileInput = form.querySelector('input[name="foto"]');
                                if (!fileInput) {
                                    fileInput = document.createElement('input');
                                    fileInput.type = 'file';
                                    fileInput.name = 'foto';
                                    fileInput.hidden = true;
                                    form.appendChild(fileInput);
                                }
                                fileInput.files = dataTransfer.files;
                            }
                            form.submit();
                        }, 'image/png');
                    } else {
                        form.submit();
                    }
                }

                setInterval(() => {
                    if (document.activeElement !== barcodeInput && !isProcessing) {
                        barcodeInput.focus();
                    }
                }, 3000);
            @endif

            // ==================== AJAX TABLE ====================
            const isAdmin = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
            const isLaporan = {{ $isLaporanMode ? 'true' : 'false' }};
            const colSpan = (isAdmin && !isLaporan) ? 7 : 6;

            const perPageSelect = document.getElementById('perPageKehadiran');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    if (window._ajaxTables['tbodyKehadiranMember']) {
                        window._ajaxTables['tbodyKehadiranMember'].setPerPage(parseInt(this.value));
                    }
                });
            }

            AjaxTable.create({
                url: '{{ route('kehadiranmember.datatable') }}',
                tbodyId: 'tbodyKehadiranMember',
                paginationId: 'paginationKehadiranMember',
                infoId: 'infoKehadiranMember',
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

            // Fungsi hapus kehadiran
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

            // ==================== EXPORT PDF FILTER ====================
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
