@extends('layout.layout')

@php
    $title = 'Neraca Keuangan';
    $subTitle = 'Laporan Keuangan Gym';
@endphp

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-gray-100">{{ $title }}</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $subTitle }}</p>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Bagian Aset --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-5">
            <h2 class="text-xl font-semibold mb-3 text-green-600">Aset</h2>
            <table class="w-full text-sm text-gray-700 dark:text-gray-300">
                <thead>
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <th class="text-left py-2">Nama Akun</th>
                        <th class="text-right py-2">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kategori->where('kode', 'AST')->first()?->akun ?? [] as $akun)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2">{{ $akun->nama }}</td>
                            <td class="text-right py-2">{{ number_format($akun->saldo, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold border-t border-gray-400">
                        <td class="py-2">Total Aset</td>
                        <td class="text-right py-2 text-green-600">{{ number_format($total_aset, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Bagian Kewajiban & Modal --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-5">
            <h2 class="text-xl font-semibold mb-3 text-blue-600">Kewajiban & Modal</h2>

            {{-- Kewajiban --}}
            <h3 class="font-medium text-blue-500 mb-2">Kewajiban</h3>
            <table class="w-full text-sm text-gray-700 dark:text-gray-300 mb-4">
                <tbody>
                    @foreach($kategori->where('kode', 'KEW')->first()?->akun ?? [] as $akun)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2">{{ $akun->nama }}</td>
                            <td class="text-right py-2">{{ number_format($akun->saldo, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold border-t border-gray-400">
                        <td class="py-2">Total Kewajiban</td>
                        <td class="text-right py-2 text-blue-600">{{ number_format($total_kewajiban, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            {{-- Modal --}}
            <h3 class="font-medium text-purple-500 mb-2">Modal</h3>
            <table class="w-full text-sm text-gray-700 dark:text-gray-300">
                <tbody>
                    @foreach($kategori->where('kode', 'MOD')->first()?->akun ?? [] as $akun)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2">{{ $akun->nama }}</td>
                            <td class="text-right py-2">{{ number_format($akun->saldo, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold border-t border-gray-400">
                        <td class="py-2">Total Modal</td>
                        <td class="text-right py-2 text-purple-600">{{ number_format($total_modal, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="font-semibold border-t-2 border-gray-500">
                        <td class="py-2">Total Kewajiban + Modal</td>
                        <td class="text-right py-2 text-blue-700">{{ number_format($total_kewajiban_modal, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Kesetimbangan Neraca --}}
    <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center">
        <p class="font-medium text-gray-800 dark:text-gray-200">
            @if(number_format($total_aset, 2) === number_format($total_kewajiban_modal, 2))
                ✅ Neraca Seimbang
            @else
                ⚠️ Neraca Belum Seimbang
            @endif
        </p>
    </div>
</div>
@endsection
