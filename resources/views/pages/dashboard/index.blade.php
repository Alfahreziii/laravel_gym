@extends('layout.layout')

@php
    $title = 'Dashboard';
    $subTitle = 'AI';
    $script = '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
@endphp

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-3 3xl:grid-cols-3 gap-6 mx-auto">
        <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-cyan-600/10 to-bg-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-900 mb-1">Total Member</p>
                        <h6 class="mb-0">{{ number_format($totalMember, 0, ',', '.') }}</h6>
                    </div>
                    <div class="w-[50px] h-[50px] bg-cyan-600 rounded-full flex justify-center items-center">
                        <iconify-icon icon="gridicons:multiple-users" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
                <p class="font-medium text-sm text-neutral-600 mt-3 mb-0 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-success-600"><iconify-icon icon="bxs:up-arrow"
                            class="text-xs"></iconify-icon> +4000</span>
                    Last 30 days users
                </p>
            </div>
        </div><!-- card end -->
        <div
            class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-purple-600/10 to-bg-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-900 mb-1">Member In GYM</p>
                        <h6 class="mb-0">{{ number_format($memberInGym, 0, ',', '.') }}</h6>
                    </div>
                    <div class="w-[50px] h-[50px] bg-purple-600 rounded-full flex justify-center items-center">
                        <iconify-icon icon="fa-solid:award" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div><!-- card end -->
        <div
            class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-blue-600/10 to-bg-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-900 mb-1">Member Aktif</p>
                        <h6 class="mb-0">{{ number_format($memberAktif, 0, ',', '.') }}</h6>
                    </div>
                    <div class="w-[50px] h-[50px] bg-blue-600 rounded-full flex justify-center items-center">
                        <iconify-icon icon="fluent:people-20-filled" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
                <p class="font-medium text-sm text-neutral-600 mt-3 mb-0 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-success-600"><iconify-icon icon="bxs:up-arrow"
                            class="text-xs"></iconify-icon> +200</span>
                    Last 30 days users
                </p>
            </div>
        </div><!-- card end -->
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 2xl:col-span-9 gap-6 mt-6">
        <div class="xl:col-span-12 2xl:col-span-12">
            <div class="card h-full w-full rounded-lg border-0">
                <div class="card-body overflow-x-auto">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h6 class="text-lg mb-0">Membership & Personal Trainer</h6>
                        <div class="flex gap-2">
                            <select id="chartYearFilter" class="form-select bg-white form-select-sm w-auto">
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            <select id="chartFilter" class="form-select bg-white form-select-sm w-auto">
                                <option value="all" selected>All</option>
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                                <option value="daily">Daily Range</option>
                            </select>
                            <select id="chartMonthFilter" class="form-select bg-white form-select-sm w-auto"
                                style="display: none;">
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
                            <input type="number" id="chartStartDate" class="form-select bg-white form-select-sm w-20"
                                placeholder="Start" min="1" max="31" style="display: none;">
                            <input type="number" id="chartEndDate" class="form-select bg-white form-select-sm w-20"
                                placeholder="End" min="1" max="31" style="display: none;">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <h6 class="mb-0" id="totalRevenueDisplay">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h6>
                        <span class="text-sm text-gray-500 ml-2" id="revenueStatus">(All Years)</span>
                    </div>
                    <div id="chart" class="pt-[28px] apexcharts-tooltip-style-1 w-full min-w-800px"></div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-12 2xl:col-span-12">
            <div class="card h-full w-full rounded-lg border-0">
                <div class="card-body overflow-x-auto">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h6 class="text-lg mb-0">Penjualan Produk</h6>
                        <div class="flex gap-2">
                            <select id="chartYearFilterProduct" class="form-select bg-white form-select-sm w-auto">
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            <select id="chartFilterProduct" class="form-select bg-white form-select-sm w-auto">
                                <option value="all" selected>All</option>
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                                <option value="daily">Daily Range</option>
                            </select>
                            <select id="chartMonthFilterProduct" class="form-select bg-white form-select-sm w-auto"
                                style="display: none;">
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
                            <input type="number" id="chartStartDateProduct"
                                class="form-select bg-white form-select-sm w-20" placeholder="Start" min="1"
                                max="31" style="display: none;">
                            <input type="number" id="chartEndDateProduct"
                                class="form-select bg-white form-select-sm w-20" placeholder="End" min="1"
                                max="31" style="display: none;">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <h6 class="mb-0" id="totalProductRevenueDisplay">Rp
                            {{ number_format($totalProductRevenue, 0, ',', '.') }}</h6>
                        <span class="text-sm text-gray-500 ml-2" id="productRevenueStatus">(All Years)</span>
                    </div>
                    <div id="chartProduct" class="pt-[28px] apexcharts-tooltip-style-1 w-full min-w-800px"></div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-12 2xl:col-span-12">
            <div class="card h-full border-0">
                <div class="card-body p-6">

                    <div class="mb-4">
                        <ul class="tab-style-gradient flex flex-wrap -mb-px text-sm font-medium text-center"
                            id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                            <li role="presentation">
                                <button
                                    class="py-2.5 px-4 border-t-2 font-semibold text-lg inline-flex items-center gap-3 text-neutral-600"
                                    id="registered-tab" data-tabs-target="#registered" type="button" role="tab"
                                    aria-controls="registered" aria-selected="true">
                                    Kehadiran Member
                                </button>
                            </li>
                            <li role="presentation">
                                <button
                                    class="py-2.5 px-4 border-t-2 font-semibold text-lg inline-flex items-center gap-3 text-neutral-600 hover:text-gray-600 hover:border-gray-300"
                                    id="subscribe-tab" data-tabs-target="#subscribe" type="button" role="tab"
                                    aria-controls="subscribe" aria-selected="false">
                                    Member In Room
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div id="default-tab-content">

                        {{-- Tab Kehadiran Member --}}
                        <div class="block" id="registered" role="tabpanel" aria-labelledby="registered-tab">
                            <div class="flex justify-between items-center mb-3 gap-2 flex-wrap">
                                <input type="text" id="searchKehadiran" placeholder="Search..."
                                    class="form-control form-control-sm w-64">
                                <span class="text-sm text-gray-500" id="infoKehadiran"></span>
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
                                        <tr>
                                            <td colspan="6" class="text-center py-8">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-between items-center mt-3 flex-wrap gap-2">
                                <span class="text-sm text-gray-500" id="infoKehadiranBottom"></span>
                                <div id="paginationKehadiran" class="flex gap-1 flex-wrap"></div>
                            </div>
                        </div>

                        {{-- Tab Member In Room --}}
                        <div class="hidden" id="subscribe" role="tabpanel" aria-labelledby="subscribe-tab">
                            <div class="flex justify-between items-center mb-3 gap-2 flex-wrap">
                                <input type="text" id="searchMemberInRoom" placeholder="Search..."
                                    class="form-control form-control-sm w-64">
                                <span class="text-sm text-gray-500" id="infoMemberInRoom"></span>
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
                                        <tr>
                                            <td colspan="6" class="text-center py-8">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-between items-center mt-3 flex-wrap gap-2">
                                <span class="text-sm text-gray-500" id="infoMemberInRoomBottom"></span>
                                <div id="paginationMemberInRoom" class="flex gap-1 flex-wrap"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        window.dashboardData = {
            // Data Membership & Trainer per tahun
            membershipByYear: @json($membershipDataByYear),

            // Data Penjualan Produk per tahun
            productByYear: @json($productDataByYear),

            // Tahun dan bulan default
            currentYear: @json($currentYear),
            currentMonth: @json($currentMonth),

            // Total revenue dari SEMUA tahun
            totalRevenueAllYears: @json($totalRevenueAllYears),
            totalProductRevenueAllYears: @json($totalProductRevenueAllYears),
        };

        // Fungsi untuk format rupiah
        function formatRupiah(number) {
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Fungsi untuk update total revenue
        function updateTotalRevenue(chartType, year, month = null) {
            let total = 0;
            let status = '';

            if (chartType === 'all') {
                // Untuk filter All, gunakan total dari SEMUA tahun
                total = window.dashboardData.totalRevenueAllYears;
                status = '(All Years)';
            } else if (chartType === 'monthly') {
                // Untuk filter Monthly, gunakan total tahun yang dipilih
                const data = window.dashboardData.membershipByYear[year];
                total = data.totalPerYear;
                status = `(Year ${year})`;
            } else if (chartType === 'weekly' || chartType === 'daily') {
                // Untuk filter Weekly/Daily, gunakan total bulan yang dipilih
                const data = window.dashboardData.membershipByYear[year];
                if (month) {
                    total = data.totalPerMonth[month];
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    status = `(${monthNames[month - 1]} ${year})`;
                }
            }

            document.getElementById('totalRevenueDisplay').textContent = formatRupiah(total);
            document.getElementById('revenueStatus').textContent = status;
        }

        // Fungsi untuk update total product revenue
        function updateTotalProductRevenue(chartType, year, month = null) {
            let total = 0;
            let status = '';

            if (chartType === 'all') {
                // Untuk filter All, gunakan total dari SEMUA tahun
                total = window.dashboardData.totalProductRevenueAllYears;
                status = '(All Years)';
            } else if (chartType === 'monthly') {
                // Untuk filter Monthly, gunakan total tahun yang dipilih
                const data = window.dashboardData.productByYear[year];
                total = data.totalPerYear;
                status = `(Year ${year})`;
            } else if (chartType === 'weekly' || chartType === 'daily') {
                // Untuk filter Weekly/Daily, gunakan total bulan yang dipilih
                const data = window.dashboardData.productByYear[year];
                if (month) {
                    total = data.totalPerMonth[month];
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    status = `(${monthNames[month - 1]} ${year})`;
                }
            }

            document.getElementById('totalProductRevenueDisplay').textContent = formatRupiah(total);
            document.getElementById('productRevenueStatus').textContent = status;
        }
    </script>

    {{-- Ajax Table Script --}}
    <script src="{{ asset('assets/js/ajax-table.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

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
