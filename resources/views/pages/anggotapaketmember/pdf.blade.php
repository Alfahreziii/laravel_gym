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
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            padding: 15px 15px;       /* padding kanan-kiri ditambah */
            margin: 0 auto;           /* center di printer */
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header h2 {
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        
        .header .date {
            font-size: 9px;
            color: #999;
            margin-top: 5px;
        }
        
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-item {
            display: table-cell;
            width: 33.33%;
            padding: 12px;
            text-align: center;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .summary-item .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }
        
        .summary-item.lunas .value {
            color: #28a745;
        }
        
        .summary-item.belum-lunas .value {
            color: #ffc107;
        }
        
        .summary-item.pendapatan .value {
            color: #007bff;
        }
        
        .info-section {
            margin-bottom: 20px;
            padding: 12px;
            background: #f0f0f0;
            border-left: 4px solid #333;
        }
        
        .info-section .info-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .info-section .info-label {
            display: table-cell;
            width: 25%;
            font-weight: bold;
            font-size: 10px;
        }
        
        .info-section .info-value {
            display: table-cell;
            width: 75%;
            font-size: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        
        table thead {
            background-color: #2c3e50;
            color: white;
        }
        
        table th {
            padding: 10px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        
        table td {
            padding: 8px 6px;
            border: 1px solid #ddd;
            font-size: 9px;
            vertical-align: top;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
        }
        
        .status-lunas {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-belum-lunas {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            font-style: italic;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <h2>Gym Management System</h2>
        <div class="date">Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }} WIB</div>
    </div>
    
    <!-- Summary Statistics -->
    <div class="summary">
        <div class="summary-row">
            <div class="summary-item">
                <div class="label">Total Transaksi</div>
                <div class="value">{{ $totalMembership }}</div>
            </div>
            <div class="summary-item lunas">
                <div class="label">Lunas</div>
                <div class="value">{{ $totalLunas }}</div>
            </div>
            <div class="summary-item belum-lunas">
                <div class="label">Belum Lunas</div>
                <div class="value">{{ $totalBelumLunas }}</div>
            </div>
        </div>
        <div class="summary-row">
            <div class="summary-item pendapatan">
                <div class="label">Total Pendapatan</div>
                <div class="value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item lunas">
                <div class="label">Terbayar</div>
                <div class="value">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item belum-lunas">
                <div class="label">Piutang</div>
                <div class="value">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Tanggal Export:</div>
            <div class="info-value">{{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</div>
        </div>
        <div class="info-row">
            <div class="info-label">Data Ditampilkan:</div>
            <div class="info-value">{{ $anggotaMemberships->count() }} Transaksi</div>
        </div>
        <div class="info-row">
            <div class="info-label">Filter Status:</div>
            <div class="info-value">{{ $statusInfo }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Filter Periode:</div>
            <div class="info-value">{{ $filterInfo }}</div>
        </div>
    </div>
    
    <!-- Data Table -->
    @if($anggotaMemberships->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="11%">Kode Transaksi</th>
                <th width="15%">Nama Anggota</th>
                <th width="15%">Paket</th>
                <th width="9%">Tgl Mulai</th>
                <th width="9%">Tgl Selesai</th>
                <th width="10%" class="text-right">Total Biaya</th>
                <th width="10%" class="text-right">Terbayar</th>
                <th width="10%" class="text-right">Sisa</th>
                <th width="7%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($anggotaMemberships as $index => $item)
            @php
                $terbayar = $item->pembayaranMemberships->sum('jumlah_bayar');
                $sisa = $item->total_biaya - $terbayar;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_transaksi }}</td>
                <td>{{ $item->anggota->name ?? '-' }}</td>
                <td>{{ $item->paketMembership->nama_paket ?? '-' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tgl_mulai)->format('d/m/Y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tgl_selesai)->format('d/m/Y') }}</td>
                <td class="text-right">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($terbayar, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($item->status_pembayaran === 'Lunas')
                        <span class="status-badge status-lunas">Lunas</span>
                    @else
                        <span class="status-badge status-belum-lunas">Belum Lunas</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="6" class="text-right" style="padding: 10px;">TOTAL:</td>
                <td class="text-right" style="padding: 10px;">
                    Rp {{ number_format($anggotaMemberships->sum('total_biaya'), 0, ',', '.') }}
                </td>
                <td class="text-right" style="padding: 10px;">
                    Rp {{ number_format($anggotaMemberships->sum(function($item) { 
                        return $item->pembayaranMemberships->sum('jumlah_bayar'); 
                    }), 0, ',', '.') }}
                </td>
                <td class="text-right" style="padding: 10px;">
                    Rp {{ number_format($anggotaMemberships->sum(function($item) { 
                        return $item->total_biaya - $item->pembayaranMemberships->sum('jumlah_bayar'); 
                    }), 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        <p>Tidak ada data membership yang tersedia untuk filter yang dipilih.</p>
    </div>
    @endif
    
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh sistem | © {{ date('Y') }} Gym Management System</p>
    </div>
</body>
</html>