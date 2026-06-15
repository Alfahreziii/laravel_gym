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
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 15px 15px;       /* padding kanan-kiri ditambah */
            margin: 0 auto;   
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
        }
        
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
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
        
        .summary-item.tidak-aktif .value {
            color: #dc3545;
        }
        
        .info-section {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .info-item {
            font-size: 11px;
        }
        
        .info-item strong {
            color: #1a1a1a;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table thead {
            background-color: #2c3e50;
            color: white;
        }
        
        table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        
        table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        table tbody tr:hover {
            background-color: #f1f1f1;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
        }
        
        .status-aktif {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-tidak-aktif {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 10px;
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
        <p>Laporan Data Anggota Gym</p>
    </div>
    
    <div class="info-section">
        <div class="info-item">
            <strong>Tanggal Export:</strong> {{ date('d F Y, H:i') }} WIB
        </div>
        <div class="info-item">
            <strong>Data Ditampilkan:</strong> {{ $anggotas->count() }} Anggota
        </div>
        <div class="info-item">
            <strong>Filter:</strong> 
            @if($statusFilter === 'aktif')
                Anggota Aktif
            @elseif($statusFilter === 'tidak_aktif')
                Anggota Tidak Aktif
            @else
                Semua Anggota
            @endif
        </div>
    </div>
    <!-- Summary Statistics -->
    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Anggota</div>
            <div class="value">{{ $totalAnggota }}</div>
        </div>
        <div class="summary-item aktif">
            <div class="label">Aktif</div>
            <div class="value">{{ $totalAktif }}</div>
        </div>
        <div class="summary-item tidak-aktif">
            <div class="label">Tidak Aktif</div>
            <div class="value">{{ $totalTidakAktif }}</div>
        </div>
    </div>
    

    
    @if($anggotas->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="10%">ID Kartu</th>
                <th width="18%">Nama Lengkap</th>
                <th width="12%">Tanggal Lahir</th>
                <th width="12%">No. Telepon</th>
                <th width="15%">Alamat</th>
                <th width="8%" class="text-center">Gol. Darah</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($anggotas as $index => $anggota)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $anggota->id_kartu }}</td>
                <td>{{ $anggota->name }}</td>
                <td>{{ \Carbon\Carbon::parse($anggota->tgl_lahir)->format('d M Y') }}</td>
                <td>{{ $anggota->no_telp }}</td>
                <td>{{ Str::limit($anggota->alamat, 30) }}</td>
                <td class="text-center">{{ $anggota->gol_darah }}</td>
                <td class="text-center">
                    @if($anggota->status_keanggotaan)
                        <span class="status-badge status-aktif">Aktif</span>
                    @else
                        <span class="status-badge status-tidak-aktif">Tidak Aktif</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>Tidak ada data anggota yang tersedia untuk filter yang dipilih.</p>
    </div>
    @endif
    
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh sistem | © {{ date('Y') }} Gym Management System</p>
    </div>
</body>
</html>