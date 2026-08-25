<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-ui-surface border border-ui-border rounded-md font-semibold text-xs text-ui-muted uppercase tracking-widest shadow-sm hover:bg-ui-background focus:outline-none focus:ring-2 focus:ring-brand-blue-light focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
