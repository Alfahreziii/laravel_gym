<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Sesi Training - {{ $trainer->name }}</title>
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
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #333;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h2 {
            font-size: 13px;
            color: #444;
            font-weight: normal;
        }

        .header .date {
            font-size: 9px;
            color: #999;
            margin-top: 4px;
        }

        .info-box {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px 12px;
            margin-bottom: 15px;
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 140px;
            padding: 2px 0;
        }

        .info-value {
            display: table-cell;
            padding: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        thead tr th {
            background: #333;
            color: #fff;
            padding: 7px 8px;
            text-align: left;
            font-size: 10px;
        }

        tbody tr td {
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        tbody tr:nth-child(even) td {
            background: #f9f9f9;
        }

        .badge-selesai {
            background: #fee2e2;
            color: #b91c1c;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
        }

        .badge-masuk {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 9px;
            color: #888;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
        }

        .summary {
            margin-bottom: 15px;
            padding: 8px 12px;
            background: #eef2ff;
            border-left: 4px solid #4f46e5;
        }

        .summary p {
            margin: 2px 0;
            font-size: 11px;
        }

        .summary strong {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Riwayat Sesi Training</h1>
        <h2>Trainer: {{ $trainer->name }}</h2>
        <div class="date">Dicetak pada: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }}</div>
    </div>

    <div class="info-box">
        <div class="info-row">
            <div class="info-label">Periode Filter</div>
            <div class="info-value">: {{ $filterInfo }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Data</div>
            <div class="info-value">: {{ $logs->count() }} log</div>
        </div>
    </div>

    <div class="summary">
        <p>Total Sesi Selesai: <strong>{{ $totalSesi }} sesi</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th style="width:120px">Tanggal & Waktu</th>
                <th style="width:60px">Tipe</th>
                <th style="width:40px">Sesi</th>
                <th style="width:60px">Total Sesi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td>
                        @if ($log->type === 'in')
                            <span class="badge-masuk">Masuk</span>
                        @else
                            <span class="badge-selesai">Selesai</span>
                        @endif
                    </td>
                    <td>{{ $log->sesi }}</td>
                    <td>{{ $log->current_sesi }}</td>
                    <td>{{ $log->clean_description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#999; font-style:italic;">
                        Tidak ada data pada periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-left">Hexagym Management System</div>
        <div class="footer-right">{{ $logs->count() }} data ditampilkan</div>
    </div>
</body>

</html>
