@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-neutral-600 dark:text-neutral-200 mb-2']) }}>
    {{ $value ?? $slot }}
</label>
