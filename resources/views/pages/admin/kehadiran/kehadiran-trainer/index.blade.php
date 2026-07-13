@extends('layout.layout')
@php
    $title = 'Kehadiran Trainer';
    $subTitle = 'Kehadiran Trainer';
    $isLaporanMode = request()->routeIs('laporan.kehadirantrainer');
@endphp

@section('content')

@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

@if(!$isLaporanMode)
@php
    $totalIn    = $kehadirantrainers->where('status', 'in')->count();
    $totalOut   = $kehadirantrainers->where('status', 'out')->count();
    $totalToday = $kehadirantrainers->count();
@endphp
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="card shadow-none hexa-stat-card rounded-xl" style="background:linear-gradient(135deg,rgba(6,182,212,.08),transparent);">
        <div class="card-body p-4">
            <p class="text-xs font-medium text-neutral-500 mb-1 uppercase tracking-wide">Check IN</p>
            <h4 class="mb-0">{{ $totalIn }}</h4>
        </div>
    </div>
    <div class="card shadow-none hexa-stat-card rounded-xl" style="background:linear-gradient(135deg,rgba(168,85,247,.08),transparent);">
        <div class="card-body p-4">
            <p class="text-xs font-medium text-neutral-500 mb-1 uppercase tracking-wide">Check OUT</p>
            <h4 class="mb-0">{{ $totalOut }}</h4>
        </div>
    </div>
    <div class="card shadow-none hexa-stat-card rounded-xl" style="background:linear-gradient(135deg,rgba(234,88,12,.08),transparent);">
        <div class="card-body p-4">
            <p class="text-xs font-medium text-neutral-500 mb-1 uppercase tracking-wide">Total Hari Ini</p>
            <h4 class="mb-0">{{ $totalToday }}</h4>
        </div>
    </div>
</div>
@endif

<x-page-table
    title="{{ $isLaporanMode ? 'Laporan Data Kehadiran Trainer' : 'Riwayat Kehadiran Trainer' }}"
    subtitle="Riwayat absensi dan kehadiran trainer."
>
    <x-slot:actions>
        @if(!$isLaporanMode)
        <button type="button" onclick="openScanner()"
            class="btn btn-primary btn-sm">
            <iconify-icon icon="solar:qr-code-linear" class="text-base"></iconify-icon>
            Scanner Absensi
        </button>
        @endif
        <button type="button" onclick="HexaModal.show('export-pdf-modal')"
            class="btn btn-secondary btn-sm">
            <iconify-icon icon="carbon:export" class="text-base"></iconify-icon>
            Export
        </button>
    </x-slot:actions>
    <x-data-table tableId="kehadiranTrainer" :colspan="(!$isLaporanMode && auth()->user()->hasRole('admin')) ? 7 : 6" placeholder="Search...">
        <x-slot:header>
            <tr>
                <th>S.L</th>
                @if (!$isLaporanMode)
                    @role('admin')
                        <th>Aksi</th>
                    @endrole
                @endif
                <th>ID Kartu</th>
                <th>Foto</th>
                <th>Nama Trainer</th>
                <th>Status</th>
                <th>Waktu</th>
            </tr>
        </x-slot:header>
    </x-data-table>
</x-page-table>

{{-- Export Modal --}}
<x-modal id="export-pdf-modal" title="Filter Export Laporan">
    <x-slot:body>
        <form action="{{ route('kehadirantrainer.export_pdf') }}" method="POST" id="export-pdf-form">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div class="col-span-12">
                    <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Pilih Periode:</label>
                    <div class="space-y-2">
                        <div class="flex items-center mb-2">
                            <input type="radio" id="filter_all" name="filter_type" value="all"
                                class="w-4 h-4 text-primary-600" checked>
                            <label for="filter_all" class="ml-2 text-sm font-medium text-gray-900">Semua Data</label>
                        </div>
                        <div class="flex items-center mb-2">
                            <input type="radio" id="filter_range" name="filter_type" value="range"
                                class="w-4 h-4 text-primary-600">
                            <label for="filter_range" class="ml-2 text-sm font-medium text-gray-900">Range Tanggal</label>
                        </div>
                    </div>
                </div>
                <div id="range-filter" class="hidden">
                    <div class="space-y-4">
                        <div>
                            <label for="tanggal_dari"
                                class="inline-block font-semibold text-neutral-600 text-sm mb-2">Dari Tanggal:</label>
                            <input type="date" id="tanggal_dari" name="tanggal_dari" class="form-control rounded-lg">
                        </div>
                        <div>
                            <label for="tanggal_sampai"
                                class="inline-block font-semibold text-neutral-600 text-sm mb-2">Sampai Tanggal:</label>
                            <input type="date" id="tanggal_sampai" name="tanggal_sampai" class="form-control rounded-lg">
                        </div>
                    </div>
                </div>
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
                        <button type="submit" formaction="{{ route('kehadirantrainer.export_excel') }}"
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

{{-- ── Scanner Drawer ── --}}
<div id="scanner-overlay"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:998;"
    onclick="closeScanner()"></div>

<div id="scanner-drawer"
    class="hexa-scanner-drawer"
    style="position:fixed; top:0; right:0; height:100%; width:380px; max-width:100vw;
           z-index:999; display:flex; flex-direction:column; background:#fff;
           box-shadow:-8px 0 32px rgba(0,0,0,0.15);
           transform:translateX(100%); transition:transform .3s cubic-bezier(.4,0,.2,1);">

    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #404040, #171717); color:#fff; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div style="display:flex; align-items:center; gap:.6rem;">
            <iconify-icon icon="solar:qr-code-linear" style="font-size:1.5rem;"></iconify-icon>
            <span style="font-weight:700; font-size:1rem;">Scanner Absensi Trainer</span>
        </div>
        <button onclick="closeScanner()"
            style="width:32px; height:32px; border-radius:50%; border:none; background:rgba(255,255,255,.2); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1rem; transition:background .2s;"
            onmouseover="this.style.background='rgba(255,255,255,.35)'"
            onmouseout="this.style.background='rgba(255,255,255,.2)'">
            <iconify-icon icon="solar:close-linear"></iconify-icon>
        </button>
    </div>

    {{-- Body --}}
    <div style="flex:1; overflow-y:auto; padding:1.25rem;">

        <div id="scanner-toast" style="display:none; margin-bottom:1rem; border-radius:.75rem; padding:.75rem 1rem; font-size:.875rem; font-weight:600; align-items:center; gap:.5rem;"></div>

        <form id="scanner-form" action="{{ route('absensi.trainer.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- RFID --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:.8125rem; font-weight:600; margin-bottom:.5rem;">ID Kartu / RFID</label>
                <input type="text" id="scanner-rfid" name="rfid"
                    class="form-control"
                    style="text-align:center; font-size:1.1rem; font-weight:700; letter-spacing:.1em; padding:.75rem 1rem; border-radius:.75rem;"
                    placeholder="Scan kartu di sini..."
                    autocomplete="off" required>
                <p style="font-size:.75rem; color:#9ca3af; text-align:center; margin-top:.375rem;">
                    Arahkan barcode scanner ke kartu trainer
                </p>
            </div>

            {{-- Webcam --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:.8125rem; font-weight:600; margin-bottom:.5rem;">Kamera</label>
                <div style="border-radius:.75rem; overflow:hidden; background:#111827; border:1px solid #e5e7eb;" class="hexa-scanner-cam-border">
                    <video id="scanner-webcam" style="width:100%; display:block;" autoplay playsinline></video>
                </div>
                <canvas id="scanner-canvas" style="display:none;"></canvas>
                <p style="font-size:.75rem; color:#9ca3af; text-align:center; margin-top:.375rem;">
                    Foto diambil otomatis saat scan
                </p>
            </div>

            {{-- Submit --}}
            <button type="submit"
                style="width:100%; display:flex; align-items:center; justify-content:center; gap:.5rem; padding:.75rem 1rem; border-radius:.75rem; font-size:1rem; font-weight:600; background:#404040; color:#fff; border:none; cursor:pointer; transition:background .2s;"
                onmouseover="this.style.background='#171717'"
                onmouseout="this.style.background='#404040'">
                <iconify-icon icon="solar:check-circle-linear" style="font-size:1.25rem;"></iconify-icon>
                Simpan Absensi
            </button>
        </form>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const isAdmin   = {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }};
            const isLaporan = {{ $isLaporanMode ? 'true' : 'false' }};
            const colSpan   = (isAdmin && !isLaporan) ? 7 : 6;

            AjaxTable.init('kehadiranTrainer', {
                url: '{{ route('kehadirantrainer.datatable') }}',
                colSpan: colSpan,
                renderRow: function(item) {
                    const foto = item.foto ?
                        `<img src="${item.foto}" alt="${item.name}"
                        class="w-10 h-10 rounded-lg object-cover cursor-pointer bg-gray-200"
                        onclick="showPhoto('${item.foto}', 'Foto Absensi')"
                        loading="lazy">` :
                        `<span class="text-gray-400 italic text-xs">No photo</span>`;

                    const statusBadge = AjaxTable.badge(item.status === 'in' ? 'success' : 'warning', item.status === 'in' ? 'CHECK IN' : 'CHECK OUT');

                    const aksiCol = (isAdmin && !isLaporan) ? `
                        <td class="whitespace-nowrap">
                            <button onclick="confirmDeleteKehadiran('${item.delete_url}')"
                                class="btn-action btn-action-del">
                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                            </button>
                        </td>` : '';

                    return `
                        <tr>
                            <td class="whitespace-nowrap">${item.no}</td>
                            ${aksiCol}
                            <td class="whitespace-nowrap">${item.rfid}</td>
                            <td class="whitespace-nowrap">${foto}</td>
                            <td class="whitespace-nowrap">${item.name}</td>
                            <td class="whitespace-nowrap">${statusBadge}</td>
                            <td class="whitespace-nowrap">${item.time}</td>
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
            const rangeFilter  = document.getElementById('range-filter');
            filterRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    rangeFilter.classList.toggle('hidden', this.value !== 'range');
                });
            });

            document.getElementById('export-pdf-form').addEventListener('submit', function(e) {
                const filterType = document.querySelector('input[name="filter_type"]:checked').value;
                if (filterType === 'range') {
                    const dari   = document.getElementById('tanggal_dari').value;
                    const sampai = document.getElementById('tanggal_sampai').value;
                    if (!dari || !sampai) {
                        e.preventDefault();
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Mohon lengkapi range tanggal!' });
                        return false;
                    }
                    if (new Date(dari) > new Date(sampai)) {
                        e.preventDefault();
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Tanggal dari tidak boleh lebih besar dari tanggal sampai!' });
                        return false;
                    }
                }
            });

            // ── Scanner Drawer ────────────────────────────────────────────────
            const drawer     = document.getElementById('scanner-drawer');
            const overlay    = document.getElementById('scanner-overlay');
            const video      = document.getElementById('scanner-webcam');
            const canvas     = document.getElementById('scanner-canvas');
            const rfidInput  = document.getElementById('scanner-rfid');
            const scanForm   = document.getElementById('scanner-form');
            const toast      = document.getElementById('scanner-toast');
            let camStream    = null;
            let isProcessing = false;
            let focusTick    = null;

            window.openScanner = function() {
                drawer.style.transform       = 'translateX(0)';
                overlay.style.display        = 'block';
                document.body.style.overflow = 'hidden';
                startCam();
                setTimeout(() => rfidInput.focus(), 350);
                focusTick = setInterval(() => {
                    if (document.activeElement !== rfidInput && !isProcessing) rfidInput.focus();
                }, 2000);
            };

            window.closeScanner = function() {
                drawer.style.transform       = 'translateX(100%)';
                overlay.style.display        = 'none';
                document.body.style.overflow = '';
                stopCam();
                clearInterval(focusTick);
                rfidInput.value     = '';
                toast.style.display = 'none';
            };

            function startCam() {
                if (camStream) return;
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
                    .then(s => { camStream = s; video.srcObject = s; })
                    .catch(() => {});
            }

            function stopCam() {
                if (!camStream) return;
                camStream.getTracks().forEach(t => t.stop());
                camStream = null;
                video.srcObject = null;
            }

            scanForm.addEventListener('submit', e => {
                e.preventDefault();
                if (isProcessing || !rfidInput.value.trim()) return;
                isProcessing = true;

                function doPost(fotoBlob) {
                    const fd = new FormData(scanForm);
                    if (fotoBlob) fd.set('foto', new File([fotoBlob], `absen_tr_${Date.now()}.jpg`, { type: 'image/jpeg' }));

                    fetch(scanForm.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    })
                    .then(async r => {
                        let res;
                        try { res = await r.json(); } catch { res = { success: false, message: 'Server error' }; }

                        rfidInput.value = '';
                        rfidInput.focus();
                        isProcessing = false;

                        showScanToast(res.success ? 'success' : 'danger', res.message || 'Terjadi kesalahan.');

                        if (res.success && window._ajaxTables && window._ajaxTables['tbodyKehadiranTrainer']) {
                            window._ajaxTables['tbodyKehadiranTrainer'].refresh();
                        }
                    })
                    .catch(() => {
                        isProcessing = false;
                        rfidInput.value = '';
                        rfidInput.focus();
                        showScanToast('danger', 'Gagal menghubungi server.');
                    });
                }

                const MAX_W = 640;
                const srcW  = video.videoWidth  || 640;
                const srcH  = video.videoHeight || 480;
                const scale = Math.min(1, MAX_W / srcW);
                canvas.width  = Math.round(srcW * scale);
                canvas.height = Math.round(srcH * scale);

                if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth > 0) {
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(blob => doPost(blob), 'image/jpeg', 0.65);
                } else {
                    doPost(null);
                }
            });

            function showScanToast(type, msg) {
                const ok = type === 'success';
                toast.style.cssText = `display:flex; align-items:center; gap:.5rem; padding:.75rem 1rem; border-radius:.75rem; font-size:.875rem; font-weight:600; margin-bottom:1rem; border:1px solid; ${ok ? 'background:#f0fdf4; color:#15803d; border-color:#bbf7d0;' : 'background:#fef2f2; color:#dc2626; border-color:#fecaca;'}`;
                toast.innerHTML = `<iconify-icon icon="${ok ? 'solar:check-circle-bold' : 'solar:close-circle-bold'}" style="font-size:1.25rem; flex-shrink:0;"></iconify-icon><span>${msg}</span>`;
                clearTimeout(toast._to);
                toast._to = setTimeout(() => { toast.style.display = 'none'; }, 5000);
            }
        });
    </script>
@endsection
