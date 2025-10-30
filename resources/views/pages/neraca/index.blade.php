@extends('layout.layout')

@php
    $title = 'Neraca Keuangan';
    $subTitle = 'Laporan Keuangan Gym';
@endphp

@section('content')

@if(session('success'))
    <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{ session('success') }}
        </div>
        <button class="remove-button text-success-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

@if(session('danger'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('danger') }}
        <button class="remove-button text-danger-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="grid md:grid-cols-3 gap-6">
    {{-- Bagian Aset --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xl font-semibold text-green-600">Aset</h3>
        </div>
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
                        <td class="py-2 flex items-center gap-2">
                            {{ $akun->nama }}
                            @if($akun->kode === 'AST001')
                                <button 
                                    type="button"
                                    data-modal-target="modal-tambah-kas" 
                                    data-modal-toggle="modal-tambah-kas"
                                    class="text-green-600 hover:text-green-700 focus:outline-none"
                                    title="Tambah Kas">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </td>
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

    {{-- Bagian Kewajiban --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-5">
        <h3 class="text-xl font-semibold mb-3 text-green-600">Kewajiban</h3>
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
    </div>

    {{-- Bagian Modal --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-5">
        <h3 class="text-xl font-semibold mb-3 text-green-600">Modal</h3>
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

<div class="grid grid-cols-12 mt-6 shadow">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30">
                            <th class="text-left py-2 px-3">Kode</th>
                            <th class="text-left py-2 px-3">Nama Akun</th>
                            <th class="text-right py-2 px-3">Debit (Rp)</th>
                            <th class="text-right py-2 px-3">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $totalDebit  = 0;
                            $totalKredit = 0;

                            // helper fungsi: tentukan sisi normal
                            $isDebitSide = function($kodeKategori) {
                                return in_array($kodeKategori, ['AST', 'BEB']); // Aset & Beban di Debit
                            };
                        @endphp

                        @foreach($kategori as $kat)
                            @foreach(($kat->akun ?? []) as $akun)
                                @php
                                    $saldo = (float) ($akun->saldo ?? 0);
                                    $kodeKat = $kat->kode ?? '';

                                    // letakkan saldo ke kolom sesuai sisi normal
                                    $debit  = $isDebitSide($kodeKat) ? $saldo : 0;
                                    $kredit = $isDebitSide($kodeKat) ? 0 : $saldo;

                                    $totalDebit  += $debit;
                                    $totalKredit += $kredit;
                                @endphp
                                <tr>
                                    <td class="py-2 px-3 font-medium text-gray-600">{{ $akun->kode ?? '-' }}</td>
                                    <td class="py-2 px-3">{{ $akun->nama }}</td>
                                    <td class="py-2 px-3 text-right">{{ $debit ? number_format($debit, 2, ',', '.') : '-' }}</td>
                                    <td class="py-2 px-3 text-right">{{ $kredit ? number_format($kredit, 2, ',', '.') : '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-400">
                            <td class="py-2 px-3 font-semibold" colspan="2">Total</td>
                            <td class="py-2 px-3 text-right font-semibold text-green-600">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right font-semibold text-blue-600">{{ number_format($totalKredit, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-3 font-medium" colspan="4">
                                @if(number_format($totalDebit, 2) === number_format($totalKredit, 2))
                                    <span class="text-emerald-600">✅ Seimbang (Debit = Kredit)</span>
                                @else
                                    <span class="text-amber-600">⚠️ Belum seimbang (cek jurnal)</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Kas --}}
<div id="modal-tambah-kas" tabindex="-1"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[600px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Tambah Kas Manual</h1>
            <button data-modal-hide="modal-tambah-kas" type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <div class="p-6">
            <form id="formTambahKas" action="{{ route('neraca.tambah-kas') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <div class="col-span-12">
                        <label for="jumlahKas" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                            Jumlah Kas (Rp)
                        </label>
                        <input 
                            type="number" 
                            name="jumlah" 
                            id="jumlahKas"
                            step="0.01"
                            min="0"
                            required
                            class="form-control rounded-lg"
                            placeholder="Masukkan jumlah kas">
                    </div>

                    <div class="col-span-12">
                        <label for="deskripsiKas" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                            Deskripsi
                        </label>
                        <textarea 
                            name="deskripsi" 
                            id="deskripsiKas"
                            rows="3"
                            required
                            class="form-control rounded-lg"
                            placeholder="Contoh: Setoran modal awal pemilik"></textarea>
                    </div>

                    <div class="col-span-12">
                        <div class="flex items-center justify-start gap-3 mt-6">
                            <button type="reset" data-modal-hide="modal-tambah-kas"
                                class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                Cancel
                            </button>
                            <button type="submit"
                                class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-button');

    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if(alert) {
                alert.remove();
            }
        });
    });
});
</script>
@endsection