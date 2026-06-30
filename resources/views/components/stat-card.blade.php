@props([
    'label' => '',
    'value' => '',
    'icon'  => 'mdi:chart-line',
    'color' => 'orange',
    'sub'   => null,
])

@php
$variants = [
    'blue' => [
        'card'  => 'bg-gradient-to-br from-indigo-50 to-violet-50/80 border-indigo-200/50 dark:from-indigo-950/30 dark:to-violet-950/20 dark:border-indigo-800/40',
        'glow'  => '0 4px 24px rgba(99,102,241,0.12), 0 1px 3px rgba(99,102,241,0.06)',
        'chip'  => 'from-indigo-500 to-violet-500',
        'label' => 'text-indigo-500 dark:text-indigo-400',
        'value' => 'text-indigo-900 dark:text-white',
        'sub'   => 'text-indigo-400 dark:text-indigo-500',
    ],
    'cyan' => [
        'card'  => 'bg-gradient-to-br from-cyan-50 to-blue-50/80 border-cyan-200/50 dark:from-cyan-950/30 dark:to-blue-950/20 dark:border-cyan-800/40',
        'glow'  => '0 4px 24px rgba(6,182,212,0.12), 0 1px 3px rgba(6,182,212,0.06)',
        'chip'  => 'from-cyan-500 to-blue-500',
        'label' => 'text-cyan-600 dark:text-cyan-400',
        'value' => 'text-cyan-900 dark:text-white',
        'sub'   => 'text-cyan-500 dark:text-cyan-500',
    ],
    'pink' => [
        'card'  => 'bg-gradient-to-br from-pink-50 to-purple-50/80 border-pink-200/50 dark:from-pink-950/30 dark:to-purple-950/20 dark:border-pink-800/40',
        'glow'  => '0 4px 24px rgba(236,72,153,0.12), 0 1px 3px rgba(236,72,153,0.06)',
        'chip'  => 'from-pink-500 to-purple-500',
        'label' => 'text-pink-600 dark:text-pink-400',
        'value' => 'text-pink-900 dark:text-white',
        'sub'   => 'text-pink-500 dark:text-pink-500',
    ],
    'teal' => [
        'card'  => 'bg-gradient-to-br from-teal-50 to-emerald-50/80 border-teal-200/50 dark:from-teal-950/30 dark:to-emerald-950/20 dark:border-teal-800/40',
        'glow'  => '0 4px 24px rgba(20,184,166,0.12), 0 1px 3px rgba(20,184,166,0.06)',
        'chip'  => 'from-teal-500 to-emerald-500',
        'label' => 'text-teal-600 dark:text-teal-400',
        'value' => 'text-teal-900 dark:text-white',
        'sub'   => 'text-teal-500 dark:text-teal-500',
    ],
    'orange' => [
        'card'  => 'bg-gradient-to-br from-primary-50 to-orange-50/80 border-primary-200/50 dark:from-primary-900/20 dark:to-orange-950/15 dark:border-primary-800/40',
        'glow'  => '0 4px 24px rgba(242,98,46,0.12), 0 1px 3px rgba(242,98,46,0.06)',
        'chip'  => 'from-primary-500 to-orange-400',
        'label' => 'text-primary-600 dark:text-primary-400',
        'value' => 'text-primary-900 dark:text-white',
        'sub'   => 'text-primary-500 dark:text-primary-400',
    ],
];

$v = $variants[$color] ?? $variants['orange'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl p-5 border ' . $v['card']]) }}
     style="box-shadow: {{ $v['glow'] }}">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider mb-2 {{ $v['label'] }}">{{ $label }}</p>
            <p class="font-display text-3xl font-bold tabular-nums leading-none {{ $v['value'] }}">{{ $value }}</p>
            @if($sub)
                <p class="text-sm mt-1.5 {{ $v['sub'] }}">{{ $sub }}</p>
            @endif
        </div>
        <div class="flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center bg-gradient-to-br {{ $v['chip'] }}">
            <iconify-icon icon="{{ $icon }}" class="text-xl text-white"></iconify-icon>
        </div>
    </div>
</div>
