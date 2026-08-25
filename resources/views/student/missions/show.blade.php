@extends('layouts.app')
@section('content')
<style>
    .reveal-shell{max-width:650px;margin:0 auto;padding-bottom:18px}.reveal-card{background:linear-gradient(145deg,var(--surface),var(--positive-soft));border:2px solid var(--brand-green);border-radius:22px;padding:26px;text-align:center;box-shadow:0 16px 38px rgba(17,24,39,.12)}.reveal-icon{font-size:48px}.reveal-instruction{background:var(--surface);border-radius:14px;padding:20px;color:var(--text-primary);font-size:20px;line-height:1.5;font-weight:750;margin:20px 0;border:1px solid var(--border)}.reveal-btn{display:flex;align-items:center;justify-content:center;width:100%;min-height:54px;background:var(--brand-green);color:var(--surface);text-decoration:none;padding:13px 16px;border:0;border-radius:12px;font-weight:800;font-size:16px;margin-top:14px;cursor:pointer;touch-action:manipulation}.reveal-btn:hover{background:var(--brand-green-dark)}.reveal-btn-secondary{background:var(--surface);color:var(--brand-blue-dark);border:1px solid var(--brand-blue-light)}.completion-success{background:var(--positive-soft);border:1px solid var(--brand-green);color:var(--brand-green-dark);padding:16px;border-radius:13px;font-weight:750;margin-top:18px}.completion-warning{background:var(--warning-soft);border:1px solid var(--warning-orange);color:var(--text-primary);padding:16px;border-radius:13px;margin-top:18px;line-height:1.5}@media(max-width:640px){.reveal-shell{margin:-5px -4px 0}.reveal-card{padding:21px 16px;border-radius:18px}.reveal-instruction{font-size:18px;padding:17px}.reveal-icon{font-size:42px}}
</style>
<div class="reveal-shell">
    <div class="reveal-card">
        <div class="reveal-icon">🌱</div>
        <p style="color:var(--brand-green-dark);font-weight:800;text-transform:uppercase;font-size:13px;margin:4px 0;">Misión encontrada</p>
        <h1 style="color:#064e3b;font-size:27px;margin:7px 0;">{{ $mission->activity->name }}</h1>
        <span style="display:inline-block;background:var(--positive-soft);color:var(--brand-green-dark);padding:5px 10px;border-radius:999px;font-size:12px;font-weight:800;">{{ $mission->activity->category }}</span>
        <div class="reveal-instruction">{{ $mission->activity->instructions }}</div>
        <p style="color:var(--warning-orange);font-weight:850;font-size:19px;">⭐ {{ $mission->activity->points }} puntos</p>
        @if($alreadyCompleted)
            <div class="completion-success">✅ Ya completaste esta misión hoy. Podrás repetirla otro día.</div>
        @elseif(!$hasFootprint)
            <div class="completion-warning">Antes de registrar actividades necesitamos conocer tu huella inicial.</div>
            <a href="{{ route('carbon.form', ['new' => 1]) }}" class="reveal-btn">Calcular mi huella primero</a>
        @else
            <form method="POST" action="{{ route('student.missions.complete', $mission->qr_token) }}" id="completionForm">
                @csrf
                <div style="margin-top:18px;padding:17px;border-radius:14px;background:#fff;border:1px solid #a7f3d0;text-align:left;">
                    <strong style="display:block;color:var(--brand-green-dark);font-size:17px;margin-bottom:5px;">Comprueba lo que realizaste</strong>
                    <p style="color:#64748b;font-size:13px;line-height:1.45;margin:0 0 15px;">Responde correctamente las dos preguntas antes de registrar la misión.</p>
                    @error('verification')
                        <div style="background:#fee2e2;color:#991b1b;padding:11px;border-radius:9px;margin-bottom:13px;">{{ $message }}</div>
                    @enderror
                    @foreach($verificationQuestions as $question)
                        <fieldset style="border:0;padding:0;margin:0 0 17px;">
                            <legend style="font-weight:800;color:#064e3b;margin-bottom:9px;line-height:1.4;">{{ $loop->iteration }}. {{ $question['label'] }}</legend>
                            <div style="display:grid;gap:8px;">
                                @foreach($question['options'] as $value => $label)
                                    <label style="display:flex;align-items:flex-start;gap:9px;padding:11px;border:1px solid #d1fae5;border-radius:10px;cursor:pointer;color:#374151;line-height:1.35;">
                                        <input type="radio" name="verification_answers[{{ $question['id'] }}]" value="{{ $value }}" required @checked(old('verification_answers.'.$question['id']) === $value) style="margin-top:3px;">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('verification_answers.'.$question['id'])
                                <div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>
                            @enderror
                        </fieldset>
                    @endforeach
                </div>
                <button type="submit" class="reveal-btn" id="completionButton">✅ Ya realicé esta acción</button>
            </form>
            <p style="color:#9ca3af;font-size:12px;margin:9px 0 0;">Confirma solamente después de realizar la misión.</p>
        @endif

        @if(session('completion_message'))
            <div style="margin-top:18px;padding:18px;border-radius:14px;background:var(--warning-soft);border:1px solid var(--warning-orange);color:var(--text-primary);line-height:1.55;text-align:left;">
                <strong style="display:block;margin-bottom:6px;font-size:17px;">🌍 El impacto de tu acción</strong>
                {{ session('completion_message') }}
            </div>
        @endif
    </div>
    <a href="{{ route('activities.index') }}" class="reveal-btn reveal-btn-secondary">← Volver a misiones</a>
</div>
@if(!$alreadyCompleted && $hasFootprint)
<script>
    document.getElementById('completionForm').addEventListener('submit', () => {
        const button = document.getElementById('completionButton');
        button.disabled = true;
        button.textContent = 'Registrando...';
        button.style.opacity = '.7';
    });
</script>
@endif
@endsection
