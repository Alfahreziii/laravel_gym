<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langganan Berakhir</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #F5F3F0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(26,22,18,.10);
        }
        .icon {
            width: 72px;
            height: 72px;
            background: #FFF0EB;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .icon svg { width: 36px; height: 36px; color: #F2622E; }
        .gym-name {
            font-size: .8125rem;
            font-weight: 600;
            letter-spacing: .08em;
            color: #F2622E;
            text-transform: uppercase;
            margin-bottom: .5rem;
        }
        h1 {
            font-size: 1.375rem;
            font-weight: 700;
            color: #1A1A18;
            margin-bottom: .75rem;
            line-height: 1.3;
        }
        p {
            font-size: .9375rem;
            color: #6E6A63;
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }
        .badge {
            display: inline-block;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .8125rem;
            font-weight: 600;
        }
        .badge-suspend  { background: #FEF2F2; color: #B91C1C; }
        .badge-nonaktif { background: #F3F4F6; color: #4B5563; }
        .contact {
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E2E0DB;
            font-size: .875rem;
            color: #9C978E;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73
                       0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898
                       0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>

        <div class="gym-name">{{ $tenant->nama_gym }}</div>

        <h1>Layanan Tidak Aktif</h1>

        <p>
            Masa aktif langganan gym ini telah berakhir atau sedang ditangguhkan
            oleh administrator sistem.
        </p>

        @if($tenant->status === 'suspend')
            <span class="badge badge-suspend">Akun Ditangguhkan</span>
        @else
            <span class="badge badge-nonaktif">Langganan Berakhir</span>
        @endif

        <div class="contact">
            Hubungi <strong>{{ $tenant->email }}</strong> atau
            <strong>{{ $tenant->no_hp }}</strong> untuk informasi lebih lanjut.
        </div>
    </div>
</body>
</html>
