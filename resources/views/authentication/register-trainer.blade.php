<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<x-head/>

<body class="dark:bg-neutral-800 bg-neutral-100">

    <style>
        .auth-section {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-image: url('{{ asset('assets/images/auth/auth-img.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .auth-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        
        .auth-form-container {
            position: relative;
            z-index: 2;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2.5rem;
        }
        
        /* Custom Scrollbar */
        .auth-form-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .auth-form-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .auth-form-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .auth-form-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        @media (max-width: 768px) {
            .auth-section {
                padding: 1rem;
            }
            
            .auth-form-container {
                padding: 1.5rem;
                border-radius: 16px;
                max-height: 95vh;
            }
        }
    </style>

    <section class="auth-section">
        <div class="auth-form-container">
            <div>
                <div>
                    <a href="{{ route('index') }}" class="mb-2.5 max-w-[135px]">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="">
                    </a>
                    <h4 class="mb-3">Pendaftaran Trainer</h4>
                    <p class="mb-6 text-secondary-light text-lg">Daftar sebagai Personal Trainer</p>
                </div>

                @if(session('error'))
                    <div class="bg-danger-100 border border-danger-600 text-danger-600 px-4 py-3 rounded-lg mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.trainer.submit') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- ========== DATA AKUN ========== --}}
                    <div class="mb-6">
                        <h6 class="text-md font-semibold mb-3 text-primary-600">📋 Data Akun Login</h6>
                        
                        {{-- Nama Lengkap --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="f7:person"></iconify-icon>
                            </span>
                            <input name="name" value="{{ old('name') }}" required type="text" 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('name') border-danger-600 @enderror" 
                                placeholder="Nama Lengkap">
                            @error('name')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="mage:email"></iconify-icon>
                            </span>
                            <input type="email" name="email" required value="{{ old('email') }}" 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('email') border-danger-600 @enderror" 
                                placeholder="Email">
                            @error('email')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-4">
                            <div class="relative">
                                <div class="icon-field">
                                    <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                        <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                    </span>
                                    <input type="password" name="password" required 
                                        class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('password') border-danger-600 @enderror" 
                                        id="password" placeholder="Password (min. 8 karakter)">
                                </div>
                                <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#password"></span>
                            </div>
                            @error('password')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="mb-4">
                            <div class="relative">
                                <div class="icon-field">
                                    <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                        <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                    </span>
                                    <input type="password" name="password_confirmation" required 
                                        class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl" 
                                        id="password_confirmation" placeholder="Konfirmasi Password">
                                </div>
                                <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#password_confirmation"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ========== DATA TRAINER ========== --}}
                    <div class="mb-6">
                        <h6 class="text-md font-semibold mb-3 text-primary-600">👤 Data Trainer</h6>

                        {{-- RFID --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="mdi:card-account-details"></iconify-icon>
                            </span>
                            <input type="text" name="rfid" value="{{ old('rfid') }}" required 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('rfid') border-danger-600 @enderror" 
                                placeholder="RFID">
                            @error('rfid')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- No Telepon --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="solar:phone-outline"></iconify-icon>
                            </span>
                            <input type="text" name="no_telp" value="{{ old('no_telp') }}" required 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('no_telp') border-danger-600 @enderror" 
                                placeholder="No Telepon">
                            @error('no_telp')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Spesialisasi --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="mdi:dumbbell"></iconify-icon>
                            </span>
                            <select name="id_specialisasi" required 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('id_specialisasi') border-danger-600 @enderror">
                                <option value="">-- Pilih Spesialisasi --</option>
                                @foreach($specialisasis as $specialisasi)
                                    <option value="{{ $specialisasi->id }}" {{ old('id_specialisasi') == $specialisasi->id ? 'selected' : '' }}>
                                        {{ $specialisasi->nama_specialisasi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_specialisasi')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Experience --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="mdi:certificate"></iconify-icon>
                            </span>
                            <input type="text" name="experience" value="{{ old('experience') }}" required 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('experience') border-danger-600 @enderror" 
                                placeholder="Pengalaman (contoh: 5 Tahun)">
                            @error('experience')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Tempat Lahir --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="mdi:map-marker"></iconify-icon>
                            </span>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('tempat_lahir') border-danger-600 @enderror" 
                                placeholder="Tempat Lahir">
                            @error('tempat_lahir')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="mdi:calendar"></iconify-icon>
                            </span>
                            <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('tgl_lahir') border-danger-600 @enderror">
                            @error('tgl_lahir')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="icon-field mb-4 relative">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="mdi:gender-male-female"></iconify-icon>
                            </span>
                            <select name="jenis_kelamin" required 
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl @error('jenis_kelamin') border-danger-600 @enderror">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="mb-4">
                            <textarea name="alamat" required rows="3" 
                                class="form-control border-neutral-300 bg-neutral-50 rounded-xl @error('alamat') border-danger-600 @enderror" 
                                placeholder="Alamat Lengkap">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Foto --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Foto Profil <span class="text-danger-600">*</span></label>
                            <input type="file" name="photo" accept="image/*" required 
                                class="border border-neutral-300 bg-neutral-50 w-full rounded-xl @error('photo') border-danger-600 @enderror">
                            @error('photo')
                                <span class="text-danger-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- ========== JADWAL TRAINER ========== --}}
                    <div class="mb-6">
                        <h6 class="text-md font-semibold mb-3 text-primary-600">📅 Jadwal Ketersediaan</h6>
                        
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
                    <div class="mt-6">
                        <div class="form-check style-check flex items-start gap-2">
                            <input class="form-check-input border border-neutral-300 mt-1.5 @error('terms') border-danger-600 @enderror" 
                                type="checkbox" name="terms" value="1" id="terms" required>
                            <label class="text-sm" for="terms">
                                Dengan mendaftar, saya menyetujui
                                <a href="javascript:void(0)" class="text-primary-600 font-semibold">Syarat & Ketentuan</a> 
                                dan 
                                <a href="javascript:void(0)" class="text-primary-600 font-semibold">Kebijakan Privasi</a>
                            </label>
                        </div>
                        @error('terms')
                            <span class="text-danger-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-6">
                        Daftar Sebagai Trainer
                    </button>
                    
                    <div class="mt-6 text-center text-sm">
                        <p class="mb-0">Sudah punya akun? <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:underline">Login</a></p>
                        <p class="mb-0 mt-2">Daftar sebagai member? <a href="{{ route('register') }}" class="text-primary-600 font-semibold hover:underline">Daftar di sini</a></p>
                    </div>

                </form>
            </div>
        </div>
    </section>

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