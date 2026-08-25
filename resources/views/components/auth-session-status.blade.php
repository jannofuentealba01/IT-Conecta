@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-sustainability-dark']) }}>
        {{ $status }}
    </div>
@endif
