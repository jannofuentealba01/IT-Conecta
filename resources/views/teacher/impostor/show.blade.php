@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell">
    <div class="teacher-header"><div><p class="teacher-eyebrow">Juego del Impostor · Sala {{ $game->room->code }}</p><h1 class="teacher-title">Control de la ronda</h1><p class="teacher-subtitle">Actualiza esta pantalla para ver las nuevas pistas y votos.</p></div><span class="teacher-badge {{ $game->status === 'playing' ? 'status-open' : 'status-closed' }}">{{ $game->status === 'playing' ? 'Fase de pistas' : 'Votación' }}</span></div>
    <div class="teacher-grid" style="margin-bottom:18px;">
        <div class="teacher-stat">Palabra secreta<strong style="font-size:21px;">{{ $game->word }}</strong></div>
        <div class="teacher-stat">Impostor<strong style="font-size:21px;">{{ $game->room->participants->firstWhere('id',$game->impostor_id)?->name }}</strong></div>
        <div class="teacher-stat">Pistas enviadas<strong>{{ $game->clues->count() }}/{{ $game->room->participants->count() }}</strong></div>
        <div class="teacher-stat">Votos registrados<strong>{{ $game->votes->count() }}/{{ $game->room->participants->count() }}</strong></div>
    </div>
    <div class="teacher-card" style="margin-bottom:18px;">
        <div class="teacher-header"><div><h2 style="margin:0 0 5px;font-size:19px;">Pistas de los estudiantes</h2><p class="teacher-meta">El impostor también debe enviar una pista.</p></div><a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.impostor.show',$game) }}">Actualizar</a></div>
        <div class="teacher-list">@forelse($game->clues as $clue)<div class="teacher-row"><strong>{{ $clue->participant?->name }}</strong><span>{{ $clue->clue }}</span></div>@empty<div class="empty-state">Todavía no se han enviado pistas.</div>@endforelse</div>
    </div>
    <div class="teacher-card">
        <h2 style="margin:0 0 8px;font-size:19px;">Control docente</h2><p class="teacher-meta" style="margin-bottom:15px;">5 puntos por enviar una pista. Al acertar el voto se entregan 10 puntos adicionales; si el impostor escapa obtiene 20. Todos son puntos de aprendizaje.</p>
        @if($game->status === 'playing')<form method="POST" action="{{ route('teacher.impostor.voting',$game) }}">@csrf<button class="teacher-btn teacher-btn-primary">Iniciar votación</button></form>@else<form method="POST" action="{{ route('teacher.impostor.finish',$game) }}">@csrf<button class="teacher-btn teacher-btn-primary">Finalizar y asignar puntos</button></form>@endif
    </div>
    <div style="margin-top:16px"><a class="teacher-btn teacher-btn-muted" href="{{ route('teacher.sessions.show',$game->room) }}">← Volver a la sala</a></div>
</div>
@endsection
