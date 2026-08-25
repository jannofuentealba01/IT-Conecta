@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-blue text-start text-base font-medium text-brand-blue-dark bg-[var(--info-soft)] focus:outline-none focus:text-brand-blue-dark focus:bg-[var(--info-soft)] focus:border-brand-blue-dark transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-ui-muted hover:text-ui-text hover:bg-ui-background hover:border-ui-border focus:outline-none focus:text-ui-text focus:bg-ui-background focus:border-ui-border transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
