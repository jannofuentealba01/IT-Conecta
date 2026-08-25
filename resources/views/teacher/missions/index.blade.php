@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell">
    <div class="teacher-header"><div><p class="teacher-eyebrow">{{ $room->course?->name }} · {{ $room->name }}</p><h1 class="teacher-title">Misiones QR de la sesión</h1><p class="teacher-subtitle">Selecciona las actividades que los estudiantes encontrarán durante esta sesión.</p></div></div>
    <form method="POST" action="{{ route('teacher.missions.update', $room) }}">
        @csrf @method('PUT')
        <div class="teacher-card teacher-list">
            @forelse($activities as $activity)
                @php($mission = $assigned->get($activity->id))
                <label class="teacher-row" style="cursor:pointer; align-items:flex-start;">
                    <input type="checkbox" name="activities[]" value="{{ $activity->id }}" @checked($mission) style="width:20px; height:20px; accent-color:var(--brand-green); margin-top:3px;">
                    <div style="flex:1;"><h3>{{ $activity->name }}</h3><p class="teacher-meta">{{ $activity->instructions }}</p><div style="margin-top:7px;"><span class="teacher-badge status-open">{{ $activity->category }}</span> <strong style="color:var(--warning-orange); font-size:13px;">⭐ {{ $activity->points }} puntos</strong></div></div>
                    @if($mission)<a href="{{ route('teacher.missions.qr', [$room, $mission]) }}" class="teacher-btn teacher-btn-secondary" onclick="event.stopPropagation();">Ver QR</a>@endif
                </label>
            @empty
                <div class="empty-state">No hay actividades activas en el catálogo.</div>
            @endforelse
        </div>
        <div style="display:flex; justify-content:space-between; gap:10px; margin-top:16px; flex-wrap:wrap;"><a href="{{ route('teacher.sessions.show', $room) }}" class="teacher-btn teacher-btn-muted">← Volver a la sesión</a><button class="teacher-btn teacher-btn-primary">Guardar selección</button></div>
    </form>
</div>
@endsection
