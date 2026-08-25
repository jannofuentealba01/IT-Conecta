<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-impact-high border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[var(--danger-dark)] active:bg-[var(--danger-dark)] focus:outline-none focus:ring-2 focus:ring-impact-high focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
