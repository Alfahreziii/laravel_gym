@extends('layout.layout')

@php
    $title = 'Neraca Keuangan';
    $subTitle = 'Laporan Keuangan Gym';
@endphp

@section('content')
@if(session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif
@if(session('danger'))
    <x-alert type="danger">{{ session('danger') }}</x-alert>
@endif

{{-- ==================== BALANCE SHEET CARDS ==================== --}}
<div class="grid md:grid-cols-2 gap-5">

    {{-- Bagian Aset --}}
    <div class="card overflow-hidden border border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="w-1.5 h-5 rounded-full bg-success-500 flex-none"></div>
            <h3 class="font-display font-semibold text-lg text-ink dark:text-ink-d">Aset</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-neutral-50 dark:bg-neutral-700/30 border-b border-neutral-200 dark:border-neutral-700">
                    <th class="text-left px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Nama Akun</th>
                    <th class="text-right px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach ($kategori->where('kode', 'AST')->first()?->akun ?? [] as $akun)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/20">
                        <td class="px-5 py-3 text-ink dark:text-ink-d">
                            <div class="flex items-center gap-2">
                                {{ $akun->nama }}
                                @if ($akun->kode === 'AST001')
                                    <button type="button" onclick="HexaModal.show('modal-tambah-kas')"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-success-50 text-success-700 hover:bg-success-100 border border-success-200 dark:bg-success-600/10 dark:border-success-600/30 dark:text-success-400 transition-colors"
                                        title="Tambah Kas">
                                        <iconify-icon icon="lucide:plus-circle" class="text-xs"></iconify-icon>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right tabular-nums text-ink dark:text-ink-d">{{ number_format($akun->saldo, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-neutral-300 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700/30">
                    <td class="px-5 py-3 font-bold text-ink dark:text-ink-d">Total Aset</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums text-success-600 dark:text-success-400">{{ number_format($total_aset, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{--
    Bagian Kewajiban — di-hide dulu sementara (belum ada akun kewajiban yang
    aktif dipakai). Untuk dimunculkan lagi, hapus wrapper comment ini.

    <div class="card overflow-hidden border border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="w-1.5 h-5 rounded-full bg-primary-500 flex-none"></div>
            <h3 class="font-display font-semibold text-lg text-ink dark:text-ink-d">Kewajiban</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-neutral-50 dark:bg-neutral-700/30 border-b border-neutral-200 dark:border-neutral-700">
                    <th class="text-left px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Nama Akun</th>
                    <th class="text-right px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($kategori->where('kode', 'KEW')->first()?->akun ?? [] as $akun)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/20">
                        <td class="px-5 py-3 text-ink dark:text-ink-d">{{ $akun->nama }}</td>
                        <td class="px-5 py-3 text-right tabular-nums text-ink dark:text-ink-d">{{ number_format($akun->saldo, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-5 py-3 text-center text-sm text-ink-2 dark:text-ink-d2 italic">Belum ada kewajiban</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-neutral-300 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700/30">
                    <td class="px-5 py-3 font-bold text-ink dark:text-ink-d">Total Kewajiban</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums text-primary-600 dark:text-primary-400">{{ number_format($total_kewajiban, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    --}}

    {{-- Bagian Modal --}}
    <div class="card overflow-hidden border border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="w-1.5 h-5 rounded-full bg-violet-500 flex-none"></div>
            <h3 class="font-display font-semibold text-lg text-ink dark:text-ink-d">Modal</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-neutral-50 dark:bg-neutral-700/30 border-b border-neutral-200 dark:border-neutral-700">
                    <th class="text-left px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Nama Akun</th>
                    <th class="text-right px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach ($kategori->where('kode', 'MOD')->first()?->akun ?? [] as $akun)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/20">
                        <td class="px-5 py-3 text-ink dark:text-ink-d">{{ $akun->nama }}</td>
                        <td class="px-5 py-3 text-right tabular-nums text-ink dark:text-ink-d">{{ number_format($akun->saldo, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-700/30">
                    <td class="px-5 py-3 font-bold text-ink dark:text-ink-d">Total Modal</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums text-violet-600 dark:text-violet-400">{{ number_format($total_modal, 2, ',', '.') }}</td>
                </tr>
                <tr class="border-t-2 border-neutral-300 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700/30">
                    <td class="px-5 py-3 font-bold text-ink dark:text-ink-d">Total Kewajiban + Modal</td>
                    <td class="px-5 py-3 text-right font-bold tabular-nums text-primary-600 dark:text-primary-400">{{ number_format($total_kewajiban_modal, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Rincian Pemasukan — dari akun MOD yang namanya mengandung "Pendapatan" --}}
    @php
        $modKat = $kategori->where('kode', 'MOD')->first();
        $pendapatanAkun = collect($modKat?->akun ?? [])
            ->filter(fn($a) => str_contains($a->nama, 'Pendapatan'))
            ->values();
        $totalPendapatan = $pendapatanAkun->sum('saldo');
        $barColors = ['bg-primary-500', 'bg-info-500', 'bg-violet-500', 'bg-warning-500'];
    @endphp
    <div class="card overflow-hidden border border-neutral-200 dark:border-neutral-700">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="font-display font-semibold text-lg text-ink dark:text-ink-d">Rincian Pemasukan</h3>
            <p class="text-xs text-ink-2 dark:text-ink-d2 mt-0.5">Breakdown sumber pendapatan</p>
        </div>
        <div class="px-5 py-5 flex flex-col gap-5">
            @forelse ($pendapatanAkun as $akun)
                @php
                    $pct = $totalPendapatan > 0 ? round($akun->saldo / $totalPendapatan * 100) : 0;
                    $barColor = $barColors[$loop->index % count($barColors)];
                @endphp
                <div>
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <span class="flex items-center gap-2 text-sm font-semibold text-ink dark:text-ink-d leading-snug">
                            <i class="w-2.5 h-2.5 rounded-[3px] {{ $barColor }} flex-none mt-0.5"></i>
                            {{ $akun->nama }}
                        </span>
                        <span class="text-sm font-semibold tabular-nums text-ink dark:text-ink-d flex-none">
                            {{ number_format($akun->saldo, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="h-[7px] rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                        <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="text-xs text-ink-3 dark:text-ink-d3 mt-1">{{ $pct }}% dari total pendapatan</div>
                </div>
            @empty
                <p class="text-sm text-ink-3 dark:text-ink-d3 text-center py-6">Belum ada data pendapatan.</p>
            @endforelse

            @if ($pendapatanAkun->isNotEmpty())
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4 mt-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-semibold text-ink-2 dark:text-ink-d2">Total Pendapatan</span>
                        <span class="font-display font-bold text-lg text-success-600 dark:text-success-400 tabular-nums">
                            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ==================== BUKU BESAR (full-width) ==================== --}}
<div class="mt-5">
    <div class="card border-0 overflow-hidden">
        <div class="flex items-center px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700">
            <div>
                <span class="font-semibold text-base text-ink dark:text-ink-d">Buku Besar</span>
                <p class="text-xs text-ink-2 dark:text-ink-d2 mt-0.5">Saldo per akun berdasarkan kategori</p>
            </div>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-neutral-50 dark:bg-neutral-700/30 border-b border-neutral-200 dark:border-neutral-700">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Kode</th>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Nama Akun</th>
                        <th class="text-right px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Debit (Rp)</th>
                        <th class="text-right px-5 py-2.5 text-xs font-semibold text-ink-2 dark:text-ink-d2 uppercase tracking-wider">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @php
                        $totalDebit = 0;
                        $totalKredit = 0;

                        // helper fungsi: tentukan sisi normal
                        $isDebitSide = function ($kodeKategori) {
                            return in_array($kodeKategori, ['AST', 'BEB']); // Aset & Beban di Debit
                        };
                    @endphp

                    @foreach ($kategori as $kat)
                        @foreach ($kat->akun ?? [] as $akun)
                            @php
                                $saldo = (float) ($akun->saldo ?? 0);
                                $kodeKat = $kat->kode ?? '';

                                // letakkan saldo ke kolom sesuai sisi normal
                                $debit = $isDebitSide($kodeKat) ? $saldo : 0;
                                $kredit = $isDebitSide($kodeKat) ? 0 : $saldo;

                                $totalDebit += $debit;
                                $totalKredit += $kredit;
                            @endphp
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/20">
                                <td class="px-5 py-3 font-medium text-ink-2 dark:text-ink-d2 tabular-nums">{{ $akun->kode ?? '-' }}</td>
                                <td class="px-5 py-3 text-ink dark:text-ink-d">{{ $akun->nama }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-ink dark:text-ink-d">
                                    {{ $debit ? number_format($debit, 2, ',', '.') : '—' }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-ink dark:text-ink-d">
                                    {{ $kredit ? number_format($kredit, 2, ',', '.') : '—' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-neutral-300 dark:border-neutral-600 bg-neutral-50 dark:bg-neutral-700/30">
                        <td class="px-5 py-3 font-bold text-ink dark:text-ink-d" colspan="2">Total</td>
                        <td class="px-5 py-3 text-right font-bold tabular-nums text-success-600 dark:text-success-400">
                            {{ number_format($totalDebit, 2, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-bold tabular-nums text-danger-600 dark:text-danger-400">
                            {{ number_format($totalKredit, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-neutral-50 dark:bg-neutral-700/30">
                        <td class="px-5 py-3" colspan="4">
                            @if (number_format($totalDebit, 2) === number_format($totalKredit, 2))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-success-50 text-success-700 border border-success-200 dark:bg-success-600/10 dark:border-success-600/30 dark:text-success-400">
                                    <iconify-icon icon="lucide:check-circle" class="text-sm"></iconify-icon>
                                    Seimbang (Debit = Kredit)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-warning-50 text-warning-700 border border-warning-200 dark:bg-warning-600/10 dark:border-warning-600/30 dark:text-warning-400">
                                    <iconify-icon icon="lucide:alert-triangle" class="text-sm"></iconify-icon>
                                    Belum seimbang (cek jurnal)
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

    <x-modal id="modal-tambah-kas" title="Tambah Kas Manual" maxWidth="max-w-[600px]">
        <x-slot:body>
            <form id="formTambahKas" action="{{ route('neraca.tambah-kas') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <div class="col-span-12">
                        <label for="jumlahKas" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                            Jumlah Kas (Rp)
                        </label>
                        <input type="number" name="jumlah" id="jumlahKas" step="0.01" min="0" required
                            class="form-control rounded-lg" placeholder="Masukkan jumlah kas">
                    </div>

                    <div class="col-span-12">
                        <label for="deskripsiKas" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                            Deskripsi
                        </label>
                        <textarea name="deskripsi" id="deskripsiKas" rows="3" required class="form-control rounded-lg"
                            placeholder="Contoh: Setoran modal awal pemilik"></textarea>
                    </div>

                    <div class="col-span-12">
                        <div class="flex items-center justify-start gap-3 mt-6">
                            <button type="reset" data-close-modal="modal-tambah-kas"
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
        </x-slot:body>
    </x-modal>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const removeButtons = document.querySelectorAll('.remove-button');

            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    if (alert) {
                        alert.remove();
                    }
                });
            });
        });
    </script>
@endsection
