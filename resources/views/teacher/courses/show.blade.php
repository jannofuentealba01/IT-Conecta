@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell">
    <div class="teacher-header">
        <div><p class="teacher-eyebrow">Curso</p><h1 class="teacher-title">{{ $course->name }}</h1><p class="teacher-subtitle">{{ $course->school_name ?: 'Sin colegio indicado' }}</p></div>
        @if($course->is_active)<a href="{{ route('teacher.sessions.create', $course) }}" class="teacher-btn teacher-btn-primary">＋ Nueva sesión</a>@endif
    </div>
    <div class="teacher-card">
        <div class="teacher-header" style="margin-bottom:14px;"><h2 style="color:var(--brand-blue-dark); font-size:19px; margin:0;">Historial de sesiones</h2><a href="{{ route('teacher.courses.edit', $course) }}" style="color:var(--brand-blue-dark); font-weight:700;">Editar curso</a></div>
        <div class="teacher-list">
            @forelse($course->rooms as $room)
                <div class="teacher-row">
                    <div><h3>{{ $room->name }}</h3><p class="teacher-meta">Código {{ $room->code }} · {{ $room->participants_count }} participantes · Creada {{ $room->created_at->format('d/m/Y H:i') }}</p></div>
                    <div style="display:flex; gap:9px; align-items:center;"><span class="teacher-badge status-{{ $room->status }}">{{ ucfirst($room->status) }}</span><a href="{{ route('teacher.sessions.show', $room) }}" class="teacher-btn teacher-btn-secondary">Administrar</a></div>
                </div>
            @empty
                <div class="empty-state">Este curso todavía no tiene sesiones.</div>
            @endforelse
        </div>
    </div>
    <div style="display:flex; justify-content:space-between; gap:10px; margin-top:16px; flex-wrap:wrap;">
        <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn-muted">← Volver a cursos</a>
        @if($course->is_active)
            <form method="POST" action="{{ route('teacher.courses.archive', $course) }}" onsubmit="return confirm('¿Archivar este curso? Los datos se conservarán.');">@csrf<button class="teacher-btn teacher-btn-danger">Archivar curso</button></form>
        @endif
    </div>
</div>
@endsection
