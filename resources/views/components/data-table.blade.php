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

{{-- Top bar: search ber-ikon (kiri) + entries per page (kanan) --}}
<div class="flex justify-between items-center px-4 md:px-6 py-3 border-b border-line-light dark:border-line-dark flex-wrap gap-3">
    <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-3 dark:text-ink-d3 pointer-events-none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/>
            </svg>
        </span>
        <input type="text" id="{{ $searchId }}" placeholder="{{ $placeholder }}"
            class="form-control form-control-sm pl-9 w-64">
    </div>
    <div class="flex items-center gap-2">
        <label for="{{ $perPageId }}" class="text-sm text-ink-2 dark:text-ink-d2 whitespace-nowrap">Tampilkan</label>
        <select id="{{ $perPageId }}" class="form-control form-control-sm w-auto">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span class="text-sm text-ink-2 dark:text-ink-d2 whitespace-nowrap">data</span>
    </div>
</div>

{{-- Table: edge-to-edge dalam card, tanpa padding horizontal --}}
<div class="overflow-x-auto">
    <table class="ajax-table w-full">
        <thead>
            {{ $header }}
        </thead>
        <tbody id="{{ $tbodyId }}">
            <tr>
                <td colspan="{{ $colspan }}" class="text-center py-8 text-sm text-ink-3 dark:text-ink-d3">Loading...</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Footer: info (kiri) + pagination (kanan) --}}
<div class="flex justify-between items-center px-4 md:px-6 py-3 border-t border-line-light dark:border-line-dark flex-wrap gap-2">
    <span class="text-sm text-ink-3 dark:text-ink-d3" id="{{ $infoId }}"></span>
    <div id="{{ $paginationId }}" class="flex gap-1 flex-wrap"></div>
</div>
