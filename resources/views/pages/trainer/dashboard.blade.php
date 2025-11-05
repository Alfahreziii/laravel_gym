@extends('layout.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Dashboard Trainer: {{ $trainer->name }}</h2>
            <p class="text-muted">Kelola sesi training Anda</p>
        </div>
    </div>

    <!-- Status Training -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Status</h5>
                    <h3>
                        @if($trainer->isTraining())
                            <span class="badge bg-danger">🔴 TRAINING</span>
                        @else
                            <span class="badge bg-success">🟢 AVAILABLE</span>
                        @endif
                    </h3>
                    @if($trainer->active_session)
                        <small>Sedang melatih: <strong>{{ $trainer->active_session->anggota->name }}</strong></small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Sesi Sudah Dijalani</h5>
                    <h3>{{ $trainer->sesi_sudah_dijalani }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Sesi Belum Dijalani</h5>
                    <h3>{{ $trainer->sesi_belum_dijalani }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Total Member</h5>
                    <h3>{{ $memberTrainers->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Member -->
    <div class="card">
        <div class="card-header">
            <h4>Daftar Member Anda</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Paket</th>
                            <th>Sesi Selesai</th>
                            <th>Sisa Sesi</th>
                            <th>Status Sesi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memberTrainers as $mt)
                        <tr class="{{ $mt->is_session_active ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ $mt->anggota->name }}</strong><br>
                                <small class="text-muted">{{ $mt->anggota->no_telp }}</small>
                            </td>
                            <td>{{ $mt->paketPersonalTrainer->nama_paket }}</td>
                            <td>{{ $mt->sesi }} / {{ $mt->paketPersonalTrainer->jumlah_sesi }}</td>
                            <td>
                                <span class="badge {{ $mt->sisa_sesi > 0 ? 'bg-info' : 'bg-secondary' }}">
                                    {{ $mt->sisa_sesi }} sesi
                                </span>
                            </td>
                            <td>
                                @if($mt->is_session_active)
                                    <span class="badge bg-warning">⏳ Sedang Training</span><br>
                                    <small>Mulai: {{ $mt->session_started_at->format('H:i') }}</small>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                @if($mt->sisa_sesi > 0)
                                    @if(!$mt->is_session_active)
                                        @if(!$trainer->isTraining())
                                            <form action="{{ route('trainer.session.start', $mt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Mulai sesi training untuk {{ $mt->anggota->name }}?')">
                                                    ▶️ Mulai Sesi
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                Sedang Melatih Lainnya
                                            </button>
                                        @endif
                                    @else
                                        <form action="{{ route('trainer.session.end', $mt->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Selesaikan sesi training untuk {{ $mt->anggota->name }}?')">
                                                ⏹️ Selesai
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-muted">Sesi Habis</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada member yang terdaftar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Link ke Log Sesi -->
    <div class="mt-3">
        <a href="{{ route('trainer.session.logs') }}" class="btn btn-outline-primary">
            📋 Lihat Riwayat Sesi
        </a>
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
@endsection