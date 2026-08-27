@props([
    'id' => null,
    'variant' => 'default',
    'label' => 'Acciones principales',
])

@php
    $allowedVariants = ['default', 'primary', 'positive', 'danger'];
    $resolvedVariant = in_array($variant, $allowedVariants, true) ? $variant : 'default';
@endphp

@once
<style>
    .sticky-action-bar{position:sticky;z-index:20;bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:18px;padding:13px;border:1px solid var(--border);border-radius:14px;background:color-mix(in srgb,var(--surface) 96%,transparent);backdrop-filter:blur(10px);box-shadow:0 8px 25px rgba(17,24,39,.16)}
    .sticky-action-bar--primary{border-color:var(--brand-blue-soft-border)}
    .sticky-action-bar--positive{border-color:var(--brand-green-soft-border)}
    .sticky-action-bar--danger{border-color:var(--danger-soft-border)}
    .sticky-action-bar__summary{min-width:0;color:var(--text-secondary)}
    .sticky-action-bar__summary strong{color:var(--text-primary)}
    .sticky-action-bar--positive .sticky-action-bar__summary>strong{color:var(--brand-green-dark)}
    .sticky-action-bar--danger .sticky-action-bar__summary>strong{color:var(--danger-dark)}
    .sticky-action-bar__actions{display:flex;align-items:center;justify-content:flex-end;gap:9px;flex-wrap:wrap}
    .sticky-action-bar__actions form{margin:0}
    @media(max-width:640px){.sticky-action-bar{bottom:6px;flex-direction:column;align-items:stretch}.sticky-action-bar__summary{text-align:center}.sticky-action-bar__actions,.sticky-action-bar__actions form{display:flex;width:100%;flex-direction:column}.sticky-action-bar__actions>*{width:100%}}
</style>
@endonce

<section
    @if($id) id="{{ $id }}" @endif
    {{ $attributes->class(['sticky-action-bar', 'sticky-action-bar--'.$resolvedVariant]) }}
    aria-label="{{ $label }}"
>
    <div class="sticky-action-bar__summary">{{ $summary ?? $slot }}</div>
    <div class="sticky-action-bar__actions">{{ $actions ?? '' }}</div>
</section>
