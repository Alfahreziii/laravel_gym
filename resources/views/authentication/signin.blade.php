<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="id">

<x-head/>

<body class="bg-white dark:bg-neutral-900">

<div class="flex min-h-screen">

    {{-- ──────────────────── PANEL KIRI ──────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden flex-col flex-shrink-0">

        {{-- Layer 1: Foto gym (paling bawah) --}}
        <img src="{{ asset('assets/images/auth/auth-img.png') }}"
             alt="Gym HexaGym"
             class="absolute inset-0 w-full h-full object-cover">

        {{-- Layer 2: Overlay gradient oranye semi-transparan --}}
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600/85 via-primary-700/80 to-primary-900/90"></div>

        {{-- Layer 3: Konten teks (paling atas) --}}
        <div class="relative z-10 flex flex-col h-full px-11 py-11">

            {{-- Logo --}}
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

            {{-- Headline + stats (didorong ke bawah) --}}
            <div class="mt-auto max-w-[420px]">
                <div class="text-[11px] font-bold tracking-[0.14em] uppercase text-white/80">Panel Admin</div>
                <h1 class="font-display font-bold text-[44px] leading-[1.04] text-white mt-3"
                    style="text-wrap: balance">
                    Kelola gym Anda dari satu tempat.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/90">
                    Keanggotaan, kehadiran, kasir, dan keuangan — semua terpantau real-time dalam satu dashboard yang ringkas.
                </p>

                {{-- Stats --}}
                <div class="flex items-center gap-6 mt-7">
                    <div>
                        <div class="font-display text-[30px] font-bold leading-none text-white">342</div>
                        <div class="text-[11px] text-white/80 mt-1">Member aktif</div>
                    </div>
                    <div class="w-px h-9 bg-white/25"></div>
                    <div>
                        <div class="font-display text-[30px] font-bold leading-none text-white">98%</div>
                        <div class="text-[11px] text-white/80 mt-1">Uptime sistem</div>
                    </div>
                    <div class="w-px h-9 bg-white/25"></div>
                    <div>
                        <div class="font-display text-[30px] font-bold leading-none text-white">3</div>
                        <div class="text-[11px] text-white/80 mt-1">Cabang</div>
                    </div>
                </div>
            </div>

            {{-- Footer kiri --}}
            <div class="mt-9 text-[11px] text-white/70">© 2026 HexaGym · Cabang Depok</div>

        </div>
    </div>

    {{-- ──────────────────── PANEL KANAN ──────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white dark:bg-neutral-800">
        <div class="w-full max-w-[384px]">

            <h2 class="font-display font-bold text-[34px] leading-[1.05] text-ink dark:text-ink-d">
                Selamat datang 👋
            </h2>
            <p class="mt-2 text-sm text-ink-2 dark:text-ink-d2">
                Masuk ke panel admin HexaGym untuk melanjutkan.
            </p>

            {{-- Error --}}
            @if ($errors->any())
                <div class="mt-5 flex items-start gap-3 px-4 py-3 rounded-xl bg-danger-50 border border-danger-200 dark:bg-danger-600/10 dark:border-danger-600/30">
                    <iconify-icon icon="lucide:alert-circle" class="text-danger-600 text-lg mt-0.5 flex-none"></iconify-icon>
                    <div class="text-[13px] text-danger-700 dark:text-danger-400">
                        @error('email'){{ $message }} @enderror
                        @error('password'){{ $message }}@enderror
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6">
                @csrf

                {{-- Email --}}
                <div class="flex flex-col gap-2 mb-4">
                    <label for="email" class="text-xs font-semibold text-ink-2 dark:text-ink-d2">Email</label>
                    <div class="relative flex items-center">
                        <iconify-icon icon="mage:email"
                            class="absolute start-3 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 dark:bg-neutral-700 dark:border-neutral-600 rounded-xl w-full"
                            placeholder="admin@hexagym.id" required autofocus>
                    </div>
                </div>

                {{-- Password --}}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <label for="your-password" class="text-xs font-semibold text-ink-2 dark:text-ink-d2">Kata sandi</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs font-semibold text-primary-600 hover:underline">Lupa sandi?</a>
                        @endif
                    </div>
                    <div class="relative flex items-center">
                        <iconify-icon icon="solar:lock-password-outline"
                            class="absolute start-3 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="password" id="your-password" name="password" autocomplete="current-password"
                            class="form-control h-[48px] ps-10 pe-10 border-neutral-200 bg-neutral-50 dark:bg-neutral-700 dark:border-neutral-600 rounded-xl w-full"
                            placeholder="••••••••" required>
                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-3 top-1/2 -translate-y-1/2 text-xl text-neutral-400 hover:text-neutral-600"
                            data-toggle="#your-password"></span>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2 mt-4">
                    <input name="remember" id="remember" type="checkbox"
                        class="form-check-input border border-neutral-300 dark:border-neutral-600">
                    <label for="remember" class="text-sm text-ink-2 dark:text-ink-d2 cursor-pointer select-none">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                {{-- Tombol Masuk --}}
                <button type="submit"
                    class="btn btn-primary w-full justify-center mt-6 py-[13px] rounded-xl font-bold text-[15px]"
                    style="box-shadow: 0 8px 20px -8px rgba(242, 98, 46, 0.6)">
                    Masuk
                    <iconify-icon icon="lucide:arrow-right" class="text-lg"></iconify-icon>
                </button>

            </form>

            {{-- Divider --}}
            <div class="flex items-center gap-3 text-xs text-neutral-400 dark:text-neutral-500 mt-6">
                <span class="flex-1 h-px bg-neutral-200 dark:bg-neutral-700"></span>
                atau
                <span class="flex-1 h-px bg-neutral-200 dark:bg-neutral-700"></span>
            </div>

            {{-- Register links --}}
            <div class="mt-5 text-center text-sm text-ink-2 dark:text-ink-d2 space-y-1.5">
                <p>Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:underline">Daftar</a>
                </p>
                <p>Ingin bergabung sebagai trainer?
                    <a href="{{ route('register.trainer') }}" class="font-semibold text-primary-600 hover:underline">Daftar di sini</a>
                </p>
            </div>

        </div>
    </div>

</div>

<x-script />
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.querySelectorAll('.toggle-password');

        togglePassword.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const input = document.querySelector(this.getAttribute('data-toggle'));
                if (input) {
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.add('ri-eye-off-line');
                    } else {
                        input.type = 'password';
                        this.classList.remove('ri-eye-off-line');
                    }
                }
            });
        });
    });
</script>

</body>
</html>
