@extends('layout.layout')
@php
    $title='Anggota';
    $subTitle = 'Anggota';
    $script='<script src="' . asset('assets/js/data-table.js') . '"></script>';
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h6 class="card-title mb-0 text-lg">Anggota Gym</h6>
                @role('admin')
                <a href="{{ route('anggota.create') }}" class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">+ Tambah Data</a>
                @endrole
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate">
                    <thead>
                        <tr>
                            <th scope="col">S.L</th>
                            <th scope="col">ID Kartu</th>
                            <th scope="col">Photo</th> {{-- Tambahan kolom Photo --}}
                            <th scope="col">Nama</th>
                            <th scope="col">Tanggal Lahir</th>
                            <th scope="col">No. Telp</th>
                            <th scope="col">Status</th>
                            @role('admin')
                            <th scope="col">Aksi</th>
                            @endrole
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anggotas as $index => $anggota)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap">{{ $anggota->id_kartu }}</td>
                            <td class="whitespace-nowrap">
                                @if($anggota->photo)
                                    <img src="{{ asset('storage/' . $anggota->photo) }}" 
                                        alt="Photo {{ $anggota->name }}" 
                                        class="w-10 h-10 rounded-full object-cover cursor-pointer anggota-photo"
                                        data-photo="{{ asset('storage/' . $anggota->photo) }}">
                                @else
                                    <span class="text-gray-400 italic">No photo</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $anggota->name }}</td>
                            <td class="whitespace-nowrap">{{ $anggota->tgl_lahir->format('d M Y') }}</td>
                            <td class="whitespace-nowrap">{{ $anggota->no_telp }}</td>
                            <td class="whitespace-nowrap">
                                @if($anggota->status_keanggotaan)
                                    <span class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Aktif</span>
                                @else
                                    <span class="bg-warning-100 text-warning-600 px-6 py-1.5 rounded-full font-medium text-sm">Tidak Aktif</span>
                                @endif
                            </td>
                            @role('admin')
                            <td class="whitespace-nowrap">
                                <a href="{{ route('anggota.edit', $anggota->id) }}" class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                <form action="{{ route('anggota.destroy', $anggota->id) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </td>
                            @endrole
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
    // Script untuk konfirmasi hapus
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data anggota yang dihapus tidak bisa dikembalikan!",
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

    // ✅ Script untuk menampilkan pop-up foto anggota
    const photos = document.querySelectorAll('.anggota-photo');
    photos.forEach(photo => {
        photo.addEventListener('click', function () {
            const imageUrl = this.getAttribute('data-photo');
            Swal.fire({
                imageUrl: imageUrl,
                imageAlt: 'Foto Anggota',
                showConfirmButton: false,
                background: 'transparent',
                width: 'auto',
                padding: '0',
                showCloseButton: true,
            });
        });
    });
});
</script>
@endsection