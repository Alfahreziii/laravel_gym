@props(['noPadding' => false, 'flush' => false])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @isset($header)
        <div class="card-header flex items-center justify-between gap-3">
            {{ $header }}
        </div>
    @endisset
    <div @class(['card-body', 'p-0' => $noPadding || $flush])>
        {{ $slot }}
    </div>
</div>
