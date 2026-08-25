<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Juego del Impostor | IT Conecta</title>@vite(['resources/css/app.css', 'resources/js/app.js'])@include('partials.color-tokens')
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;padding:14px;font-family:"Segoe UI",Arial,sans-serif;color:var(--text-primary);background:var(--surface-muted)}.page{width:min(650px,100%);margin:auto}.top{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px}.back,.refresh{min-height:48px;padding:0 14px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:var(--brand-blue-dark);text-decoration:none;font-weight:800}.card{background:var(--surface);border-radius:19px;padding:19px;margin-bottom:13px;box-shadow:0 10px 25px rgba(17,24,39,.09)}.timer{text-align:center;border:2px solid var(--brand-purple)}.timer.warning{background:var(--warning-soft);border-color:var(--warning-orange);color:var(--text-primary)}.timer.closed{background:var(--danger-soft);border-color:var(--danger);color:var(--danger-dark)}.timer strong{display:block;font-size:38px;line-height:1;margin:7px 0}.role{text-align:center;color:var(--surface)}.role h1{font-size:25px;margin:5px 0 8px}.crew{background:linear-gradient(135deg,var(--brand-purple),var(--brand-blue))}.impostor{background:linear-gradient(135deg,var(--brand-purple-dark),var(--brand-purple))}.phase{display:inline-flex;padding:5px 10px;border-radius:99px;background:var(--game-soft);color:var(--brand-purple-dark);font-size:12px;font-weight:850}h2{font-size:19px;margin:0 0 9px}p{line-height:1.5}.input{width:100%;min-height:52px;border:2px solid var(--brand-purple);border-radius:12px;padding:12px;font-size:16px}.btn{width:100%;min-height:54px;border:0;border-radius:13px;margin-top:10px;background:var(--brand-purple);color:var(--surface);font-size:16px;font-weight:850;cursor:pointer}.btn:hover{background:var(--brand-purple-dark)}.btn:disabled{opacity:.55}.vote{background:var(--warning-orange);margin-top:8px}.clues{list-style:none;padding:0;margin:0;display:grid;gap:8px}.clues li{padding:11px;border-radius:10px;background:var(--surface-muted)}.alert{padding:12px;border-radius:11px;margin-bottom:12px;font-weight:750}.vote-alert{background:var(--warning-soft);color:var(--text-primary);border:2px solid var(--warning-orange)}.success{background:var(--positive-soft);color:var(--brand-green-dark)}.error{background:var(--danger-soft);color:var(--danger-dark)}.muted{color:var(--text-secondary);font-size:13px}
    </style>
</head>
<body><main class="page">
    <nav class="top"><strong>🎭 Juego del Impostor</strong><a class="refresh" href="{{ route('student.impostor.show',$game) }}">Actualizar</a></nav>
    @include('student.partials.identity-bar', ['participant' => $participant])
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert error">No fue posible registrar la respuesta. Actualiza la pantalla e inténtalo nuevamente.</div>@endif
    <section id="gameTimer" class="card timer {{ $game->status === 'voting' ? 'warning' : ($game->status === 'closed' ? 'closed' : '') }}" data-status="{{ $game->status }}" data-voting-at="{{ $game->voting_at?->toIso8601String() }}" data-closes-at="{{ $game->closes_at?->toIso8601String() }}" data-results-at="{{ $game->results_at?->toIso8601String() }}">
        <span id="timerLabel">{{ $game->status === 'closed' ? 'Partida finalizada' : 'Tiempo restante' }}</span>
        <strong id="timerValue">--:--</strong>
        <span id="timerHelp">{{ $game->status === 'voting' ? '¡Vota ahora! Solo queda el último minuto.' : ($game->status === 'closed' ? 'Los resultados aparecerán en 30 segundos.' : 'En el minuto 4 comenzará la votación obligatoria.') }}</span>
    </section>
    @php($isImpostor = $game->impostors->contains('id', $participant->id) || ($game->impostors->isEmpty() && $participant->id === $game->impostor_id))
    <section class="card role {{ $isImpostor ? 'impostor' : 'crew' }}">
        @if($isImpostor)<div>😈 TU ROL</div><h1>Eres uno de los impostores</h1><p>No conoces la palabra. Escucha las pistas e intenta pasar desapercibido.</p>@else<div>🌱 PALABRA SECRETA</div><h1>{{ $game->word }}</h1><p>Entrega una pista relacionada sin decir la palabra.</p>@endif
    </section>
    @if($game->status === 'voting' && !$hasVoted)<div class="alert vote-alert">⚠️ Llegó el minuto 4. Debes votar antes de que termine el tiempo.</div>@endif
    <section class="card"><span class="phase">{{ $game->status === 'playing' ? 'Fase de pistas' : ($game->status === 'voting' ? 'Fase de votación obligatoria' : 'Votación cerrada') }}</span>
        @if($game->status === 'playing')<h2 style="margin-top:13px">Escribe una pista</h2>@if($hasClue)<p class="alert success">Tu pista ya fue enviada. Espera a que el profesor inicie la votación.</p>@else<form method="POST" action="{{ route('student.impostor.clue',$game) }}" onsubmit="this.querySelector('button').disabled=true">@csrf<input class="input" name="clue" maxlength="120" autocomplete="off" required placeholder="Una palabra o frase breve"><button class="btn">Enviar pista</button></form>@endif
        @elseif($game->status === 'voting')<h2 style="margin-top:13px">¿Quién es el impostor?</h2>@if($hasVoted)<p class="alert success">Tu voto quedó registrado. Espera los resultados.</p>@else @foreach($game->room->participants as $suspect) @if($suspect->id !== $participant->id)<form method="POST" action="{{ route('student.impostor.vote',$game) }}" onsubmit="this.querySelector('button').disabled=true">@csrf<input type="hidden" name="suspect_id" value="{{ $suspect->id }}"><button type="submit" class="btn vote">Votar por {{ $suspect->name }}</button></form>@endif @endforeach @endif
        @else<h2 style="margin-top:13px">La votación terminó</h2><p>Espera: los resultados aparecerán automáticamente.</p>@endif
    </section>
    <section class="card"><h2>Pistas compartidas</h2><ul class="clues">@forelse($game->clues as $clue)<li><strong>{{ $clue->participant?->name }}:</strong> {{ $clue->clue }}</li>@empty<li class="muted">Todavía no hay pistas.</li>@endforelse</ul></section>
    <a class="back" href="{{ route('student.dashboard') }}">← Volver al panel</a>
</main>
<script>
const timer = document.getElementById('gameTimer');
const value = document.getElementById('timerValue');
const label = document.getElementById('timerLabel');
const help = document.getElementById('timerHelp');
const votingAt = Date.parse(timer.dataset.votingAt || '');
const closesAt = Date.parse(timer.dataset.closesAt || '');
const resultsAt = Date.parse(timer.dataset.resultsAt || '');
let currentStatus = timer.dataset.status;

function format(seconds) {
    const safe = Math.max(0, seconds);
    return `${String(Math.floor(safe / 60)).padStart(2,'0')}:${String(safe % 60).padStart(2,'0')}`;
}
function tick() {
    const now = Date.now();
    if (currentStatus === 'closed' || now >= closesAt) {
        timer.className = 'card timer closed';
        label.textContent = 'Partida finalizada';
        value.textContent = format(Math.ceil((resultsAt - now) / 1000));
        help.textContent = 'Los resultados aparecerán cuando llegue a 00:00.';
        if (now >= resultsAt) location.reload();
        return;
    }
    value.textContent = format(Math.ceil((closesAt - now) / 1000));
    if (now >= votingAt) {
        timer.className = 'card timer warning';
        help.textContent = '¡Vota ahora! Solo queda el último minuto.';
        if (currentStatus === 'playing') location.reload();
    }
}
tick();
setInterval(tick, 1000);
</script>
</body></html>
