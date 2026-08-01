@extends('layout.layout')

@php
    $title = 'Dashboard';
    $subTitle = 'Overview';
    $script = '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
@endphp

@section('content')
    {{-- ===== KPI Row ===== --}}
    @php
        $maleRatio = $totalMember > 0 ? round(($memberLakiLaki / $totalMember) * 100, 1) : 0;
        $femaleRatio = $totalMember > 0 ? round(($memberPerempuan / $totalMember) * 100, 1) : 0;
        $aktifRatio = $totalMember > 0 ? round(($memberAktif / $totalMember) * 100, 1) : 0;
    @endphp

    {{--
        5 cards, grid strategy:
        - mobile (< sm) : 1 col  → 5 baris
        - sm–xl         : 2 col  → baris 1: cards 1-2, baris 2: cards 3-4, baris 3: card 5
                          card 5 pakai sm:col-span-2 agar tidak menggantung sendiri
        - xl+           : 5 col  → semua dalam satu baris
    --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-6">

        <x-stat-card color="blue" label="Total Member" icon="member"
            value="{{ number_format($totalMember, 0, ',', '.') }}" />

        <x-stat-card color="cyan" label="Male Members" icon="male"
            value="{{ number_format($memberLakiLaki, 0, ',', '.') }}" sub="{{ $maleRatio }}% dari total" />

        <x-stat-card color="pink" label="Female Members" icon="female"
            value="{{ number_format($memberPerempuan, 0, ',', '.') }}" sub="{{ $femaleRatio }}% dari total" />

        <x-stat-card color="teal" label="Member In GYM" icon="member-in-gym"
            value="{{ number_format($memberInGym, 0, ',', '.') }}" />

        <x-stat-card color="orange" class="sm:col-span-2 xl:col-span-1" label="Member Aktif" icon="member"
            value="{{ number_format($memberAktif, 0, ',', '.') }}" sub="{{ $aktifRatio }}% dari total" />

    </div>

    {{-- ===== Member Terbaru + Kehadiran Langsung ===== --}}
    @php
        $avatarPalette = [
            '#0F766E',
            '#C2410C',
            '#1D4ED8',
            '#7C3AED',
            '#BE123C',
            '#0E7490',
            '#B45309',
            '#1E40AF',
            '#047857',
            '#9D174D',
        ];
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-[1.9fr_1fr] gap-5 mt-6">

        {{-- Kiri: Member Terbaru --}}
        <x-card noPadding>
            <x-slot:header>
                <div>
                    <h6
                        class="font-display font-semibold text-[20px] leading-snug tracking-[.01em] text-ink dark:text-ink-d">
                        Member terbaru
                    </h6>
                    <p class="text-[12px] text-ink-2 dark:text-ink-d2">Diperbarui hari ini</p>
                </div>
                @role('admin')
                    <a href="{{ route('anggota.create') }}"
                        class="btn btn-primary btn-sm whitespace-nowrap inline-flex items-center gap-1.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            style="width:15px;height:15px">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        Tambah member
                    </a>
                @endrole
            </x-slot:header>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table style="width:100%;border-collapse:collapse;min-width:520px">
                    <thead>
                        <tr>
                            <th
                                style="font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:#9C978E;font-weight:600;text-align:left;padding:10px 18px;white-space:nowrap">
                                Member</th>
                            <th
                                style="font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:#9C978E;font-weight:600;text-align:left;padding:10px 18px;white-space:nowrap">
                                Paket</th>
                            <th
                                style="font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:#9C978E;font-weight:600;text-align:left;padding:10px 18px;white-space:nowrap">
                                Status</th>
                            <th
                                style="font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:#9C978E;font-weight:600;text-align:left;padding:10px 18px;white-space:nowrap">
                                Berakhir</th>
                            <th style="padding:10px 18px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memberTerbaru as $member)
                            @php
                                $ci = abs(crc32($member['name'])) % count($avatarPalette);
                                $avatarBg = $avatarPalette[$ci];
                                $nameParts = explode(' ', trim($member['name']));
                                $initials = strtoupper(
                                    mb_substr($nameParts[0], 0, 1) .
                                        (isset($nameParts[1]) ? mb_substr($nameParts[1], 0, 1) : ''),
                                );
                            @endphp
                            <tr class="hover:bg-surface-raised dark:hover:bg-surface-dark-raised transition-colors">
                                <td style="padding:13px 18px;border-top:1px solid #E2E0DB;font-size:14px">
                                    <div style="display:flex;align-items:center;gap:11px">
                                        @if ($member['photo_url'] && !str_contains($member['photo_url'], 'user-grid-img14'))
                                            <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] }}"
                                                style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex:none">
                                        @else
                                            <span
                                                style="width:36px;height:36px;border-radius:50%;display:grid;place-items:center;font-size:12px;font-weight:700;color:#fff;flex:none;background:{{ $avatarBg }}">
                                                {{ $initials }}
                                            </span>
                                        @endif
                                        <div>
                                            <div style="font-weight:600">{{ $member['name'] }}</div>
                                            <div style="font-size:12px;color:#6E6A63">{{ $member['email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:13px 18px;border-top:1px solid #E2E0DB;font-size:14px">
                                    {{ $member['nama_paket'] }}
                                </td>
                                <td style="padding:13px 18px;border-top:1px solid #E2E0DB;font-size:14px">
                                    @php
                                        $badgeBg = match ($member['status_type']) {
                                            'success' => [
                                                'bg' => 'rgba(34,197,94,.13)',
                                                'text' => '#15803D',
                                                'dot' => '#22C55E',
                                            ],
                                            'warning' => [
                                                'bg' => 'rgba(245,158,11,.13)',
                                                'text' => '#B45309',
                                                'dot' => '#F59E0B',
                                            ],
                                            'danger' => [
                                                'bg' => 'rgba(239,68,68,.13)',
                                                'text' => '#B91C1C',
                                                'dot' => '#EF4444',
                                            ],
                                            default => [
                                                'bg' => 'rgba(107,114,128,.10)',
                                                'text' => '#4B5563',
                                                'dot' => '#9CA3AF',
                                            ],
                                        };
                                    @endphp
                                    <span
                                        style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:{{ $badgeBg['bg'] }};color:{{ $badgeBg['text'] }}">
                                        <i
                                            style="width:6px;height:6px;border-radius:50%;background:{{ $badgeBg['dot'] }};flex:none"></i>
                                        {{ $member['status_label'] }}
                                    </span>
                                </td>
                                <td
                                    style="padding:13px 18px;border-top:1px solid #E2E0DB;font-size:14px;font-variant-numeric:tabular-nums">
                                    {{ $member['tgl_selesai'] }}
                                </td>
                                <td style="padding:13px 18px;border-top:1px solid #E2E0DB;font-size:14px">
                                    <div style="display:flex;gap:4px">
                                        <a href="{{ $member['edit_url'] }}"
                                            style="width:30px;height:30px;border-radius:8px;border:1px solid #E2E0DB;background:#FAFAF9;display:grid;place-items:center;color:#9C978E;text-decoration:none;transition:background .15s,color .15s"
                                            onmouseover="this.style.background='#EBEAE7';this.style.color='#1A1A18'"
                                            onmouseout="this.style.background='#FAFAF9';this.style.color='#9C978E'">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                style="width:14px;height:14px">
                                                <path d="M4 20h4L18 9.9a2 2 0 0 0-2.8-2.8L5 17.2V20Z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:40px 18px;text-align:center;color:#9C978E;font-size:14px">
                                    Belum ada member terdaftar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (count($memberTerbaru))
                <div class="px-[18px] pb-4 pt-3 border-t border-line-light dark:border-line-dark">
                    <a href="{{ route('anggota.index') }}"
                        class="text-[13px] font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400
                              inline-flex items-center gap-1 transition-colors">
                        Lihat semua member
                        <iconify-icon icon="lucide:arrow-right" class="text-xs"></iconify-icon>
                    </a>
                </div>
            @endif
        </x-card>

        {{-- Kanan: Kehadiran hari ini (tabs: Kehadiran Member / Member In Room) --}}
        <x-card noPadding>
            <x-slot:header>
                <h6 class="font-display font-semibold text-[20px] leading-snug tracking-[.01em] text-ink dark:text-ink-d">
                    Kehadiran hari ini
                </h6>
                <span
                    class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-[.04em]
                             text-primary-700 dark:text-primary-300
                             bg-primary-50 dark:bg-primary-900/20
                             px-[9px] py-1 rounded-full">
                    <span class="w-[7px] h-[7px] rounded-full bg-primary-500 animate-pulse flex-none"></span>
                    LIVE
                </span>
            </x-slot:header>

            {{-- Tab nav — active state dikelola JS, bukan x-init --}}
            <div class="flex border-b border-line-light dark:border-line-dark px-4 md:px-6" id="kehadiran-tab"
                data-tabs-toggle="#kehadiran-tab-content" role="tablist">
                <button
                    class="pb-2.5 pt-2.5 px-1 mr-5 border-b-2 border-transparent text-xs font-semibold text-ink-2
                           dark:text-ink-d2 hover:text-ink dark:hover:text-ink-d transition-colors duration-150"
                    id="kehadiranMember-tab" data-tabs-target="#kehadiranMemberPanel" type="button" role="tab"
                    aria-selected="true">
                    Kehadiran Member
                </button>
                <button
                    class="pb-2.5 pt-2.5 px-1 mr-5 border-b-2 border-transparent text-xs font-semibold text-ink-2
                           dark:text-ink-d2 hover:text-ink dark:hover:text-ink-d transition-colors duration-150"
                    id="memberInRoom-tab" data-tabs-target="#memberInRoomPanel" type="button" role="tab"
                    aria-selected="false">
                    Member In Room
                </button>
            </div>

            <div id="kehadiran-tab-content">

                {{-- Tab: Kehadiran Member --}}
                <div class="block" id="kehadiranMemberPanel" role="tabpanel">
                    <div class="px-4 md:px-6 pt-3 pb-1 flex justify-end">
                        <input type="text" id="searchKehadiran" placeholder="Cari nama..."
                            class="form-control form-control-sm" style="max-width:160px">
                    </div>
                    <div class="overflow-x-auto px-4 md:px-6">
                        <table
                            class="ajax-table border border-neutral-200 dark:border-neutral-700 rounded-lg border-separate w-full"
                            style="min-width:320px">
                            <thead>
                                <tr>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">No</th>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">Nama</th>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">Status</th>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyKehadiran">
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-xs text-ink-3">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 md:px-6 py-3 flex justify-between items-center flex-wrap gap-2">
                        <span class="text-xs text-ink-2 dark:text-ink-d2" id="infoKehadiran"></span>
                        <div id="paginationKehadiran" class="flex gap-1 flex-wrap"></div>
                    </div>
                </div>

                {{-- Tab: Member In Room --}}
                <div class="hidden" id="memberInRoomPanel" role="tabpanel">
                    <div class="px-4 md:px-6 pt-3 pb-1 flex justify-end">
                        <input type="text" id="searchMemberInRoom" placeholder="Cari nama..."
                            class="form-control form-control-sm" style="max-width:160px">
                    </div>
                    <div class="overflow-x-auto px-4 md:px-6">
                        <table
                            class="ajax-table border border-neutral-200 dark:border-neutral-700 rounded-lg border-separate w-full"
                            style="min-width:320px">
                            <thead>
                                <tr>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">No</th>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">Nama</th>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">Status</th>
                                    <th scope="col" style="padding:8px 12px;font-size:11px">Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMemberInRoom">
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-xs text-ink-3">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 md:px-6 py-3 flex justify-between items-center flex-wrap gap-2">
                        <span class="text-xs text-ink-2 dark:text-ink-d2" id="infoMemberInRoom"></span>
                        <div id="paginationMemberInRoom" class="flex gap-1 flex-wrap"></div>
                    </div>
                </div>

            </div>
        </x-card>

    </div>

    {{-- ===== Charts & Tables ===== --}}
    @unless (Auth::user()->hasRole('spv'))
    <div class="flex flex-col gap-6 mt-6">

        {{-- Chart 1: Membership & PT --}}
        <x-card>
            <x-slot:header>
                <div>
                    <p class="font-display font-semibold text-base text-ink dark:text-ink-d mb-0.5">
                        Membership &amp; Personal Trainer
                    </p>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-xl font-bold tabular-nums text-ink dark:text-ink-d"
                            id="totalRevenueDisplay">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                        <span class="text-sm text-ink-2 dark:text-ink-d2" id="revenueStatus">(All Years)</span>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <select id="chartYearFilter" class="form-select form-select-sm w-auto">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    <select id="chartFilter" class="form-select form-select-sm w-auto">
                        <option value="all" selected>All</option>
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                        <option value="daily">Daily Range</option>
                    </select>
                    <select id="chartMonthFilter" class="form-select form-select-sm w-auto" style="display:none;">
                        <option value="1" {{ $currentMonth == 1 ? 'selected' : '' }}>January</option>
                        <option value="2" {{ $currentMonth == 2 ? 'selected' : '' }}>February</option>
                        <option value="3" {{ $currentMonth == 3 ? 'selected' : '' }}>March</option>
                        <option value="4" {{ $currentMonth == 4 ? 'selected' : '' }}>April</option>
                        <option value="5" {{ $currentMonth == 5 ? 'selected' : '' }}>May</option>
                        <option value="6" {{ $currentMonth == 6 ? 'selected' : '' }}>June</option>
                        <option value="7" {{ $currentMonth == 7 ? 'selected' : '' }}>July</option>
                        <option value="8" {{ $currentMonth == 8 ? 'selected' : '' }}>August</option>
                        <option value="9" {{ $currentMonth == 9 ? 'selected' : '' }}>September</option>
                        <option value="10" {{ $currentMonth == 10 ? 'selected' : '' }}>October</option>
                        <option value="11" {{ $currentMonth == 11 ? 'selected' : '' }}>November</option>
                        <option value="12" {{ $currentMonth == 12 ? 'selected' : '' }}>December</option>
                    </select>
                    <input type="number" id="chartStartDate" class="form-select form-select-sm w-20"
                        placeholder="Start" min="1" max="31" style="display:none;">
                    <input type="number" id="chartEndDate" class="form-select form-select-sm w-20" placeholder="End"
                        min="1" max="31" style="display:none;">
                </div>
            </x-slot:header>

            <div id="chart" class="pt-4 w-full"></div>
        </x-card>

        {{-- Chart 2: Penjualan Produk --}}
        <x-card>
            <x-slot:header>
                <div>
                    <p class="font-display font-semibold text-base text-ink dark:text-ink-d mb-0.5">
                        Penjualan Produk
                    </p>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-xl font-bold tabular-nums text-ink dark:text-ink-d"
                            id="totalProductRevenueDisplay">Rp
                            {{ number_format($totalProductRevenue, 0, ',', '.') }}</span>
                        <span class="text-sm text-ink-2 dark:text-ink-d2" id="productRevenueStatus">(All Years)</span>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <select id="chartYearFilterProduct" class="form-select form-select-sm w-auto">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                    <select id="chartFilterProduct" class="form-select form-select-sm w-auto">
                        <option value="all" selected>All</option>
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                        <option value="daily">Daily Range</option>
                    </select>
                    <select id="chartMonthFilterProduct" class="form-select form-select-sm w-auto" style="display:none;">
                        <option value="1" {{ $currentMonth == 1 ? 'selected' : '' }}>January</option>
                        <option value="2" {{ $currentMonth == 2 ? 'selected' : '' }}>February</option>
                        <option value="3" {{ $currentMonth == 3 ? 'selected' : '' }}>March</option>
                        <option value="4" {{ $currentMonth == 4 ? 'selected' : '' }}>April</option>
                        <option value="5" {{ $currentMonth == 5 ? 'selected' : '' }}>May</option>
                        <option value="6" {{ $currentMonth == 6 ? 'selected' : '' }}>June</option>
                        <option value="7" {{ $currentMonth == 7 ? 'selected' : '' }}>July</option>
                        <option value="8" {{ $currentMonth == 8 ? 'selected' : '' }}>August</option>
                        <option value="9" {{ $currentMonth == 9 ? 'selected' : '' }}>September</option>
                        <option value="10" {{ $currentMonth == 10 ? 'selected' : '' }}>October</option>
                        <option value="11" {{ $currentMonth == 11 ? 'selected' : '' }}>November</option>
                        <option value="12" {{ $currentMonth == 12 ? 'selected' : '' }}>December</option>
                    </select>
                    <input type="number" id="chartStartDateProduct" class="form-select form-select-sm w-20"
                        placeholder="Start" min="1" max="31" style="display:none;">
                    <input type="number" id="chartEndDateProduct" class="form-select form-select-sm w-20"
                        placeholder="End" min="1" max="31" style="display:none;">
                </div>
            </x-slot:header>

            <div id="chartProduct" class="pt-4 w-full"></div>
        </x-card>

    </div>
    @endunless
@endsection


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        window.dashboardData = {
            membershipByYear: @json($membershipDataByYear),
            productByYear: @json($productDataByYear),
            currentYear: @json($currentYear),
            currentMonth: @json($currentMonth),
            totalRevenueAllYears: @json($totalRevenueAllYears),
            totalProductRevenueAllYears: @json($totalProductRevenueAllYears),
        };

        function formatRupiah(number) {
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>

    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // --- Tab active-state untuk card Kehadiran hari ini ---
            (function() {
                const ACTIVE = ['!border-primary-500', '!text-primary-600', 'dark:!text-primary-400'];
                const INACTIVE = ['border-transparent', 'text-ink-2', 'dark:text-ink-d2'];
                const tabs = document.querySelectorAll('#kehadiran-tab [role="tab"]');

                function activate(btn) {
                    tabs.forEach(function(t) {
                        t.classList.remove(...ACTIVE);
                        t.classList.add(...INACTIVE);
                    });
                    btn.classList.remove(...INACTIVE);
                    btn.classList.add(...ACTIVE);
                }
                tabs.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        activate(this);
                    });
                });
                if (tabs[0]) activate(tabs[0]);
            })();

            // --- Compact row renderer (4 kolom: No, Nama, Status, Waktu) ---
            const compactRow = function(item) {
                const isIn = item.status && item.status.toLowerCase() === 'in';
                const badge = isIn ?
                    '<span style="font-size:10px;font-weight:700;color:#BC3E14;background:rgba(242,98,46,.10);padding:2px 7px;border-radius:999px;white-space:nowrap">Hadir</span>' :
                    '<span style="font-size:10px;font-weight:700;color:#9C978E;background:rgba(156,151,142,.10);padding:2px 7px;border-radius:999px;white-space:nowrap">Keluar</span>';
                const waktu = (item.time ?? '-').split(' ').pop();
                return `<tr>
                    <td style="padding:8px 12px;font-size:12px;white-space:nowrap">${item.no ?? '-'}</td>
                    <td style="padding:8px 12px;font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px" title="${item.name ?? ''}">${item.name ?? '-'}</td>
                    <td style="padding:8px 12px">${badge}</td>
                    <td style="padding:8px 12px;font-size:12px;font-variant-numeric:tabular-nums;color:#6E6A63;white-space:nowrap">${waktu}</td>
                </tr>`;
            };

            AjaxTable.create({
                url: '{{ route('dashboard.kehadiran') }}',
                tbodyId: 'tbodyKehadiran',
                paginationId: 'paginationKehadiran',
                infoId: 'infoKehadiran',
                searchId: 'searchKehadiran',
                perPage: 5,
                colSpan: 4,
                renderRow: compactRow,
            });

            AjaxTable.create({
                url: '{{ route('dashboard.memberInRoom') }}',
                tbodyId: 'tbodyMemberInRoom',
                paginationId: 'paginationMemberInRoom',
                infoId: 'infoMemberInRoom',
                searchId: 'searchMemberInRoom',
                perPage: 5,
                colSpan: 4,
                renderRow: compactRow,
            });

        });
    </script>
@endsection
