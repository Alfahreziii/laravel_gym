@props([
    'label' => '',
    'value' => '',
    'icon'  => 'member',
    'color' => 'orange',
    'sub'   => null,
    'trend' => null,
])

@php
$variants = [
    // blue → primary tone (biru→ungu)
    'blue' => [
        'bg'        => 'linear-gradient(135deg,rgba(59,130,246,.13),rgba(139,92,246,.11))',
        'border'    => 'rgba(124,108,246,.24)',
        'glow'      => '0 14px 34px -12px rgba(99,102,241,.32)',
        'glowHover' => '0 14px 34px -12px rgba(99,102,241,.46)',
        'iconGrad'  => 'linear-gradient(135deg,#3B82F6,#8B5CF6)',
        'label'     => 'text-indigo-500 dark:text-indigo-400',
        'value'     => 'text-ink dark:text-ink-d',
        'sub'       => 'text-ink-2 dark:text-ink-d2',
        'badge'     => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300',
    ],
    // cyan → attend tone (cyan→teal)
    'cyan' => [
        'bg'        => 'linear-gradient(135deg,rgba(6,182,212,.13),rgba(13,148,136,.10))',
        'border'    => 'rgba(6,182,212,.24)',
        'glow'      => '0 14px 34px -12px rgba(6,182,212,.30)',
        'glowHover' => '0 14px 34px -12px rgba(6,182,212,.46)',
        'iconGrad'  => 'linear-gradient(135deg,#06B6D4,#0D9488)',
        'label'     => 'text-cyan-600 dark:text-cyan-400',
        'value'     => 'text-ink dark:text-ink-d',
        'sub'       => 'text-ink-2 dark:text-ink-d2',
        'badge'     => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-300',
    ],
    // pink → rose accent
    'pink' => [
        'bg'        => 'linear-gradient(135deg,rgba(236,72,153,.13),rgba(168,85,247,.10))',
        'border'    => 'rgba(236,72,153,.24)',
        'glow'      => '0 14px 34px -12px rgba(236,72,153,.30)',
        'glowHover' => '0 14px 34px -12px rgba(236,72,153,.46)',
        'iconGrad'  => 'linear-gradient(135deg,#EC4899,#A855F7)',
        'label'     => 'text-pink-600 dark:text-pink-400',
        'value'     => 'text-ink dark:text-ink-d',
        'sub'       => 'text-ink-2 dark:text-ink-d2',
        'badge'     => 'bg-pink-100 text-pink-700 dark:bg-pink-900/50 dark:text-pink-300',
    ],
    // teal → money tone (emerald→teal)
    'teal' => [
        'bg'        => 'linear-gradient(135deg,rgba(16,185,129,.13),rgba(20,184,166,.10))',
        'border'    => 'rgba(16,185,129,.24)',
        'glow'      => '0 14px 34px -12px rgba(16,185,129,.30)',
        'glowHover' => '0 14px 34px -12px rgba(16,185,129,.46)',
        'iconGrad'  => 'linear-gradient(135deg,#10B981,#14B8A6)',
        'label'     => 'text-emerald-600 dark:text-emerald-400',
        'value'     => 'text-ink dark:text-ink-d',
        'sub'       => 'text-ink-2 dark:text-ink-d2',
        'badge'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
    ],
    // orange → warn tone (amber→merah)
    'orange' => [
        'bg'        => 'linear-gradient(135deg,rgba(245,158,11,.14),rgba(239,68,68,.11))',
        'border'    => 'rgba(245,158,11,.26)',
        'glow'      => '0 14px 34px -12px rgba(239,68,68,.30)',
        'glowHover' => '0 14px 34px -12px rgba(239,68,68,.46)',
        'iconGrad'  => 'linear-gradient(135deg,#F59E0B,#EF4444)',
        'label'     => 'text-amber-600 dark:text-amber-400',
        'value'     => 'text-ink dark:text-ink-d',
        'sub'       => 'text-ink-2 dark:text-ink-d2',
        'badge'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
    ],
];

$v             = $variants[$color] ?? $variants['orange'];
$trendPositive = $trend && str_starts_with(trim($trend), '+');
$trendText     = $trend ? ltrim(trim($trend), '+-') : null;
@endphp

<div {{ $attributes->merge(['class' => 'stat-card rounded-2xl p-5 border transition-all duration-300']) }}
     style="background: {{ $v['bg'] }}; border-color: {{ $v['border'] }}; box-shadow: {{ $v['glow'] }}; backdrop-filter: blur(7px); --glow-hover: {{ $v['glowHover'] }}">

    {{-- Top row: icon chip + optional trend badge --}}
    <div class="flex items-start justify-between mb-3">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-sm"
             style="background: {{ $v['iconGrad'] }}">
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
