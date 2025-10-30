<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembayaran Membership - {{ $transaksi->kode_transaksi }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #000;
        }
        
        .header p {
            font-size: 11px;
            color: #666;
        }
        
        .nota-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        
        .info-value {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        table th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        table td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            margin-top: 10px;
        }
        
        .footer {
            clear: both;
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GYM FITNESS CENTER</h1>
        <!-- <p>Jl. Contoh No. 123, Depok, Jawa Barat</p>
        <p>Telp: (021) 1234567 | Email: info@gymfitness.com</p> -->
    </div>

    <div class="nota-title">Nota Pembayaran Membership</div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Kode Transaksi</div>
            <div class="info-value">: {{ $transaksi->kode_transaksi }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Transaksi</div>
            <div class="info-value">: {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nama Anggota</div>
            <div class="info-value">: {{ $anggota->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Paket Membership</div>
            <div class="info-value">: {{ $paket->nama_paket ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value">: {{ $transaksi->status_pembayaran }}</div>
        </div>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 10px;">Rincian Biaya</h3>
    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Harga Paket {{ $paket->nama_paket ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($paket->harga ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Diskon</td>
                <td class="text-right">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($paket->harga ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Diskon:</span>
            <span>- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
        </div>
        <div class="total-row final">
            <span>Total Biaya:</span>
            <span>Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    <h3 style="margin-top: 40px; margin-bottom: 10px;">Riwayat Pembayaran</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Bayar</th>
                <th>Metode Pembayaran</th>
                <th class="text-right">Jumlah Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembayaran as $index => $bayar)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($bayar->tgl_bayar)->format('d/m/Y') }}</td>
                <td>{{ ucfirst($bayar->metode_pembayaran) }}</td>
                <td class="text-right">Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total Dibayarkan:</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Nota ini dicetak pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>Terima kasih atas kepercayaan Anda. Semoga sehat selalu!</p>
    </div>
</body>
</html>