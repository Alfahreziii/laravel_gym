@props(['type' => 'neutral', 'dot' => false])

@php
$colors = [
    'success' => 'bg-success-50 text-success-700 dark:bg-success-600/20 dark:text-success-400',
    'danger'  => 'bg-danger-50 text-danger-700 dark:bg-danger-600/20 dark:text-danger-400',
    'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-600/20 dark:text-warning-400',
    'info'    => 'bg-info-50 text-info-700 dark:bg-info-600/20 dark:text-info-400',
    'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-600/20 dark:text-primary-400',
    'neutral' => 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300',
];
$dotColors = [
    'success' => 'bg-success-500', 'danger' => 'bg-danger-500', 'warning' => 'bg-warning-500',
    'info' => 'bg-info-500', 'primary' => 'bg-primary-500', 'neutral' => 'bg-neutral-400',
];
$cls = $colors[$type] ?? $colors['neutral'];
$dotCls = $dotColors[$type] ?? $dotColors['neutral'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold $cls"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotCls }} flex-shrink-0"></span>
    @endif
    {{ $slot }}
</span>
