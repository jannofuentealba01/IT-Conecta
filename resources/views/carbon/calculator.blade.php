@extends('layouts.app')

@section('content')
<style>
    .carbon-shell { max-width:760px; margin:0 auto; }
    .carbon-card { background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:28px; box-shadow:0 15px 35px rgba(17,24,39,.08); }
    .carbon-title { color:var(--brand-blue-dark); font-size:25px; margin:0 0 7px; }
    .carbon-subtitle { color:var(--text-secondary); line-height:1.5; margin:0 0 22px; }
    .question { display:none; }
    .question.active { display:block; }
    .question-label { display:block; color:var(--text-primary); font-size:19px; font-weight:750; line-height:1.4; }
    .form-select { width:100%; padding:13px; border-radius:11px; border:1.5px solid var(--brand-blue-light); margin:18px 0; color:var(--text-primary); background:var(--surface); font-size:15px; }
    .form-select:focus { border-color:var(--brand-blue); box-shadow:0 0 0 3px var(--focus-ring); }
    .carbon-btn { min-height:48px; display:inline-flex; align-items:center; justify-content:center; background:var(--brand-blue); color:var(--surface); border:0; padding:12px 19px; border-radius:10px; cursor:pointer; font-weight:750; text-decoration:none; }
    .carbon-btn:hover { background:var(--brand-blue-dark); }
    .carbon-btn-secondary { background:var(--info-soft); color:var(--brand-blue-dark); border:1px solid var(--brand-blue-light); }
    .question-actions { display:flex; justify-content:space-between; gap:10px; }
    .progress-track { height:8px; background:var(--border); border-radius:999px; margin-bottom:25px; overflow:hidden; }
    .progress-bar { height:100%; width:10%; background:linear-gradient(90deg,var(--brand-blue),var(--brand-blue-light)); transition:width .2s; }
    .result-box { padding:25px; border-radius:17px; text-align:center; margin-top:20px; }
    .result-low { background:var(--positive-soft); border:1px solid var(--impact-low); color:var(--brand-green-dark); }
    .result-medium { background:var(--impact-medium); border:1px solid var(--impact-medium); color:var(--text-primary); }
    .result-high { background:var(--danger-soft); border:1px solid var(--impact-high); color:var(--danger-dark); }
    .history { margin-top:18px; }
    .history-row { display:flex; justify-content:space-between; gap:15px; padding:13px 0; border-bottom:1px solid var(--border); }
    .history-row:last-child { border-bottom:0; }
    .history-value { display:inline-block; padding:5px 9px; border-radius:8px; font-weight:850; }
    .history-value-low { color:var(--brand-green-dark); background:var(--positive-soft); }
    .history-value-medium { color:var(--text-primary); background:var(--impact-medium); }
    .history-value-high { color:var(--danger-dark); background:var(--danger-soft); }
    .method-note { margin-top:18px; padding:13px; background:var(--surface-muted); border-radius:10px; color:var(--text-secondary); font-size:12px; line-height:1.5; }
    .result-actions { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
    .impact-btn { background:#fff; color:inherit; border:1px solid currentColor; }
    .mobile-return-end{display:none}
    @media(max-width:640px) { .carbon-card{padding:20px 16px}.question-actions{flex-direction:column-reverse}.carbon-btn{width:100%}.result-actions{flex-direction:column}.result-actions .early-return{display:none}.mobile-return-end{display:flex;margin-top:16px;width:100%} }
</style>

<div class="carbon-shell">
    <div class="carbon-card">
        <h1 class="carbon-title">📊 Calculadora de Huella de Carbono</h1>
        <p class="carbon-subtitle">Obtendrás una estimación educativa de tus emisiones anuales. El resultado quedará guardado para medir tu progreso.</p>

        @error('calculator')
            <div style="background:var(--danger-soft); color:var(--danger-dark); padding:12px; border-radius:10px; margin-bottom:18px;">{{ $message }}</div>
        @enderror

        @if($showQuestionnaire)
            @if($calculationCount === 0)
                <div style="background:var(--positive-soft); color:var(--brand-green-dark); padding:12px; border-radius:10px; margin-bottom:18px; line-height:1.45;">
                    Responde con calma y de acuerdo con tus hábitos cotidianos.
                </div>
            @endif
            @if($errors->any())
                <div style="background:var(--danger-soft); color:var(--danger-dark); padding:12px; border-radius:10px; margin-bottom:18px;">Revisa las respuestas antes de continuar.</div>
            @endif

            <div class="progress-track"><div class="progress-bar" id="progressBar"></div></div>
            <form method="POST" action="{{ $calculateUrl ?? route('carbon.calculate') }}" id="carbonForm">
                @csrf
                @foreach($questions as $key => $question)
                    <div class="question {{ $loop->first ? 'active' : '' }}" data-question="{{ $loop->index }}">
                        <p style="color:var(--brand-blue-dark); font-size:13px; font-weight:800; margin-bottom:8px;">Pregunta {{ $loop->iteration }} de {{ count($questions) }}</p>
                        <label class="question-label" for="{{ $key }}">{{ $question['label'] }}</label>
                        <select id="{{ $key }}" name="{{ $key }}" class="form-select" required>
                            <option value="">Selecciona una alternativa...</option>
                            @foreach($question['options'] as $value => $option)
                                <option value="{{ $value }}" @selected(old($key) === $value)>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        @error($key)<div style="color:var(--danger-dark); font-size:13px; margin-bottom:12px;">{{ $message }}</div>@enderror
                        <div class="question-actions">
                            @unless($loop->first)<button type="button" class="carbon-btn carbon-btn-secondary previous">← Anterior</button>@else<span></span>@endunless
                            @if($loop->last)<button type="submit" class="carbon-btn">Guardar mi resultado ⚡</button>@else<button type="button" class="carbon-btn next">Siguiente →</button>@endif
                        </div>
                    </div>
                @endforeach
            </form>
        @elseif($currentFootprint)
            <div class="result-box result-{{ $classification['key'] }}">
                <div style="font-size:34px;">{{ $classification['icon'] }}</div>
                <h2 style="font-size:34px; margin:7px 0;">{{ number_format((float)$currentFootprint->initial_kg_co2e_year, 2, ',', '.') }}</h2>
                <p style="font-weight:750; margin:0 0 7px;">kg CO₂e por año</p>
                <p style="margin:0 0 19px;">{{ $classification['message'] }}</p>
                <div class="result-actions">
                    <a href="{{ $panelUrl ?? route('student.dashboard') }}" class="carbon-btn early-return">Volver al panel</a>
                    <a href="{{ $impactUrl ?? route('carbon.impact') }}" class="carbon-btn impact-btn">Ver impacto en cifras reales</a>
                </div>
                @if($canCalculate)
                    <div style="margin-top:13px;">
                        <a href="{{ ($formUrl ?? route('carbon.form')).'?new=1' }}" style="color:var(--text-secondary);font-size:12px;text-decoration:underline;text-underline-offset:2px;">Repetir</a>
                    </div>
                @endif
            </div>
            <p class="method-note">Este resultado es una estimación educativa basada en la versión {{ $currentFootprint->calculator_version }} de la calculadora. No representa una medición certificada.</p>
        @endif
    </div>

    @if($history->isNotEmpty())
        <div class="carbon-card history">
            <h2 style="color:var(--brand-blue-dark); font-size:18px; margin:0 0 5px;">Historial de cálculos</h2>
            <p class="carbon-subtitle" style="margin-bottom:8px;">El cálculo más reciente se utiliza como tu huella inicial vigente.</p>
            @foreach($history as $item)
                <div class="history-row">
                    <div><strong class="history-value history-value-{{ $item->footprint_classification['key'] }}">{{ number_format((float)$item->initial_kg_co2e_year, 2, ',', '.') }} kg CO₂e/año</strong><div style="color:var(--text-secondary); font-size:12px;margin-top:4px;">{{ $item->created_at->format('d/m/Y H:i') }}</div></div>
                    @if($item->is_current)<span style="color:var(--brand-green-dark); font-size:12px; font-weight:800;">VIGENTE</span>@else<span style="color:var(--text-secondary); font-size:12px;">Anterior</span>@endif
                </div>
            @endforeach
        </div>
    @endif

    @if($showQuestionnaire)
        <a href="{{ $panelUrl ?? route('student.dashboard') }}" class="carbon-btn carbon-btn-secondary" style="margin-top:16px; width:100%;">← Volver al panel</a>
    @elseif($currentFootprint)
        <a href="{{ $panelUrl ?? route('student.dashboard') }}" class="carbon-btn carbon-btn-secondary mobile-return-end">← Volver al panel</a>
    @endif
</div>

@if($showQuestionnaire)
<script>
    const questions = Array.from(document.querySelectorAll('.question'));
    const progressBar = document.getElementById('progressBar');
    let current = 0;

    function showQuestion(index) {
        questions[current].classList.remove('active');
        current = index;
        questions[current].classList.add('active');
        progressBar.style.width = `${((current + 1) / questions.length) * 100}%`;
    }

    document.querySelectorAll('.next').forEach(button => button.addEventListener('click', () => {
        const select = questions[current].querySelector('select');
        if (!select.value) { select.reportValidity(); return; }
        showQuestion(current + 1);
    }));
    document.querySelectorAll('.previous').forEach(button => button.addEventListener('click', () => showQuestion(current - 1)));
    progressBar.style.width = `${100 / questions.length}%`;
</script>
@endif
@endsection
