<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Member - {{ $anggota->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .card-container {
            width: 85.6mm; /* 3.37 inch */
            height: 54mm; /* 2.125 inch - ukuran kartu kredit standar */
            margin: 10mm auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .card-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .gym-name {
            color: white;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .card-body {
            padding: 12px;
            color: white;
        }
        .member-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
            float: left;
            margin-right: 12px;
        }
        .member-info {
            overflow: hidden;
        }
        .member-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        .member-id {
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .barcode-section {
            clear: both;
            background: white;
            border-radius: 6px;
            padding: 8px;
            margin-top: 10px;
            text-align: center;
        }
        .barcode-section svg {
            max-width: 100%;
            height: auto;
        }
        .barcode-text {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-top: 4px;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
        }
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.2);
            padding: 4px 12px;
            text-align: center;
        }
        .valid-until {
            color: white;
            font-size: 9px;
            opacity: 0.9;
        }
        .decorative-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }
        .circle-1 {
            width: 100px;
            height: 100px;
            top: -30px;
            right: -30px;
        }
        .circle-2 {
            width: 60px;
            height: 60px;
            bottom: -20px;
            left: -20px;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Decorative Elements -->
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        
        <!-- Card Header -->
        <div class="card-header">
            <div class="gym-name">GYM MEMBERSHIP CARD</div>
        </div>
        
        <!-- Card Body -->
        <div class="card-body">
            <!-- Member Photo & Info -->
            @if($anggota->user && $anggota->user->photo)
                <img src="{{ public_path('storage/' . $anggota->user->photo) }}" class="member-photo" alt="Photo">
            @else
                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.3); float: left; margin-right: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 3px solid white;">
                    👤
                </div>
            @endif
            
            <div class="member-info">
                <div class="member-name">{{ $anggota->name }}</div>
                <div class="member-id">ID: {{ $anggota->id_kartu }}</div>
                @if($anggota->status_keanggotaan)
                    <div style="display: inline-block; background: rgba(46, 213, 115, 0.9); padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold;">
                        ✓ ACTIVE MEMBER
                    </div>
                @endif
            </div>
            
            <!-- Barcode Section -->
            <div class="barcode-section">
                {!! DNS1D::getBarcodeHTML($anggota->id_kartu, 'C128', 1.5, 40) !!}
                <div class="barcode-text">{{ $anggota->id_kartu }}</div>
            </div>
        </div>
        
        <!-- Card Footer -->
        @if($anggota->active_membership)
        <div class="card-footer">
            <div class="valid-until">
                Valid Until: {{ $anggota->active_membership->tgl_selesai->format('d M Y') }}
            </div>
        </div>
        @endif
    </div>

    <!-- Bagian Belakang Kartu (Optional) -->
    <div style="page-break-before: always;"></div>
    
    <div class="card-container" style="background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);">
        <div style="padding: 20px; color: white; height: 100%;">
            <h3 style="font-size: 12px; margin-bottom: 10px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 8px;">
                TERMS & CONDITIONS
            </h3>
            <ul style="font-size: 8px; line-height: 1.6; padding-left: 15px;">
                <li>Kartu member bersifat pribadi dan tidak dapat dipinjamkan</li>
                <li>Wajib scan barcode saat masuk dan keluar gym</li>
                <li>Kartu hilang segera lapor ke staff</li>
                <li>Patuhi peraturan dan tata tertib gym</li>
            </ul>
            
            <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 8px; font-size: 9px;">
                <strong style="display: block; margin-bottom: 4px;">CUSTOMER SERVICE</strong>
                <div>📞 0812-3456-7890 | 📧 info@gym.com</div>
            </div>
        </div>
    </div>
</body>
</html>