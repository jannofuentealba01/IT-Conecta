@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand-blue text-sm font-medium leading-5 text-ui-text focus:outline-none focus:border-brand-blue-dark transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-ui-muted hover:text-ui-text hover:border-ui-border focus:outline-none focus:text-ui-text focus:border-brand-blue transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
