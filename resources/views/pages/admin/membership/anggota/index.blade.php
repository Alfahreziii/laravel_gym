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

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display text-[32px] font-bold leading-tight text-ink dark:text-ink-d">
                {{ $isLaporanMode ? 'Laporan Anggota' : 'Member' }}
            </h1>
            <p class="text-sm text-ink-3 dark:text-ink-d3 mt-0.5">
                {{ $isLaporanMode ? 'Laporan data keanggotaan gym' : 'Kelola data dan status keanggotaan gym' }}
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="HexaModal.show('export-pdf-modal')"
                class="btn btn-secondary btn-sm">
                <iconify-icon icon="carbon:export" class="text-base"></iconify-icon>
                Export
            </button>
            @if (!$isLaporanMode)
                @role('admin|spv')
                    <a href="{{ route('anggota.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-700 text-white text-sm font-semibold transition">
                        <iconify-icon icon="lucide:plus" class="text-base"></iconify-icon>
                        Tambah Member
                    </a>
                @endrole
            @endif
        </div>
    </div>

    {{-- Summary Cards --}}
    @if (!$isLaporanMode)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card label="Total Member" :value="$totalAnggota" icon="member" color="blue" />
            <x-stat-card label="Member Aktif" :value="$totalAktif" icon="kehadiran" color="teal" />
            <x-stat-card label="Tidak Aktif" :value="$totalTidakAktif" icon="paket-member" color="orange" />
            <x-stat-card label="Daftar Bulan Ini" :value="$totalBaru" icon="users" color="pink" />
        </div>
    @endif

    {{-- Main Card --}}
    <x-card noPadding>
        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 md:px-6 py-3 border-b border-line-light dark:border-line-dark">
            <div class="relative w-full sm:w-64 shrink-0">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-3 dark:text-ink-d3 pointer-events-none">
                    <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                </span>
                <input id="searchAnggota" type="search" placeholder="Cari member..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-line-light dark:border-line-dark bg-canvas-light dark:bg-canvas-dark text-sm text-ink dark:text-ink-d placeholder-ink-3 dark:placeholder-ink-d3 focus:outline-none focus:ring-2 focus:ring-primary-500/30 transition">
            </div>

            <nav id="anggota-status-tabs" class="flex items-center gap-1 flex-1 flex-wrap" role="tablist">
                <button role="tab" data-filter="all"
                    class="anggota-tab px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap">
                    Semua <span class="ml-0.5 text-xs opacity-60">({{ $totalAnggota }})</span>
                </button>
                <button role="tab" data-filter="aktif"
                    class="anggota-tab px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap">
                    Aktif <span class="ml-0.5 text-xs opacity-60">({{ $totalAktif }})</span>
                </button>
                <button role="tab" data-filter="tidak_aktif"
                    class="anggota-tab px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap">
                    Tidak Aktif <span class="ml-0.5 text-xs opacity-60">({{ $totalTidakAktif }})</span>
                </button>
            </nav>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="ajax-table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-line-light dark:border-line-dark">
                        <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">No</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">Member</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">Kontak</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">Paket</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">Bergabung</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">Berakhir</th>
                        @if (!$isLaporanMode)
                            @role('admin|spv')
                                <th class="px-4 md:px-6 py-3 text-xs font-semibold text-ink-3 dark:text-ink-d3 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            @endrole
                        @endif
                    </tr>
                </thead>
                <tbody id="tbodyAnggota">
                    <tr>
                        <td colspan="8" class="px-4 md:px-6 py-10 text-center text-sm text-ink-3 dark:text-ink-d3">
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 md:px-6 py-3 border-t border-line-light dark:border-line-dark">
            <span id="infoAnggota" class="text-sm text-ink-3 dark:text-ink-d3"></span>
            <div id="paginationAnggota" class="flex items-center gap-1"></div>
        </div>
    </x-card>

    {{-- Export PDF Modal --}}
    <x-modal id="export-pdf-modal" title="Filter Export Laporan">
        <x-slot:body>
            <form action="{{ route('anggota.export_pdf') }}" method="POST" id="export-pdf-form">
                @csrf
                <div class="grid grid-cols-1 gap-6">
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

    {{-- Status Fingerprint Modal --}}
    <x-modal id="status-finger-modal" title="Ubah Status Fingerprint" maxWidth="max-w-[600px]">
        <x-slot:body>
            <form id="status-finger-form">
                <div class="grid grid-cols-1 gap-6">
                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Anggota:</label>
                        <p id="statusFingerAnggotaInfo" class="text-neutral-800 font-medium"></p>
                    </div>
                    <div class="col-span-12">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Status Fingerprint:</label>
                        <select id="statusFingerSelect" class="form-control" required>
                            <option value="2">— Default (Tidak Ada Aksi) —</option>
                            <option value="0">🟢 Enroll Fingerprint</option>
                            <option value="1">🔴 Delete Fingerprint</option>
                        </select>
                        <small class="text-muted">Enroll = daftarkan sidik jari, Delete = hapus sidik jari, Default = tidak ada aksi</small>
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
    (function () {
        const isAdmin    = {{ auth()->user()->hasAnyRole(['admin', 'spv']) ? 'true' : 'false' }};
        const isLaporan  = {{ $isLaporanMode ? 'true' : 'false' }};
        const showAksi   = isAdmin && !isLaporan;
        const colSpan    = showAksi ? 8 : 7;

        // ── Avatar palette (same as dashboard) ──────────────────────────
        const palette = ['#0F766E','#C2410C','#1D4ED8','#7C3AED','#BE123C','#0E7490','#B45309','#1E40AF','#047857','#9D174D'];

        function initials(name) {
            const parts = (name || '').trim().split(/\s+/);
            return parts.length >= 2
                ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
                : (parts[0] || '?')[0].toUpperCase();
        }

        function avatarColor(name) {
            let hash = 0;
            for (let i = 0; i < (name || '').length; i++) hash = (hash * 31 + name.charCodeAt(i)) | 0;
            return palette[Math.abs(hash) % palette.length];
        }

        // ── Status filter state ──────────────────────────────────────────
        let statusFilter = 'all';

        const ACTIVE_TAB   = ['bg-primary-500/10', 'text-primary-600', 'dark:text-primary-400', 'font-semibold'];
        const INACTIVE_TAB = ['text-ink-2', 'dark:text-ink-d2', 'hover:bg-canvas-light', 'dark:hover:bg-canvas-dark'];

        const tabs = document.querySelectorAll('.anggota-tab');

        function activateTab(btn) {
            tabs.forEach(function (t) {
                t.classList.remove(...ACTIVE_TAB);
                t.classList.add(...INACTIVE_TAB);
            });
            btn.classList.remove(...INACTIVE_TAB);
            btn.classList.add(...ACTIVE_TAB);
        }

        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                statusFilter = this.dataset.filter;
                activateTab(this);
                if (window._ajaxTables && window._ajaxTables['tbodyAnggota']) {
                    window._ajaxTables['tbodyAnggota'].refresh();
                }
            });
        });
        if (tabs[0]) activateTab(tabs[0]);

        // ── renderRow ────────────────────────────────────────────────────
        function renderRow(item) {
            const esc = function (v) { return String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); };

            const avatarHtml = item.foto
                ? `<img src="${esc(item.foto)}" alt="${esc(item.name)}"
                        class="w-9 h-9 rounded-full object-cover shrink-0 cursor-pointer"
                        onclick="showPhoto('${esc(item.foto)}')" loading="lazy">`
                : `<span class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center text-xs font-bold text-white select-none"
                        style="background:${avatarColor(item.name)}">${initials(item.name)}</span>`;

            const memberCell = `
                <div class="flex items-center gap-2.5 min-w-0">
                    ${avatarHtml}
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-ink dark:text-ink-d truncate max-w-[160px]" title="${esc(item.name)}">${esc(item.name)}</div>
                        <div class="text-xs text-ink-3 dark:text-ink-d3 truncate max-w-[160px]" title="${esc(item.email)}">${esc(item.email)}</div>
                    </div>
                </div>`;

            const statusBadge = item.status
                ? AjaxTable.badge('success', 'Aktif')
                : AjaxTable.badge('neutral', 'Tidak Aktif');

            const fingerTitle = item.status_finger == 0 ? 'Enroll' : item.status_finger == 1 ? 'Delete' : 'Default';
            const fingerColor = item.status_finger == 0 ? '#16a34a' : item.status_finger == 1 ? '#dc2626' : '#9C978E';

            const fingerBtn = `
                <button type="button" onclick="openStatusFingerModal(this)"
                    data-url="${esc(item.status_finger_url)}"
                    data-status="${item.status_finger}"
                    data-name="${esc(item.name)}"
                    data-kartu="${esc(item.id_kartu)}"
                    title="Fingerprint: ${fingerTitle}"
                    class="w-7 h-7 rounded-full flex items-center justify-center hover:opacity-80 transition"
                    style="background:${fingerColor}1a;color:${fingerColor}">
                    <iconify-icon icon="lucide:scan-line" style="font-size:14px"></iconify-icon>
                </button>`;

            const aksiCell = showAksi ? `
                <td class="px-4 md:px-6 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                        <a href="${esc(item.edit_url)}" title="Edit"
                            class="w-7 h-7 rounded-full flex items-center justify-center bg-success-100 text-success-600 hover:bg-success-200 transition">
                            <iconify-icon icon="lucide:edit" style="font-size:13px"></iconify-icon>
                        </a>
                        <button type="button" onclick="confirmDelete('${esc(item.delete_url)}')" title="Hapus"
                            class="w-7 h-7 rounded-full flex items-center justify-center bg-danger-100 text-danger-600 hover:bg-danger-200 transition">
                            <iconify-icon icon="mingcute:delete-2-line" style="font-size:13px"></iconify-icon>
                        </button>
                        ${fingerBtn}
                    </div>
                </td>` : '';

            return `<tr class="border-b border-line-light dark:border-line-dark">
                <td class="px-4 md:px-6 py-3 text-xs text-ink-3 dark:text-ink-d3 whitespace-nowrap">${item.no}</td>
                <td class="px-4 md:px-6 py-3">${memberCell}</td>
                <td class="px-4 md:px-6 py-3 text-sm text-ink-2 dark:text-ink-d2 whitespace-nowrap">${esc(item.no_telp)}</td>
                <td class="px-4 md:px-6 py-3 text-sm text-ink-2 dark:text-ink-d2 whitespace-nowrap max-w-[140px] truncate" title="${esc(item.paket)}">${esc(item.paket)}</td>
                <td class="px-4 md:px-6 py-3 whitespace-nowrap">${statusBadge}</td>
                <td class="px-4 md:px-6 py-3 text-sm text-ink-2 dark:text-ink-d2 whitespace-nowrap font-variant-numeric tabular-nums">${esc(item.bergabung)}</td>
                <td class="px-4 md:px-6 py-3 text-sm text-ink-2 dark:text-ink-d2 whitespace-nowrap font-variant-numeric tabular-nums">${esc(item.berakhir)}</td>
                ${aksiCell}
            </tr>`;
        }

        // ── AjaxTable ────────────────────────────────────────────────────
        AjaxTable.create({
            url:          '{{ route('anggota.datatable') }}',
            tbodyId:      'tbodyAnggota',
            paginationId: 'paginationAnggota',
            infoId:       'infoAnggota',
            searchId:     'searchAnggota',
            perPage:      10,
            colSpan:      colSpan,
            renderRow:    renderRow,
            extraParams:  function () { return { status_filter: statusFilter }; },
        });

        // ── confirmDelete ────────────────────────────────────────────────
        window.confirmDelete = function (url) {
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Data anggota dan akun login yang dihapus tidak bisa dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then(function (result) {
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

        // ── openStatusFingerModal ────────────────────────────────────────
        let statusFingerUrl = null;

        window.openStatusFingerModal = function (btn) {
            statusFingerUrl = btn.dataset.url;
            document.getElementById('statusFingerSelect').value = btn.dataset.status;
            document.getElementById('statusFingerAnggotaInfo').textContent =
                btn.dataset.name + ' (ID Kartu: ' + btn.dataset.kartu + ')';
            HexaModal.show('status-finger-modal');
        };

        const statusFingerForm = document.getElementById('status-finger-form');
        if (statusFingerForm) {
            statusFingerForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!statusFingerUrl) return;

                const submitBtn  = document.getElementById('statusFingerSubmitBtn');
                const csrfToken  = document.querySelector('meta[name="csrf-token"]');
                submitBtn.disabled = true;

                fetch(statusFingerUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ status_finger: document.getElementById('statusFingerSelect').value }),
                })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    submitBtn.disabled = false;
                    if (!result.ok) {
                        Swal.fire('Gagal', result.data.message || 'Gagal memperbarui status fingerprint.', 'error');
                        return;
                    }
                    HexaModal.hide('status-finger-modal');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: result.data.message, timer: 1500, showConfirmButton: false });
                    if (window._ajaxTables && window._ajaxTables['tbodyAnggota']) {
                        window._ajaxTables['tbodyAnggota'].refresh();
                    }
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error');
                });
            });
        }

        // ── showPhoto ────────────────────────────────────────────────────
        window.showPhoto = function (url) {
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
    })();
    </script>
@endsection
