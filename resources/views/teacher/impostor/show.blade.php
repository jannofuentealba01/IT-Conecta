@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<style>.teacher-game .teacher-title{color:var(--brand-purple-dark)}</style>
<div class="teacher-shell teacher-game">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
        ['label' => $game->room->course?->name ?? 'Curso', 'url' => route('teacher.courses.show', $game->room->course_id)],
        ['label' => $game->room->name, 'url' => route('teacher.sessions.show', $game->room)],
        ['label' => 'Juego del Impostor'],
    ]" />
    <div class="teacher-header"><div><p class="teacher-eyebrow">Juego del Impostor · Sala {{ $game->room->code }}</p><h1 class="teacher-title">{{ $game->status === 'waiting' ? 'Preparar la ronda' : 'Control de la ronda' }}</h1><p class="teacher-subtitle">{{ $game->status === 'waiting' ? 'Comprueba quiénes están conectados. El tiempo comenzará solamente cuando tú inicies la partida.' : 'El juego dura 5 minutos y la votación comienza automáticamente en el minuto 4.' }}</p></div><span id="teacherGameStatus" class="teacher-badge {{ in_array($game->status, ['waiting','playing'], true) ? 'status-open' : 'status-closed' }}">{{ $game->status === 'waiting' ? 'Esperando inicio' : ($game->status === 'playing' ? 'Fase de pistas' : ($game->status === 'voting' ? 'Votación' : 'Partida cerrada')) }}</span></div>
    @if($game->status !== 'waiting')
    <div id="teacherConnectionStatus" class="sync-status sync-status--checking" role="status" aria-live="polite" aria-atomic="true" style="margin-bottom:16px">
        <span data-sync-label>Verificando conexión…</span>
    </div>
    @endif
    @if($game->status === 'waiting')
    <div class="teacher-card" style="margin-bottom:18px;">
        <div class="teacher-header" style="margin-bottom:14px;"><div><h2 class="teacher-section-title teacher-section-title--game">Estudiantes preparados</h2><p class="teacher-meta">Se requieren al menos 3. La palabra y los impostores se asignarán al iniciar.</p></div><a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.impostor.show',$game) }}">↻ Actualizar</a></div>
        <div class="teacher-list">
            @forelse($game->room->participants as $participant)
                <div class="teacher-row"><strong>{{ $participant->name }}</strong><span class="teacher-badge status-open">Conectado</span></div>
            @empty
                <div class="empty-state">Aún no han ingresado estudiantes a la sala.</div>
            @endforelse
        </div>
    </div>
    <div class="teacher-card" style="text-align:center;">
        <h2 class="teacher-section-title teacher-section-title--game">¿Todo listo?</h2>
        <p class="teacher-meta" style="margin-bottom:15px;">Al iniciar se asignarán los roles y comenzará inmediatamente el contador de 5 minutos en todos los dispositivos.</p>
        <form method="POST" action="{{ route('teacher.impostor.launch',$game) }}" data-confirm-title="¿Iniciar la partida?" data-confirm-text="El contador de 5 minutos comenzará inmediatamente para todos los participantes." data-confirm-button="Sí, iniciar" data-confirm-variant="game">@csrf<button class="teacher-btn teacher-btn-game" {{ $game->room->participants->count() < 3 ? 'disabled' : '' }}>▶ Iniciar partida</button></form>
        @if($game->room->participants->count() < 3)<p class="teacher-error" style="margin-top:12px;">Faltan {{ 3 - $game->room->participants->count() }} estudiante(s) para poder iniciar.</p>@endif
    </div>
    @else
    <div class="teacher-card" style="margin-bottom:18px;text-align:center" id="teacherTimer" data-status="{{ $game->status }}" data-state-url="{{ route('teacher.impostor.state', $game) }}" data-voting-at="{{ $game->voting_at?->toIso8601String() }}" data-closes-at="{{ $game->closes_at?->toIso8601String() }}" data-results-at="{{ $game->results_at?->toIso8601String() }}"><div class="teacher-meta" id="teacherTimerLabel">Tiempo restante</div><strong id="teacherTimerValue" style="display:block;font-size:38px;color:var(--brand-purple-dark);margin:5px 0">--:--</strong><div class="teacher-meta" id="teacherTimerHelp">La votación se habilita al llegar al minuto 4.</div></div>
    <div class="teacher-grid" style="margin-bottom:18px;">
        <div class="teacher-stat">Palabra secreta<strong style="font-size:21px;">{{ $game->word }}</strong></div>
        @php($displayImpostors = $game->impostors->isNotEmpty() ? $game->impostors : $game->room->participants->where('id', $game->impostor_id))
        <div class="teacher-stat">Impostores ({{ $displayImpostors->count() }})<strong style="font-size:18px;">{{ $displayImpostors->pluck('name')->join(', ') }}</strong></div>
        <div class="teacher-stat">Pistas enviadas<strong><span id="teacherCluesCount">{{ $game->clues->count() }}</span>/{{ $game->room->participants->count() }}</strong></div>
        <div class="teacher-stat">Votos registrados<strong><span id="teacherVotesCount">{{ $game->votes->count() }}</span>/{{ $game->room->participants->count() }}</strong></div>
    </div>
    <div class="teacher-card" style="margin-bottom:18px;">
        <div class="teacher-header"><div><h2 class="teacher-section-title teacher-section-title--game">Pistas de los estudiantes</h2><p class="teacher-meta">El impostor también debe enviar una pista.</p></div><button type="button" id="teacherManualRefresh" class="teacher-btn teacher-btn-secondary" data-sync-action>Actualizar</button></div>
        <div class="teacher-list">@forelse($game->clues as $clue)<div class="teacher-row"><strong>{{ $clue->participant?->name }}</strong><span>{{ $clue->clue }}</span></div>@empty<div class="empty-state">Todavía no se han enviado pistas.</div>@endforelse</div>
    </div>
    <div class="teacher-card">
        <h2 class="teacher-section-title teacher-section-title--game">Control docente</h2><p class="teacher-meta" style="margin-bottom:15px;">5 puntos por enviar una pista. Al acertar el voto se entregan 10 puntos adicionales; si el impostor escapa obtiene 20. Todos son puntos de aprendizaje.</p>
        <div id="teacherPlayingControls" @if($game->status !== 'playing') hidden @endif>
            <p class="teacher-meta">La votación se iniciará automáticamente. Si la adelantas, comenzará inmediatamente un minuto completo para votar.</p>
            <form method="POST" action="{{ route('teacher.impostor.voting',$game) }}">@csrf<button class="teacher-btn teacher-btn-game" data-sync-action>Votar ahora</button></form>
        </div>
        <div id="teacherVotingControls" @if($game->status !== 'voting') hidden @endif>
            <p class="teacher-meta" style="font-weight:800;color:var(--text-primary)">La votación está en curso. “Mostrar resultados” se habilitará cuando termine este minuto.</p>
        </div>
        <div id="teacherClosedControls" @if($game->status !== 'closed') hidden @endif>
            <p class="teacher-meta">La votación terminó. Puedes mostrar los resultados ahora o esperar a que aparezcan automáticamente.</p>
            <form method="POST" action="{{ route('teacher.impostor.finish',$game) }}">@csrf<button class="teacher-btn teacher-btn-game" data-sync-action>Mostrar resultados</button></form>
        </div>
    </div>
    @endif
    <div style="margin-top:16px"><a class="teacher-btn teacher-btn-muted" href="{{ route('teacher.sessions.show',$game->room) }}">← Volver a la sala</a></div>
</div>
@if($game->status !== 'waiting')
<script>
function initializeTeacherGameSync(){
const box=document.getElementById('teacherTimer'), value=document.getElementById('teacherTimerValue'), label=document.getElementById('teacherTimerLabel'), help=document.getElementById('teacherTimerHelp'), statusBadge=document.getElementById('teacherGameStatus'), cluesCount=document.getElementById('teacherCluesCount'), votesCount=document.getElementById('teacherVotesCount'), playingControls=document.getElementById('teacherPlayingControls'), votingControls=document.getElementById('teacherVotingControls'), closedControls=document.getElementById('teacherClosedControls');
let votingAt=Date.parse(box.dataset.votingAt||''), closesAt=Date.parse(box.dataset.closesAt||''), resultsAt=Date.parse(box.dataset.resultsAt||''), status=box.dataset.status, serverOffset=0, syncing=false, redirecting=false;
const connection=window.ITConectaGameSync.createGameSyncStatus(document.getElementById('teacherConnectionStatus'),{actionRoot:document.querySelector('.teacher-game'),onRetry:synchronize});
document.getElementById('teacherManualRefresh')?.addEventListener('click',()=>location.reload());
const format=s=>`${String(Math.floor(Math.max(0,s)/60)).padStart(2,'0')}:${String(Math.max(0,s)%60).padStart(2,'0')}`;
function showControls(){playingControls.hidden=status!=='playing';votingControls.hidden=status!=='voting';closedControls.hidden=status!=='closed';statusBadge.className=`teacher-badge ${status==='playing'?'status-open':'status-closed'}`;statusBadge.textContent=status==='playing'?'Fase de pistas':(status==='voting'?'Votación':'Partida cerrada')}
function tick(){const now=Date.now()+serverOffset;if(status==='closed'){label.textContent='Resultados automáticos en';value.textContent=format(Math.ceil((resultsAt-now)/1000));help.textContent='Puedes mostrarlos inmediatamente con el botón inferior.';return;}if(status==='voting'){label.textContent='Tiempo para votar';value.textContent=format(Math.ceil((closesAt-now)/1000));help.textContent='Votación obligatoria: queda el último minuto.';return;}label.textContent='Tiempo restante';value.textContent=format(Math.ceil((closesAt-now)/1000));help.textContent=now>=votingAt?'Sincronizando el inicio de la votación…':'La votación se habilita al llegar al minuto 4.'}
async function synchronize(){if(syncing||redirecting)return;syncing=true;try{const response=await window.ITConectaGameSync.fetchWithTimeout(box.dataset.stateUrl,{headers:{Accept:'application/json'},cache:'no-store'});const data=await response.json();connection.succeeded();serverOffset=Date.parse(data.server_now)-Date.now();votingAt=Date.parse(data.voting_at||'');closesAt=Date.parse(data.closes_at||'');resultsAt=Date.parse(data.results_at||'');status=data.status;if(cluesCount)cluesCount.textContent=data.clues_count;if(votesCount)votesCount.textContent=data.votes_count;if(data.results_url){redirecting=true;location.replace(data.results_url);return;}showControls();tick()}catch(error){connection.failed()}finally{syncing=false}}
showControls();tick();synchronize();setInterval(tick,250);setInterval(synchronize,1000);document.addEventListener('visibilitychange',()=>{if(!document.hidden)synchronize()});
}
if(window.ITConectaGameSync)initializeTeacherGameSync();else window.addEventListener('it-conecta:frontend-ready',initializeTeacherGameSync,{once:true});
</script>
@endif
@endsection
