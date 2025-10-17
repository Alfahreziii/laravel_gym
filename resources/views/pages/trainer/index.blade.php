@extends('layout.layout')
@php
    $title='Trainer';
    $subTitle = 'Trainer';
    $script='<script src="' . asset('assets/js/data-table.js') . '"></script>';
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
@if(session('danger'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('danger') }}
        <button class="remove-button text-danger-600 text-2xl"> 
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Daftar Trainer</h6>
                <a href="{{ route('trainer.create') }}" 
                   class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white 
                          focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 
                          text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white 
                          dark:focus:ring-primary-800">
                    + Tambah Trainer
                </a>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate">
                    <thead>
                        <tr>
                            <th scope="col">S.L</th>
                            <th scope="col">RFID</th>
                            <th scope="col">Foto</th>
                            <th scope="col">Nama</th>
                            <th scope="col">No Telp</th>
                            <th scope="col">Spesialisasi</th>
                            <th scope="col">Experience</th>
                            <th scope="col">Tanggal Gabung</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trainers as $index => $trainer)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">{{ $trainer->rfid }}</td>
                            <td class="whitespace-nowrap">
                                <img src="{{ asset('storage/'.$trainer->photo) }}" alt="Foto" class="w-12 h-12 rounded-full">
                            </td>
                            <td class="whitespace-nowrap">{{ $trainer->name }}</td>
                            <td class="whitespace-nowrap">{{ $trainer->no_telp }}</td>
                            <td class="whitespace-nowrap">
                                {{ $trainer->specialisasi ? $trainer->specialisasi->nama_specialisasi : '-' }}
                            </td>
                            <td class="whitespace-nowrap">{{ $trainer->experience }}</td>
                            <td class="whitespace-nowrap">{{ $trainer->tgl_gabung->format('d-m-Y') }}</td>
                            <td class="whitespace-nowrap">
                                @if($trainer->status === 'aktif')
                                    <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Aktif</span>
                                @elseif($trainer->status === 'nonaktif')
                                    <span class="bg-warning-100 text-warning-600 px-6 py-1.5 rounded-full font-medium text-sm">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <a href="{{ route('trainer.edit', $trainer->id) }}" class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                <form action="{{ route('trainer.destroy', $trainer->id) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data trainer yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeButtons = document.querySelectorAll('.remove-button');

        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = this.closest('.alert');
                if(alert) {
                    alert.remove();
                }
            });
        });
    });
</script>
@endsection
