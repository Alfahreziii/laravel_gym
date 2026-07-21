<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="id">

<x-head/>

<body class="bg-white dark:bg-neutral-900">

<div class="flex min-h-screen">

    {{-- ──────────────────── PANEL KIRI ──────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden flex-col flex-shrink-0">

        <img src="{{ asset('assets/images/auth/auth-img.png') }}"
             alt="Gym TradeFitness"
             class="absolute inset-0 w-full h-full object-cover">

        <div class="absolute inset-0 bg-gradient-to-br from-primary-600/85 via-primary-700/80 to-primary-900/90"></div>

        <div class="relative z-10 flex flex-col h-full px-11 py-11">

            <div class="flex items-center gap-3">
                <div>
                    <div class="font-display font-bold text-[26px] leading-none text-white">TradeFitness</div>
                    <div class="text-[11px] text-white/85 font-medium tracking-[0.04em] mt-0.5">Gym Management System</div>
                </div>
            </div>

            <div class="mt-auto max-w-[420px]">
                <div class="text-[11px] font-bold tracking-[0.14em] uppercase text-white/80">Daftar Akun</div>
                <h1 class="font-display font-bold text-[44px] leading-[1.04] text-white mt-3"
                    style="text-wrap: balance">
                    Mulai perjalanan fitness Anda.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/90">
                    Daftarkan diri dan dapatkan akses ke kelas, trainer, serta tracking kehadiran di TradeFitness.
                </p>
            </div>

            <div class="mt-9 text-[11px] text-white/70">© 2026 HexaMultiDigital</div>

        </div>
    </div>

    {{-- ──────────────────── PANEL KANAN ──────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white dark:bg-neutral-800">
        <div class="w-full max-w-[384px]">

            <h2 class="font-display font-bold text-[34px] leading-[1.05] text-ink dark:text-ink-d">
                Buat akun baru 💪
            </h2>
            <p class="mt-2 text-sm text-ink-2 dark:text-ink-d2">
                Isi data di bawah untuk mulai bergabung dengan TradeFitness.
            </p>

            @if ($errors->any())
                <div class="mt-5 flex items-start gap-3 px-4 py-3 rounded-xl bg-danger-50 border border-danger-200 dark:bg-danger-600/10 dark:border-danger-600/30">
                    <iconify-icon icon="lucide:alert-circle" class="text-danger-600 text-lg mt-0.5 flex-none"></iconify-icon>
                    <div class="text-[13px] text-danger-700 dark:text-danger-400">
                        @error('name'){{ $message }} @enderror
                        @error('email'){{ $message }} @enderror
                        @error('password'){{ $message }} @enderror
                        @error('password_confirmation'){{ $message }}@enderror
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="mt-6">
                @csrf

                {{-- Nama Lengkap --}}
                <div class="flex flex-col gap-2 mb-4">
                    <label for="name" class="text-xs font-semibold text-ink-2 dark:text-ink-d2">Nama Lengkap</label>
                    <div class="relative flex items-center">
                        <iconify-icon icon="f7:person"
                            class="absolute start-3 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input name="name" id="name" autocomplete="name" value="{{ old('name') }}" required type="text"
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 dark:bg-neutral-700 dark:border-neutral-600 rounded-xl w-full"
                            placeholder="Nama lengkap Anda" autofocus>
                    </div>
                </div>

                {{-- Email --}}
                <div class="flex flex-col gap-2 mb-4">
                    <label for="email" class="text-xs font-semibold text-ink-2 dark:text-ink-d2">Email</label>
                    <div class="relative flex items-center">
                        <iconify-icon icon="mage:email"
                            class="absolute start-3 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="email" name="email" id="email" autocomplete="email" required value="{{ old('email') }}"
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 dark:bg-neutral-700 dark:border-neutral-600 rounded-xl w-full"
                            placeholder="email@contoh.com">
                    </div>
                </div>

                {{-- Kata Sandi --}}
                <div class="flex flex-col gap-2 mb-4">
                    <label for="password" class="text-xs font-semibold text-ink-2 dark:text-ink-d2">Kata sandi</label>
                    <div class="relative flex items-center">
                        <iconify-icon icon="solar:lock-password-outline"
                            class="absolute start-3 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="password" name="password" id="password" required autocomplete="new-password"
                            class="form-control h-[48px] ps-10 pe-10 border-neutral-200 bg-neutral-50 dark:bg-neutral-700 dark:border-neutral-600 rounded-xl w-full"
                            placeholder="Min. 8 karakter">
                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-3 top-1/2 -translate-y-1/2 text-xl text-neutral-400 hover:text-neutral-600"
                            data-toggle="#password"></span>
                    </div>
                </div>

                {{-- Konfirmasi Kata Sandi --}}
                <div class="flex flex-col gap-2 mb-5">
                    <label for="password_confirmation" class="text-xs font-semibold text-ink-2 dark:text-ink-d2">Konfirmasi kata sandi</label>
                    <div class="relative flex items-center">
                        <iconify-icon icon="solar:lock-password-outline"
                            class="absolute start-3 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                            class="form-control h-[48px] ps-10 pe-10 border-neutral-200 bg-neutral-50 dark:bg-neutral-700 dark:border-neutral-600 rounded-xl w-full"
                            placeholder="Ulangi kata sandi">
                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-3 top-1/2 -translate-y-1/2 text-xl text-neutral-400 hover:text-neutral-600"
                            data-toggle="#password_confirmation"></span>
                    </div>
                </div>

                {{-- T&C --}}
                <div class="flex items-start gap-2">
                    <input class="form-check-input border border-neutral-300 dark:border-neutral-600 mt-0.5 flex-shrink-0"
                        type="checkbox" value="" id="condition">
                    <label class="text-sm text-ink-2 dark:text-ink-d2 leading-relaxed" for="condition">
                        Dengan mendaftar, saya menyetujui
                        <a href="javascript:void(0)" class="text-primary-600 font-semibold hover:underline">Syarat & Ketentuan</a>
                        dan
                        <a href="javascript:void(0)" class="text-primary-600 font-semibold hover:underline">Kebijakan Privasi</a>
                    </label>
                </div>

                <button type="submit"
                    class="btn btn-primary w-full justify-center mt-6 py-[13px] rounded-xl font-bold text-[15px]"
                    style="box-shadow: 0 8px 20px -8px rgba(242, 98, 46, 0.6)">
                    Daftar Sekarang
                    <iconify-icon icon="lucide:arrow-right" class="text-lg"></iconify-icon>
                </button>

            </form>

            <div class="mt-5 text-center text-sm text-ink-2 dark:text-ink-d2">
                <p>Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:underline">Masuk</a>
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
