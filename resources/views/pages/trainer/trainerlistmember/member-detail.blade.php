@extends('layout.layout')

@php
    $title='Detail Member';
    $subTitle = $member->name;
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

<!-- Statistik -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-4 3xl:grid-cols-4 gap-6 mx-auto mb-6">
    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-blue-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Total Paket</p>
                    <h6>{{ $totalPaket }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-blue-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:package-variant" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-green-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Sesi Aktif</p>
                    <h6>{{ $totalSesiAktif }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-green-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:timer-sand" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-purple-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Sesi Selesai</p>
                    <h6>{{ $totalSesiSelesai }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-purple-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:check-circle" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-none border border-gray-200 rounded-lg h-full bg-gradient-to-r from-orange-600/10 to-bg-white">
        <div class="card-body p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-neutral-900 mb-1">Sesi Kadaluarsa</p>
                    <h6>{{ $totalSesiKadaluarsa }}</h6>
                </div>
                <div class="w-[50px] h-[50px] bg-orange-600 rounded-full flex justify-center items-center">
                    <iconify-icon icon="mdi:clock-alert" class="text-white text-2xl mb-0"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Member -->
 <div class="grid grid-cols-12">
    <div class="col-span-12">       
        <div class="card mb-6 border border-gray-200">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Informasi Member</h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-muted mb-1">Nama</p>
                        <p class="font-semibold">{{ $member->name }}</p>
                    </div>
                    <div>
                        <p class="text-muted mb-1">No. Telepon</p>
                        <p class="font-semibold">{{ $member->no_telp }}</p>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Email</p>
                        <p class="font-semibold">{{ $member->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Status Hari Ini</p>
                        @if($isCheckedIn)
                            <span class="badge bg-success">✅ Sudah Check-in</span>
                        @else
                            <span class="badge bg-secondary">❌ Belum Check-in</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
 </div>



<!-- Paket Aktif -->
  <div class="grid grid-cols-12">
      <div class="col-span-12">    
        @if($activePackages->count() > 0)
        <div class="card mb-6 border border-gray-200">
            <div class="card-header">
                <h6 class="card-title mb-0 text-lg">Paket Aktif</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Paket</th>
                                <th>Periode</th>
                                <th>Sesi Tersisa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activePackages as $mt)
                            <tr class="{{ $mt->is_session_active ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $mt->paketPersonalTrainer->nama_paket }}</strong><br>
                                    <small class="text-muted">{{ $mt->kode_transaksi }}</small>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($mt->tgl_mulai)->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($mt->tgl_selesai)->format('d M Y') }}
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $mt->sesi }} / {{ $mt->paketPersonalTrainer->jumlah_sesi }}</span>
                                </td>
                                <td>
                                    @if($mt->is_session_active)
                                        <span class="badge bg-warning">⏳ Sedang Training</span><br>
                                        <small>Mulai: {{ $mt->session_started_at->format('H:i') }}</small>
                                    @else
                                        <span class="badge bg-success">✅ Siap Training</span>
                                    @endif
                                </td>
                                <td>
                                    @if($mt->sesi > 0)
                                        @if(!$mt->is_session_active)
                                            @if(!$trainer->isTraining())
                                                @if($isCheckedIn)
                                                    <button type="button"
                                                        data-modal-target="start-session-modal"
                                                        data-modal-toggle="start-session-modal"
                                                        data-member="{{ $member->name }}"
                                                        data-action="{{ route('trainer.session.start', $mt->id) }}"
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
                                                data-member="{{ $member->name }}"
                                                data-action="{{ route('trainer.session.end', $mt->id) }}"
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
        @endif
    </div>
 </div>
<!-- Riwayat Semua Paket -->
   <div class="grid grid-cols-12">
       <div class="col-span-12">  
          <div class="card border border-gray-200">
              <div class="card-header">
                  <h6 class="card-title mb-0 text-lg">Riwayat Semua Paket</h6>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table id="history-table" class="table border border-neutral-200 rounded-lg">
                          <thead>
                              <tr>
                                  <th>Kode Transaksi</th>
                                  <th>Paket</th>
                                  <th>Periode</th>
                                  <th>Total Sesi</th>
                                  <th>Sesi Selesai</th>
                                  <th>Sisa Sesi</th>
                                  <th>Status</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach($memberTrainers as $mt)
                              <tr>
                                  <td>{{ $mt->kode_transaksi }}</td>
                                  <td>{{ $mt->paketPersonalTrainer->nama_paket }}</td>
                                  <td>
                                      {{ \Carbon\Carbon::parse($mt->tgl_mulai)->format('d M Y') }} -
                                      {{ \Carbon\Carbon::parse($mt->tgl_selesai)->format('d M Y') }}
                                  </td>
                                  <td>{{ $mt->paketPersonalTrainer->jumlah_sesi }}</td>
                                  <td>{{ $mt->sesi_sudah_dijalani }}</td>
                                  <td>{{ $mt->sesi }}</td>
                                  <td>
                                      @php
                                          $today = now()->toDateString();
                                          $isActive = $mt->tgl_mulai <= $today && $mt->tgl_selesai >= $today && $mt->sesi > 0;
                                          $isExpired = $mt->tgl_selesai < $today || $mt->sesi <= 0;
                                      @endphp
                                      
                                      @if($isActive)
                                          <span class="badge bg-success">✅ Aktif</span>
                                      @elseif($isExpired)
                                          <span class="badge bg-warning">⏰ Kadaluarsa</span>
                                      @else
                                          <span class="badge bg-secondary">⏳ Belum Dimulai</span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Mulai Sesi
    const startForm = document.getElementById('startSessionForm');
    const startNameHolder = document.getElementById('sessionMemberName');

    document.querySelectorAll('.open-start-session-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const memberName = this.getAttribute('data-member');
            const actionUrl = this.getAttribute('data-action');

            startNameHolder.textContent = memberName;
            startForm.setAttribute('action', actionUrl);
        });
    });

    // Modal Selesai Sesi
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