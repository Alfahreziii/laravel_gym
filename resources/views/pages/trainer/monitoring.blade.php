@extends('layout.layout')

@php
    $title = 'Monitoring Training';
    $subTitle = 'Monitoring Training';
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

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">Monitoring Training Session</h6>
                    <a href="{{ route('trainer.dashboard') }}" class="text-neutral-600 hover:text-primary-600">
                        <iconify-icon icon="ion:arrow-back" class="text-2xl"></iconify-icon>
                    </a>
                </div>

                @if (!$activeMember)
                    <div class="card-body text-center py-12">
                        <iconify-icon icon="mdi:clipboard-alert-outline"
                            class="text-6xl text-neutral-400 mb-4"></iconify-icon>
                        <h5 class="text-xl font-semibold text-neutral-700 mb-2">Tidak Ada Sesi Training Aktif</h5>
                        <p class="text-neutral-600 mb-4">Mulai sesi training terlebih dahulu untuk menggunakan fitur
                            monitoring.</p>
                        <a href="{{ route('trainer.dashboard') }}" class="btn btn-primary">
                            Kembali ke Dashboard
                        </a>
                    </div>
                @else
                    <div class="card-body">
                        <!-- Info Member -->
                        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h6 class="font-semibold text-lg text-neutral-900 mb-1">
                                        {{ $activeMember->anggota->name }}
                                    </h6>
                                    <p class="text-sm text-neutral-600 mb-1">
                                        <strong>Paket:</strong> {{ $activeMember->paketPersonalTrainer->nama_paket }}
                                    </p>
                                    <p class="text-sm text-neutral-600">
                                        <strong>Sesi ke:</strong> {{ $sesiKe }} /
                                        {{ $activeMember->paketPersonalTrainer->jumlah_sesi }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-block bg-success-100 text-success-600 px-4 py-2 rounded-full font-medium text-sm">
                                        🏋️ Sedang Training
                                    </span>
                                    <p class="text-xs text-neutral-600 mt-2">
                                        Mulai: {{ $activeMember->session_started_at->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if ($playlists->isEmpty())
                            <div class="text-center py-8">
                                <iconify-icon icon="mdi:playlist-remove"
                                    class="text-5xl text-neutral-400 mb-3"></iconify-icon>
                                <p class="text-neutral-600">Anda belum memiliki playlist training.</p>
                                <a href="{{ route('trainerplaylist.index') }}"
                                    class="text-primary-600 hover:underline mt-2 inline-block">
                                    Buat Playlist Sekarang
                                </a>
                            </div>
                        @else
                            @php
                                $totalPlaylists = $playlists->count();
                                $totalSaved = $savedPlaylists->count();
                                $allSaved = $totalSaved >= $totalPlaylists;
                            @endphp

                            {{-- Info progress checklist --}}
                            <div
                                class="mb-4 p-3 rounded-lg {{ $allSaved ? 'bg-success-50 border border-success-200' : 'bg-warning-50 border border-warning-200' }}">
                                <p class="text-sm font-medium {{ $allSaved ? 'text-success-700' : 'text-warning-700' }}">
                                    <iconify-icon icon="{{ $allSaved ? 'mdi:check-circle' : 'mdi:alert-circle' }}"
                                        class="mr-1"></iconify-icon>
                                    {{ $totalSaved }} / {{ $totalPlaylists }} latihan telah dicatat
                                    @if (!$allSaved)
                                        — Semua latihan harus dicatat sebelum sesi dapat diselesaikan
                                    @else
                                        — Semua latihan sudah dicatat, sesi siap diselesaikan
                                    @endif
                                </p>
                            </div>

                            <form action="{{ route('playlist-member.store') }}" method="POST" id="playlistForm">
                                @csrf
                                <input type="hidden" name="id_member_trainer" value="{{ $activeMember->id }}">
                                <input type="hidden" name="sesi_ke" value="{{ $sesiKe }}">

                                <div class="space-y-3 mb-6">
                                    @foreach ($playlists as $playlist)
                                        @php
                                            // keyBy latihan, jadi cek pakai nama latihan
                                            $isSaved = $savedPlaylists->has($playlist->latihan);
                                            $savedData = $isSaved ? $savedPlaylists->get($playlist->latihan) : null;
                                        @endphp
                                        <div class="border rounded-lg p-4 playlist-item {{ $isSaved ? 'border-success-400 bg-success-50' : 'border-neutral-200' }}"
                                            data-playlist-id="{{ $playlist->id }}"
                                            data-is-saved="{{ $isSaved ? 'true' : 'false' }}">
                                            <div class="flex items-start gap-4">
                                                <!-- Checkbox -->
                                                <div class="pt-1">
                                                    <input type="checkbox"
                                                        class="w-5 h-5 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 playlist-checkbox"
                                                        name="playlist_ids[]" value="{{ $playlist->id }}"
                                                        data-playlist-id="{{ $playlist->id }}"
                                                        {{ $isSaved ? 'checked disabled' : '' }}>
                                                </div>

                                                <!-- Nama Latihan -->
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <label
                                                            class="font-semibold text-neutral-900 cursor-pointer flex items-center gap-2">
                                                            {{ $playlist->latihan }}
                                                            @if ($isSaved)
                                                                <span
                                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-success-700 bg-success-100 rounded-full">
                                                                    <iconify-icon icon="mdi:check-circle"
                                                                        class="mr-1"></iconify-icon>
                                                                    Tersimpan
                                                                </span>
                                                            @endif
                                                        </label>

                                                        @if ($isSaved)
                                                            <button type="button"
                                                                class="text-danger-600 hover:text-danger-700 delete-saved-btn"
                                                                data-latihan="{{ $playlist->latihan }}">
                                                                <iconify-icon icon="mdi:delete-outline"
                                                                    class="text-xl"></iconify-icon>
                                                            </button>
                                                        @endif
                                                    </div>

                                                    <!-- Keterangan Input -->
                                                    <div class="keterangan-section"
                                                        style="{{ $isSaved ? '' : 'display: none;' }}">
                                                        <label class="text-xs text-neutral-600 mb-1 block">Keterangan
                                                            (opsional)
                                                            :</label>
                                                        <textarea class="form-control rounded-lg text-sm keterangan-input" rows="2"
                                                            name="keterangan[{{ $playlist->id }}]" placeholder="Tambahkan catatan atau keterangan (opsional)"
                                                            {{ $isSaved ? 'disabled readonly' : '' }}>{{ $savedData ? $savedData->keterangan : '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-between gap-3 pt-4 border-t border-neutral-200">
                                    <a href="{{ route('trainer.dashboard') }}"
                                        class="border border-neutral-600 hover:bg-neutral-100 text-neutral-600 text-base px-6 py-3 rounded-lg">
                                        <iconify-icon icon="ion:arrow-back" class="text-xl mr-2"></iconify-icon>
                                        Kembali
                                    </a>

                                    <div class="flex gap-3">
                                        @if (!$allSaved)
                                            <button type="submit"
                                                class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg"
                                                id="submitBtn">
                                                <iconify-icon icon="material-symbols:save"
                                                    class="text-xl mr-2"></iconify-icon>
                                                Simpan Playlist
                                            </button>
                                        @endif

                                        <button type="button" id="endSessionBtn"
                                            class="bg-danger-600 hover:bg-danger-700 text-white text-base px-6 py-3 rounded-lg {{ !$allSaved ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            {{ !$allSaved ? 'disabled' : '' }} data-modal-target="end-session-modal"
                                            data-modal-toggle="{{ $allSaved ? 'end-session-modal' : '' }}">
                                            <iconify-icon icon="mdi:stop-circle" class="text-xl mr-2"></iconify-icon>
                                            Selesai Sesi
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Selesai Sesi -->
    <div id="end-session-modal" tabindex="-1"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="rounded-2xl bg-white max-w-[800px] w-full">
            <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                <h1 class="text-xl font-semibold">Selesai Sesi Training</h1>
                <button data-modal-hide="end-session-modal" type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <div class="p-6">
                @if ($activeMember)
                    <form action="{{ route('trainer.session.end', $activeMember->id) }}" method="POST">
                        @csrf
                        <p class="text-neutral-700 text-base mb-4">
                            Apakah Anda yakin ingin menyelesaikan sesi training untuk
                            <strong>{{ $activeMember->anggota->name }}</strong>?
                        </p>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" data-modal-hide="end-session-modal"
                                class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-6 py-2 rounded-lg">
                                Batal
                            </button>
                            <button type="submit"
                                class="bg-primary-600 hover:bg-primary-700 text-white text-base px-6 py-2 rounded-lg">
                                Selesai
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const activeMemberId = {{ $activeMember ? $activeMember->id : 'null' }};
            const sesiKe = {{ $sesiKe ?? 'null' }};

            // Handle checkbox change untuk show/hide keterangan
            document.querySelectorAll('.playlist-checkbox').forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.addEventListener('change', function() {
                        const playlistItem = this.closest('.playlist-item');
                        const keteranganSection = playlistItem.querySelector('.keterangan-section');
                        const keteranganInput = playlistItem.querySelector('.keterangan-input');

                        if (this.checked) {
                            keteranganSection.style.display = 'block';
                            keteranganInput.disabled = false;
                            keteranganInput.removeAttribute('readonly');
                        } else {
                            keteranganSection.style.display = 'none';
                            keteranganInput.disabled = true;
                            keteranganInput.value = '';
                        }
                    });
                }
            });

            // Handle tombol Selesai Sesi jika masih disabled
            const endSessionBtn = document.getElementById('endSessionBtn');
            if (endSessionBtn && endSessionBtn.disabled) {
                endSessionBtn.addEventListener('click', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum Semua Latihan Dicatat!',
                        text: 'Harap simpan semua playlist latihan terlebih dahulu sebelum menyelesaikan sesi.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Mengerti'
                    });
                });
            }

            // Handle delete saved playlist
            document.querySelectorAll('.delete-saved-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const latihan = this.dataset.latihan;

                    Swal.fire({
                        title: 'Hapus Playlist?',
                        text: `Anda yakin ingin menghapus "${latihan}" dari daftar yang sudah disimpan?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e3342f',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteSavedPlaylist(latihan);
                        }
                    });
                });
            });

            function deleteSavedPlaylist(latihan) {
                fetch('{{ route('playlist-member.destroy') }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_member_trainer: activeMemberId,
                            latihan: latihan, // kirim latihan bukan id_playlist_trainer
                            sesi_ke: sesiKe
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menghapus playlist'
                        });
                    });
            }

            // Handle form submit
            const form = document.getElementById('playlistForm');
            const submitBtn = document.getElementById('submitBtn');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const checkedBoxes = document.querySelectorAll(
                        '.playlist-checkbox:not([disabled]):checked');

                    if (checkedBoxes.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian!',
                            text: 'Silakan pilih minimal 1 playlist baru yang belum disimpan.'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Konfirmasi',
                        text: `Anda akan menyimpan ${checkedBoxes.length} playlist baru. Lanjutkan?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Simpan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML =
                                '<iconify-icon icon="eos-icons:loading" class="text-xl mr-2"></iconify-icon>Menyimpan...';
                            form.submit();
                        }
                    });
                });
            }

            // Handle alert close button
            document.querySelectorAll('.remove-button').forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    if (alert) {
                        alert.remove();
                    }
                });
            });
        });
    </script>
@endsection
