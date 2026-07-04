@props(['title', 'subtitle' => null])

{{-- Page header: h1 + subtitle (kiri) + action buttons (kanan) --}}
<div class="flex items-end justify-between gap-4 flex-wrap mb-5">
    <div>
        <h1 class="font-display text-[32px] font-bold leading-[1.05] tracking-[.005em] text-ink dark:text-ink-d">
            {{ $title }}
        </h1>
        @if($subtitle)
            <p class="text-sm text-ink-2 dark:text-ink-d2 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-2 flex-wrap shrink-0">{{ $actions }}</div>
    @endisset
</div>

{{-- Card: flush (p-0) agar tabel edge-to-edge, padding diatur per-section di data-table --}}
<x-card flush>
    {{ $slot }}
</x-card>
