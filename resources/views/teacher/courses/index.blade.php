@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell">
    <div class="teacher-header">
        <div><p class="teacher-eyebrow">Gestión docente</p><h1 class="teacher-title">Cursos</h1><p class="teacher-subtitle">Cada curso conserva el historial de todas sus sesiones.</p></div>
        <a href="{{ route('teacher.courses.create') }}" class="teacher-btn teacher-btn-primary">＋ Crear curso</a>
    </div>
    <div class="teacher-card teacher-list">
        @forelse($courses as $course)
            <div class="teacher-row">
                <div>
                    <h3>{{ $course->name }}</h3>
                    <p class="teacher-meta">{{ $course->school_name ?: 'Sin colegio indicado' }} · {{ $course->rooms_count }} sesiones · {{ $course->participants_count }} participaciones</p>
                </div>
                <div style="display:flex; gap:9px; align-items:center;">
                    @unless($course->is_active)<span class="teacher-badge status-archived">Archivado</span>@endunless
                    <a href="{{ route('teacher.courses.show', $course) }}" class="teacher-btn teacher-btn-secondary">Ver curso</a>
                </div>
            </div>
        @empty
            <div class="empty-state">No hay cursos. Crea el primero para comenzar.</div>
        @endforelse
    </div>
    <a href="{{ route('teacher.dashboard') }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Volver al panel</a>
</div>
@endsection
