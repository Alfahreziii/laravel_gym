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
        
        .summary-item {
            display: table-cell;
            width: 25%;
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
        
        .summary-item.in .value {
            color: #28a745;
        }
        
        .summary-item.out .value {
            color: #dc3545;
        }
        
        .summary-item.member .value {
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
            vertical-align: middle;
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
        
        .status-in {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-out {
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
    
    <!-- Summary -->
    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Kehadiran</div>
            <div class="value">{{ $totalKehadiran }}</div>
        </div>
        <div class="summary-item in">
            <div class="label">Check In</div>
            <div class="value">{{ $totalIn }}</div>
        </div>
        <div class="summary-item out">
            <div class="label">Check Out</div>
            <div class="value">{{ $totalOut }}</div>
        </div>
        <div class="summary-item member">
            <div class="label">Member Unik</div>
            <div class="value">{{ $totalMemberUnik }}</div>
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
            <div class="info-value">{{ $kehadiranMembers->count() }} Record Kehadiran</div>
        </div>
        <div class="info-row">
            <div class="info-label">Filter Periode:</div>
            <div class="info-value">{{ $filterInfo }}</div>
        </div>
    </div>

    <!-- Data Table -->
    @if($kehadiranMembers->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="12%">RFID</th>
                <th width="20%">Nama Member</th>
                <th width="18%">Tanggal</th>
                <th width="10%">Waktu</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kehadiranMembers as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->rfid }}</td>
                <td>{{ $item->anggota->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('H:i:s') }} WIB</td>
                <td class="text-center">
                    @if($item->status === 'in')
                        <span class="status-badge status-in">CHECK IN</span>
                    @else
                        <span class="status-badge status-out">CHECK OUT</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="no-data">
            <p>Tidak ada data kehadiran untuk periode ini.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini digenerate otomatis oleh sistem | © {{ date('Y') }} Gym Management System</p>
    </div>

</body>
</html>
