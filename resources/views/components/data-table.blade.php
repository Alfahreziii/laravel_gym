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
@endphp

<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <span class="text-sm text-gray-500" id="{{ $infoId }}"></span>
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

<div class="flex justify-between items-center mt-4 flex-wrap gap-2">
    <div id="{{ $paginationId }}" class="flex gap-1 flex-wrap"></div>
</div>
