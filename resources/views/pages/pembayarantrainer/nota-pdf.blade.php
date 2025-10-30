<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembayaran Personal Trainer - {{ $transaksi->kode_transaksi }}</title>
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
            width: 180px;
            font-weight: bold;
        }
        
        .info-value {
            flex: 1;
        }
        
        .trainer-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        
        .trainer-info h4 {
            margin-bottom: 10px;
            color: #007bff;
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
            margin-top: 10px;
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
        
        .info-highlight {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GYM FITNESS CENTER</h1>
    </div>

    <div class="nota-title">Nota Pembayaran Personal Trainer</div>

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
            <div class="info-value">: {{ $anggota->nama ?? $anggota->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Paket Personal Trainer</div>
            <div class="info-value">: {{ $paket->nama_paket ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value">: {{ $transaksi->status_pembayaran }}</div>
        </div>
    </div>

    <div class="trainer-info">
        <h4>Informasi Trainer</h4>
        <div class="info-row">
            <div class="info-label">Nama Trainer</div>
            <div class="info-value">: {{ $trainer->nama ?? $trainer->name ?? '-' }}</div>
        </div>
        @if(isset($trainer->spesialisasi))
        <div class="info-row">
            <div class="info-label">Spesialisasi</div>
            <div class="info-value">: {{ $trainer->spesialisasi }}</div>
        </div>
        @endif
        @if(isset($transaksi->tgl_mulai))
        <div class="info-row">
            <div class="info-label">Tanggal Mulai</div>
            <div class="info-value">: {{ \Carbon\Carbon::parse($transaksi->tgl_mulai)->format('d/m/Y') }}</div>
        </div>
        @endif
        @if(isset($transaksi->tgl_selesai))
        <div class="info-row">
            <div class="info-label">Tanggal Selesai</div>
            <div class="info-value">: {{ \Carbon\Carbon::parse($transaksi->tgl_selesai)->format('d/m/Y') }}</div>
        </div>
        @endif
    </div>

    <h3 style="margin-top: 20px;">Rincian Biaya</h3>
    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Biaya Paket {{ $paket->nama_paket ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($paket->biaya ?? 0, 0, ',', '.') }}</td>
            </tr>
            @if($transaksi->diskon > 0)
            <tr>
                <td>Diskon</td>
                <td class="text-right">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($paket->biaya ?? 0, 0, ',', '.') }}</span>
        </div>
        @if($transaksi->diskon > 0)
        <div class="total-row">
            <span>Diskon:</span>
            <span>- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row final">
            <span>Total Biaya:</span>
            <span>Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    <h3 style="margin-top: 20px; margin-bottom: 10px;">Riwayat Pembayaran</h3>
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

    @if(isset($paket->deskripsi) || isset($paket->jumlah_sesi))
    <div class="info-highlight">
        <strong>Keterangan Paket:</strong>
        @if(isset($paket->jumlah_sesi))
        <br>Jumlah Sesi: {{ $paket->jumlah_sesi }} sesi
        @endif
        @if(isset($paket->durasi_sesi))
        <br>Durasi per Sesi: {{ $paket->durasi_sesi }} menit
        @endif
        @if(isset($paket->deskripsi))
        <br>{{ $paket->deskripsi }}
        @endif
    </div>
    @endif

    <div class="footer">
        <p>Nota ini dicetak pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>Terima kasih atas kepercayaan Anda. Semoga sehat selalu!</p>
        <p style="margin-top: 10px; font-style: italic;">Semangat berlatih bersama trainer profesional kami!</p>
    </div>
</body>
</html>