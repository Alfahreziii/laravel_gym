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
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 11px;
            color: #555;
        }

        .summary-box {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-item {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
        }

        .summary-item .label {
            font-size: 10px;
            color: #777;
            margin-bottom: 2px;
        }

        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
        }

        .summary-item.primary .value {
            color: #2563eb;
        }

        .summary-item.success .value {
            color: #16a34a;
        }

        .summary-item.warning .value {
            color: #d97706;
        }

        .summary-item.info .value {
            color: #0891b2;
        }

        .filter-info {
            margin-bottom: 12px;
            font-size: 10px;
            color: #555;
        }

        .filter-info span {
            font-weight: bold;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        thead tr {
            background-color: #1e40af;
            color: #fff;
        }

        thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        tbody tr:nth-child(even) {
            background-color: #f3f4f6;
        }

        tbody tr:hover {
            background-color: #e5e7eb;
        }

        tbody td {
            padding: 6px 10px;
            font-size: 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-in {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-out {
            background-color: #fef9c3;
            color: #b45309;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #999;
        }

        .print-date {
            margin-top: 6px;
            font-size: 9px;
            color: #aaa;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>
            @if ($filterType === 'range')
                Periode: {{ $filterInfo }}
            @else
                Semua Periode
            @endif
        </p>
    </div>

    {{-- Statistik Ringkasan --}}
    <table style="margin-bottom: 16px; border: none; border-collapse: separate; border-spacing: 6px;">
        <tr>
            <td style="width: 25%; border: 1px solid #ddd; border-radius: 6px; padding: 8px 12px; text-align: center;">
                <div style="font-size: 10px; color: #777; margin-bottom: 2px;">Total Kehadiran</div>
                <div style="font-size: 18px; font-weight: bold; color: #2563eb;">{{ $totalKehadiran }}</div>
            </td>
            <td style="width: 25%; border: 1px solid #ddd; border-radius: 6px; padding: 8px 12px; text-align: center;">
                <div style="font-size: 10px; color: #777; margin-bottom: 2px;">Check IN</div>
                <div style="font-size: 18px; font-weight: bold; color: #16a34a;">{{ $totalIn }}</div>
            </td>
            <td style="width: 25%; border: 1px solid #ddd; border-radius: 6px; padding: 8px 12px; text-align: center;">
                <div style="font-size: 10px; color: #777; margin-bottom: 2px;">Check OUT</div>
                <div style="font-size: 18px; font-weight: bold; color: #d97706;">{{ $totalOut }}</div>
            </td>
            <td style="width: 25%; border: 1px solid #ddd; border-radius: 6px; padding: 8px 12px; text-align: center;">
                <div style="font-size: 10px; color: #777; margin-bottom: 2px;">Trainer Unik</div>
                <div style="font-size: 18px; font-weight: bold; color: #0891b2;">{{ $totalTrainerUnik }}</div>
            </td>
        </tr>
    </table>

    <div class="filter-info">
        Menampilkan <span>{{ $kehadiranTrainers->count() }}</span> data kehadiran trainer
        @if ($filterType === 'range')
            untuk periode <span>{{ $filterInfo }}</span>
        @else
            dari semua periode
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>ID Kartu</th>
                <th>Nama Trainer</th>
                <th style="width: 80px;">Status</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kehadiranTrainers as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->rfid ?? '-' }}</td>
                    <td>{{ $item->nama ?? '-' }}</td>
                    <td>
                        @if ($item->status === 'in')
                            <span class="badge badge-in">CHECK IN</span>
                        @else
                            <span class="badge badge-out">CHECK OUT</span>
                        @endif
                    </td>
                    <td>{{ $item->created_at->format('d M Y - H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                        Tidak ada data kehadiran trainer
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-date">
        Dicetak pada: {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm:ss') }}
    </div>
</body>

</html>
