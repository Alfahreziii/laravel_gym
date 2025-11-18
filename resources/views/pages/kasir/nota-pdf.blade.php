<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota - {{ $transaction->transaction_code }}</title>
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
            width: 75mm;              /* supaya tidak kepotong */
            padding: 15px 15px;       /* padding kanan-kiri ditambah */
            margin: 0 auto;           /* center di printer */
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #000;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .header p {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .info-section {
            margin-bottom: 12px;
            font-size: 10px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 35%;
        }
        
        .info-value {
            display: table-cell;
            width: 5%;
            text-align: center;
        }
        
        .info-content {
            display: table-cell;
            width: 60%;
        }
        
        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 10px;
        }
        
        .item-row {
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ccc;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-detail {
            display: table;
            width: 100%;
            font-size: 9px;
            color: #333;
            margin-top: 2px;
        }
        
        .item-detail-left {
            display: table-cell;
            width: 60%;
        }
        
        .item-detail-right {
            display: table-cell;
            width: 40%;
            text-align: right;
        }
        
        .item-price-info {
            display: table;
            width: 100%;
            margin-top: 3px;
        }
        
        .item-price-left {
            display: table-cell;
            width: 60%;
            font-size: 9px;
            color: #666;
        }
        
        .item-price-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            font-weight: bold;
        }
        
        .summary {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px dashed #000;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
            font-size: 10px;
        }
        
        .summary-label {
            display: table-cell;
            width: 60%;
        }
        
        .summary-value {
            display: table-cell;
            width: 40%;
            text-align: right;
        }
        
        .summary-row.total {
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #000;
        }
        
        .payment-info {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #000;
            text-align: center;
            font-size: 9px;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        .thank-you {
            font-weight: bold;
            margin-top: 8px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>GYM MANAGEMENT</h1>
        <p>Jl. Contoh Alamat No. 123</p>
        <p>Telp: 0812-3456-7890</p>
    </div>

    <!-- Info Transaksi -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">No. Nota</div>
            <div class="info-value">:</div>
            <div class="info-content">{{ $transaction->transaction_code }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal</div>
            <div class="info-value">:</div>
            <div class="info-content">{{ $tanggal }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kasir</div>
            <div class="info-value">:</div>
            <div class="info-content">{{ $kasir }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Pembayaran</div>
            <div class="info-value">:</div>
            <div class="info-content" style="text-transform: uppercase;">{{ $transaction->metode_pembayaran }}</div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Items -->
    <div class="items-table">
        @foreach($items as $item)
        @php
            $subtotal = $item->price * $item->qty;
            $totalDiskon = $item->diskon * $item->qty;
            $finalPrice = $subtotal - $totalDiskon;
            $hasDiscount = $item->diskon > 0;
        @endphp
        <div class="item-row">
            <div class="item-name">{{ $item->product->name ?? 'Produk Tidak Diketahui' }}</div>
            <div class="item-detail">
                <div class="item-detail-left">
                    {{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                </div>
                <div class="item-detail-right">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </div>
            </div>
            
            @if($hasDiscount)
            <div class="item-price-info">
                <div class="item-price-left">
                    Diskon: -Rp {{ number_format($totalDiskon, 0, ',', '.') }}
                </div>
                <div class="item-price-right">
                    Rp {{ number_format($finalPrice, 0, ',', '.') }}
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-row">
            <div class="summary-label">Subtotal</div>
            <div class="summary-value">Rp {{ number_format($transaction->harga_sebelum_diskon ?? 0, 0, ',', '.') }}</div>
        </div>
        
        @if($transaction->discount_item > 0)
        <div class="summary-row">
            <div class="summary-label">Diskon Barang</div>
            <div class="summary-value">-Rp {{ number_format($transaction->diskon_barang, 0, ',', '.') }}</div>
        </div>
        @endif
        
        @if($transaction->discount > 0)
        <div class="summary-row">
            <div class="summary-label">Diskon Transaksi</div>
            <div class="summary-value">-Rp {{ number_format($transaction->diskon, 0, ',', '.') }}</div>
        </div>
        @endif
        
        <div class="summary-row total">
            <div class="summary-label">TOTAL</div>
            <div class="summary-value">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Payment Info -->
    <div class="payment-info">
        <div class="summary-row">
            <div class="summary-label">Dibayar</div>
            <div class="summary-value">Rp {{ number_format($transaction->dibayarkan ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Kembalian</div>
            <div class="summary-value">Rp {{ number_format($transaction->kembalian ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p class="thank-you">TERIMA KASIH</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        <p>Simpan nota ini sebagai bukti pembayaran</p>
        <p style="margin-top: 8px;">{{ now()->format('Y') }} © Gym Management System</p>
    </div>
</body>
</html>