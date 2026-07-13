<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-200 font-semibold rounded-[10px] hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
