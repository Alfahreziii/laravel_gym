@extends('layout.layout')

@php
    $title='Member List';
    $subTitle = $trainer->name ;
    $script = '<script src="' . asset('assets/js/data-table.js') . '"></script>';
@endphp

@section('content')

@if(session('success'))
    <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
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
@if(session('error'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('error') }}
        <button class="remove-button text-danger-600 text-2xl"> 
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif
    <!-- Daftar Member -->
    <div class="grid grid-cols-12 mt-6">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">Daftar Member Anda</h6>
                    <a href="{{ route('trainer.session.logs') }}" class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                        📋 Lihat Riwayat Sesi
                    </a>
                </div>
                <div class="card-body">
                    <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate">
                        <thead>
                            <tr>
                                <th scope="col">Member</th>
                                <th scope="col">Paket Aktif</th>
                                <th scope="col">Sesi Aktif</th>
                                <th scope="col">Sesi Selesai</th>
                                <th scope="col">Sesi Kadaluarsa</th>
                                <th scope="col">Status Kehadiran</th>
                                <th scope="col">Status Sesi</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedMembers as $member)
                            <tr class="{{ $member->is_session_active ? 'table-warning' : '' }}">
                                <td>
                                    <a title="Member Detail" href="{{ route('trainerlistmember.detail', $member->id_anggota) }}">
                                        <strong class="text-primary-600">{{ $member->anggota->name }}</strong><br>
                                        <small class="text-muted">{{ $member->anggota->no_telp }}</small>
                                    </a>
                                    
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $member->total_paket_aktif }} paket</span>
                                </td>
                                <td>
                                    <span class="badge {{ $member->total_sesi_aktif > 0 ? 'bg-info' : 'bg-secondary' }}">
                                        {{ $member->total_sesi_aktif }} sesi
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $member->total_sesi_selesai }} sesi</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $member->total_sesi_kadaluarsa }} sesi</span>
                                </td>
                                <td>
                                    @if($member->is_checked_in)
                                        <span class="badge bg-success">✅ Hadir</span>
                                    @else
                                        <span class="badge bg-secondary">❌ Belum Check-in</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->is_session_active)
                                        <span class="badge bg-warning">⏳ Sedang Training</span><br>
                                        <small>Mulai: {{ $member->session_started_at->format('H:i') }}</small>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>

                                    
                                    @if($member->total_sesi_aktif > 0)
                                        @if(!$member->is_session_active)
                                            @if(!$trainer->isTraining())
                                                @if($member->is_checked_in)
                                                    <button type="button"
                                                        data-modal-target="start-session-modal"
                                                        data-modal-toggle="start-session-modal"
                                                        data-member="{{ $member->anggota->name }}"
                                                        data-action="{{ route('trainer.session.start', $member->active_session->id ?? 0) }}"
                                                        class="btn btn-sm btn-success open-start-session-modal">
                                                        ▶️ Mulai Sesi
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        Belum Check-in
                                                    </button>
                                                @endif
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    Sedang Melatih Lainnya
                                                </button>
                                            @endif
                                        @else
                                            <button type="button"
                                                data-modal-target="end-session-modal"
                                                data-modal-toggle="end-session-modal"
                                                data-member="{{ $member->anggota->name }}"
                                                data-action="{{ route('trainer.session.end', $member->active_session->id) }}"
                                                class="btn btn-sm btn-danger open-end-session-modal">
                                                ⏹️ Selesai
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-muted">Sesi Habis</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <div class="p-6">
            <form id="endSessionForm" method="POST">
                @csrf
                <p class="text-neutral-700 text-base mb-4">
                    Apakah Anda yakin ingin menyelesaikan sesi training untuk <strong id="endSessionMemberName"></strong>?
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
        </div>
    </div>
</div>

<!-- Modal Mulai Sesi -->
<div id="start-session-modal" tabindex="-1"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[800px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Mulai Sesi Training</h1>
            <button data-modal-hide="start-session-modal" type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <div class="p-6">
            <form id="startSessionForm" method="POST">
                @csrf
                <p class="text-neutral-700 text-base mb-4">
                    Apakah Anda yakin ingin memulai sesi training untuk <strong id="sessionMemberName"></strong>?
                </p>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" data-modal-hide="start-session-modal"
                        class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-6 py-2 rounded-lg">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-primary-600 hover:bg-primary-700 text-white text-base px-6 py-2 rounded-lg">
                        Mulai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}'
    });
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('start-session-modal');
    const form = document.getElementById('startSessionForm');
    const nameHolder = document.getElementById('sessionMemberName');

    document.querySelectorAll('.open-start-session-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const memberName = this.getAttribute('data-member');
            const actionUrl = this.getAttribute('data-action');

            nameHolder.textContent = memberName;
            form.setAttribute('action', actionUrl);
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const endModal = document.getElementById('end-session-modal');
    const endForm = document.getElementById('endSessionForm');
    const endNameHolder = document.getElementById('endSessionMemberName');

    document.querySelectorAll('.open-end-session-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const memberName = this.getAttribute('data-member');
            const actionUrl = this.getAttribute('data-action');

            endNameHolder.textContent = memberName;
            endForm.setAttribute('action', actionUrl);
        });
    });
});
</script>

@endsection