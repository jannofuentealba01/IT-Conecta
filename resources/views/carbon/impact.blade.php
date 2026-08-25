@extends('layouts.app')

@section('content')
<style>
    .impact-shell{max-width:760px;margin:0 auto}
    .impact-card{border-radius:22px;padding:30px;box-shadow:0 16px 40px rgba(17,24,39,.10);border:2px solid}
    .impact-card.low{background:var(--positive-soft);border-color:var(--impact-low);color:var(--brand-green-dark)}
    .impact-card.medium{background:var(--impact-medium);border-color:var(--impact-medium);color:var(--text-primary)}
    .impact-card.high{background:var(--danger-soft);border-color:var(--impact-high);color:var(--danger-dark)}
    .impact-label{font-size:13px;font-weight:850;text-transform:uppercase;letter-spacing:.06em;margin:0 0 8px}
    .footprint{background:rgba(255,255,255,.72);border-radius:16px;padding:20px;text-align:center;margin-bottom:22px}
    .footprint strong{display:block;font-size:35px;line-height:1.1;margin-bottom:5px}
    .curiosity{background:var(--surface);border-radius:16px;padding:24px;margin:0 0 18px;font-size:21px;line-height:1.55;font-weight:750}
    .source{background:rgba(255,255,255,.66);border-radius:13px;padding:16px;line-height:1.5;font-size:14px}
    .source a{color:inherit;font-weight:800;text-decoration:underline;text-underline-offset:3px}
    .notice{margin:18px 0 0;padding-top:17px;border-top:1px solid currentColor;font-size:12px;line-height:1.55;opacity:.86}
    .actions{display:flex;gap:10px;margin-top:18px}
    .impact-action{min-height:48px;display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:11px;text-decoration:none;font-weight:800;background:var(--brand-blue);color:var(--surface);border:1px solid var(--brand-blue)}
    @media(max-width:640px){.impact-card{padding:20px 16px}.curiosity{font-size:18px;padding:19px}.actions{flex-direction:column}.impact-action{width:100%}}
</style>

<div class="impact-shell">
    <section class="impact-card {{ $impact['classification'] }}">
        <p class="impact-label">🌍 Tu impacto en cifras reales</p>

        <div class="footprint">
            <span>Mi huella anual</span>
            <strong>{{ number_format((float) $footprint->initial_kg_co2e_year, 2, ',', '.') }}</strong>
            <span>kg CO₂e al año</span>
        </div>

        <p class="impact-label">¿Sabías que…?</p>
        <div class="curiosity">{{ $impact['fact']['text'] }}</div>

        <div class="source">
            <strong>Fuente:</strong> {{ $impact['fact']['source'] }}<br>
            @foreach($impact['fact']['references'] as $reference)
                <a href="{{ $reference['url'] }}" target="_blank" rel="noopener noreferrer">{{ $reference['label'] }}</a>@unless($loop->last)<span> · </span>@endunless
            @endforeach
        </div>

        <p class="notice"><strong>Aviso educativo:</strong> estas equivalencias son aproximadas y buscan facilitar la comprensión. No significan que hayas consumido literalmente esa cantidad de combustible, electricidad o recursos, ni constituyen una medición certificada.</p>
    </section>

    <div class="actions">
        <a class="impact-action" href="{{ $formUrl ?? route('carbon.form') }}">← Volver a mi resultado</a>
        <a class="impact-action" href="{{ $panelUrl ?? route('student.dashboard') }}">Volver al panel</a>
    </div>
</div>
@endsection
