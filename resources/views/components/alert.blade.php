@props(['type' => 'success', 'dismissible' => true])

@php
$config = [
    'success' => ['bg' => 'bg-success-50 dark:bg-success-600/20', 'text' => 'text-success-700 dark:text-success-400', 'icon' => 'mdi:check-circle-outline'],
    'danger'  => ['bg' => 'bg-danger-50 dark:bg-danger-600/20',   'text' => 'text-danger-700 dark:text-danger-400',   'icon' => 'mdi:alert-circle-outline'],
    'warning' => ['bg' => 'bg-warning-50 dark:bg-warning-600/20', 'text' => 'text-warning-700 dark:text-warning-400', 'icon' => 'mdi:alert-outline'],
    'info'    => ['bg' => 'bg-info-50 dark:bg-info-600/20',       'text' => 'text-info-700 dark:text-info-400',       'icon' => 'mdi:information-outline'],
];
$c = $config[$type] ?? $config['success'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    {{ $attributes->merge(['class' => "flex items-center justify-between gap-4 rounded-xl px-5 py-3 mb-4 font-medium {$c['bg']} {$c['text']}"]) }}>
    <div class="flex items-center gap-3">
        <iconify-icon icon="{{ $c['icon'] }}" class="text-xl flex-shrink-0"></iconify-icon>
        <span>{{ $slot }}</span>
    </div>
    @if($dismissible)
        <button type="button" @click="show = false" class="text-xl flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    @endif
</div>
