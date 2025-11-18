<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 15px 15px;       /* padding kanan-kiri ditambah */
            margin: 0 auto;   
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2c3e50;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: bold;
        }
        
        .header p {
            font-size: 11px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .filter-info {
            background-color: #3498db;
            color: white;
            padding: 10px 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .filter-info strong {
            color: #fff;
        }
        
        .summary-section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            color: #2c3e50;
            padding: 8px 10px;
            background-color: #ecf0f1;
            border-left: 4px solid #3498db;
        }
        
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 10px 12px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: middle;
            font-size: 10px;
        }
        
        .summary-row:last-child .summary-cell {
            border-bottom: none;
        }
        
        .summary-cell:first-child {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 45%;
            color: #2c3e50;
        }
        
        .summary-cell:last-child {
            text-align: right;
            color: #27ae60;
            font-weight: bold;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 7px;
        }
        
        table.data-table thead {
            background-color: #34495e;
            color: white;
        }
        
        table.data-table th {
            padding: 10px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
            font-size: 8px;
        }
        
        table.data-table td {
            padding: 8px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        table.data-table tbody tr:hover {
            background-color: #e8f4f8;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #bdc3c7;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
            background-color: #ecf0f1;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .highlight-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Dicetak pada: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm:ss') }} WIB</p>
    </div>

    <!-- Filter Info -->
    <div class="filter-info">
        <strong>Periode Transaksi:</strong> {{ $filterInfo }}
    </div>

    <!-- Statistik Keseluruhan -->
    <div class="summary-section">
        <div class="section-title">📊 Statistik Keseluruhan (Semua Data)</div>
        <div class="summary">
            <div class="summary-row">
                <div class="summary-cell">Total Transaksi</div>
                <div class="summary-cell">{{ number_format($totalTransaksi, 0, ',', '.') }} transaksi</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Pendapatan Bersih</div>
                <div class="summary-cell">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Harga Sebelum Diskon</div>
                <div class="summary-cell">Rp {{ number_format($totalSebelumDiskon, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Diskon Barang</div>
                <div class="summary-cell">Rp {{ number_format($totalDiskonBarang, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Diskon Manual</div>
                <div class="summary-cell">Rp {{ number_format($totalDiskonManual, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Seluruh Diskon</div>
                <div class="summary-cell">Rp {{ number_format($totalDiskonBarang + $totalDiskonManual, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total HPP (Harga Pokok Penjualan)</div>
                <div class="summary-cell">Rp {{ number_format($totalHPP, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Laba Kotor</div>
                <div class="summary-cell">Rp {{ number_format($totalPendapatan - $totalHPP, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @if($filterType !== 'all')
    <!-- Statistik Periode yang Dipilih -->
    <div class="summary-section">
        <div class="section-title">📅 Statistik Periode yang Dipilih</div>
        <div class="summary">
            <div class="summary-row">
                <div class="summary-cell">Total Transaksi</div>
                <div class="summary-cell">{{ number_format($filteredTotalTransaksi, 0, ',', '.') }} transaksi</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Pendapatan Bersih</div>
                <div class="summary-cell">Rp {{ number_format($filteredTotalPendapatan, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Harga Sebelum Diskon</div>
                <div class="summary-cell">Rp {{ number_format($filteredTotalSebelumDiskon, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Diskon Barang</div>
                <div class="summary-cell">Rp {{ number_format($filteredTotalDiskonBarang, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Diskon Manual</div>
                <div class="summary-cell">Rp {{ number_format($filteredTotalDiskonManual, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total Seluruh Diskon</div>
                <div class="summary-cell">Rp {{ number_format($filteredTotalDiskonBarang + $filteredTotalDiskonManual, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Total HPP (Harga Pokok Penjualan)</div>
                <div class="summary-cell">Rp {{ number_format($filteredTotalHPP, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">Laba Kotor</div>
                <div class="summary-cell">Rp {{ number_format($filteredTotalPendapatan - $filteredTotalHPP, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Data Transaksi -->
    <div class="section-title">📋 Daftar Transaksi Penjualan</div>
    
    @if($transactions->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="10%">Kode Transaksi</th>
                <th width="9%">Tanggal</th>
                <th width="8%" class="text-right">Total</th>
                <th width="8%" class="text-right">Dibayar</th>
                <th width="8%" class="text-right">Kembali</th>
                <th width="7%">Metode</th>
                <th width="9%" class="text-right">Sbl Diskon</th>
                <th width="8%" class="text-right">Disk Brg</th>
                <th width="8%" class="text-right">Disk Manual</th>
                <th width="8%" class="text-right">Total Disk</th>
                <th width="8%" class="text-right">Total HPP</th>
                <th width="8%" class="text-right">Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $item)
            @php
                $labaKotor = $item->total_amount - $item->total_hpp_transaction;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $item->transaction_code }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($item->total_amount, 0, ',', '.') }}</strong></td>
                <td class="text-right">Rp {{ number_format($item->dibayarkan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->kembalian, 0, ',', '.') }}</td>
                <td>
                    @if(strtolower($item->metode_pembayaran) == 'cash' || strtolower($item->metode_pembayaran) == 'tunai')
                        <span class="badge badge-success">{{ $item->metode_pembayaran }}</span>
                    @else
                        <span class="badge badge-info">{{ $item->metode_pembayaran ?? '-' }}</span>
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($item->harga_sebelum_diskon, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->diskon_barang, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->diskon, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->diskon_barang + $item->diskon, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total_hpp_transaction, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($labaKotor, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Total -->
    <div class="highlight-box">
        <table style="width: 100%; font-size: 10px;">
            <tr>
                <td style="text-align: right; padding: 3px;"><strong>Grand Total Transaksi:</strong></td>
                <td style="text-align: right; width: 150px; padding: 3px;"><strong>Rp {{ number_format($transactions->sum('total_amount'), 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: right; padding: 3px;"><strong>Total Dibayarkan:</strong></td>
                <td style="text-align: right; padding: 3px;"><strong>Rp {{ number_format($transactions->sum('dibayarkan'), 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: right; padding: 3px;"><strong>Total Kembalian:</strong></td>
                <td style="text-align: right; padding: 3px;"><strong>Rp {{ number_format($transactions->sum('kembalian'), 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: right; padding: 3px;"><strong>Total HPP:</strong></td>
                <td style="text-align: right; padding: 3px;"><strong>Rp {{ number_format($transactions->sum('total_hpp_transaction'), 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: right; padding: 3px;"><strong>Total Laba Kotor:</strong></td>
                <td style="text-align: right; padding: 3px;"><strong>Rp {{ number_format($transactions->sum('total_amount') - $transactions->sum('total_hpp_transaction'), 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    @else
    <div class="no-data">
        <p><strong>Tidak ada data transaksi untuk periode yang dipilih.</strong></p>
        <p>Silakan pilih periode lain atau lihat semua data transaksi.</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Laporan Riwayat Penjualan</strong></p>
        <p>Dokumen ini digenerate secara otomatis oleh sistem</p>
        <p>© {{ now()->format('Y') }} - Sistem Informasi Kasir</p>
    </div>
</body>
</html>