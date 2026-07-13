<!-- resources/views/auth/verify-email.blade.php -->
<!DOCTYPE html>
<html lang="id">

<x-head />

<body class="bg-white dark:bg-neutral-900">

<div class="flex min-h-screen">

    {{-- ──────────────────── PANEL KIRI ──────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden flex-col flex-shrink-0">

        <img src="{{ asset('assets/images/auth/forgot-pass-img.png') }}"
             alt="Gym HexaGym"
             class="absolute inset-0 w-full h-full object-cover">

        <div class="absolute inset-0 bg-gradient-to-br from-primary-600/85 via-primary-700/80 to-primary-900/90"></div>

        <div class="relative z-10 flex flex-col h-full px-11 py-11">

            <div class="flex items-center gap-3">
                <svg viewBox="0 0 40 40" fill="none" class="w-[42px] h-[42px] flex-shrink-0">
                    <path d="M20 2 35.3 11v18L20 38 4.7 29V11Z" fill="#fff"/>
                    <g stroke="#F2622E" stroke-width="2.6" stroke-linecap="round">
                        <path d="M13 20h14"/>
                        <path d="M13 16.5v7M27 16.5v7"/>
                        <path d="M10.5 18v4M29.5 18v4"/>
                    </g>
                </svg>
                <div>
                    <div class="font-display font-bold text-[26px] leading-none text-white">HexaGym</div>
                    <div class="text-[11px] text-white/85 font-medium tracking-[0.04em] mt-0.5">Gym Management System</div>
                </div>
            </div>

            <div class="mt-auto max-w-[420px]">
                <div class="text-[11px] font-bold tracking-[0.14em] uppercase text-white/80">Verifikasi Akun</div>
                <h1 class="font-display font-bold text-[44px] leading-[1.04] text-white mt-3"
                    style="text-wrap: balance">
                    Satu langkah lagi.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/90">
                    Verifikasi email Anda untuk mengaktifkan akun dan mulai menggunakan semua fitur HexaGym.
                </p>
            </div>

            <div class="mt-9 text-[11px] text-white/70">© 2026 HexaGym · Cabang Depok</div>

        </div>
    </div>

    {{-- ──────────────────── PANEL KANAN ──────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white dark:bg-neutral-800">
        <div class="w-full max-w-[384px]">

            <h2 class="font-display font-bold text-[34px] leading-[1.05] text-ink dark:text-ink-d">
                Verifikasi email Anda 📧
            </h2>
            <p class="mt-2 text-sm text-ink-2 dark:text-ink-d2 leading-relaxed">
                Kami telah mengirimkan tautan verifikasi ke email Anda. Silakan cek kotak masuk atau folder spam untuk mengaktifkan akun.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mt-5 flex items-start gap-3 px-4 py-3 rounded-xl bg-success-50 border border-success-200 dark:bg-success-600/10 dark:border-success-600/30">
                    <iconify-icon icon="lucide:check-circle" class="text-success-600 text-lg mt-0.5 flex-none"></iconify-icon>
                    <p class="text-[13px] text-success-700 dark:text-success-400">
                        Tautan verifikasi baru telah dikirim ke email Anda.
                    </p>
                </div>
            @endif

            <div class="flex flex-col gap-3 mt-7">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                        class="btn btn-primary w-full justify-center py-[13px] rounded-xl font-bold text-[15px]"
                        style="box-shadow: 0 8px 20px -8px rgba(242, 98, 46, 0.6)">
                        <iconify-icon icon="lucide:send" class="text-lg"></iconify-icon>
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 text-sm text-ink-2 dark:text-ink-d2 hover:text-danger-600 dark:hover:text-danger-400 hover:underline transition-colors">
                        Keluar dari akun
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>

<x-script />

</body>
</html>
