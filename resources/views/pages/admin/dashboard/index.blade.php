@extends('layout.layout')

@php
    $title = 'Dashboard';
    $subTitle = 'Overview';
    $script = '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
@endphp

@section('content')

    {{-- ===== KPI Row ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-3 gap-6">

        <x-stat-card
            color="blue"
            label="Total Member"
            icon="gridicons:multiple-users"
            value="{{ number_format($totalMember, 0, ',', '.') }}"
        />

        <x-stat-card
            color="cyan"
            label="Male Members"
            icon="fa-solid:male"
            value="{{ number_format($memberLakiLaki, 0, ',', '.') }}"
            sub="{{ $totalMember > 0 ? round(($memberLakiLaki / $totalMember) * 100, 1) : 0 }}% dari total member"
        />

        <x-stat-card
            color="pink"
            label="Female Members"
            icon="fa-solid:female"
            value="{{ number_format($memberPerempuan, 0, ',', '.') }}"
            sub="{{ $totalMember > 0 ? round(($memberPerempuan / $totalMember) * 100, 1) : 0 }}% dari total member"
        />

        <x-stat-card
            color="teal"
            label="Member In GYM"
            icon="mdi:location-enter"
            value="{{ number_format($memberInGym, 0, ',', '.') }}"
        />

        <x-stat-card
            color="orange"
            label="Member Aktif"
            icon="fluent:people-20-filled"
            value="{{ number_format($memberAktif, 0, ',', '.') }}"
            sub="{{ $totalMember > 0 ? round(($memberAktif / $totalMember) * 100, 1) : 0 }}% dari total member"
        />

    </div>

    {{-- ===== Charts & Tables ===== --}}
    <div class="flex flex-col gap-6 mt-6">

        {{-- Chart 1: Membership & PT --}}
        <x-card>
            <x-slot:header>
                <div>
                    <p class="font-display font-semibold text-base text-neutral-900 dark:text-neutral-100 mb-0.5">
                        Membership &amp; Personal Trainer
                    </p>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-xl font-bold tabular-nums text-neutral-900 dark:text-neutral-100"
                            id="totalRevenueDisplay">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400"
                            id="revenueStatus">(All Years)</span>
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
                        <option value="1"  {{ $currentMonth == 1  ? 'selected' : '' }}>January</option>
                        <option value="2"  {{ $currentMonth == 2  ? 'selected' : '' }}>February</option>
                        <option value="3"  {{ $currentMonth == 3  ? 'selected' : '' }}>March</option>
                        <option value="4"  {{ $currentMonth == 4  ? 'selected' : '' }}>April</option>
                        <option value="5"  {{ $currentMonth == 5  ? 'selected' : '' }}>May</option>
                        <option value="6"  {{ $currentMonth == 6  ? 'selected' : '' }}>June</option>
                        <option value="7"  {{ $currentMonth == 7  ? 'selected' : '' }}>July</option>
                        <option value="8"  {{ $currentMonth == 8  ? 'selected' : '' }}>August</option>
                        <option value="9"  {{ $currentMonth == 9  ? 'selected' : '' }}>September</option>
                        <option value="10" {{ $currentMonth == 10 ? 'selected' : '' }}>October</option>
                        <option value="11" {{ $currentMonth == 11 ? 'selected' : '' }}>November</option>
                        <option value="12" {{ $currentMonth == 12 ? 'selected' : '' }}>December</option>
                    </select>
                    <input type="number" id="chartStartDate" class="form-select form-select-sm w-20"
                        placeholder="Start" min="1" max="31" style="display:none;">
                    <input type="number" id="chartEndDate" class="form-select form-select-sm w-20"
                        placeholder="End" min="1" max="31" style="display:none;">
                </div>
            </x-slot:header>

            <div id="chart" class="pt-4 w-full"></div>
        </x-card>

        {{-- Chart 2: Penjualan Produk --}}
        <x-card>
            <x-slot:header>
                <div>
                    <p class="font-display font-semibold text-base text-neutral-900 dark:text-neutral-100 mb-0.5">
                        Penjualan Produk
                    </p>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-xl font-bold tabular-nums text-neutral-900 dark:text-neutral-100"
                            id="totalProductRevenueDisplay">Rp {{ number_format($totalProductRevenue, 0, ',', '.') }}</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400"
                            id="productRevenueStatus">(All Years)</span>
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
                        <option value="1"  {{ $currentMonth == 1  ? 'selected' : '' }}>January</option>
                        <option value="2"  {{ $currentMonth == 2  ? 'selected' : '' }}>February</option>
                        <option value="3"  {{ $currentMonth == 3  ? 'selected' : '' }}>March</option>
                        <option value="4"  {{ $currentMonth == 4  ? 'selected' : '' }}>April</option>
                        <option value="5"  {{ $currentMonth == 5  ? 'selected' : '' }}>May</option>
                        <option value="6"  {{ $currentMonth == 6  ? 'selected' : '' }}>June</option>
                        <option value="7"  {{ $currentMonth == 7  ? 'selected' : '' }}>July</option>
                        <option value="8"  {{ $currentMonth == 8  ? 'selected' : '' }}>August</option>
                        <option value="9"  {{ $currentMonth == 9  ? 'selected' : '' }}>September</option>
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

        {{-- Tabel Kehadiran --}}
        <x-card>
            {{-- Tab nav --}}
            <div class="flex border-b border-[#E2E0DB] dark:border-neutral-700 mb-5 -mx-6 px-6"
                id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                <button
                    class="pb-3 px-1 mr-6 border-b-2 border-transparent text-sm font-semibold text-neutral-500
                           hover:text-neutral-800 hover:border-neutral-300
                           dark:text-neutral-400 dark:hover:text-neutral-200
                           transition-colors duration-150"
                    id="registered-tab" data-tabs-target="#registered"
                    type="button" role="tab" aria-controls="registered" aria-selected="true"
                    x-data
                    x-init="$el.classList.add('!border-primary-500', '!text-primary-600', 'dark:!text-primary-400')">
                    Kehadiran Member
                </button>
                <button
                    class="pb-3 px-1 mr-6 border-b-2 border-transparent text-sm font-semibold text-neutral-500
                           hover:text-neutral-800 hover:border-neutral-300
                           dark:text-neutral-400 dark:hover:text-neutral-200
                           transition-colors duration-150"
                    id="subscribe-tab" data-tabs-target="#subscribe"
                    type="button" role="tab" aria-controls="subscribe" aria-selected="false">
                    Member In Room
                </button>
            </div>

            <div id="default-tab-content">

                {{-- Tab Kehadiran Member --}}
                <div class="block" id="registered" role="tabpanel" aria-labelledby="registered-tab">
                    <div class="flex justify-between items-center mb-3 gap-2 flex-wrap">
                        <input type="text" id="searchKehadiran" placeholder="Search..."
                            class="form-control form-control-sm w-64">
                        <span class="text-sm text-neutral-500" id="infoKehadiran"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="ajax-table border border-neutral-200 rounded-lg border-separate">
                            <thead>
                                <tr>
                                    <th scope="col">S.L</th>
                                    <th scope="col">RFID</th>
                                    <th scope="col">Foto</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Time</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyKehadiran">
                                <tr><td colspan="6" class="text-center py-8 text-neutral-400">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center mt-3 flex-wrap gap-2">
                        <span class="text-sm text-neutral-500" id="infoKehadiranBottom"></span>
                        <div id="paginationKehadiran" class="flex gap-1 flex-wrap"></div>
                    </div>
                </div>

                {{-- Tab Member In Room --}}
                <div class="hidden" id="subscribe" role="tabpanel" aria-labelledby="subscribe-tab">
                    <div class="flex justify-between items-center mb-3 gap-2 flex-wrap">
                        <input type="text" id="searchMemberInRoom" placeholder="Search..."
                            class="form-control form-control-sm w-64">
                        <span class="text-sm text-neutral-500" id="infoMemberInRoom"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="ajax-table border border-neutral-200 rounded-lg border-separate">
                            <thead>
                                <tr>
                                    <th scope="col">S.L</th>
                                    <th scope="col">RFID</th>
                                    <th scope="col">Foto</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Time</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMemberInRoom">
                                <tr><td colspan="6" class="text-center py-8 text-neutral-400">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center mt-3 flex-wrap gap-2">
                        <span class="text-sm text-neutral-500" id="infoMemberInRoomBottom"></span>
                        <div id="paginationMemberInRoom" class="flex gap-1 flex-wrap"></div>
                    </div>
                </div>

            </div>
        </x-card>

    </div>

@endsection


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        window.dashboardData = {
            membershipByYear:           @json($membershipDataByYear),
            productByYear:              @json($productDataByYear),
            currentYear:                @json($currentYear),
            currentMonth:               @json($currentMonth),
            totalRevenueAllYears:       @json($totalRevenueAllYears),
            totalProductRevenueAllYears:@json($totalProductRevenueAllYears),
        };

        function formatRupiah(number) {
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>

    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            AjaxTable.create({
                url: '{{ route('dashboard.kehadiran') }}',
                tbodyId: 'tbodyKehadiran',
                paginationId: 'paginationKehadiran',
                infoId: 'infoKehadiranBottom',
                searchId: 'searchKehadiran',
                perPage: 5,
                colSpan: 6,
            });

            AjaxTable.create({
                url: '{{ route('dashboard.memberInRoom') }}',
                tbodyId: 'tbodyMemberInRoom',
                paginationId: 'paginationMemberInRoom',
                infoId: 'infoMemberInRoomBottom',
                searchId: 'searchMemberInRoom',
                perPage: 5,
                colSpan: 6,
            });

        });
    </script>
@endsection
