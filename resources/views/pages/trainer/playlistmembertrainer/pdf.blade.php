<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Gym - {{ $memberTrainer->anggota->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.5;
            color: #111;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 3px solid #333;
        }

        .header h1 {
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .header h2 {
            font-size: 13px;
            color: #555;
            font-weight: normal;
        }

        .header .date {
            font-size: 9px;
            color: #999;
            margin-top: 4px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 6px 12px;
            width: 33.3%;
            border-right: 1px solid #ddd;
            vertical-align: top;
        }

        .info-cell:last-child {
            border-right: none;
        }

        .info-label {
            font-size: 9px;
            color: #888;
            margin-bottom: 2px;
        }

        .info-value {
            font-weight: bold;
            font-size: 11px;
        }

        .summary {
            background: #eef2ff;
            border-left: 4px solid #4f46e5;
            padding: 7px 12px;
            margin-bottom: 16px;
            font-size: 11px;
        }

        .sesi-block {
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            overflow: hidden;
        }

        .sesi-header {
            background: #e0e7ff;
            padding: 8px 12px;
            display: table;
            width: 100%;
        }

        .sesi-header-left {
            display: table-cell;
            font-weight: bold;
            font-size: 12px;
        }

        .sesi-header-right {
            display: table-cell;
            text-align: right;
            font-size: 10px;
            color: #444;
        }

        .sesi-body {
            padding: 10px 12px;
        }

        .playlist-item {
            border-bottom: 1px solid #eee;
            padding: 6px 0;
        }

        .playlist-item:last-child {
            border-bottom: none;
        }

        .playlist-name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .playlist-keterangan {
            font-size: 10px;
            color: #555;
        }

        .playlist-time {
            font-size: 9px;
            color: #999;
            text-align: right;
        }

        .empty-sesi {
            font-style: italic;
            color: #999;
            font-size: 10px;
            padding: 6px 0;
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
        }

        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Riwayat Gym Member</h1>
        <h2>Trainer: {{ $trainer->name }}</h2>
        <div class="date">Dicetak pada: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }}</div>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Nama Member</div>
                <div class="info-value">{{ $memberTrainer->anggota->name }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Paket Training</div>
                <div class="info-value">{{ $memberTrainer->paketPersonalTrainer->nama_paket }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Progress Sesi</div>
                <div class="info-value">
                    {{ $memberTrainer->sesi }} / {{ $memberTrainer->paketPersonalTrainer->jumlah_sesi }} sesi
                    (Sisa: {{ $memberTrainer->sisa_sesi }})
                </div>
            </div>
        </div>
    </div>

    <div class="summary">
        Total Sesi Selesai: <strong>{{ $history->count() }} sesi</strong> &nbsp;|&nbsp;
        Total Durasi Latihan: <strong>{{ $totalDurasi > 0 ? $totalDurasi . ' menit' : '-' }}</strong>
    </div>

    @if ($history->isEmpty())
        <p style="text-align:center; padding:20px; color:#999; font-style:italic;">Belum ada riwayat training.</p>
    @else
        @foreach ($history as $sesiKe => $playlists)
            <div class="sesi-block">
                <div class="sesi-header">
                    <div class="sesi-header-left">Sesi ke-{{ $sesiKe }}</div>
                    <div class="sesi-header-right">
                        @if (!empty($tanggalPerSesi[$sesiKe]))
                            {{ $tanggalPerSesi[$sesiKe]->format('d M Y') }} &nbsp;|&nbsp;
                        @endif
                        @if (!empty($durasiPerSesi[$sesiKe]))
                            Durasi: {{ $durasiPerSesi[$sesiKe] }} menit &nbsp;|&nbsp;
                        @endif
                        {{ $playlists->count() }} latihan
                    </div>
                </div>
                <div class="sesi-body">
                    @if ($playlists->isEmpty())
                        <p class="empty-sesi">Sesi ini diselesaikan tanpa data playlist tercatat.</p>
                    @else
                        @foreach ($playlists as $playlist)
                            <div class="playlist-item">
                                <table style="width:100%">
                                    <tr>
                                        <td style="width:85%; vertical-align:top;">
                                            <div class="playlist-name">{{ $playlist->latihan }}</div>
                                            @if ($playlist->keterangan)
                                                <div class="playlist-keterangan">{{ $playlist->keterangan }}</div>
                                            @else
                                                <div class="playlist-keterangan" style="font-style:italic">Tidak ada
                                                    keterangan</div>
                                            @endif
                                        </td>
                                        <td style="width:15%; vertical-align:top; text-align:right;">
                                            <div class="playlist-time">{{ $playlist->created_at->format('d M Y') }}
                                            </div>
                                            <div class="playlist-time">{{ $playlist->created_at->format('H:i') }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <div class="footer">
        <div class="footer-left">Hexagym Management System</div>
        <div class="footer-right">{{ $history->count() }} sesi ditampilkan</div>
    </div>

</body>

</html>
