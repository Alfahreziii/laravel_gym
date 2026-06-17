<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Persetujuan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .waiting-container {
            background: #fff;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
        }

        .waiting-container img {
            width: 100px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        .waiting-container h1 {
            font-size: 1.8rem;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .waiting-container p {
            color: #4b5563;
            font-size: 1rem;
            margin-bottom: 30px;
        }

        .waiting-container .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .waiting-container .btn:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>

    <div class="waiting-container">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Waiting Icon">
        <h1>Menunggu Persetujuan</h1>
        <p>Akun Anda sedang dalam proses verifikasi oleh admin. Mohon tunggu beberapa saat hingga status Anda aktif.</p>
        <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="btn">
            Log Out
        </button>
        </form>
    </div>

</body>
</html>
