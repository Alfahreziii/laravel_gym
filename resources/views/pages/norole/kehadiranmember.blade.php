<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Member - Gym System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <!-- Remix Icon -->
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">

    <!-- Ajax Table CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/ajax-table.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .scanner-container {
            position: sticky;
            top: 20px;
        }

        .stats-card {
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .webcam-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
        }

        #webcam {
            width: 100%;
            height: auto;
            display: block;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .status-indicator::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-in::before {
            background-color: #10b981;
        }

        .status-out::before {
            background-color: #f59e0b;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .recent-item {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-neutral-100">
    <!-- Header -->
    <div class="flex">
        <div class="bg-white">
            <a href="{{ route('index') }}" class="sidebar-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
                <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
            </a>
        </div>
        <div class="navbar-header border-b border-neutral-200 w-full">
            <div class="flex items-center justify-end py-2 px-6">
                <h6 class="text-lg font-semibold text-neutral-700">Sistem Absensi Member</h6>
            </div>
        </div>
    </div>

    <div class="wrapper w-full p-6">
        <!-- Alert Messages -->
        @if (session('success'))
            <div
                class="alert alert-success bg-success-50 text-success-600 border-success-200 px-6 py-4 mb-6 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="ri-checkbox-circle-line text-2xl"></i>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
                <button class="remove-button text-success-600 hover:text-success-800 text-2xl">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        @endif

        @if (session('danger'))
            <div
                class="alert alert-danger bg-danger-50 text-danger-600 border-danger-200 px-6 py-4 mb-6 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="ri-error-warning-line text-2xl"></i>
                    <span class="font-semibold">{{ session('danger') }}</span>
                </div>
                <button class="remove-button text-danger-600 hover:text-danger-800 text-2xl">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        @endif

        <div class="grid grid-cols-12 gap-6">
            <!-- Kolom Kiri: Data Table -->
            <div class="col-span-12 lg:col-span-7 xl:col-span-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    @php
                        $today = now()->toDateString();
                        $todayData = $kehadiranmembers->filter(
                            fn($item) => $item->created_at->toDateString() === $today,
                        );
                        $totalToday = $todayData->count();
                        $totalIn = $todayData->where('status', 'in')->count();
                        $totalOut = $todayData->where('status', 'out')->count();
                        $uniqueMembers = $todayData->unique('rfid')->count();
                    @endphp

                    <div
                        class="stats-card card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-cyan-600/10 to-bg-white">
                        <div class="card-body p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-neutral-900 mb-1">Check IN</p>
                                    <h6 class="mb-0">{{ $totalIn }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="stats-card card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-purple-600/10 to-bg-white">
                        <div class="card-body p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-neutral-900 mb-1">Check OUT</p>
                                    <h6 class="mb-0">{{ $totalOut }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="stats-card card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-blue-600/10 to-bg-white">
                        <div class="card-body p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-neutral-900 mb-1">Member Unik</p>
                                    <h6 class="mb-0">{{ $uniqueMembers }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="stats-card card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-cyan-600/10 to-bg-white">
                        <div class="card-body p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-neutral-900 mb-1">Total Hari Ini</p>
                                    <h6 class="mb-0">{{ $totalToday }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card border-0 shadow-lg rounded-xl overflow-hidden">
                    <div class="card-header bg-white border-b border-neutral-200 p-6">
                        <h6 class="text-lg font-bold text-neutral-800">
                            <i class="ri-history-line mr-2"></i>
                            Riwayat Kehadiran Member
                        </h6>
                    </div>
                    <div class="card-body p-6">
                        {{-- Search & per page --}}
                        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                            <input type="text" id="searchAbsen" placeholder="Search nama, RFID, status..."
                                class="form-control form-control-sm w-64">
                            <div class="flex items-center gap-2">
                                <select id="perPageAbsen" class="form-select form-select-sm w-auto">
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
                                        <th class="text-left">No</th>
                                        <th class="text-left">ID Kartu</th>
                                        <th class="text-left">Foto</th>
                                        <th class="text-left">Nama</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-left">Waktu</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyAbsen">
                                    <tr>
                                        <td colspan="7" class="text-center py-8">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination & info --}}
                        <div class="flex justify-between items-center mt-4 flex-wrap gap-2">
                            <span class="text-sm text-gray-500" id="infoAbsen"></span>
                            <div id="paginationAbsen" class="flex gap-1 flex-wrap"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Scanner Form -->
            <div class="col-span-12 lg:col-span-5 xl:col-span-4">
                <div class="scanner-container">
                    <div class="card border-0 shadow-xl rounded-xl overflow-hidden">
                        <div class="card-header bg-gradient-to-r from-primary-600 to-primary-700 text-white p-6">
                            <h6 class="text-lg font-bold flex items-center">
                                <i class="ri-qr-scan-2-line text-2xl mr-3"></i>
                                Scan Kartu Member
                            </h6>
                        </div>
                        <div class="card-body p-6">
                            <form id="kehadiranForm" action="{{ route('absen.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <!-- RFID Input -->
                                <div class="mb-6">
                                    <label for="rfid" class="block font-semibold text-neutral-700 text-sm mb-3">
                                        <i class="ri-barcode-line mr-1"></i>
                                        ID Kartu / RFID
                                    </label>
                                    <input type="text" id="rfid" name="rfid"
                                        class="form-control text-center text-xl font-bold tracking-wider py-4 rounded-xl border-2 border-neutral-300 focus:border-primary-500"
                                        placeholder="Scan kartu di sini..." autocomplete="off" autofocus required>
                                    <p class="text-xs text-neutral-500 mt-2 text-center">
                                        <i class="ri-information-line"></i>
                                        Klik input atau langsung scan dengan barcode scanner
                                    </p>
                                </div>

                                <!-- Webcam Preview -->
                                <div class="mb-6">
                                    <label class="block font-semibold text-neutral-700 text-sm mb-3">
                                        <i class="ri-camera-line mr-1"></i>
                                        Kamera Absensi
                                    </label>
                                    <div class="webcam-container border-2 border-neutral-300 bg-neutral-900">
                                        <video id="webcam" autoplay playsinline></video>
                                    </div>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <p class="text-xs text-neutral-500 mt-2 text-center">
                                        Foto akan diambil otomatis saat scan
                                    </p>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit"
                                    class="btn btn-primary-600 w-full flex items-center justify-center gap-2 py-3 rounded-xl font-semibold hover:bg-primary-700 transition-colors">
                                    <i class="ri-save-line text-xl"></i>
                                    <span>Simpan Absensi</span>
                                </button>
                            </form>

                            <!-- Instructions -->
                            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <h6 class="font-bold text-blue-800 mb-3 flex items-center">
                                    <i class="ri-lightbulb-line mr-2"></i>
                                    Cara Penggunaan:
                                </h6>
                                <ol class="list-decimal list-inside p-4 space-y-2 text-sm text-blue-700">
                                    <li>Pastikan kamera aktif dan terang</li>
                                    <li>Arahkan scanner ke kartu member</li>
                                    <li>Sistem akan otomatis detect IN/OUT</li>
                                    <li>Foto diambil otomatis dari webcam</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card border-0 shadow-lg rounded-xl overflow-hidden mt-6">
                    <div class="card-header bg-white border-b border-neutral-200 p-4">
                        <h6 class="font-bold text-neutral-800 flex items-center">
                            <i class="ri-time-line mr-2"></i>
                            Aktivitas Terbaru
                        </h6>
                    </div>
                    <div class="card-body p-4 max-h-96 overflow-y-auto">
                        @forelse($todayData->take(10) as $item)
                            <div
                                class="recent-item flex items-center justify-between py-3 border-b border-neutral-100 last:border-0">
                                <div class="flex items-center gap-3">
                                    @if ($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}"
                                            class="w-10 h-10 rounded-full object-cover shadow-sm">
                                    @else
                                        <div
                                            class="w-10 h-10 bg-neutral-200 rounded-full flex items-center justify-center">
                                            <i class="ri-user-line text-neutral-500"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-sm text-neutral-800">
                                            {{ $item->nama ?? '-' }}
                                        </div>
                                        <div class="text-xs text-neutral-500">{{ $item->created_at->format('H:i:s') }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if ($item->status === 'in')
                                        <span
                                            class="bg-success-100 text-success-700 px-3 py-1 rounded-full text-xs font-bold">IN</span>
                                    @else
                                        <span
                                            class="bg-warning-100 text-warning-700 px-3 py-1 rounded-full text-xs font-bold">OUT</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-neutral-500">
                                <i class="ri-inbox-line text-4xl mb-2"></i>
                                <p class="text-sm">Belum ada absensi hari ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            const form = document.getElementById('kehadiranForm');
            const rfidInput = document.getElementById('rfid');
            let isProcessing = false;

            // ==================== WEBCAM ====================
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    }
                })
                .then(stream => video.srcObject = stream)
                .catch(err => {
                    console.error('Kamera error:', err);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kamera Tidak Tersedia',
                        text: 'Absensi akan berjalan tanpa foto',
                        confirmButtonText: 'OK'
                    });
                });

            // Auto-focus input
            setInterval(() => {
                if (document.activeElement !== rfidInput && !isProcessing) {
                    rfidInput.focus();
                }
            }, 2000);

            // Handle form submit — AJAX, tidak reload page
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (isProcessing || rfidInput.value.trim() === '') return;
                isProcessing = true;

                function submitAjax(fotoBlob) {
                    const fd = new FormData(form);
                    if (fotoBlob) {
                        fd.set('foto', new File([fotoBlob], `absensi_${Date.now()}.png`, {
                            type: 'image/png'
                        }));
                    }

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: fd
                        })
                        .then(async r => {
                            const text = await r.text();
                            let res;
                            try {
                                res = JSON.parse(text);
                            } catch (e) {
                                // Response bukan JSON — tampilkan di console untuk debug
                                console.error('Non-JSON response (status ' + r.status + '):', text
                                    .substring(0, 500));
                                throw new Error('Server error ' + r.status + ': ' + text.substring(
                                    0, 100));
                            }

                            rfidInput.value = '';
                            rfidInput.focus();
                            isProcessing = false;

                            if (res.success) {
                                showInlineAlert('success', res.message);

                                if (res.notif) {
                                    // Update lastSeen ke timestamp absen ini
                                    // sehingga polling tidak nangkap data yang sama
                                    window._absenLastSeen = res.notif.timestamp;
                                    showNotif(res.notif);
                                    speakText(buildTTS(res.notif));
                                }

                                // Refresh datatable pakai method refresh() dari ajax-table.js
                                if (window._ajaxTables && window._ajaxTables['tbodyAbsen']) {
                                    window._ajaxTables['tbodyAbsen'].refresh();
                                }
                            } else {
                                showInlineAlert('danger', res.message || 'Terjadi kesalahan.');
                            }
                        })
                        .catch(err => {
                            isProcessing = false;
                            rfidInput.value = '';
                            rfidInput.focus();
                            console.error('Submit error:', err.message);
                            showInlineAlert('danger', 'Error: ' + err.message);
                        });
                }

                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth > 0) {
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(blob => submitAjax(blob), 'image/png');
                } else {
                    submitAjax(null);
                }
            });

            // Tampilkan alert inline (tanpa reload)
            window.showInlineAlert = function(type, msg) {
                const existing = document.getElementById('inline-alert');
                if (existing) existing.remove();

                const isSuccess = type === 'success';
                const div = document.createElement('div');
                div.id = 'inline-alert';
                div.className =
                    `alert alert-${type} bg-${type}-50 text-${type}-600 border-${type}-200 px-6 py-4 mb-6 rounded-xl flex items-center justify-between shadow-sm`;
                div.innerHTML = `
                    <div class="flex items-center gap-3">
                        <i class="${isSuccess ? 'ri-checkbox-circle-line' : 'ri-error-warning-line'} text-2xl"></i>
                        <span class="font-semibold">${msg}</span>
                    </div>
                    <button onclick="this.closest('.alert').remove()" class="text-${type}-600 hover:text-${type}-800 text-2xl">
                        <i class="ri-close-line"></i>
                    </button>`;

                const wrapper = document.querySelector('.wrapper');
                wrapper.insertBefore(div, wrapper.firstChild);

                setTimeout(() => div.remove(), 5000);
            };

            // ==================== AJAX TABLE ====================
            const perPageSelect = document.getElementById('perPageAbsen');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    if (window._ajaxTables['tbodyAbsen']) {
                        window._ajaxTables['tbodyAbsen'].setPerPage(parseInt(this.value));
                    }
                });
            }

            AjaxTable.create({
                url: '{{ route('absen.datatable') }}',
                tbodyId: 'tbodyAbsen',
                paginationId: 'paginationAbsen',
                infoId: 'infoAbsen',
                searchId: 'searchAbsen',
                perPage: 10,
                colSpan: 7,
                renderRow: function(item) {
                    const foto = item.foto ?
                        `<img src="${item.foto}" alt="Photo"
                            class="w-12 h-12 rounded-lg object-cover cursor-pointer shadow-sm hover:shadow-md transition-shadow"
                            onclick="showPhoto('${item.foto}')"
                            loading="lazy">` :
                        `<div class="w-12 h-12 bg-neutral-200 rounded-lg flex items-center justify-center">
                            <i class="ri-user-line text-neutral-400 text-xl"></i>
                           </div>`;

                    const statusBadge = item.status === 'in' ?
                        `<span class="status-indicator status-in bg-success-100 text-success-700 px-4 py-1.5 rounded-full text-xs font-bold">IN</span>` :
                        `<span class="status-indicator status-out bg-warning-100 text-warning-700 px-4 py-1.5 rounded-full text-xs font-bold">OUT</span>`;

                    return `
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="py-3 whitespace-nowrap">${item.no}</td>
                            <td class="py-3 font-mono text-sm whitespace-nowrap">${item.rfid}</td>
                            <td class="py-3 whitespace-nowrap">${foto}</td>
                            <td class="py-3 font-semibold whitespace-nowrap">${item.name}</td>
                            <td class="py-3 text-center whitespace-nowrap">${statusBadge}</td>
                            <td class="py-3 text-sm text-neutral-600 whitespace-nowrap">
                                <div>${item.date}</div>
                                <div class="text-xs text-neutral-500">${item.time}</div>
                            </td>
                            <td class="py-3 text-center whitespace-nowrap">
                                <button onclick="confirmDeleteAbsen('${item.delete_url}')"
                                    class="w-9 h-9 bg-danger-100 hover:bg-danger-200 text-danger-600 rounded-lg inline-flex items-center justify-center transition-colors">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }
            });

            // ==================== FUNGSI HAPUS ====================
            window.confirmDeleteAbsen = function(url) {
                Swal.fire({
                    title: 'Hapus Data?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const f = document.createElement('form');
                        f.method = 'POST';
                        f.action = url;
                        f.innerHTML = `
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                        `;
                        document.body.appendChild(f);
                        f.submit();
                    }
                });
            };

            // ==================== FUNGSI POPUP FOTO ====================
            window.showPhoto = function(url) {
                Swal.fire({
                    imageUrl: url,
                    imageAlt: 'Foto Absensi',
                    showConfirmButton: false,
                    background: 'transparent',
                    width: 'auto',
                    padding: '0',
                    showCloseButton: true,
                });
            };

            // ==================== ALERT REMOVE BUTTON ====================
            document.querySelectorAll('.remove-button').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.alert').remove();
                });
            });
        });
    </script>

    <!-- ===== NOTIF ABSEN REALTIME ===== -->
    <div id="absen-notif-container"
        style="position:fixed;top:1.25rem;right:1.25rem;z-index:9999;pointer-events:none;"></div>

    <style>
        @keyframes notifSlideIn {
            from {
                opacity: 0;
                transform: translateX(120%)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        @keyframes notifSlideOut {
            from {
                opacity: 1;
                transform: translateX(0)
            }

            to {
                opacity: 0;
                transform: translateX(120%)
            }
        }

        #notif-card {
            animation: notifSlideIn .4s cubic-bezier(.34, 1.56, .64, 1) both;
        }
    </style>

    <script>
        (function() {
            const POLL_URL = '{{ route('absen_notif.public') }}';
            const POLL_MS = 3000;
            const NOTIF_SEC = 8;

            // Expose lastSeen ke window agar bisa di-update dari AJAX handler
            window._absenLastSeen =
                {{ session('last_absen_ts') ? session('last_absen_ts') : 'Math.floor(Date.now() / 1000)' }};
            let lastSeen = window._absenLastSeen;
            let countTimer = null;
            let ttsRunning = false;
            let ttsQueue = [];

            // ─── TTS ──────────────────────────────────────────────
            function speakText(text) {
                if (!window.speechSynthesis) return;
                ttsQueue.push(text);
                if (!ttsRunning) flushTTS();
            }

            function flushTTS() {
                if (!ttsQueue.length) {
                    ttsRunning = false;
                    return;
                }
                ttsRunning = true;
                const text = ttsQueue.shift();
                const utter = new SpeechSynthesisUtterance(text);
                utter.lang = 'id-ID';
                utter.rate = 0.95;
                utter.pitch = 1.05;
                utter.volume = 1;
                utter.onend = utter.onerror = flushTTS;

                function doSpeak() {
                    const voices = window.speechSynthesis.getVoices();
                    const v = voices.find(x => x.lang === 'id-ID') || voices.find(x => x.lang.startsWith('id')) || null;
                    if (v) utter.voice = v;
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.speak(utter);
                }
                window.speechSynthesis.getVoices().length > 0 ? doSpeak() : (window.speechSynthesis.onvoiceschanged =
                    doSpeak);
            }

            function buildTTS(d) {
                const st = d.status === 'in' ? 'Check In' : 'Check Out';
                const sk = d.is_aktif ? 'Member Aktif' : 'Member Tidak Aktif';
                let t = st + '. ' + d.nama + '. ' + sk + '.';
                if (d.is_aktif && d.sisa_hari !== null && d.sisa_hari <= 7)
                    t += ' Peringatan, membership hampir habis, sisa ' + d.sisa_hari + ' hari.';
                else if (!d.is_aktif)
                    t += ' Harap perpanjang membership Anda.';
                return t;
            }

            // ─── Render Card ──────────────────────────────────────
            function showNotif(d) {
                clearInterval(countTimer);

                const isAktif = d.is_aktif;
                const grad = isAktif ?
                    'linear-gradient(135deg,#0f4c2a 0%,#1a7a40 60%,#22c55e 100%)' :
                    'linear-gradient(135deg,#4c0f0f 0%,#7a1a1a 60%,#ef4444 100%)';
                const icon = d.status === 'in' ? 'mdi:location-enter' : 'mdi:location-exit';
                const shieldIcon = isAktif ? 'mdi:shield-check' : 'mdi:shield-alert';
                const sLabel = isAktif ? '✓ AKTIF' : '✗ TIDAK AKTIF';
                const sBg = isAktif ? 'rgba(34,197,94,.4)' : 'rgba(239,68,68,.4)';
                const shBg = isAktif ? 'rgba(34,197,94,.3)' : 'rgba(239,68,68,.3)';

                let membershipLine = '';
                if (isAktif) {
                    let sisaStr = '';
                    if (d.sisa_hari !== null) {
                        const col = d.sisa_hari <= 7 ? '#fde047' : 'rgba(255,255,255,.8)';
                        sisaStr =
                            ` &bull; <span style="color:${col};font-weight:${d.sisa_hari<=7?'700':'400'}">${d.sisa_hari} hari lagi</span>`;
                    }
                    membershipLine =
                        `<p style="color:rgba(255,255,255,.8);font-size:.75rem;margin:.25rem 0 0">Aktif hingga <strong>${d.tgl_selesai}</strong>${sisaStr}</p>`;
                } else {
                    membershipLine =
                        `<p style="color:rgba(255,255,255,.8);font-size:.75rem;margin:.25rem 0 0">${d.alasan_tidak_aktif}</p>`;
                }

                const fotoHtml = d.foto ?
                    `<img src="${d.foto}" style="width:56px;height:56px;border-radius:.875rem;object-fit:cover;border:2px solid rgba(255,255,255,.4)">` :
                    `<div style="width:56px;height:56px;border-radius:.875rem;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center"><i class="ri-user-line" style="color:white;font-size:1.75rem"></i></div>`;

                const html = `
            <div id="notif-card" style="position:relative;width:320px;border-radius:1.25rem;overflow:hidden;
                 box-shadow:0 8px 32px rgba(0,0,0,.35);background:${grad};pointer-events:auto;">
                <div style="position:absolute;top:-40px;right:-40px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.12)"></div>
                <div style="position:absolute;bottom:-28px;left:-28px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.07)"></div>

                <!-- Header -->
                <div style="padding:1rem 1.25rem .6rem;display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1">
                    <div style="display:flex;align-items:center;gap:.4rem">
                        <div style="width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center">
                            <i class="${d.status === 'in' ? 'ri-login-box-line' : 'ri-logout-box-line'}" style="color:white;font-size:.9rem"></i>
                        </div>
                        <span style="color:white;font-weight:700;font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;opacity:.9">
                            CHECK ${d.status.toUpperCase()}
                        </span>
                    </div>
                    <button onclick="closeNotif()"
                            style="width:26px;height:26px;border:none;background:rgba(255,255,255,.15);border-radius:50%;cursor:pointer;color:white;font-size:.9rem;display:flex;align-items:center;justify-content:center"
                            onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <!-- Body -->
                <div style="padding:0 1.25rem 1.25rem;position:relative;z-index:1">
                    <!-- Foto + Nama -->
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem">
                        ${fotoHtml}
                        <div>
                            <p style="color:rgba(255,255,255,.65);font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;margin:0">Nama Member</p>
                            <h2 style="color:white;font-weight:700;font-size:1.05rem;margin:.1rem 0 0;line-height:1.2">${d.nama}</h2>
                        </div>
                    </div>

                    <!-- Status Keanggotaan -->
                    <div style="border-radius:.65rem;padding:.75rem;background:rgba(0,0,0,.2);margin-bottom:.75rem">
                        <div style="display:flex;align-items:center;gap:.6rem">
                            <div style="width:34px;height:34px;border-radius:50%;background:${shBg};flex-shrink:0;display:flex;align-items:center;justify-content:center">
                                <i class="${isAktif ? 'ri-shield-check-line' : 'ri-shield-cross-line'}" style="color:white;font-size:1.1rem"></i>
                            </div>
                            <div style="flex:1">
                                <p style="color:rgba(255,255,255,.65);font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;margin:0 0 .2rem">Status Keanggotaan</p>
                                <span style="padding:.12rem .6rem;border-radius:999px;font-size:.7rem;font-weight:700;background:${sBg};color:white">${sLabel}</span>
                                ${membershipLine}
                            </div>
                        </div>
                    </div>

                    <!-- Waktu + Progress -->
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.55);font-size:.65rem;display:flex;align-items:center;gap:.2rem">
                            <i class="ri-time-line"></i> ${d.waktu}
                        </span>
                        <div style="display:flex;align-items:center;gap:.35rem">
                            <div style="width:64px;height:4px;border-radius:999px;background:rgba(255,255,255,.2);overflow:hidden">
                                <div id="notif-bar" style="height:100%;background:rgba(255,255,255,.75);width:100%;transition:width linear"></div>
                            </div>
                            <span id="notif-cd" style="color:rgba(255,255,255,.55);font-size:.65rem">${NOTIF_SEC}s</span>
                        </div>
                    </div>
                </div>
            </div>`;

                const container = document.getElementById('absen-notif-container');
                container.innerHTML = html;
                container.style.display = 'block';

                let rem = NOTIF_SEC * 1000;
                countTimer = setInterval(() => {
                    rem -= 100;
                    const bar = document.getElementById('notif-bar');
                    const cd = document.getElementById('notif-cd');
                    if (bar) bar.style.width = ((rem / (NOTIF_SEC * 1000)) * 100) + '%';
                    if (cd) cd.textContent = Math.ceil(rem / 1000) + 's';
                    if (rem <= 0) {
                        clearInterval(countTimer);
                        closeNotif();
                    }
                }, 100);
            }

            // Expose ke window supaya bisa dipanggil dari AJAX form handler
            window.speakText = speakText;
            window.showNotif = showNotif;
            window.buildTTS = buildTTS;

            window.closeNotif = function() {
                clearInterval(countTimer);
                const card = document.getElementById('notif-card');
                if (card) card.style.animation = 'notifSlideOut .3s ease forwards';
                setTimeout(() => {
                    const c = document.getElementById('absen-notif-container');
                    if (c) {
                        c.style.display = 'none';
                        c.innerHTML = '';
                    }
                }, 300);
            };

            // ─── Polling ─────────────────────────────────────────────
            let pollAbort = null;
            let isFetching = false;

            async function poll() {
                // Skip kalau request sebelumnya belum selesai
                if (isFetching) return;

                // Abort request lama jika ada
                if (pollAbort) pollAbort.abort();
                pollAbort = new AbortController();
                isFetching = true;

                try {
                    const since = window._absenLastSeen;
                    const res = await fetch(POLL_URL + '?since=' + since, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: pollAbort.signal
                    });
                    if (!res.ok) {
                        isFetching = false;
                        return;
                    }
                    const json = await res.json();
                    // Double-check: timestamp harus lebih baru dari lastSeen saat ini
                    // (bukan dari saat fetch dimulai) untuk handle race condition
                    if (json.has_new && json.data && json.data.timestamp > window._absenLastSeen) {
                        window._absenLastSeen = json.data.timestamp;
                        showNotif(json.data);
                        speakText(buildTTS(json.data));
                    }
                } catch (e) {
                    // Abaikan AbortError (request di-cancel)
                } finally {
                    isFetching = false;
                }
            }

            setInterval(poll, POLL_MS);
        })();
    </script>
    <!-- ===== END NOTIF ABSEN ===== -->
</body>

</html>
