@extends('layout.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>📋 Riwayat Sesi Training</h2>
            <p class="text-muted">Trainer: {{ $trainer->name }}</p>
            <a href="{{ route('trainer.dashboard') }}" class="btn btn-secondary">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Tipe</th>
                            <th>Sesi</th>
                            <th>Total Sesi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($log->type === 'in')
                                    <span class="badge bg-success">Masuk</span>
                                @else
                                    <span class="badge bg-danger">Selesai</span>
                                @endif
                            </td>
                            <td>{{ $log->sesi }}</td>
                            <td>{{ $log->current_sesi }}</td>
                            <td>{{ $log->description }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada riwayat sesi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection