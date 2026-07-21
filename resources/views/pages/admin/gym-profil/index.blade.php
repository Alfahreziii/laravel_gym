@extends('layout.layout')
@php
    $title = 'Profil Gym';
    $subTitle = 'Identitas Bisnis';
@endphp

@section('content')
    @if (session('success'))
        <div
            class="alert alert-success bg-success-50 dark:bg-success-600/25
        text-success-600 dark:text-success-400 border-success-50
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-4">
                {{ session('success') }}
            </div>
            <button class="remove-button text-success-600 text-2xl">
                <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div
            class="alert alert-danger bg-danger-100 dark:bg-danger-600/25
        text-danger-600 dark:text-danger-400 border-danger-100
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
            {{ session('error') }}
            <button class="remove-button text-danger-600 text-2xl">
                <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-5">

        {{-- ========== IDENTITAS GYM (editable) ========== --}}
        <div class="col-span-12 lg:col-span-7">
            <div class="card border-0 h-full">
                <div class="card-header">
                    <h6 class="text-lg font-semibold mb-0">Identitas Gym</h6>
                    <p class="text-sm text-neutral-500 mb-0">Nama, logo, dan kontak yang tampil di aplikasi ini.</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('gym_profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-12 gap-4">

                            {{-- Logo --}}
                            <div class="col-span-12">
                                <label class="form-label">Logo Gym</label>
                                <div class="flex items-center gap-4 mb-2">
                                    @if ($tenant->logo)
                                        <img src="{{ asset('storage/' . $tenant->logo) }}" alt="Logo saat ini"
                                            class="w-20 h-20 object-cover rounded-lg border border-neutral-200">
                                    @else
                                        <div
                                            class="w-20 h-20 rounded-lg border border-neutral-200 bg-neutral-50 flex items-center justify-center text-neutral-400">
                                            <iconify-icon icon="solar:gallery-broken" class="text-2xl"></iconify-icon>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <input
                                            class="border border-neutral-200 w-full rounded-lg @error('logo') is-invalid @enderror"
                                            type="file" name="logo" accept="image/*">
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Kosongkan jika tidak ingin diubah. JPG/PNG, maks
                                            2MB.</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Nama Gym --}}
                            <div class="col-span-12">
                                <label class="form-label">Nama Gym <span class="text-danger-600">*</span></label>
                                <input type="text" name="nama_gym"
                                    class="form-control @error('nama_gym') is-invalid @enderror"
                                    value="{{ old('nama_gym', $tenant->nama_gym) }}" required>
                                @error('nama_gym')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-span-12 md:col-span-6">
                                <label class="form-label">Email <span class="text-danger-600">*</span></label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $tenant->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- No HP --}}
                            <div class="col-span-12 md:col-span-6">
                                <label class="form-label">No. HP <span class="text-danger-600">*</span></label>
                                <input type="text" name="no_hp"
                                    class="form-control @error('no_hp') is-invalid @enderror"
                                    value="{{ old('no_hp', $tenant->no_hp) }}" required>
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Alamat --}}
                            <div class="col-span-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $tenant->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tombol --}}
                            <div class="col-span-12 mt-2">
                                <button type="submit" class="btn btn-primary-600">Simpan Perubahan</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========== INFO LANGGANAN (read-only) ========== --}}
        <div class="col-span-12 lg:col-span-5">
            <div class="card border-0 h-full">
                <div class="card-header">
                    <h6 class="text-lg font-semibold mb-0">Info Langganan</h6>
                    <p class="text-sm text-neutral-500 mb-0">Dikelola oleh admin platform, tidak bisa diubah dari sini.
                    </p>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-12 gap-4">

                        <div class="col-span-12">
                            <label class="form-label">Subdomain</label>
                            <p class="form-control bg-neutral-50 font-mono">{{ $tenant->subdomain }}</p>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Status</label>
                            <p class="form-control bg-neutral-50">
                                <span
                                    class="bg-success-100 text-success-600 px-4 py-1 rounded-full font-medium text-sm">
                                    Aktif
                                </span>
                            </p>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Paket</label>
                            <p class="form-control bg-neutral-50">{{ $tenant->package->label ?? '-' }}</p>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Mulai Langganan</label>
                            <p class="form-control bg-neutral-50">{{ $tenant->tgl_mulai?->format('d M Y') ?? '-' }}</p>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <label class="form-label">Berakhir</label>
                            <p class="form-control bg-neutral-50">{{ $tenant->tgl_selesai?->format('d M Y') ?? '-' }}</p>
                        </div>

                        <div class="col-span-12">
                            <label class="form-label">Modul Aktif</label>
                            <div class="flex flex-wrap gap-2">
                                @php $mod = $tenant->module; @endphp
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold {{ $mod?->trainer ? 'bg-info-100 text-info-600' : 'bg-neutral-100 text-neutral-400' }}">
                                    Trainer
                                </span>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold {{ $mod?->pos ? 'bg-info-100 text-info-600' : 'bg-neutral-100 text-neutral-400' }}">
                                    POS
                                </span>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold {{ $mod?->keuangan ? 'bg-info-100 text-info-600' : 'bg-neutral-100 text-neutral-400' }}">
                                    Keuangan
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.remove-button').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    this.closest('.alert')?.remove();
                });
            });
        });
    </script>
@endsection
