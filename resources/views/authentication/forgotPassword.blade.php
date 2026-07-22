<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="id">

<x-head />

<body class="bg-white dark:bg-neutral-900">

{{-- Dark mode toggle --}}
<button type="button" id="theme-toggle" class="navbar-ctrl-btn fixed top-5 right-5 z-30" title="Ganti tema">
    <x-icon.moon id="theme-toggle-dark-icon" class="text-[19px]" />
    <iconify-icon id="theme-toggle-light-icon" icon="ph:sun-bold" class="text-[19px] hidden"></iconify-icon>
</button>

<div class="flex min-h-screen">

    {{-- ──────────────────── PANEL KIRI ──────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden flex-col flex-shrink-0">

        <img src="{{ asset('assets/images/auth/forgot-pass-img.png') }}"
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
                <div class="text-[11px] font-bold tracking-[0.14em] uppercase text-white/80">Pemulihan Akun</div>
                <h1 class="font-display font-bold text-[44px] leading-[1.04] text-white mt-3"
                    style="text-wrap: balance">
                    Kami bantu pulihkan aksesnya.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/90">
                    Lupa kata sandi bukan masalah — masukkan email dan kami kirimkan tautan reset langsung ke kotak masuk Anda.
                </p>
            </div>

            <div class="mt-9 text-[11px] text-white/70">© 2026 HexaMultiDigital</div>

        </div>
    </div>

    {{-- ──────────────────── PANEL KANAN ──────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white dark:bg-neutral-800">
        <div class="w-full max-w-[384px]">

            <h2 class="font-display font-bold text-[34px] leading-[1.05] text-ink dark:text-ink-d">
                Lupa kata sandi? 🔑
            </h2>
            <p class="mt-2 text-sm text-ink-2 dark:text-ink-d2">
                Masukkan email terdaftar untuk menerima tautan reset kata sandi.
            </p>

            <form method="POST" action="{{ route('password.email') }}" class="mt-7">
                @csrf

                <div class="flex flex-col gap-2 mb-5">
                    <label for="email" class="text-xs font-semibold text-ink-2 dark:text-ink-d2">Email</label>
                    <div class="relative flex items-center">
                        <iconify-icon icon="mage:email"
                            class="absolute start-3 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="email" name="email" id="email" required autofocus
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 dark:bg-neutral-700 dark:border-neutral-600 rounded-xl w-full"
                            placeholder="email@contoh.com">
                    </div>
                </div>

                <button type="submit"
                    class="btn btn-primary w-full justify-center py-[13px] rounded-xl font-bold text-[15px]"
                    style="box-shadow: 0 8px 20px -8px rgba(242, 98, 46, 0.6)">
                    Kirim Tautan Reset
                    <iconify-icon icon="lucide:send" class="text-lg"></iconify-icon>
                </button>

            </form>

            <div class="mt-5 text-center text-sm text-ink-2 dark:text-ink-d2">
                <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:underline">
                    ← Kembali ke halaman masuk
                </a>
            </div>

        </div>
    </div>

</div>

<x-script/>

{{-- Modal konfirmasi email terkirim --}}
<x-modal id="popup-modal" title="Email Terkirim">
    <x-slot:body>
        <div class="text-center py-2">
            <iconify-icon icon="lucide:mail-check" class="text-5xl text-success-500 mb-3"></iconify-icon>
            <p class="text-sm text-ink-2 dark:text-ink-d2 mb-0">
                Tautan reset kata sandi telah dikirim ke email Anda. Silakan cek kotak masuk atau folder spam.
            </p>
            <button type="button" data-close-modal="popup-modal"
                class="btn btn-primary w-full justify-center py-[13px] rounded-xl font-bold mt-6">
                Tutup
            </button>
        </div>
    </x-slot:body>
</x-modal>

@if (session('status'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        HexaModal.show('popup-modal');
    });
</script>
@endif

</body>
</html>
