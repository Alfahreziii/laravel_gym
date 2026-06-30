@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-neutral-300 dark:border-neutral-500 rounded-[10px] bg-white dark:bg-transparent px-3 py-2.5 w-full transition-colors focus:border-primary-500 focus:ring-2 focus:ring-primary-400/40 focus:outline-none disabled:bg-neutral-100 disabled:dark:bg-neutral-800']) }}>
