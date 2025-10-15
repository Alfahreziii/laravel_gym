@extends('layout.layout')

@php
    $title='Dashboard';
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

    <div class="grid grid-cols-1 xl:grid-cols-12 2xl:col-span-9 gap-6 mt-6">
        <div class="xl:col-span-12 2xl:col-span-12">
            <div class="card h-full w-full rounded-lg border-0">
                <div class="card-body overflow-x-auto">
                    <div class="flex flex-wrap items-center justify-between">
                        <h6 class="text-lg mb-0">Sales Statistic</h6>
                        <select id="chartFilter" class="form-select bg-white form-select-sm w-auto">
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <h6 class="mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h6>
                    </div>
                    <div id="chart" class="pt-[28px] apexcharts-tooltip-style-1 w-full min-w-800px"></div>
                </div>
            </div>
        </div>
        <div class="xl:col-span-12 2xl:col-span-12">
            <div class="card h-full border-0">
                <div class="card-body p-6">

                    <div class="mb-4">
                        <ul class="tab-style-gradient flex flex-wrap -mb-px text-sm font-medium text-center" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                            <li class="" role="presentation">
                                <button class="py-2.5 px-4 border-t-2 font-semibold text-lg inline-flex items-center gap-3 text-neutral-600"
                                    id="registered-tab"
                                    data-tabs-target="#registered"
                                    type="button"
                                    role="tab"
                                    aria-controls="registered"
                                    aria-selected="true">
                                    Kehadiran Member
                                </button>
                            </li>
                            <li class="" role="presentation">
                                <button class="py-2.5 px-4 border-t-2 font-semibold text-lg inline-flex items-center gap-3 text-neutral-600 hover:text-gray-600 hover:border-gray-300" id="subscribe-tab" data-tabs-target="#subscribe" type="button" role="tab" aria-controls="subscribe" aria-selected="false">
                                    Member In Room
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div id="default-tab-content">
                        <div class="block" id="registered" role="tabpanel" aria-labelledby="registered-tab">
                            <div class="">
                                <table id="table-kehadiran" class="border border-neutral-200 rounded-lg border-separate">
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
                                    <tbody>
                                        @foreach($kehadiranmembers as $index => $item)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                                            <td class="whitespace-nowrap">{{ $item->rfid }}</td>
                                            <td class="whitespace-nowrap">
                                                    <img src="{{ asset('storage/' . $item->foto) }}" 
                                                        alt="image {{ $item->anggota->name }}" 
                                                        class="w-10 h-10 rounded object-cover">
                                            </td>
                                            <td class="whitespace-nowrap">{{ $item->anggota->name }}</td>
                                            <td class="whitespace-nowrap">{{ $item->status }}</td>
                                            <td class="whitespace-nowrap">{{ $item->created_at }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="hidden" id="subscribe" role="tabpanel" aria-labelledby="subscribe-tab">
                            <div class="">
                                <table id="table-memberinroom" class="border border-neutral-200 rounded-lg border-separate">
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
                                    <tbody>
                                        @foreach($memberInGymToday as $index => $item)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                                            <td class="whitespace-nowrap">{{ $item->rfid }}</td>
                                            <td class="whitespace-nowrap">
                                                    <img src="{{ asset('storage/' . $item->foto) }}" 
                                                        alt="image {{ $item->anggota->name }}" 
                                                        class="w-10 h-10 rounded object-cover">
                                            </td>
                                            <td class="whitespace-nowrap">{{ $item->anggota->name }}</td>
                                            <td class="whitespace-nowrap">{{ $item->status }}</td>
                                            <td class="whitespace-nowrap">{{ $item->created_at }}</td>
                                        </tr>
                                        @endforeach
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
    };
</script>

{!! $script !!}

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tables = {
        "#table-kehadiran": null,
        "#table-memberinroom": null,
    };

    Object.keys(tables).forEach(id => {
        const table = document.querySelector(id);
        if (!table) return;

        try {
            tables[id] = new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: true,
                perPage: 5,
            });
            console.log(`✅ DataTable berhasil diinisialisasi untuk ${id}`);
        } catch (error) {
            console.error(`❌ Gagal inisialisasi DataTable ${id}:`, error);
        }
    });

    // 👇 Tambahkan event ini
    const subscribeTabBtn = document.getElementById("subscribe-tab");
    subscribeTabBtn.addEventListener("click", () => {
        setTimeout(() => {
            if (tables["#table-memberinroom"]) {
                tables["#table-memberinroom"].refresh();
                console.log("🔄 DataTable Member In Room direfresh");
            }
        }, 200);
    });
});

</script>
@endsection

