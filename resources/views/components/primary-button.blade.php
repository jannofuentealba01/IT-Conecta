<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-brand-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-blue-dark focus:bg-brand-blue-dark active:bg-brand-blue-dark focus:outline-none focus:ring-2 focus:ring-brand-blue-light focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
