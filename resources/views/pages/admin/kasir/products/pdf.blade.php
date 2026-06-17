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
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.6;
            color: #333;
            padding: 15px 15px;       /* padding kanan-kiri ditambah */
            margin: 0 auto;   
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
        
        .summary-item.aktif .value {
            color: #28a745;
        }
        
        .summary-item.nonaktif .value {
            color: #dc3545;
        }
        
        .summary-item.stok .value {
            color: #007bff;
        }
        
        .summary-item.nilai .value {
            color: #17a2b8;
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
        
        .status-aktif {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-nonaktif {
            background-color: #f8d7da;
            color: #721c24;
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
                <div class="label">Total Produk</div>
                <div class="value">{{ $totalProduk }}</div>
            </div>
            <div class="summary-item aktif">
                <div class="label">Produk Aktif</div>
                <div class="value">{{ $produkAktif }}</div>
            </div>
            <div class="summary-item nonaktif">
                <div class="label">Produk Nonaktif</div>
                <div class="value">{{ $produkNonaktif }}</div>
            </div>
        </div>
        <div class="summary-row">
            <div class="summary-item stok">
                <div class="label">Total Stok</div>
                <div class="value">{{ number_format($totalStok, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item nilai">
                <div class="label">Nilai Stok (HPP)</div>
                <div class="value">Rp {{ number_format($totalNilaiStok, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item nilai">
                <div class="label">Nilai Jual</div>
                <div class="value">Rp {{ number_format($totalNilaiJual, 0, ',', '.') }}</div>
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
            <div class="info-value">{{ $products->count() }} Produk</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Kategori:</div>
            <div class="info-value">{{ $products->pluck('kategori_product_id')->unique()->count() }} Kategori</div>
        </div>
    </div>
    
    <!-- Data Table -->
    @if($products->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="13%">Nama Produk</th>
                <th width="10%">Kategori</th>
                <th width="9%" class="text-right">HPP</th>
                <th width="9%" class="text-right">Harga Jual</th>
                <th width="8%" class="text-center">Diskon</th>
                <th width="9%" class="text-right">Harga Setelah Diskon</th>
                <th width="6%" class="text-center">Stok</th>
                <th width="6%" class="text-center">Reorder</th>
                <th width="7%" class="text-center">Status</th>
                <th width="11%" class="text-right">Nilai Stok (HPP)</th>
                <th width="11%" class="text-right">Nilai Jual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
            @php
                // Hitung harga setelah diskon
                $hargaSetelahDiskon = $product->price;
                if ($product->discount > 0) {
                    if ($product->discount_type == 'percent') {
                        $hargaSetelahDiskon = $product->price - ($product->price * $product->discount / 100);
                    } else {
                        $hargaSetelahDiskon = $product->price - $product->discount;
                    }
                }
                
                // Nilai jual = (harga setelah diskon) × quantity
                $nilaiJual = $hargaSetelahDiskon * $product->quantity;
                
                // Nilai stok HPP = HPP × quantity
                $nilaiStokHpp = $product->hpp * $product->quantity;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->kategori->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($product->hpp, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($product->discount > 0)
                        {{ $product->discount_type == 'percent' ? $product->discount . '%' : 'Rp ' . number_format($product->discount, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($hargaSetelahDiskon, 0, ',', '.') }}</td>
                <td class="text-center">{{ $product->quantity }}</td>
                <td class="text-center">{{ $product->reorder }}</td>
                <td class="text-center">
                    @if($product->is_active)
                        <span class="status-badge status-aktif">Aktif</span>
                    @else
                        <span class="status-badge status-nonaktif">Nonaktif</span>
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($nilaiStokHpp, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($nilaiJual, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="10" class="text-right" style="padding: 10px;">TOTAL:</td>
                <td class="text-right" style="padding: 10px;">
                    Rp {{ number_format($totalNilaiStok, 0, ',', '.') }}
                </td>
                <td class="text-right" style="padding: 10px;">
                    Rp {{ number_format($totalNilaiJual, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        <p>Tidak ada data produk yang tersedia.</p>
    </div>
    @endif
    
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh sistem | © {{ date('Y') }} Gym Management System</p>
    </div>
</body>
</html>