@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<style>.teacher-game .teacher-btn-primary{background:var(--brand-purple)}.teacher-game .teacher-btn-primary:hover{background:var(--brand-purple-dark)}.teacher-game .teacher-title{color:var(--brand-purple-dark)}</style>
<div class="teacher-shell teacher-game">
    <div class="teacher-header"><div><p class="teacher-eyebrow">Juego del Impostor · Sala {{ $game->room->code }}</p><h1 class="teacher-title">{{ $game->status === 'waiting' ? 'Preparar la ronda' : 'Control de la ronda' }}</h1><p class="teacher-subtitle">{{ $game->status === 'waiting' ? 'Comprueba quiénes están conectados. El tiempo comenzará solamente cuando tú inicies la partida.' : 'El juego dura 5 minutos y la votación comienza automáticamente en el minuto 4.' }}</p></div><span class="teacher-badge {{ in_array($game->status, ['waiting','playing'], true) ? 'status-open' : 'status-closed' }}">{{ $game->status === 'waiting' ? 'Esperando inicio' : ($game->status === 'playing' ? 'Fase de pistas' : ($game->status === 'voting' ? 'Votación' : 'Partida cerrada')) }}</span></div>
    @if($game->status === 'waiting')
    <div class="teacher-card" style="margin-bottom:18px;">
        <div class="teacher-header" style="margin-bottom:14px;"><div><h2 style="margin:0 0 5px;font-size:19px;">Estudiantes preparados</h2><p class="teacher-meta">Se requieren al menos 3. La palabra y los impostores se asignarán al iniciar.</p></div><a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.impostor.show',$game) }}">↻ Actualizar</a></div>
        <div class="teacher-list">
            @forelse($game->room->participants as $participant)
                <div class="teacher-row"><strong>{{ $participant->name }}</strong><span class="teacher-badge status-open">Conectado</span></div>
            @empty
                <div class="empty-state">Aún no han ingresado estudiantes a la sala.</div>
            @endforelse
        </div>
    </div>
    <div class="teacher-card" style="text-align:center;">
        <h2 style="margin:0 0 8px;font-size:20px;">¿Todo listo?</h2>
        <p class="teacher-meta" style="margin-bottom:15px;">Al iniciar se asignarán los roles y comenzará inmediatamente el contador de 5 minutos en todos los dispositivos.</p>
        <form method="POST" action="{{ route('teacher.impostor.launch',$game) }}" onsubmit="return confirm('¿Iniciar la partida ahora? El contador de 5 minutos comenzará inmediatamente.');">@csrf<button class="teacher-btn teacher-btn-primary" {{ $game->room->participants->count() < 3 ? 'disabled' : '' }}>▶ Iniciar partida</button></form>
        @if($game->room->participants->count() < 3)<p class="teacher-error" style="margin-top:12px;">Faltan {{ 3 - $game->room->participants->count() }} estudiante(s) para poder iniciar.</p>@endif
    </div>
    @else
    <div class="teacher-card" style="margin-bottom:18px;text-align:center" id="teacherTimer" data-status="{{ $game->status }}" data-voting-at="{{ $game->voting_at?->toIso8601String() }}" data-closes-at="{{ $game->closes_at?->toIso8601String() }}" data-results-at="{{ $game->results_at?->toIso8601String() }}"><div class="teacher-meta" id="teacherTimerLabel">Tiempo restante</div><strong id="teacherTimerValue" style="display:block;font-size:38px;color:var(--brand-purple-dark);margin:5px 0">--:--</strong><div class="teacher-meta" id="teacherTimerHelp">La votación se habilita al llegar al minuto 4.</div></div>
    <div class="teacher-grid" style="margin-bottom:18px;">
        <div class="teacher-stat">Palabra secreta<strong style="font-size:21px;">{{ $game->word }}</strong></div>
        @php($displayImpostors = $game->impostors->isNotEmpty() ? $game->impostors : $game->room->participants->where('id', $game->impostor_id))
        <div class="teacher-stat">Impostores ({{ $displayImpostors->count() }})<strong style="font-size:18px;">{{ $displayImpostors->pluck('name')->join(', ') }}</strong></div>
        <div class="teacher-stat">Pistas enviadas<strong>{{ $game->clues->count() }}/{{ $game->room->participants->count() }}</strong></div>
        <div class="teacher-stat">Votos registrados<strong>{{ $game->votes->count() }}/{{ $game->room->participants->count() }}</strong></div>
    </div>
    <div class="teacher-card" style="margin-bottom:18px;">
        <div class="teacher-header"><div><h2 style="margin:0 0 5px;font-size:19px;">Pistas de los estudiantes</h2><p class="teacher-meta">El impostor también debe enviar una pista.</p></div><a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.impostor.show',$game) }}">Actualizar</a></div>
        <div class="teacher-list">@forelse($game->clues as $clue)<div class="teacher-row"><strong>{{ $clue->participant?->name }}</strong><span>{{ $clue->clue }}</span></div>@empty<div class="empty-state">Todavía no se han enviado pistas.</div>@endforelse</div>
    </div>
    <div class="teacher-card">
        <h2 style="margin:0 0 8px;font-size:19px;">Control docente</h2><p class="teacher-meta" style="margin-bottom:15px;">5 puntos por enviar una pista. Al acertar el voto se entregan 10 puntos adicionales; si el impostor escapa obtiene 20. Todos son puntos de aprendizaje.</p>
        @if($game->status === 'playing')
            <p class="teacher-meta">La votación se iniciará automáticamente. Si la adelantas, comenzará inmediatamente un minuto completo para votar.</p>
            <form method="POST" action="{{ route('teacher.impostor.voting',$game) }}">@csrf<button class="teacher-btn teacher-btn-primary">Votar ahora</button></form>
        @elseif($game->status === 'voting')
            <p class="teacher-meta" style="font-weight:800;color:#92400e">La votación está en curso. “Mostrar resultados” se habilitará cuando termine este minuto.</p>
        @else
            <p class="teacher-meta">La votación terminó. Puedes mostrar los resultados ahora o esperar a que aparezcan automáticamente.</p>
            <form method="POST" action="{{ route('teacher.impostor.finish',$game) }}">@csrf<button class="teacher-btn teacher-btn-primary">Mostrar resultados</button></form>
        @endif
    </div>
    @endif
    <div style="margin-top:16px"><a class="teacher-btn teacher-btn-muted" href="{{ route('teacher.sessions.show',$game->room) }}">← Volver a la sala</a></div>
</div>
@if($game->status !== 'waiting')
<script>
const box=document.getElementById('teacherTimer'), value=document.getElementById('teacherTimerValue'), label=document.getElementById('teacherTimerLabel'), help=document.getElementById('teacherTimerHelp');
const votingAt=Date.parse(box.dataset.votingAt||''), closesAt=Date.parse(box.dataset.closesAt||''), resultsAt=Date.parse(box.dataset.resultsAt||''); let status=box.dataset.status;
const format=s=>`${String(Math.floor(Math.max(0,s)/60)).padStart(2,'0')}:${String(Math.max(0,s)%60).padStart(2,'0')}`;
function tick(){const now=Date.now();if(status==='closed'||now>=closesAt){label.textContent='Resultados automáticos en';value.textContent=format(Math.ceil((resultsAt-now)/1000));help.textContent='Puedes mostrarlos inmediatamente con el botón inferior.';if(now>=resultsAt)location.reload();return;}value.textContent=format(Math.ceil((closesAt-now)/1000));if(now>=votingAt){help.textContent='Votación obligatoria: queda el último minuto.';if(status==='playing')location.reload();}}
tick();setInterval(tick,1000);
</script>
@endif
@endsection
