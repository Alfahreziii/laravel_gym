<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="id">

<x-head/>

<body class="bg-white dark:bg-neutral-900">

<div class="flex min-h-screen">

    {{-- ──────────────────── PANEL KIRI (sticky) ──────────────────── --}}
    <div class="hidden lg:block lg:w-[480px] xl:w-[520px] flex-shrink-0">
        <div class="sticky top-0 h-screen relative overflow-hidden flex flex-col">

            <img src="{{ asset('assets/images/auth/auth-img.png') }}"
                 alt="Gym HexaGym"
                 class="absolute inset-0 w-full h-full object-cover">

            <div class="absolute inset-0 bg-gradient-to-br from-primary-600/85 via-primary-700/80 to-primary-900/90"></div>

            <div class="relative z-10 flex flex-col h-full px-10 py-10">

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

                <div class="mt-auto max-w-[380px]">
                    <div class="text-[11px] font-bold tracking-[0.14em] uppercase text-white/80">Gabung Tim Trainer</div>
                    <h1 class="font-display font-bold text-[38px] leading-[1.04] text-white mt-3"
                        style="text-wrap: balance">
                        Jadilah bagian dari tim trainer kami.
                    </h1>
                    <p class="mt-4 text-sm leading-relaxed text-white/90">
                        Daftarkan diri sebagai personal trainer profesional dan bantu member mencapai target fitness mereka.
                    </p>
                </div>

                <div class="mt-9 text-[11px] text-white/70">© 2026 HexaGym · Cabang Depok</div>

            </div>
        </div>
    </div>

    {{-- ──────────────────── PANEL KANAN (scrollable) ──────────────────── --}}
    <div class="flex-1 py-12 px-8 bg-white dark:bg-neutral-800 overflow-y-auto">
        <div class="max-w-[640px] mx-auto">

            <h2 class="font-display font-bold text-[30px] leading-[1.05] text-ink dark:text-ink-d">
                Pendaftaran Trainer 🏋️
            </h2>
            <p class="mt-1.5 text-sm text-ink-2 dark:text-ink-d2 mb-6">
                Lengkapi data berikut untuk mendaftar sebagai personal trainer HexaGym.
            </p>

            @if(session('error'))
                <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl bg-danger-50 border border-danger-200 dark:bg-danger-600/10 dark:border-danger-600/30">
                    <iconify-icon icon="lucide:alert-circle" class="text-danger-600 text-lg mt-0.5 flex-none"></iconify-icon>
                    <p class="text-[13px] text-danger-700 dark:text-danger-400">{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('register.trainer.submit') }}" enctype="multipart/form-data">
                @csrf

                {{-- ========== DATA AKUN ========== --}}
                <div class="mb-7">
                    <h6 class="text-sm font-bold mb-4 text-primary-600 flex items-center gap-2">
                        <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                        Data Akun Login
                    </h6>

                    {{-- Nama Lengkap --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="f7:person"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input name="name" value="{{ old('name') }}" required type="text"
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('name') border-danger-600 @enderror"
                            placeholder="Nama Lengkap">
                        @error('name')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="mage:email"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('email') border-danger-600 @enderror"
                            placeholder="Email">
                        @error('email')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <div class="relative">
                            <iconify-icon icon="solar:lock-password-outline"
                                class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                            <input type="password" name="password" required
                                class="form-control h-[48px] ps-10 pe-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('password') border-danger-600 @enderror"
                                id="password" placeholder="Password (min. 8 karakter)">
                            <span class="toggle-password ri-eye-line cursor-pointer absolute end-3 top-1/2 -translate-y-1/2 text-xl text-neutral-400 hover:text-neutral-600"
                                data-toggle="#password"></span>
                        </div>
                        @error('password')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-4">
                        <div class="relative">
                            <iconify-icon icon="solar:lock-password-outline"
                                class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                            <input type="password" name="password_confirmation" required
                                class="form-control h-[48px] ps-10 pe-10 border-neutral-200 bg-neutral-50 rounded-xl w-full"
                                id="password_confirmation" placeholder="Konfirmasi Password">
                            <span class="toggle-password ri-eye-line cursor-pointer absolute end-3 top-1/2 -translate-y-1/2 text-xl text-neutral-400 hover:text-neutral-600"
                                data-toggle="#password_confirmation"></span>
                        </div>
                    </div>
                </div>

                {{-- ========== DATA TRAINER ========== --}}
                <div class="mb-7">
                    <h6 class="text-sm font-bold mb-4 text-primary-600 flex items-center gap-2">
                        <iconify-icon icon="lucide:user-circle" class="text-base"></iconify-icon>
                        Data Trainer
                    </h6>

                    {{-- RFID --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="mdi:card-account-details"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="text" name="rfid" value="{{ old('rfid') }}" required
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('rfid') border-danger-600 @enderror"
                            placeholder="RFID">
                        @error('rfid')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- No Telepon --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="solar:phone-outline"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="text" name="no_telp" value="{{ old('no_telp') }}" required
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('no_telp') border-danger-600 @enderror"
                            placeholder="No Telepon">
                        @error('no_telp')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Spesialisasi --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="mdi:dumbbell"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <select name="id_specialisasi" required
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('id_specialisasi') border-danger-600 @enderror">
                            <option value="">-- Pilih Spesialisasi --</option>
                            @foreach($specialisasis as $specialisasi)
                                <option value="{{ $specialisasi->id }}" {{ old('id_specialisasi') == $specialisasi->id ? 'selected' : '' }}>
                                    {{ $specialisasi->nama_specialisasi }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_specialisasi')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Experience --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="mdi:certificate"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="text" name="experience" value="{{ old('experience') }}" required
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('experience') border-danger-600 @enderror"
                            placeholder="Pengalaman (contoh: 5 Tahun)">
                        @error('experience')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tempat Lahir --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="mdi:map-marker"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('tempat_lahir') border-danger-600 @enderror"
                            placeholder="Tempat Lahir">
                        @error('tempat_lahir')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="mdi:calendar"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('tgl_lahir') border-danger-600 @enderror">
                        @error('tgl_lahir')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="mb-4 relative">
                        <iconify-icon icon="mdi:gender-male-female"
                            class="absolute start-3 top-1/2 -translate-y-1/2 text-lg text-neutral-400 pointer-events-none z-10"></iconify-icon>
                        <select name="jenis_kelamin" required
                            class="form-control h-[48px] ps-10 border-neutral-200 bg-neutral-50 rounded-xl w-full @error('jenis_kelamin') border-danger-600 @enderror">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="mb-4">
                        <textarea name="alamat" required rows="3"
                            class="form-control border-neutral-200 bg-neutral-50 rounded-xl w-full @error('alamat') border-danger-600 @enderror"
                            placeholder="Alamat Lengkap">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Foto --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Foto Profil <span class="text-danger-600">*</span></label>
                        <input type="file" name="photo" accept="image/*" required
                            class="border border-neutral-200 bg-neutral-50 w-full rounded-xl @error('photo') border-danger-600 @enderror">
                        @error('photo')
                            <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- ========== JADWAL TRAINER ========== --}}
                <div class="mb-7">
                    <h6 class="text-sm font-bold mb-4 text-primary-600 flex items-center gap-2">
                        <iconify-icon icon="lucide:calendar-days" class="text-base"></iconify-icon>
                        Jadwal Ketersediaan
                    </h6>

                    <div id="jadwal-container">
                        <div class="jadwal-item border border-neutral-200 rounded-xl p-4 mb-3 bg-neutral-50">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-12 md:col-span-4">
                                    <label class="text-sm font-medium mb-1 block">Hari</label>
                                    <select name="jadwal[0][day_of_week]" class="form-control h-[48px] border-neutral-300 bg-white rounded-lg" required>
                                        <option value="">-- Pilih Hari --</option>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                        <option value="Sabtu">Sabtu</option>
                                        <option value="Minggu">Minggu</option>
                                    </select>
                                </div>
                                <div class="col-span-6 md:col-span-3">
                                    <label class="text-sm font-medium mb-1 block">Jam Mulai</label>
                                    <input type="time" name="jadwal[0][start_time]" class="form-control h-[48px] border-neutral-300 bg-white rounded-lg" required>
                                </div>
                                <div class="col-span-6 md:col-span-3">
                                    <label class="text-sm font-medium mb-1 block">Jam Selesai</label>
                                    <input type="time" name="jadwal[0][end_time]" class="form-control h-[48px] border-neutral-300 bg-white rounded-lg" required>
                                </div>
                                <div class="col-span-12 md:col-span-2 flex items-end">
                                    <button type="button" class="btn-remove-jadwal w-full h-[48px] text-danger-600 hover:bg-danger-600 border border-danger-600 hover:text-white rounded-lg text-sm font-medium">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-jadwal" class="text-primary-600 hover:bg-primary-600 border border-primary-600 hover:text-white rounded-lg text-sm px-4 py-2.5 font-medium inline-flex items-center gap-2">
                        <iconify-icon icon="mdi:plus"></iconify-icon> Tambah Jadwal
                    </button>
                </div>

                {{-- Terms & Conditions --}}
                <div class="mb-6">
                    <div class="form-check style-check flex items-start gap-2">
                        <input class="form-check-input border border-neutral-300 mt-1.5 @error('terms') border-danger-600 @enderror"
                            type="checkbox" name="terms" value="1" id="terms" required>
                        <label class="text-sm text-ink-2 dark:text-ink-d2" for="terms">
                            Dengan mendaftar, saya menyetujui
                            <a href="javascript:void(0)" class="text-primary-600 font-semibold hover:underline">Syarat & Ketentuan</a>
                            dan
                            <a href="javascript:void(0)" class="text-primary-600 font-semibold hover:underline">Kebijakan Privasi</a>
                        </label>
                    </div>
                    @error('terms')
                        <span class="text-danger-600 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                    class="btn btn-primary w-full justify-center py-[13px] rounded-xl font-bold text-[15px]"
                    style="box-shadow: 0 8px 20px -8px rgba(242, 98, 46, 0.6)">
                    Daftar Sebagai Trainer
                    <iconify-icon icon="lucide:arrow-right" class="text-lg"></iconify-icon>
                </button>

                <div class="mt-5 text-center text-sm text-ink-2 dark:text-ink-d2 space-y-1.5">
                    <p>Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:underline">Masuk</a>
                    </p>
                    <p>Daftar sebagai member?
                        <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:underline">Daftar di sini</a>
                    </p>
                </div>

            </form>
        </div>
    </div>

</div>

<x-script />

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle Password
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

    // Jadwal Management
    let index = 1;
    const hariOptions = `
        <option value="">-- Pilih Hari --</option>
        <option value="Senin">Senin</option>
        <option value="Selasa">Selasa</option>
        <option value="Rabu">Rabu</option>
        <option value="Kamis">Kamis</option>
        <option value="Jumat">Jumat</option>
        <option value="Sabtu">Sabtu</option>
        <option value="Minggu">Minggu</option>
    `;

    const addBtn = document.getElementById('add-jadwal');
    const container = document.getElementById('jadwal-container');

    addBtn.addEventListener('click', function() {
        const html = `
        <div class="jadwal-item border border-neutral-200 rounded-xl p-4 mb-3 bg-neutral-50">
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-12 md:col-span-4">
                    <label class="text-sm font-medium mb-1 block">Hari</label>
                    <select name="jadwal[${index}][day_of_week]" class="form-control h-[48px] border-neutral-300 bg-white rounded-lg" required>
                        ${hariOptions}
                    </select>
                </div>
                <div class="col-span-6 md:col-span-3">
                    <label class="text-sm font-medium mb-1 block">Jam Mulai</label>
                    <input type="time" name="jadwal[${index}][start_time]" class="form-control h-[48px] border-neutral-300 bg-white rounded-lg" required>
                </div>
                <div class="col-span-6 md:col-span-3">
                    <label class="text-sm font-medium mb-1 block">Jam Selesai</label>
                    <input type="time" name="jadwal[${index}][end_time]" class="form-control h-[48px] border-neutral-300 bg-white rounded-lg" required>
                </div>
                <div class="col-span-12 md:col-span-2 flex items-end">
                    <button type="button" class="btn-remove-jadwal w-full h-[48px] text-danger-600 hover:bg-danger-600 border border-danger-600 hover:text-white rounded-lg text-sm font-medium">
                        Hapus
                    </button>
                </div>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        index++;
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-jadwal') || e.target.closest('.btn-remove-jadwal')) {
            const button = e.target.classList.contains('btn-remove-jadwal') ? e.target : e.target.closest('.btn-remove-jadwal');
            button.closest('.jadwal-item').remove();
        }
    });
});
</script>

</body>
</html>
