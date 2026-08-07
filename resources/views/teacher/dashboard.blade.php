@extends('layouts.app')

@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell">
    <div class="teacher-header">
        <div>
            <p class="teacher-eyebrow">Área docente</p>
            <h1 class="teacher-title">Bienvenido, {{ auth()->user()->name }}</h1>
            <p class="teacher-subtitle">Administra tus cursos, abre sesiones y revisa a los estudiantes conectados.</p>
        </div>
        <a href="{{ route('teacher.courses.create') }}" class="teacher-btn teacher-btn-primary">＋ Crear curso</a>
    </div>

    <div class="teacher-grid" style="margin-bottom:20px;">
        <div class="teacher-stat">Cursos activos<strong>{{ $stats['courses'] }}</strong></div>
        <div class="teacher-stat">Sesiones creadas<strong>{{ $stats['sessions'] }}</strong></div>
        <div class="teacher-stat">Sesiones abiertas<strong>{{ $stats['open_sessions'] }}</strong></div>
        <div class="teacher-stat">Participaciones<strong>{{ $stats['participants'] }}</strong></div>
    </div>

    <div class="teacher-grid" style="margin-bottom:20px;">
        <a href="{{ route('teacher.courses.index') }}" class="teacher-card" style="text-decoration:none;">
            <h2 style="color:#065f46; font-size:18px; margin:0 0 8px;">📚 Cursos y sesiones</h2>
            <p class="teacher-meta">Crea cursos, genera códigos y abre sesiones para tus estudiantes.</p>
        </a>
        <a href="{{ route('teacher.activities.index') }}" class="teacher-card" style="text-decoration:none;">
            <h2 style="color:#065f46; font-size:18px; margin:0 0 8px;">🌱 Catálogo de actividades</h2>
            <p class="teacher-meta">Revisa las acciones ambientales disponibles.</p>
        </a>
    </div>

    <div class="teacher-card">
        <div class="teacher-header" style="margin-bottom:14px;">
            <div><h2 style="color:#065f46; font-size:19px; margin:0;">Sesiones recientes</h2></div>
            <a href="{{ route('teacher.courses.index') }}" style="color:#047857; font-weight:700;">Ver todos los cursos</a>
        </div>
        <div class="teacher-list">
            @forelse($recentRooms as $room)
                <div class="teacher-row">
                    <div>
                        <h3>{{ $room->name }}</h3>
                        <p class="teacher-meta">{{ $room->course?->name ?? 'Sin curso' }} · {{ $room->participants_count }} participantes · Código {{ $room->code }}</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="teacher-badge status-{{ $room->status }}">{{ ucfirst($room->status) }}</span>
                        <a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.sessions.show', $room) }}">Abrir panel</a>
                    </div>
                </div>
            @empty
                <div class="empty-state">Aún no has creado sesiones.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
