<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin — HexaGym</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #FAF9F5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 32px rgba(26,22,18,.10), 0 1px 4px rgba(26,22,18,.06);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .card-accent {
            background: linear-gradient(135deg, #F2622E 0%, #E04E1B 55%, #BC3E14 100%);
            padding: 2rem 2.5rem 1.875rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.625rem;
        }

        .logo-wordmark {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 22px;
            line-height: 1;
            color: #fff;
            letter-spacing: 0.02em;
        }

        .logo-sub {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.13em;
            color: rgba(255,255,255,0.72);
            text-transform: uppercase;
            margin-top: 3px;
        }

        .accent-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.65);
            margin-bottom: 5px;
        }

        .accent-heading {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 30px;
            line-height: 1.1;
            color: #fff;
        }

        .card-body { padding: 2rem 2.5rem 2.25rem; }

        .error-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            margin-bottom: 1.25rem;
        }

        .error-text { font-size: 13px; color: #B91C1C; line-height: 1.5; }

        .form-group { margin-bottom: 1rem; }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #6E6A63;
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
            display: flex;
        }

        .form-input {
            width: 100%;
            height: 46px;
            padding: 0 12px 0 40px;
            border: 1.5px solid #E2E0DB;
            border-radius: 10px;
            background: #FAFAF9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: #1A1A18;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .form-input:focus {
            border-color: #F2622E;
            box-shadow: 0 0 0 3px rgba(242,98,46,.12);
            background: #fff;
        }

        .form-input::placeholder { color: #C4C0BA; }

        .input-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9CA3AF;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            line-height: 1;
        }

        .input-toggle:hover { color: #6E6A63; }

        .btn-login {
            width: 100%;
            height: 48px;
            margin-top: 1.5rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #F2622E 0%, #E04E1B 100%);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .01em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px -6px rgba(242,98,46,.55);
            transition: opacity .15s, box-shadow .15s;
        }

        .btn-login:hover {
            opacity: .93;
            box-shadow: 0 8px 24px -6px rgba(242,98,46,.65);
        }

        .btn-login:active { opacity: .87; }

        .card-footer {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid #EBEAE7;
            text-align: center;
            font-size: 11.5px;
            color: #9C978E;
            letter-spacing: .01em;
        }

        /* ── Responsive ───────────────────────────────────── */
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .card-accent { padding: 1.75rem 1.5rem 1.5rem; }
            .card-body { padding: 1.75rem 1.5rem 2rem; }
        }
    </style>
</head>
<body>

<div class="card">

    <div class="card-accent">
        <div class="logo">
            <svg viewBox="0 0 40 40" fill="none" style="width:36px;height:36px;flex-shrink:0">
                <path d="M20 2 35.3 11v18L20 38 4.7 29V11Z" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.5)" stroke-width="1.5"/>
                <g stroke="#fff" stroke-width="2.4" stroke-linecap="round">
                    <path d="M13 20h14"/>
                    <path d="M13 16.5v7M27 16.5v7"/>
                    <path d="M10.5 18v4M29.5 18v4"/>
                </g>
            </svg>
            <div>
                <div class="logo-wordmark">HexaGym</div>
                <div class="logo-sub">Super Admin</div>
            </div>
        </div>
        <div class="accent-label">Portal Manajemen</div>
        <div class="accent-heading">Masuk ke Panel<br>Super Admin</div>
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="error-box">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                 style="width:15px;height:15px;color:#DC2626;flex-shrink:0;margin-top:1px">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div class="error-text">
                @foreach ($errors->all() as $error){{ $error }}@endforeach
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('super_admin.login.post') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                             style="width:16px;height:16px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </span>
                    <input type="email" id="email" name="email"
                           class="form-input"
                           value="{{ old('email') }}"
                           placeholder="admin@sistemgate.com"
                           required autofocus autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                             style="width:16px;height:16px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </span>
                    <input type="password" id="password" name="password"
                           class="form-input"
                           placeholder="••••••••"
                           required autocomplete="current-password"
                           style="padding-right:44px">
                    <button type="button" class="input-toggle" onclick="togglePass()">
                        <svg id="ico-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                             style="width:17px;height:17px">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        <svg id="ico-eye-off" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                             style="width:17px;height:17px;display:none">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Masuk
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"
                     style="width:16px;height:16px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </button>
        </form>

        <div class="card-footer">HexaGym &middot; Super Admin Panel</div>
    </div>
</div>

<script>
function togglePass() {
    const inp = document.getElementById('password');
    const on  = document.getElementById('ico-eye');
    const off = document.getElementById('ico-eye-off');
    if (inp.type === 'password') {
        inp.type = 'text';
        on.style.display  = 'none';
        off.style.display = 'block';
    } else {
        inp.type = 'password';
        on.style.display  = 'block';
        off.style.display = 'none';
    }
}
</script>

</body>
</html>
