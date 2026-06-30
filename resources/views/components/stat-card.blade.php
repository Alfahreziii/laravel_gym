@props([
    'label'  => '',
    'value'  => '',
    'icon'   => 'mdi:chart-line',
    'color'  => 'neutral',
    'sub'    => null,
])

@php
$chipClass = $color === 'primary'
    ? 'bg-primary-100 text-primary-600 dark:bg-primary-600/20 dark:text-primary-400'
    : 'bg-neutral-200 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400';
@endphp

<div {{ $attributes->merge(['class' => 'card p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-2">{{ $label }}</p>
            <p class="font-display text-3xl font-bold tabular-nums text-neutral-900 dark:text-neutral-100 leading-none">{{ $value }}</p>
            @if($sub)
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1.5">{{ $sub }}</p>
            @endif
        </div>
        <div class="flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center {{ $chipClass }}">
            <iconify-icon icon="{{ $icon }}" class="text-xl"></iconify-icon>
        </div>
    </div>
</div>
