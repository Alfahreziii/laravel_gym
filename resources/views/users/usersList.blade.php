@extends('layout.layout')
@php
    $title='List User';
    $subTitle = 'List User Management System';
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
        <button class="remove-button text-success-600 text-2xl">                                         <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
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
            <div class="card-body">
                <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            @role('admin')
                            <th scope="col">Aksi</th>
                            @endrole
                            <th scope="col">Foto</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $item)
                        <tr>
                            <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                            @role('admin')
                            <td class="whitespace-nowrap">
                                <!-- Tombol Edit -->
                                <button type="button" title="Ubah Role" data-modal-target="edit-popup-modal-{{ $item->id }}" data-modal-toggle="edit-popup-modal-{{ $item->id }}" class="w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </button>
                            </td>
                            @endrole
                            <td class="whitespace-nowrap">
                                @if($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" 
                                        alt="Photo {{ $item->anggota->name }}" 
                                        class="w-10 h-10 rounded-lg object-cover cursor-pointer item-photo"
                                        data-photo="{{ asset('storage/' . $item->foto) }}">
                                @else
                                    <span class="text-gray-400 italic">No photo</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $item->name }}</td>
                            <td class="whitespace-nowrap">{{ $item->email }}</td>
                            <td class="whitespace-nowrap">{{ $item->getRoleNames()->implode(', ') }}</td>
                            {{-- Bagian Status yang sudah diperbaiki --}}
                            <td>
                                @php
                                if ($item->last_activity) {
                                    $last = \Carbon\Carbon::parse($item->last_activity)->setTimezone('Asia/Jakarta');
                                    $now = now()->setTimezone('Asia/Jakarta');
                                    
                                    // Gunakan abs() untuk mendapatkan selisih absolut
                                    $diffInMinutes = abs($now->diffInMinutes($last));
                                    
                                    // Atau lebih baik, cek apakah last_activity dalam 2 jam terakhir
                                    $isActive = $last->diffInMinutes($now, false) <= 2; // false = hasil bisa negatif
                                    
                                    if ($isActive && $last <= $now) {
                                        $status = 'Active';
                                        $statusClass = 'text-green-600 font-semibold';
                                        $timeInfo = '(' . number_format($diffInMinutes, 0) . ' menit yang lalu)';
                                    } else {
                                        $status = 'Offline';
                                        $statusClass = 'text-gray-400';
                                        
                                        // Format waktu offline lebih readable
                                        if ($diffInMinutes < 60) {
                                            $timeInfo = '(' . number_format($diffInMinutes, 0) . ' menit yang lalu)';
                                        } elseif ($diffInMinutes < 1440) {
                                            $hours = floor($diffInMinutes / 60);
                                            $timeInfo = '(' . $hours . ' jam yang lalu)';
                                        } else {
                                            $days = floor($diffInMinutes / 1440);
                                            $timeInfo = '(' . $days . ' hari yang lalu)';
                                        }
                                    }
                                } else {
                                    $status = 'Never Active';
                                    $statusClass = 'text-gray-300 italic';
                                    $timeInfo = '';
                                }
                                @endphp
                                
                                <span class="{{ $statusClass }}">
                                    {{ $status }} <span class="text-xs text-gray-500">{{ $timeInfo }}</span>
                                </span>
                            </td>
                        </tr>

                        <!-- Modal Edit Role - Letakkan di dalam foreach loop -->
                        <div id="edit-popup-modal-{{ $item->id }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="rounded-2xl bg-white max-w-[800px] w-full">
                                <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
                                    <h1 class="text-xl font-semibold">Edit Role User</h1>
                                    <button data-modal-hide="edit-popup-modal-{{ $item->id }}" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                
                                <div class="p-6">
                                    <form action="{{ route('role.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="grid grid-cols-1 gap-6">
                                            <!-- User Info (Read Only) -->
                                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Nama User:</label>
                                                        <p class="text-base font-semibold text-gray-900">{{ $item->name }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Email:</label>
                                                        <p class="text-base font-semibold text-gray-900">{{ $item->email }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Role Dropdown -->
                                            <div>
                                                <label for="role_{{ $item->id }}" class="inline-block font-semibold text-neutral-600 text-sm mb-2">
                                                    Pilih Role <span class="text-danger-600">*</span>
                                                </label>
                                                <select id="role_{{ $item->id }}" name="role" class="form-control rounded-lg w-full border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                                    <option value="" disabled>-- Pilih Role --</option>
                                                    @php
                                                        $currentRole = $item->getRoleNames()->first();
                                                    @endphp
                                                    <option value="admin" {{ $currentRole == 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="spv" {{ $currentRole == 'spv' ? 'selected' : '' }}>Supervisor (SPV)</option>
                                                    <option value="guest" {{ $currentRole == 'guest' ? 'selected' : '' }}>Guest</option>
                                                </select>
                                                <p class="text-xs text-gray-500 mt-1">Role saat ini: <span class="font-semibold text-primary-600">{{ ucfirst($currentRole ?? 'Tidak ada role') }}</span></p>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-gray-200">
                                                <button type="button" data-modal-hide="edit-popup-modal-{{ $item->id }}" class="border border-gray-300 hover:bg-gray-100 text-gray-700 text-base px-8 py-2.5 rounded-lg transition-colors">
                                                    Batal
                                                </button>
                                                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-base px-8 py-2.5 rounded-lg transition-colors font-medium">
                                                    Update Role
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
</div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection