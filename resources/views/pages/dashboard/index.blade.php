@extends('layout.layout')

@php
    $title='Dashboard';
    $subTitle = 'AI';
    $script= '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
@endphp

@section('content')

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-6">
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
                    <span class="inline-flex items-center gap-1 text-success-600"><iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon> +4000</span>
                    Last 30 days users
                </p>
            </div>
        </div><!-- card end -->
        <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-purple-600/10 to-bg-white">
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
        <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-blue-600/10 to-bg-white">
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
                    <span class="inline-flex items-center gap-1 text-success-600"><iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon> +200</span>
                    Last 30 days users
                </p>
            </div>
        </div><!-- card end -->
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mt-6">
        <div class="xl:col-span-12 2xl:col-span-6">
            <div class="card h-full rounded-lg border-0">
                <div class="card-body">
                    <div class="flex flex-wrap items-center justify-between">
                        <h6 class="text-lg mb-0">Sales Statistic</h6>
                        <select id="chartFilter" class="form-select bg-white form-select-sm w-auto">
                            <option value="yearly">Yearly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="today">Today</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <h6 class="mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h6>
                    </div>
                    <div id="chart" class="pt-[28px] apexcharts-tooltip-style-1"></div>
                </div>
            </div>
        </div>
        <div class="xl:col-span-6 2xl:col-span-3">
            <div class="card h-full rounded-lg border-0">
                <div class="card-body p-6">
                    <h6 class="mb-3 font-semibold text-lg">Total Subscriber</h6>
                    <div class="flex items-center gap-2 mb-5">
                        <h6 class="font-semibold mb-0">5,000</h6>
                        <span class="text-sm font-semibold rounded-full bg-danger-100 text-danger-600 border border-danger-200 px-2 py-1.5 line-height-1 flex items-center gap-1">
                            10% <iconify-icon icon="iconamoon:arrow-down-2-fill" class="icon"></iconify-icon>
                        </span>
                        - 20 Per Day
                    </div>

                    <div id="barChart"></div>

                </div>
            </div>
        </div>
        <div class="xl:col-span-6 2xl:col-span-3">
            <div class="card h-full rounded-lg border-0 overflow-hidden">
                <div class="card-body p-6">
                    <div class="flex items-center flex-wrap gap-2 justify-between">
                        <h6 class="mb-2 font-bold text-lg">Users Overview</h6>
                        <div class="">
                            <select class="form-select form-select-sm w-auto bg-white border text-secondary-light">
                                <option>Today</option>
                                <option>Weekly</option>
                                <option>Monthly</option>
                                <option>Yearly</option>
                            </select>
                        </div>
                    </div>


                    <div id="userOverviewDonutChart" class="apexcharts-tooltip-z-none"></div>

                    <ul class="flex flex-wrap items-center justify-between mt-4 gap-3">
                        <li class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-sm bg-primary-600"></span>
                            <span class="text-secondary-light text-sm font-normal">
                                New:
                                <span class="text-neutral-600 font-semibold">400</span>
                            </span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-sm bg-warning-600"></span>
                            <span class="text-secondary-light text-sm font-normal">
                                Subscribed:
                                <span class="text-neutral-600 font-semibold">300</span>
                            </span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
        <div class="xl:col-span-12 2xl:col-span-9">
            <div class="card h-full border-0">
                <div class="card-body p-6">

                    <div class="mb-4">
                        <ul class="tab-style-gradient flex flex-wrap -mb-px text-sm font-medium text-center" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                            <li class="" role="presentation">
                                <button class="py-2.5 px-4 border-t-2 font-semibold text-lg inline-flex items-center gap-3 text-neutral-600" id="registered-tab" data-tabs-target="#registered" type="button" role="tab" aria-controls="registered" aria-selected="false">
                                    Latest Registered
                                    <span class="text-white px-2 py-0.5 bg-neutral-600 rounded-full text-sm">20</span>
                                </button>
                            </li>
                            <li class="" role="presentation">
                                <button class="py-2.5 px-4 border-t-2 font-semibold text-lg inline-flex items-center gap-3 text-neutral-600 hover:text-gray-600 hover:border-gray-300" id="subscribe-tab" data-tabs-target="#subscribe" type="button" role="tab" aria-controls="subscribe" aria-selected="false">
                                    Latest Subscribe
                                    <span class="text-white px-2 py-0.5 bg-neutral-600 rounded-full text-sm">20</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div id="default-tab-content">
                        <div class="hidden" id="registered" role="tabpanel" aria-labelledby="registered-tab">
                            <div class="overflow-x-auto">
                                <table class="table bordered-table sm-table mb-0 table-auto">
                                    <thead>
                                        <tr>
                                            <th scope="col">Users </th>
                                            <th scope="col">Registered On</th>
                                            <th scope="col">Plan</th>
                                            <th scope="col" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user1.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Dianne Russell</h6>
                                                        <span class="text-sm text-secondary-light font-medium">redaniel@gmail.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Free</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user2.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Wade Warren</h6>
                                                        <span class="text-sm text-secondary-light font-medium">xterris@gmail.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Basic</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user3.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Albert Flores</h6>
                                                        <span class="text-sm text-secondary-light font-medium">seannand@mail.ru</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Standard</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user4.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Bessie Cooper </h6>
                                                        <span class="text-sm text-secondary-light font-medium">igerrin@gmail.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Business</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user5.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Arlene McCoy</h6>
                                                        <span class="text-sm text-secondary-light font-medium">fellora@mail.ru</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Enterprise </td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="hidden" id="subscribe" role="tabpanel" aria-labelledby="subscribe-tab">
                            <div class="overflow-x-auto">
                                <table class="table bordered-table sm-table mb-0 table-auto">
                                    <thead>
                                        <tr>
                                            <th scope="col">Users Name </th>
                                            <th scope="col">Registered On</th>
                                            <th scope="col">Plan</th>
                                            <th scope="col" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user1.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Dianne Russell</h6>
                                                        <span class="text-sm text-secondary-light font-medium">redaniel@gmail.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Free</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user2.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Wade Warren</h6>
                                                        <span class="text-sm text-secondary-light font-medium">xterris@gmail.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Basic</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user3.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Albert Flores</h6>
                                                        <span class="text-sm text-secondary-light font-medium">seannand@mail.ru</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Standard</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user4.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Bessie Cooper </h6>
                                                        <span class="text-sm text-secondary-light font-medium">igerrin@gmail.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Business</td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="{{ asset('assets/images/users/user5.png') }}" alt="" class="w-10 h-10 rounded-full shrink-0 me-2 overflow-hidden">
                                                    <div class="grow">
                                                        <h6 class="text-base mb-0 font-medium">Arlene McCoy</h6>
                                                        <span class="text-sm text-secondary-light font-medium">fellora@mail.ru</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>27 Mar 2025</td>
                                            <td>Enterprise </td>
                                            <td class="text-center">
                                                <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Active</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
        monthlyData: @json($monthlyData),
        weeklyData: @json($weeklyData),
        donutData: @json($donutData),
    };
</script>
{!! $script !!}
@endsection