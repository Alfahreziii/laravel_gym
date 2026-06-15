<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Keuangan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1a1a1a;
        }

        .header {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            color: #1d4ed8;
        }

        .header p {
            font-size: 9px;
            color: #6b7280;
            margin-top: 3px;
        }

        .filter-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 8px;
            color: #0369a1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background-color: #1d4ed8;
            color: #fff;
        }

        thead th {
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            font-weight: 600;
        }

        thead th.text-right {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 4px 6px;
            font-size: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody td.text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 8px;
            font-size: 7.5px;
            font-weight: 600;
        }

        .badge-aset {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-kewajiban {
            background: #fef9c3;
            color: #a16207;
        }

        .badge-modal {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-beban {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-default {
            background: #f3f4f6;
            color: #374151;
        }

        .debit {
            color: #15803d;
            font-weight: bold;
        }

        .kredit {
            color: #b91c1c;
            font-weight: bold;
        }

        .zero {
            color: #d1d5db;
        }

        .footer-row td {
            background-color: #1e3a8a;
            color: #fff;
            font-weight: bold;
            font-size: 9px;
            padding: 6px 6px;
        }

        .footer-row td.text-right {
            text-align: right;
        }

        .summary {
            margin-top: 12px;
            display: flex;
            gap: 10px;
        }

        .summary-box {
            flex: 1;
            border-radius: 4px;
            padding: 8px 12px;
        }

        .summary-box.debit-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
        }

        .summary-box.kredit-box {
            background: #fff1f2;
            border: 1px solid #fca5a5;
        }

        .summary-box.selisih-box {
            background: #eff6ff;
            border: 1px solid #93c5fd;
        }

        .summary-box p {
            font-size: 8px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .summary-box h4 {
            font-size: 11px;
            font-weight: bold;
        }

        .summary-box.debit-box h4 {
            color: #15803d;
        }

        .summary-box.kredit-box h4 {
            color: #b91c1c;
        }

        .summary-box.selisih-box h4 {
            color: #1d4ed8;
        }

        .print-info {
            text-align: right;
            font-size: 7.5px;
            color: #9ca3af;
            margin-top: 10px;
        }

        .kode-akun {
            font-family: monospace;
            background: #f3f4f6;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>LAPORAN TRANSAKSI KEUANGAN</h2>
        <p>Detail jurnal keuangan sistem — dicetak {{ now()->format('d M Y, H:i') }} WIB</p>
    </div>

    @if (count($filterLabel) > 0)
        <div class="filter-info">
            <strong>Filter aktif:</strong> {{ implode(' | ', $filterLabel) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:8%">Tanggal</th>
                <th style="width:8%">Kode Akun</th>
                <th style="width:14%">Nama Akun</th>
                <th style="width:9%">Kategori</th>
                <th style="width:30%">Deskripsi</th>
                <th style="width:10%">Sumber</th>
                <th style="width:9%" class="text-right">Debit (Rp)</th>
                <th style="width:9%" class="text-right">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $index => $t)
                @php
                    $kategoriNama = $t->akun?->kategori?->nama ?? '';
                    $badgeClass = match ($kategoriNama) {
                        'Aset' => 'badge-aset',
                        'Kewajiban' => 'badge-kewajiban',
                        'Modal' => 'badge-modal',
                        'Beban' => 'badge-beban',
                        default => 'badge-default',
                    };
                    $referensiMap = [
                        'anggota_memberships' => 'Membership',
                        'products' => 'Produk',
                        'transaksi_spas' => 'Spa',
                        'personal_trainers' => 'PT',
                    ];
                    $sumber =
                        ($referensiMap[$t->referensi_tabel] ?? $t->referensi_tabel) .
                        ($t->referensi_id ? ' #' . $t->referensi_id : '');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d M Y') : '-' }}</td>
                    <td><span class="kode-akun">{{ $t->akun?->kode ?? '-' }}</span></td>
                    <td>{{ $t->akun?->nama ?? '-' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $kategoriNama ?: '-' }}</span></td>
                    <td>{{ $t->deskripsi ?? '-' }}</td>
                    <td>{{ $sumber ?: '-' }}</td>
                    <td class="text-right">
                        @if ($t->debit > 0)
                            <span class="debit">{{ number_format($t->debit, 0, ',', '.') }}</span>
                        @else
                            <span class="zero">-</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if ($t->kredit > 0)
                            <span class="kredit">{{ number_format($t->kredit, 0, ',', '.') }}</span>
                        @else
                            <span class="zero">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding: 20px; color: #9ca3af;">
                        Tidak ada data transaksi
                    </td>
                </tr>
            @endforelse

            {{-- Footer total --}}
            @if ($transaksis->count() > 0)
                <tr class="footer-row">
                    <td colspan="7">TOTAL ({{ $transaksis->count() }} transaksi)</td>
                    <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($transaksis->count() > 0)
        @php $selisih = $totalDebit - $totalKredit; @endphp
        <div class="summary">
            <div class="summary-box debit-box">
                <p>Total Debit</p>
                <h4>Rp {{ number_format($totalDebit, 0, ',', '.') }}</h4>
            </div>
            <div class="summary-box kredit-box">
                <p>Total Kredit</p>
                <h4>Rp {{ number_format($totalKredit, 0, ',', '.') }}</h4>
            </div>
            <div class="summary-box selisih-box">
                <p>Selisih (Debit - Kredit)</p>
                <h4>Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                    {{ $selisih == 0 ? '✓ Balanced' : ($selisih > 0 ? '↑ Surplus' : '↓ Deficit') }}
                </h4>
            </div>
        </div>
    @endif

    <div class="print-info">
        Dicetak otomatis oleh sistem &mdash; {{ now()->format('d M Y H:i:s') }}
    </div>

</body>

</html>
