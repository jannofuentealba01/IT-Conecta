@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
@php($impactLabels = ['low'=>'Bajo · 10 pts','medium'=>'Medio · 20 pts','high'=>'Alto · 35 pts','very_high'=>'Muy alto · 50 pts'])
<div class="teacher-shell">
    <div class="teacher-header">
        <div><p class="teacher-eyebrow">Catálogo reutilizable</p><h1 class="teacher-title">Actividades ambientales</h1><p class="teacher-subtitle">Estas actividades pueden reutilizarse en distintas sesiones. La reducción real de CO₂ se añadirá cuando exista la metodología.</p></div>
        <a href="{{ route('activities.create') }}" class="teacher-btn teacher-btn-primary">＋ Nueva actividad</a>
    </div>
    <div class="teacher-card teacher-list">
        @forelse($activities as $activity)
            <div class="teacher-row" style="align-items:flex-start;">
                <div style="flex:1; min-width:240px;">
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-bottom:5px;"><h3 style="margin:0;">{{ $activity->name }}</h3><span class="teacher-badge status-open">{{ $activity->category }}</span>@if(!$activity->is_active)<span class="teacher-badge status-archived">Inactiva</span>@endif</div>
                    <p class="teacher-meta" style="margin-bottom:8px;">{{ $activity->instructions }}</p>
                    <strong style="color:var(--warning-orange); font-size:13px;">⭐ {{ $impactLabels[$activity->impact_level] ?? $activity->points.' pts' }}</strong>
                    <span style="color:#9ca3af; font-size:12px; margin-left:8px;">{{ $activity->user_id ? 'Creada por ti' : 'Actividad global' }}</span>
                </div>
                @if($activity->user_id === auth()->id() || (auth()->user()->rol === 'admin' && !$activity->user_id))
                    <div style="display:flex; gap:8px; flex-wrap:wrap;"><a href="{{ route('activities.edit', $activity) }}" class="teacher-btn teacher-btn-secondary">Editar</a>@if($activity->is_active)<form method="POST" action="{{ route('activities.destroy', $activity) }}" onsubmit="return confirm('¿Desactivar esta actividad?');">@csrf @method('DELETE')<button class="teacher-btn teacher-btn-danger">Desactivar</button></form>@endif</div>
                @endif
            </div>
        @empty
            <div class="empty-state">El catálogo aún no contiene actividades.</div>
        @endforelse
    </div>
    <a href="{{ route('teacher.dashboard') }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Volver al panel</a>
</div>
@endsection
