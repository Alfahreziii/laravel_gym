@extends('layout.layout')
@php
    $title='View Profile';
    $subTitle = 'View Profile';
    $script ='<script>
                    // ======================== Upload Image Start =====================
                    function readURL(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
                                $("#imagePreview").hide();
                                $("#imagePreview").fadeIn(650);
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                    $("#imageUpload").change(function() {
                        readURL(this);
                    });
                    // ======================== Upload Image End =====================

                    // ================== Password Show Hide Js Start ==========
                    function initializePasswordToggle(toggleSelector) {
                        $(toggleSelector).on("click", function() {
                            $(this).toggleClass("ri-eye-off-line");
                            var input = $($(this).attr("data-toggle"));
                            if (input.attr("type") === "password") {
                                input.attr("type", "text");
                            } else {
                                input.attr("type", "password");
                            }
                        });
                    }
                    // Call the function
                    initializePasswordToggle(".toggle-password");
                    // ========================= Password Show Hide Js End ===========================
            </script>';
@endphp

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4">
            <div class="user-grid-card relative border border-neutral-200 rounded-2xl overflow-hidden bg-white h-full">
                <div class="pb-6 ms-6 mb-6 me-6 mt-5">
                    <div class="text-center border-b border-neutral-200 pb-4">
                        @if($user->photo)
                            <img src="{{ asset('storage/' . $user->photo) }}" 
                                 alt="{{ $user->name }}" 
                                 class="border br-white border-width-2-px w-200-px h-[200px] rounded-full object-cover mx-auto">
                        @else
                            <img src="{{ asset('assets/images/user-grid/user-grid-img14.png') }}" 
                                 alt="Default Avatar" 
                                 class="border br-white border-width-2-px w-200-px h-[200px] rounded-full object-cover mx-auto">
                        @endif
                        
                        <h6 class="mb-0 mt-4">{{ $user->name }}</h6>
                        <span class="text-secondary-light mb-4">{{ $user->email }}</span>
                        
                        @if($user->email_verified_at)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-success-100 text-success-600 text-sm mt-2">
                                <i class="ri-checkbox-circle-line"></i> Email Terverifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-warning-100 text-warning-600 text-sm mt-2">
                                <i class="ri-error-warning-line"></i> Email Belum Terverifikasi
                            </span>
                        @endif
                    </div>
                    
                    <div class="mt-6">
                        <h6 class="text-xl mb-4">Personal Info</h6>
                        <ul>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[35%] text-base font-semibold text-neutral-600">Full Name</span>
                                <span class="w-[65%] text-secondary-light font-medium">: {{ $user->name }}</span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[35%] text-base font-semibold text-neutral-600">Email</span>
                                <span class="w-[65%] text-secondary-light font-medium">: {{ $user->email }}</span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[35%] text-base font-semibold text-neutral-600">Role</span>
                                <span class="w-[65%] text-secondary-light font-medium">
                                    : 
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-primary-100 text-primary-600 text-sm">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </span>
                            </li>
                            <li class="flex items-center gap-1 mb-3">
                                <span class="w-[35%] text-base font-semibold text-neutral-600">Member Since</span>
                                <span class="w-[65%] text-secondary-light font-medium">: {{ $user->created_at->format('d M Y') }}</span>
                            </li>
                            
                            @if($user->isTrainer() && $user->trainer)
                                <li class="flex items-center gap-1 mb-3">
                                    <span class="w-[35%] text-base font-semibold text-neutral-600">RFID</span>
                                    <span class="w-[65%] text-secondary-light font-medium">: {{ $user->trainer->rfid }}</span>
                                </li>
                                <li class="flex items-center gap-1 mb-3">
                                    <span class="w-[35%] text-base font-semibold text-neutral-600">Phone</span>
                                    <span class="w-[65%] text-secondary-light font-medium">: {{ $user->trainer->no_telp }}</span>
                                </li>
                                <li class="flex items-center gap-1 mb-3">
                                    <span class="w-[35%] text-base font-semibold text-neutral-600">Specialization</span>
                                    <span class="w-[65%] text-secondary-light font-medium">: {{ $user->trainer->specialisasi->nama_specialisasi ?? '-' }}</span>
                                </li>
                                <li class="flex items-center gap-1 mb-3">
                                    <span class="w-[35%] text-base font-semibold text-neutral-600">Experience</span>
                                    <span class="w-[65%] text-secondary-light font-medium">: {{ $user->trainer->experience }}</span>
                                </li>
                                <li class="flex items-center gap-1 mb-3">
                                    <span class="w-[35%] text-base font-semibold text-neutral-600">Status</span>
                                    <span class="w-[65%] text-secondary-light font-medium">
                                        : 
                                        @if($user->trainer->status == 'aktif')
                                            <span class="px-2 py-1 rounded bg-success-100 text-success-600 text-sm">Aktif</span>
                                        @else
                                            <span class="px-2 py-1 rounded bg-danger-100 text-danger-600 text-sm">{{ ucfirst($user->trainer->status) }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endif
                            
                            @if($user->last_activity)
                                <li class="flex items-center gap-1">
                                    <span class="w-[35%] text-base font-semibold text-neutral-600">Last Activity</span>
                                    <span class="w-[65%] text-secondary-light font-medium">: {{ $user->last_activity->diffForHumans() }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-span-12 lg:col-span-8">
            <div class="card h-full border-0">
                <div class="card-body p-6">
                    
                    {{-- Alert Messages --}}
                    @if(session('success'))
                        <div class="bg-success-100 border border-success-600 text-success-600 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-danger-100 border border-danger-600 text-danger-600 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-danger-100 border border-danger-600 text-danger-600 px-4 py-3 rounded mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <ul class="tab-style-gradient flex flex-wrap text-sm font-medium text-center mb-5" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                        <li class="" role="presentation">
                            <button class="py-2.5 px-4 border-t-2 font-semibold text-base inline-flex items-center gap-3 text-neutral-600" id="edit-profile-tab" data-tabs-target="#edit-profile" type="button" role="tab" aria-controls="edit-profile" aria-selected="true">
                                Edit Profile
                            </button>
                        </li>
                        <li class="" role="presentation">
                            <button class="py-2.5 px-4 border-t-2 font-semibold text-base inline-flex items-center gap-3 text-neutral-600 hover:text-gray-600 hover:border-gray-300" id="change-password-tab" data-tabs-target="#change-password" type="button" role="tab" aria-controls="change-password" aria-selected="false">
                                Change Password
                            </button>
                        </li>
                    </ul>

                    <div id="default-tab-content">
                        {{-- Edit Profile Tab --}}
                        <div class="" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
                            <h6 class="text-base text-neutral-600 mb-4">Profile Image</h6>
                            
                            @if($user->isTrainer() && $user->trainer)
                                {{-- ========== FORM UNTUK TRAINER ========== --}}
                                <form action="{{ route('users.trainer.profile.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <!-- Upload Image Start -->
                                    <div class="mb-6 mt-4">
                                        <div class="avatar-upload">
                                            <div class="avatar-edit absolute bottom-0 end-0 me-6 mt-4 z-[1] cursor-pointer">
                                                <input type='file' name="photo" id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                                <label for="imageUpload" class="w-8 h-8 flex justify-center items-center bg-primary-100 text-primary-600 border border-primary-600 hover:bg-primary-100 text-lg rounded-full cursor-pointer">
                                                    <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                                </label>
                                            </div>
                                            <div class="avatar-preview">
                                                <div id="imagePreview" style="background-image: url('{{ $user->photo ? asset('storage/' . $user->photo) : asset('assets/images/user-grid/user-grid-img14.png') }}');">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($user->photo)
                                            <div class="mt-3 text-center">
                                                <button type="button" onclick="deletePhoto()" class="text-danger-600 text-sm hover:underline">
                                                    <i class="ri-delete-bin-line"></i> Hapus Foto
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <!-- Upload Image End -->
                                    
                                    {{-- DATA USER --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-x-6">
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="name" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Full Name <span class="text-danger-600">*</span>
                                                </label>
                                                <input type="text" 
                                                    class="form-control rounded-lg @error('name') border-danger-600 @enderror" 
                                                    id="name" 
                                                    name="name"
                                                    value="{{ old('name', $user->name) }}"
                                                    placeholder="Enter Full Name"
                                                    required>
                                                @error('name')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="email" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Email <span class="text-danger-600">*</span>
                                                </label>
                                                <input type="email" 
                                                    class="form-control rounded-lg @error('email') border-danger-600 @enderror" 
                                                    id="email" 
                                                    name="email"
                                                    value="{{ old('email', $user->email) }}"
                                                    placeholder="Enter email address"
                                                    required>
                                                @error('email')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- DATA TRAINER --}}
                                    <div class="mb-4">
                                        <h6 class="text-base text-neutral-600 mb-3 font-semibold border-b pb-2">Data Trainer</h6>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-x-6">
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="no_telp" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    No Telepon <span class="text-danger-600">*</span>
                                                </label>
                                                <input type="text" 
                                                    class="form-control rounded-lg @error('no_telp') border-danger-600 @enderror" 
                                                    id="no_telp" 
                                                    name="no_telp"
                                                    value="{{ old('no_telp', $user->trainer->no_telp) }}"
                                                    placeholder="Enter Phone Number"
                                                    required>
                                                @error('no_telp')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="jenis_kelamin" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Jenis Kelamin <span class="text-danger-600">*</span>
                                                </label>
                                                <select class="form-control rounded-lg @error('jenis_kelamin') border-danger-600 @enderror" 
                                                        id="jenis_kelamin" 
                                                        name="jenis_kelamin" 
                                                        required>
                                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                                    <option value="Laki-laki" {{ old('jenis_kelamin', $user->trainer->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                                    <option value="Perempuan" {{ old('jenis_kelamin', $user->trainer->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                                </select>
                                                @error('jenis_kelamin')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="tempat_lahir" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Tempat Lahir <span class="text-danger-600">*</span>
                                                </label>
                                                <input type="text" 
                                                    class="form-control rounded-lg @error('tempat_lahir') border-danger-600 @enderror" 
                                                    id="tempat_lahir" 
                                                    name="tempat_lahir"
                                                    value="{{ old('tempat_lahir', $user->trainer->tempat_lahir) }}"
                                                    placeholder="Enter Birth Place"
                                                    required>
                                                @error('tempat_lahir')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="tgl_lahir" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Tanggal Lahir <span class="text-danger-600">*</span>
                                                </label>
                                                <input type="date" 
                                                    class="form-control rounded-lg @error('tgl_lahir') border-danger-600 @enderror" 
                                                    id="tgl_lahir" 
                                                    name="tgl_lahir"
                                                    value="{{ old('tgl_lahir', $user->trainer->tgl_lahir?->format('Y-m-d')) }}"
                                                    required>
                                                @error('tgl_lahir')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12">
                                            <div class="mb-5">
                                                <label for="alamat" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Alamat <span class="text-danger-600">*</span>
                                                </label>
                                                <textarea class="form-control rounded-lg @error('alamat') border-danger-600 @enderror" 
                                                        id="alamat" 
                                                        name="alamat" 
                                                        rows="3" 
                                                        placeholder="Enter Address"
                                                        required>{{ old('alamat', $user->trainer->alamat) }}</textarea>
                                                @error('alamat')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12">
                                            <div class="mb-5">
                                                <label for="experience" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Pengalaman
                                                </label>
                                                <input type="text" 
                                                    class="form-control rounded-lg @error('experience') border-danger-600 @enderror" 
                                                    id="experience" 
                                                    name="experience"
                                                    value="{{ old('experience', $user->trainer->experience) }}"
                                                    placeholder="Enter Experience">
                                                @error('experience')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12">
                                            <div class="mb-5">
                                                <label for="keterangan" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Keterangan
                                                </label>
                                                <input type="text" 
                                                    class="form-control rounded-lg @error('keterangan') border-danger-600 @enderror" 
                                                    id="keterangan" 
                                                    name="keterangan"
                                                    value="{{ old('keterangan', $user->trainer->keterangan) }}"
                                                    placeholder="Enter Note">
                                                @error('keterangan')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('trainer.dashboard') }}" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-base px-14 py-[11px] rounded-lg">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary border border-primary-600 text-base px-14 py-3 rounded-lg">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            @else
                                {{-- ========== FORM UNTUK USER BIASA ========== --}}
                                <form action="{{ route('users.profile.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <!-- Upload Image Start -->
                                    <div class="mb-6 mt-4">
                                        <div class="avatar-upload">
                                            <div class="avatar-edit absolute bottom-0 end-0 me-6 mt-4 z-[1] cursor-pointer">
                                                <input type='file' name="photo" id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                                <label for="imageUpload" class="w-8 h-8 flex justify-center items-center bg-primary-100 text-primary-600 border border-primary-600 hover:bg-primary-100 text-lg rounded-full cursor-pointer">
                                                    <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                                </label>
                                            </div>
                                            <div class="avatar-preview">
                                                <div id="imagePreview" style="background-image: url('{{ $user->photo ? asset('storage/' . $user->photo) : asset('assets/images/user-grid/user-grid-img14.png') }}');">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($user->photo)
                                            <div class="mt-3 text-center">
                                                <button type="button" onclick="deletePhoto()" class="text-danger-600 text-sm hover:underline">
                                                    <i class="ri-delete-bin-line"></i> Hapus Foto
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    <!-- Upload Image End -->
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-x-6">
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="name" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Full Name <span class="text-danger-600">*</span>
                                                </label>
                                                <input type="text" 
                                                    class="form-control rounded-lg @error('name') border-danger-600 @enderror" 
                                                    id="name" 
                                                    name="name"
                                                    value="{{ old('name', $user->name) }}"
                                                    placeholder="Enter Full Name"
                                                    required>
                                                @error('name')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="mb-5">
                                                <label for="email" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Email <span class="text-danger-600">*</span>
                                                </label>
                                                <input type="email" 
                                                    class="form-control rounded-lg @error('email') border-danger-600 @enderror" 
                                                    id="email" 
                                                    name="email"
                                                    value="{{ old('email', $user->email) }}"
                                                    placeholder="Enter email address"
                                                    required>
                                                @error('email')
                                                    <span class="text-danger-600 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('dashboard') }}" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-base px-14 py-[11px] rounded-lg">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary border border-primary-600 text-base px-14 py-3 rounded-lg">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                        {{-- Change Password Tab --}}
                        <div class="hidden" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                            <form action="{{ route('users.profile.password.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-5">
                                    <label for="current-password" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                        Current Password <span class="text-danger-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               class="form-control rounded-lg @error('current_password') border-danger-600 @enderror" 
                                               id="current-password" 
                                               name="current_password"
                                               placeholder="Enter Current Password"
                                               required>
                                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#current-password"></span>
                                    </div>
                                    @error('current_password')
                                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="mb-5">
                                    <label for="password" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                        New Password <span class="text-danger-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               class="form-control rounded-lg @error('password') border-danger-600 @enderror" 
                                               id="password" 
                                               name="password"
                                               placeholder="Enter New Password"
                                               required>
                                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#password"></span>
                                    </div>
                                    @error('password')
                                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                                    @enderror
                                    <small class="text-secondary-light">Minimal 8 karakter</small>
                                </div>
                                
                                <div class="mb-5">
                                    <label for="password_confirmation" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                        Confirm New Password <span class="text-danger-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               class="form-control rounded-lg" 
                                               id="password_confirmation" 
                                               name="password_confirmation"
                                               placeholder="Confirm New Password"
                                               required>
                                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#password_confirmation"></span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-center gap-3">
                                    <button type="reset" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-base px-14 py-[11px] rounded-lg">
                                        Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-base px-14 py-3 rounded-lg">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form untuk delete photo --}}
    <form id="deletePhotoForm" action="{{ route('users.profile.photo.delete') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function deletePhoto() {
            if (confirm('Apakah Anda yakin ingin menghapus foto profile?')) {
                document.getElementById('deletePhotoForm').submit();
            }
        }
    </script>

@endsection