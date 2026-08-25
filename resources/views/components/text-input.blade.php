@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-ui-border focus:border-brand-blue focus:ring-brand-blue-light rounded-md shadow-sm']) }}>
