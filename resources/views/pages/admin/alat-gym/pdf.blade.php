<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Alat Gym</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 15px 15px;       /* padding kanan-kiri ditambah */
            margin: 0 auto;   
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
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
            font-size: 10px;
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
        
        .kondisi-summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        
        .kondisi-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .kondisi-item.baik {
            background: #d4edda;
        }
        
        .kondisi-item.rusak {
            background: #f8d7da;
        }
        
        .kondisi-item.perbaikan {
            background: #fff3cd;
        }
        
        .kondisi-item .label {
            font-size: 9px;
            margin-bottom: 3px;
        }
        
        .kondisi-item.baik .label {
            color: #155724;
        }
        
        .kondisi-item.rusak .label {
            color: #721c24;
        }
        
        .kondisi-item.perbaikan .label {
            color: #856404;
        }
        
        .kondisi-item .value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .kondisi-item.baik .value {
            color: #155724;
        }
        
        .kondisi-item.rusak .value {
            color: #721c24;
        }
        
        .kondisi-item.perbaikan .value {
            color: #856404;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        
        table thead {
            background: #333;
            color: white;
        }
        
        table th {
            padding: 10px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        
        table td {
            padding: 8px 6px;
            border: 1px solid #ddd;
            font-size: 9px;
            vertical-align: middle;
        }
        
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .kondisi-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
        }
        
        .kondisi-baik {
            background: #d4edda;
            color: #155724;
        }
        
        .kondisi-rusak {
            background: #f8d7da;
            color: #721c24;
        }
        
        .kondisi-perbaikan {
            background: #fff3cd;
            color: #856404;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        .total-row {
            background: #e9ecef !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Laporan Inventaris Alat Gym</h1>
        <h2>Gym Management System</h2>
        <div class="date">Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }} WIB</div>
    </div>
    
    <!-- Summary Utama -->
    <div class="summary">
        <div class="summary-row">
            <div class="summary-item">
                <div class="label">Total Jenis Alat</div>
                <div class="value">{{ $totalAlat }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Unit</div>
                <div class="value">{{ number_format($totalJumlah) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Nilai Aset</div>
                <div class="value" style="font-size: 14px;">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Summary Kondisi -->
    <div class="kondisi-summary">
        <div class="summary-row">
            <div class="kondisi-item baik">
                <div class="label">KONDISI BAIK</div>
                <div class="value">{{ $kondisiBaik }} Alat</div>
            </div>
            <div class="kondisi-item perbaikan">
                <div class="label">PERLU PERBAIKAN</div>
                <div class="value">{{ $kondisiPerluPerbaikan }} Alat</div>
            </div>
            <div class="kondisi-item rusak">
                <div class="label">RUSAK</div>
                <div class="value">{{ $kondisiRusak }} Alat</div>
            </div>
        </div>
    </div>
    
    <!-- Data Table -->
    @if($alatGyms->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Barcode</th>
                <th width="18%">Nama Alat</th>
                <th width="6%">Qty</th>
                <th width="12%">Harga/Unit</th>
                <th width="12%">Total Nilai</th>
                <th width="10%">Tgl Beli</th>
                <th width="10%">Lokasi</th>
                <th width="10%">Kondisi</th>
                <th width="8%">Vendor</th>
            </tr>
        </thead>
        <tbody>
            @php $totalNilaiItem = 0; @endphp
            @foreach($alatGyms as $index => $item)
                @php 
                    $nilaiTotal = $item->harga * $item->jumlah;
                    $totalNilaiItem += $nilaiTotal;
                @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->nama_alat_gym }}</td>
                <td class="text-center">{{ $item->jumlah }}</td>
                <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($nilaiTotal, 0, ',', '.') }}</td>
                <td class="text-center">
                    {{ $item->tgl_pembelian ? \Carbon\Carbon::parse($item->tgl_pembelian)->format('d/m/Y') : '-' }}
                </td>
                <td>{{ $item->lokasi_alat ?? '-' }}</td>
                <td class="text-center">
                    @if($item->kondisi_alat === 'Baik')
                        <span class="kondisi-badge kondisi-baik">Baik</span>
                    @elseif($item->kondisi_alat === 'Rusak')
                        <span class="kondisi-badge kondisi-rusak">Rusak</span>
                    @elseif($item->kondisi_alat === 'Perlu Perbaikan')
                        <span class="kondisi-badge kondisi-perbaikan">Perlu Perbaikan</span>
                    @else
                        {{ $item->kondisi_alat ?? '-' }}
                    @endif
                </td>
                <td>{{ $item->vendor ?? '-' }}</td>
            </tr>
            @endforeach
            
            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-center">{{ $totalJumlah }}</td>
                <td colspan="1"></td>
                <td class="text-right">Rp {{ number_format($totalNilaiItem, 0, ',', '.') }}</td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>

    <!-- Detail Informasi (dengan Keterangan jika ada) -->
    @php
        $alatWithKeterangan = $alatGyms->filter(fn($item) => !empty($item->keterangan));
    @endphp
    
    @if($alatWithKeterangan->count() > 0)
    <div style="margin-top: 30px; page-break-before: always;">
        <h3 style="font-size: 14px; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px;">
            Detail Keterangan Alat
        </h3>
        
        @foreach($alatWithKeterangan as $index => $item)
            <div style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-left: 4px solid #333;">
                <div style="font-weight: bold; margin-bottom: 5px; font-size: 11px;">
                    {{ $item->nama_alat_gym }} ({{ $item->barcode }})
                </div>
                <div style="display: table; width: 100%; font-size: 9px; margin-bottom: 5px;">
                    <div style="display: table-row;">
                        <div style="display: table-cell; width: 15%; color: #666;">Jumlah</div>
                        <div style="display: table-cell; width: 35%;">: {{ $item->jumlah }} unit</div>
                        <div style="display: table-cell; width: 15%; color: #666;">Kondisi</div>
                        <div style="display: table-cell; width: 35%;">: {{ $item->kondisi_alat }}</div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; color: #666;">Lokasi</div>
                        <div style="display: table-cell;">: {{ $item->lokasi_alat ?? '-' }}</div>
                        <div style="display: table-cell; color: #666;">Vendor</div>
                        <div style="display: table-cell;">: {{ $item->vendor ?? '-' }}</div>
                    </div>
                </div>
                <div style="font-size: 9px; color: #555; margin-top: 5px;">
                    <strong>Keterangan:</strong> {{ $item->keterangan }}
                </div>
            </div>
        @endforeach
    </div>
    @endif
    
    @else
    <div class="no-data">
        Tidak ada data alat gym yang tersedia
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>Gym Management System - Inventaris Alat Gym</strong></p>
        <p>Dokumen ini digenerate secara otomatis oleh sistem</p>
    </div>
</body>
</html>