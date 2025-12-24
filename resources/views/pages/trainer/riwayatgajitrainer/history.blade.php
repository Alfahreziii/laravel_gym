@extends('layout.layout')
@php
    $title='History Pembayaran Gaji Trainer';
    $subTitle = 'History Pembayaran Gaji - ' . $trainer->name;
@endphp

@section('content')

<div class="grid grid-cols-12 gap-4">
    <!-- Back Button & Header -->
    <div class="col-span-12">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('riwayat_gaji_trainer.index') }}" 
                   class="w-10 h-10 bg-neutral-100 hover:bg-neutral-200 rounded-full inline-flex items-center justify-center">
                    <iconify-icon icon="ep:back" class="text-xl"></iconify-icon>
                </a>
                <div>
                    <h4 class="text-2xl font-bold text-neutral-900">History Pembayaran Gaji</h4>
                    <p class="text-neutral-600 mt-1">{{ $trainer->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="col-span-12 md:col-span-4">
        <div class="card border-0 overflow-hidden">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-success-100 rounded-full flex items-center justify-center">
                        <iconify-icon icon="solar:dollar-bold" class="text-3xl text-success-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Total Pembayaran</p>
                        <h5 class="text-xl font-bold text-success-600">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 md:col-span-4">
        <div class="card border-0 overflow-hidden">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center">
                        <iconify-icon icon="solar:graph-up-bold" class="text-3xl text-primary-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Total Sesi</p>
                        <h5 class="text-xl font-bold text-primary-600">{{ number_format($totalSesi, 0, ',', '.') }} Sesi</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 md:col-span-4">
        <div class="card border-0 overflow-hidden">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-warning-100 rounded-full flex items-center justify-center">
                        <iconify-icon icon="solar:star-bold" class="text-3xl text-warning-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Total Bonus</p>
                        <h5 class="text-xl font-bold text-warning-600">Rp {{ number_format($totalBonus, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Trainer -->
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Nama Trainer</p>
                        <p class="font-semibold text-neutral-900">{{ $trainer->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Base Rate per Sesi</p>
                        <p class="font-semibold text-neutral-900">Rp {{ number_format($trainer->settingGaji->base_rate ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-600 mb-1">Status</p>
                        <span class="bg-success-100 text-success-600 px-3 py-1 rounded-full text-sm font-medium">
                            {{ ucfirst($trainer->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel History -->
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Riwayat Pembayaran</h6>
                <span class="text-sm text-neutral-600">Total: {{ $riwayatGaji->total() }} transaksi</span>
            </div>
            <div class="card-body">
                @if($riwayatGaji->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-700">No</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-700">Periode</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-neutral-700">Tgl Bayar</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-neutral-700">Jumlah Sesi</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-neutral-700">Base Rate</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-neutral-700">Bonus</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-neutral-700">Total Dibayarkan</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-neutral-700">Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($riwayatGaji as $index => $riwayat)
                            <tr class="border-b border-neutral-200 hover:bg-neutral-50 transition-colors">
                                <td class="px-4 py-3 text-sm">
                                    {{ ($riwayatGaji->currentPage() - 1) * $riwayatGaji->perPage() + $index + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm">
                                        <div class="font-medium text-neutral-900">
                                            {{ $riwayat->tgl_mulai->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-neutral-600">
                                            s/d {{ $riwayat->tgl_selesai->format('d M Y') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-neutral-900">
                                        {{ $riwayat->tgl_bayar->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-neutral-600">
                                        {{ $riwayat->tgl_bayar->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-primary-100 text-primary-600 px-3 py-1.5 rounded-full text-sm font-semibold">
                                        {{ $riwayat->jumlah_sesi }} Sesi
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-neutral-900">
                                    Rp {{ number_format($riwayat->base_rate, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($riwayat->bonus > 0)
                                        <span class="text-sm font-medium text-warning-600">
                                            +Rp {{ number_format($riwayat->bonus, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-sm text-neutral-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-success-600">
                                        Rp {{ number_format($riwayat->total_dibayarkan, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-info-100 text-info-600 px-3 py-1 rounded-full text-xs font-medium">
                                        {{ $riwayat->metode_pembayaran_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-neutral-50 border-t-2 border-neutral-300">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right font-bold text-neutral-900">Total:</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-primary-200 text-primary-700 px-3 py-1.5 rounded-full text-sm font-bold">
                                        {{ $riwayatGaji->sum('jumlah_sesi') }} Sesi
                                    </span>
                                </td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-warning-600">
                                        Rp {{ number_format($riwayatGaji->sum('bonus'), 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-base font-bold text-success-600">
                                        Rp {{ number_format($riwayatGaji->sum('total_dibayarkan'), 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $riwayatGaji->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <iconify-icon icon="solar:inbox-outline" class="text-6xl text-neutral-300"></iconify-icon>
                    <p class="mt-4 text-neutral-600 text-lg">Belum ada riwayat pembayaran</p>
                    <p class="mt-2 text-sm text-neutral-500">Data pembayaran akan muncul setelah melakukan pembayaran gaji trainer</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection