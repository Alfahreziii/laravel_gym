@props([
    'label' => '',
    'value' => '',
    'icon'  => 'member',
    'color' => 'orange',
    'sub'   => null,
    'trend' => null,   // e.g. '+4.8%' or '-2.1%' — only show if data available
])

@php
$variants = [
    'blue' => [
        'card'      => 'bg-gradient-to-br from-indigo-50 to-violet-50/80 border-indigo-200/50 dark:from-indigo-950/30 dark:to-violet-950/20 dark:border-indigo-800/40',
        'glow'      => '0 4px 24px rgba(99,102,241,0.12), 0 1px 3px rgba(99,102,241,0.06)',
        'glowHover' => '0 8px 32px rgba(99,102,241,0.22), 0 2px 8px rgba(99,102,241,0.12)',
        'chip'      => 'from-indigo-500 to-violet-500',
        'label'     => 'text-indigo-500 dark:text-indigo-400',
        'value'     => 'text-indigo-900 dark:text-white',
        'sub'       => 'text-indigo-400 dark:text-indigo-500',
        'badge'     => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300',
    ],
    'cyan' => [
        'card'      => 'bg-gradient-to-br from-cyan-50 to-blue-50/80 border-cyan-200/50 dark:from-cyan-950/30 dark:to-blue-950/20 dark:border-cyan-800/40',
        'glow'      => '0 4px 24px rgba(6,182,212,0.12), 0 1px 3px rgba(6,182,212,0.06)',
        'glowHover' => '0 8px 32px rgba(6,182,212,0.22), 0 2px 8px rgba(6,182,212,0.12)',
        'chip'      => 'from-cyan-500 to-blue-500',
        'label'     => 'text-cyan-600 dark:text-cyan-400',
        'value'     => 'text-cyan-900 dark:text-white',
        'sub'       => 'text-cyan-500 dark:text-cyan-500',
        'badge'     => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-300',
    ],
    'pink' => [
        'card'      => 'bg-gradient-to-br from-pink-50 to-purple-50/80 border-pink-200/50 dark:from-pink-950/30 dark:to-purple-950/20 dark:border-pink-800/40',
        'glow'      => '0 4px 24px rgba(236,72,153,0.12), 0 1px 3px rgba(236,72,153,0.06)',
        'glowHover' => '0 8px 32px rgba(236,72,153,0.22), 0 2px 8px rgba(236,72,153,0.12)',
        'chip'      => 'from-pink-500 to-purple-500',
        'label'     => 'text-pink-600 dark:text-pink-400',
        'value'     => 'text-pink-900 dark:text-white',
        'sub'       => 'text-pink-500 dark:text-pink-500',
        'badge'     => 'bg-pink-100 text-pink-700 dark:bg-pink-900/50 dark:text-pink-300',
    ],
    'teal' => [
        'card'      => 'bg-gradient-to-br from-teal-50 to-emerald-50/80 border-teal-200/50 dark:from-teal-950/30 dark:to-emerald-950/20 dark:border-teal-800/40',
        'glow'      => '0 4px 24px rgba(20,184,166,0.12), 0 1px 3px rgba(20,184,166,0.06)',
        'glowHover' => '0 8px 32px rgba(20,184,166,0.22), 0 2px 8px rgba(20,184,166,0.12)',
        'chip'      => 'from-teal-500 to-emerald-500',
        'label'     => 'text-teal-600 dark:text-teal-400',
        'value'     => 'text-teal-900 dark:text-white',
        'sub'       => 'text-teal-500 dark:text-teal-500',
        'badge'     => 'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300',
    ],
    'orange' => [
        'card'      => 'bg-gradient-to-br from-primary-50 to-orange-50/80 border-primary-200/50 dark:from-primary-900/20 dark:to-orange-950/15 dark:border-primary-800/40',
        'glow'      => '0 4px 24px rgba(242,98,46,0.12), 0 1px 3px rgba(242,98,46,0.06)',
        'glowHover' => '0 8px 32px rgba(242,98,46,0.22), 0 2px 8px rgba(242,98,46,0.12)',
        'chip'      => 'from-primary-500 to-orange-400',
        'label'     => 'text-primary-600 dark:text-primary-400',
        'value'     => 'text-primary-900 dark:text-white',
        'sub'       => 'text-primary-500 dark:text-primary-400',
        'badge'     => 'bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300',
    ],
];

$v               = $variants[$color] ?? $variants['orange'];
$glow            = $v['glow'];
$glowHover       = $v['glowHover'];
$trendPositive   = $trend && str_starts_with(trim($trend), '+');
$trendText       = $trend ? ltrim(trim($trend), '+-') : null;
@endphp

<div x-data
     x-on:mouseenter="$el.style.boxShadow='{{ $glowHover }}'; $el.style.transform='translateY(-2px)'"
     x-on:mouseleave="$el.style.boxShadow='{{ $glow }}'; $el.style.transform=''"
     {{ $attributes->merge(['class' => 'rounded-2xl p-5 border transition-all duration-200 ' . $v['card']]) }}
     style="box-shadow: {{ $glow }}">

    {{-- Top row: icon chip (left) + trend badge (right, conditional) --}}
    <div class="flex items-start justify-between mb-3">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-gradient-to-br {{ $v['chip'] }} shadow-sm">
            <x-dynamic-component :component="'icon.' . $icon" class="text-xl text-white" />
        </div>
        @if($trend)
            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-semibold
                {{ $trendPositive
                    ? $v['badge']
                    : 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-300' }}">
                @if($trendPositive)
                    <x-icon.arrow-up class="text-sm -ml-0.5" />
                @else
                    <x-icon.arrow-down class="text-sm -ml-0.5" />
                @endif
                {{ $trendText }}
            </span>
        @endif
    </div>

    {{-- Label --}}
    <p class="text-xs font-semibold uppercase tracking-wider mb-1.5 {{ $v['label'] }}">{{ $label }}</p>

    {{-- Value --}}
    <p class="font-display text-3xl font-bold tabular-nums leading-none {{ $v['value'] }}">{{ $value }}</p>

    {{-- Sub-text --}}
    @if($sub)
        <p class="text-sm mt-2 {{ $v['sub'] }}">{{ $sub }}</p>
    @endif

</div>
