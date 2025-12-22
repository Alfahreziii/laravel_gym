@extends('layout.layout')
@php
    $title='Parameter Gaji Trainer';
    $subTitle = 'Parameter Gaji Trainer';
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
                <h6 class="card-title mb-0 text-lg">Data Parameter Gaji Trainer</h6>
                <div class="flex gap-2">
                    @role('admin')
                    <a href="{{ route('gaji_trainer.create') }}" 
                       class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                       + Tambah Data
                    </a>
                    @endrole
                </div>
            </div>
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            @role('admin')
                            <th>Aksi</th>
                            @endrole
                            <th>Nama Trainer</th>
                            <th>Level</th>
                            <th>Base Rate per Sesi</th>
                            <th>Tanggal Gajian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gajiTrainers as $index => $item)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            @role('admin')
                            <td class="whitespace-nowrap flex gap-2">
                                <a href="{{ route('gaji_trainer.edit', $item->id) }}" title="Edit Setting Gaji"
                                   class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                <form action="{{ route('gaji_trainer.destroy', $item->id) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" title="Hapus Setting Gaji" 
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </td>
                            @endrole
                            <td class="whitespace-nowrap">
                                <a class="text-primary-600 font-semibold" href="{{ route('gaji_trainer.edit', $item->id) }}">
                                    {{ $item->trainer->name ?? '-' }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="bg-info-100 text-info-600 px-3 py-1.5 rounded-full font-medium text-sm">
                                    {{ $item->level->name ?? '-' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap font-semibold text-success-600">
                                {{ $item->formatted_base_rate }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $item->tgl_gajian->format('d') }} setiap bulan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-neutral-500">
                                Belum ada data parameter gaji trainer
                            </td>
                        </tr>
                        @endforelse
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
    // Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Setting gaji trainer yang dihapus tidak bisa dikembalikan!",
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

    // Remove alert
    document.querySelectorAll('.remove-button').forEach(button => {
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