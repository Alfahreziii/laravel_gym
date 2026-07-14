<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
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
        .code-badge {
            font-size: .8125rem;
            font-weight: 700;
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
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: .6rem 1.25rem;
            background: #F2622E;
            color: #fff;
            border-radius: 10px;
            font-size: .9375rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-back:hover { background: #D9521F; }
        .btn-back svg { width: 16px; height: 16px; }
        .footer-note {
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
                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25
                       2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25
                       2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
            </svg>
        </div>

        <div class="code-badge">Error 403</div>

        <h1>Fitur Tidak Tersedia</h1>

        <p>
            @if(isset($exception) && $exception->getMessage() === 'Fitur ini tidak tersedia di paket Anda.')
                Fitur ini tidak tersedia di paket langganan gym Anda saat ini.
                Hubungi administrator untuk informasi upgrade paket.
            @else
                Anda tidak memiliki izin untuk mengakses halaman ini.
            @endif
        </p>

        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : (auth()->check() ? route('dashboard') : route('login')) }}"
           class="btn-back">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>

        <div class="footer-note">
            Jika Anda yakin ini kesalahan, hubungi administrator sistem Anda.
        </div>
    </div>
</body>
</html>
