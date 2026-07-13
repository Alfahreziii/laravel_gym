<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 px-4 py-2.5 bg-danger-500 hover:bg-danger-600 text-white font-semibold rounded-[10px] transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-danger-400 focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
