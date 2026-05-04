@extends('layout.layout')
@php
    $title = 'Riwayat Sesi';
    $subTitle = 'Riwayat Sesi Training';
@endphp

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <div>
                        <h6 class="card-title mb-0 text-lg">Riwayat Sesi Training</h6>
                        <p class="text-sm text-neutral-500 mt-1">Trainer: {{ $trainer->name }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('trainer.dashboard') }}"
                            class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">
                            <iconify-icon icon="lucide:arrow-left" class="mr-2"></iconify-icon>
                            Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="border border-neutral-200 rounded-lg border-separate w-full">
                            <thead>
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-sm font-semibold text-neutral-600 bg-neutral-50">S.L
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-sm font-semibold text-neutral-600 bg-neutral-50">
                                        Tanggal & Waktu</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-sm font-semibold text-neutral-600 bg-neutral-50">
                                        Tipe</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-sm font-semibold text-neutral-600 bg-neutral-50">
                                        Sesi</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-sm font-semibold text-neutral-600 bg-neutral-50">
                                        Total Sesi</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-sm font-semibold text-neutral-600 bg-neutral-50">
                                        Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $index => $log)
                                    @php
                                        // Ambil durasi dari description, pastikan selalu positif & dibulatkan
                                        $description = $log->description;
                                        $cleanDescription = preg_replace_callback(
                                            '/durasi:\s*(-?[\d.]+)\s*menit/i',
                                            function ($matches) {
                                                $durasi = round(abs((float) $matches[1]));
                                                return "durasi: {$durasi} menit";
                                            },
                                            $description,
                                        );
                                    @endphp
                                    <tr class="border-b border-neutral-100 hover:bg-neutral-50 transition">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-700">
                                            {{ $logs->firstItem() + $index }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-700">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            @if ($log->type === 'in')
                                                <span
                                                    class="bg-success-100 text-success-600 px-6 py-1.5 rounded-full font-medium text-sm">Masuk</span>
                                            @else
                                                <span
                                                    class="bg-danger-100 text-danger-600 px-6 py-1.5 rounded-full font-medium text-sm">Selesai</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-700">{{ $log->sesi }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-700">
                                            {{ $log->current_sesi }}</td>
                                        <td class="px-4 py-3 text-sm text-neutral-700">{{ $cleanDescription }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-neutral-400 italic">Belum ada
                                            riwayat sesi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination & info --}}
                    <div class="flex justify-between items-center mt-4 flex-wrap gap-2">
                        <span class="text-sm text-gray-500">
                            Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} data
                        </span>
                        <div>
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
