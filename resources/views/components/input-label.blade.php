@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-ui-muted']) }}>
    {{ $value ?? $slot }}
</label>
