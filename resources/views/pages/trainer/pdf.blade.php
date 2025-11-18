<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan Data Trainer' }}</title>
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
        
        .summary-item.aktif .value {
            color: #28a745;
        }
        
        .summary-item.nonaktif .value {
            color: #dc3545;
        }
        
        .summary-item.pending .value {
            color: #ffc107;
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
            vertical-align: top;
        }
        
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
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
            background: #d4edda;
            color: #155724;
        }
        
        .status-nonaktif {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .text-center {
            text-align: center;
        }
        
        .jadwal-list {
            font-size: 8px;
            line-height: 1.4;
        }
        
        .jadwal-item {
            margin-bottom: 3px;
        }
        
        .jadwal-item strong {
            display: inline-block;
            width: 35px;
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

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $title ?? 'Laporan Data Trainer' }}</h1>
        <h2>Gym Management System</h2>
        <div class="date">Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }} WIB</div>
    </div>
    
    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Tanggal Export:</div>
            <div class="info-value">{{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Data:</div>
            <div class="info-value">{{ $trainers->count() }} Trainer</div>
        </div>
        <div class="info-row">
            <div class="info-label">Filter Status:</div>
            <div class="info-value">
                @if($statusFilter === 'aktif')
                    Trainer Aktif
                @elseif($statusFilter === 'nonaktif')
                    Trainer Non-Aktif
                @elseif($statusFilter === 'pending')
                    Trainer Pending
                @else
                    Semua Trainer
                @endif
            </div>
        </div>
    </div>
    
    <!-- Summary -->
    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Trainer</div>
            <div class="value">{{ $totalTrainer }}</div>
        </div>
        <div class="summary-item aktif">
            <div class="label">Aktif</div>
            <div class="value">{{ $totalAktif }}</div>
        </div>
        <div class="summary-item nonaktif">
            <div class="label">Non-Aktif</div>
            <div class="value">{{ $totalNonaktif }}</div>
        </div>
        <div class="summary-item pending">
            <div class="label">Pending</div>
            <div class="value">{{ $totalPending }}</div>
        </div>
    </div>
    
    <!-- Data Table -->
    @if($trainers->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="8%">RFID</th>
                <th width="15%">Nama</th>
                <th width="10%">No. Telp</th>
                <th width="12%">Spesialisasi</th>
                <th width="12%">Experience</th>
                <th width="8%">Tgl Gabung</th>
                <th width="6%">Sesi<br>Belum</th>
                <th width="6%">Sesi<br>Sudah</th>
                <th width="8%">Status</th>
                <th width="12%">Jadwal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trainers as $index => $trainer)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $trainer->rfid }}</td>
                <td>{{ $trainer->user->name ?? '-' }}</td>
                <td>{{ $trainer->no_telp }}</td>
                <td>{{ $trainer->specialisasi ? $trainer->specialisasi->nama_specialisasi : '-' }}</td>
                <td>{{ $trainer->experience }}</td>
                <td class="text-center">{{ $trainer->tgl_gabung->format('d/m/Y') }}</td>
                <td class="text-center">{{ $trainer->sesi_belum_dijalani }}</td>
                <td class="text-center">{{ $trainer->sesi_sudah_dijalani }}</td>
                <td class="text-center">
                    @if($trainer->status === 'aktif')
                        <span class="status-badge status-aktif">Aktif</span>
                    @elseif($trainer->status === 'nonaktif')
                        <span class="status-badge status-nonaktif">Non-Aktif</span>
                    @else
                        <span class="status-badge status-pending">Pending</span>
                    @endif
                </td>
                <td>
                    @if($trainer->schedules->count() > 0)
                        <div class="jadwal-list">
                            @foreach($trainer->schedules as $schedule)
                                <div class="jadwal-item">
                                    <strong>{{ $schedule->day_of_week }}:</strong>
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span style="color: #999; font-style: italic;">Tidak ada jadwal</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detail Information (Optional) -->
    <div style="margin-top: 30px; page-break-before: always;">
        <h3 style="font-size: 14px; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px;">
            Detail Informasi Trainer
        </h3>
        
        @foreach($trainers as $index => $trainer)
            @if($index > 0 && $index % 3 === 0)
                <div class="page-break"></div>
            @endif
            
            <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #333; page-break-inside: avoid;">
                <div style="display: table; width: 100%; margin-bottom: 10px;">
                    <div style="display: table-row;">
                        <div style="display: table-cell; width: 30%; font-weight: bold; padding: 3px 0;">{{ $index + 1 }}. {{ $trainer->user->name ?? '-' }}</div>
                        <div style="display: table-cell; width: 70%; padding: 3px 0;">
                            @if($trainer->status === 'aktif')
                                <span class="status-badge status-aktif">Aktif</span>
                            @elseif($trainer->status === 'nonaktif')
                                <span class="status-badge status-nonaktif">Non-Aktif</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div style="display: table; width: 100%; font-size: 9px;">
                    <div style="display: table-row;">
                        <div style="display: table-cell; width: 20%; padding: 2px 0; color: #666;">RFID</div>
                        <div style="display: table-cell; width: 30%; padding: 2px 0;">: {{ $trainer->rfid }}</div>
                        <div style="display: table-cell; width: 20%; padding: 2px 0; color: #666;">Email</div>
                        <div style="display: table-cell; width: 30%; padding: 2px 0;">: {{ $trainer->user->email ?? '-' }}</div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 2px 0; color: #666;">No. Telp</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ $trainer->no_telp }}</div>
                        <div style="display: table-cell; padding: 2px 0; color: #666;">Jenis Kelamin</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ $trainer->jenis_kelamin }}</div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 2px 0; color: #666;">Tempat Lahir</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ $trainer->tempat_lahir }}</div>
                        <div style="display: table-cell; padding: 2px 0; color: #666;">Tanggal Lahir</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ \Carbon\Carbon::parse($trainer->tgl_lahir)->format('d/m/Y') }}</div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 2px 0; color: #666;">Spesialisasi</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ $trainer->specialisasi ? $trainer->specialisasi->nama_specialisasi : '-' }}</div>
                        <div style="display: table-cell; padding: 2px 0; color: #666;">Experience</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ $trainer->experience }}</div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 2px 0; color: #666;">Tanggal Gabung</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ $trainer->tgl_gabung->format('d/m/Y') }}</div>
                        <div style="display: table-cell; padding: 2px 0; color: #666;">Sesi (Sudah/Belum)</div>
                        <div style="display: table-cell; padding: 2px 0;">: {{ $trainer->sesi_sudah_dijalani }} / {{ $trainer->sesi_belum_dijalani }}</div>
                    </div>
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 2px 0; color: #666; vertical-align: top;">Alamat</div>
                        <div style="display: table-cell; padding: 2px 0; vertical-align: top;" colspan="3">: {{ $trainer->alamat }}</div>
                    </div>
                    @if($trainer->keterangan)
                    <div style="display: table-row;">
                        <div style="display: table-cell; padding: 2px 0; color: #666; vertical-align: top;">Keterangan</div>
                        <div style="display: table-cell; padding: 2px 0; vertical-align: top;" colspan="3">: {{ $trainer->keterangan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="no-data">
        Tidak ada data trainer yang tersedia untuk filter yang dipilih.
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>Gym Management System</strong></p>
        <p>Dokumen ini digenerate secara otomatis oleh sistem | © {{ date('Y') }}</p>
    </div>
</body>
</html>