@props([
    'tableId',
    'colspan' => 5,
    'placeholder' => 'Cari...',
])
@php
    $cap          = ucfirst($tableId);
    $tbodyId      = 'tbody'      . $cap;
    $paginationId = 'pagination' . $cap;
    $infoId       = 'info'       . $cap;
    $searchId     = 'search'     . $cap;
    $perPageId    = 'perPage'    . $cap;
@endphp

{{-- Top bar: entries per page (kiri) + search (kanan) --}}
<div class="flex justify-between items-center mb-4 flex-wrap gap-3">
    <div class="flex items-center gap-2">
        <label for="{{ $perPageId }}" class="text-sm text-neutral-600 dark:text-neutral-400 whitespace-nowrap">Show</label>
        <select id="{{ $perPageId }}" class="form-control form-control-sm w-auto">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span class="text-sm text-neutral-600 dark:text-neutral-400 whitespace-nowrap">entries</span>
    </div>
    <input type="text" id="{{ $searchId }}" placeholder="{{ $placeholder }}"
        class="form-control form-control-sm w-64">
</div>

<div class="overflow-x-auto">
    <table class="ajax-table border border-neutral-200 dark:border-neutral-700 rounded-lg border-separate w-full">
        <thead>
            {{ $header }}
        </thead>
        <tbody id="{{ $tbodyId }}">
            <tr>
                <td colspan="{{ $colspan }}" class="text-center py-8">Loading...</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Bottom bar: info (kiri) + pagination (kanan) --}}
<div class="flex justify-between items-center mt-4 flex-wrap gap-2">
    <span class="text-sm text-gray-500" id="{{ $infoId }}"></span>
    <div id="{{ $paginationId }}" class="flex gap-1 flex-wrap"></div>
</div>
