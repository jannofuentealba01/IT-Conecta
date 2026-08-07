@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
@php($statusLabels = ['draft'=>'Preparada','open'=>'Abierta','closed'=>'Cerrada','archived'=>'Archivada'])
<div class="teacher-shell">
    <div class="teacher-header">
        <div><p class="teacher-eyebrow">{{ $room->course?->name }}</p><h1 class="teacher-title">{{ $room->name }}</h1><p class="teacher-subtitle">Creada {{ $room->created_at->format('d/m/Y H:i') }}</p></div>
        <span class="teacher-badge status-{{ $room->status }}">{{ $statusLabels[$room->status] ?? ucfirst($room->status) }}</span>
    </div>

    <div class="teacher-card" style="margin-bottom:18px; text-align:center;">
        <p class="teacher-eyebrow">Código de ingreso</p>
        <div class="session-code">{{ $room->code }}</div>
        @if($room->status === 'open')
            <p style="color:#166534; font-weight:750;">● Sala disponible hasta {{ $room->expires_at?->format('H:i') }}</p>
        @elseif($room->status === 'draft')
            <p class="teacher-meta">Abre la sesión para permitir el ingreso de estudiantes.</p>
        @else
            <p class="teacher-meta">El código ya no acepta nuevos ingresos. Los registros permanecen guardados.</p>
        @endif
        <div style="display:flex; justify-content:center; gap:10px; margin-top:18px; flex-wrap:wrap;">
            @if($room->status === 'draft')
                <form method="POST" action="{{ route('teacher.sessions.open', $room) }}">@csrf<button class="teacher-btn teacher-btn-primary">▶ Abrir sesión</button></form>
            @elseif($room->status === 'open')
                <form method="POST" action="{{ route('teacher.sessions.close', $room) }}" onsubmit="return confirm('¿Cerrar la sesión? Los estudiantes no podrán volver a ingresar.');">@csrf<button class="teacher-btn teacher-btn-danger">■ Cerrar sesión</button></form>
            @endif
        </div>
    </div>

    <div class="teacher-card" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
        <div><h2 style="color:#065f46; font-size:19px; margin:0 0 5px;">Misiones QR</h2><p class="teacher-meta">Selecciona actividades del catálogo y genera los códigos para distribuirlos.</p></div>
        <a href="{{ route('teacher.missions.index', $room) }}" class="teacher-btn teacher-btn-primary">Administrar misiones</a>
    </div>

    <div class="teacher-card" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
        <div><h2 style="color:#065f46; font-size:19px; margin:0 0 5px;">Resultados de la sala</h2><p class="teacher-meta">Consulta huellas iniciales, puntos y actividades realizadas. El reporte permanece disponible al cerrar la sala.</p></div>
        <a href="{{ route('teacher.sessions.report', $room) }}" class="teacher-btn teacher-btn-secondary">Ver resultados</a>
    </div>

    <div class="teacher-card" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
        <div><h2 style="color:#065f46; font-size:19px; margin:0 0 5px;">Juego del Impostor</h2><p class="teacher-meta">El profesor inicia y controla la ronda. Los premios se registran como aprendizaje y no reducen la huella.</p></div>
        <form method="POST" action="{{ route('teacher.impostor.start', $room) }}">@csrf<button class="teacher-btn teacher-btn-primary" {{ $room->status !== 'open' ? 'disabled' : '' }}>Iniciar o continuar juego</button></form>
    </div>

    <div class="teacher-card">
        <div class="teacher-header" style="margin-bottom:14px;"><div><h2 style="color:#065f46; font-size:19px; margin:0 0 5px;">Estudiantes conectados</h2><p class="teacher-meta">{{ $room->participants->count() }} participantes registrados</p></div><a href="{{ route('teacher.sessions.show', $room) }}" class="teacher-btn teacher-btn-secondary">↻ Actualizar</a></div>
        <div class="teacher-list">
            @forelse($room->participants as $participant)
                @php($isRecentlyActive = ($participant->last_seen_at ?? $participant->updated_at)->gt(now()->subMinutes(5)))
                <div class="teacher-row"><div><h3>{{ $participant->name }}</h3><p class="teacher-meta">Ingresó {{ ($participant->joined_at ?? $participant->created_at)->format('d/m/Y H:i') }} · Última actividad {{ ($participant->last_seen_at ?? $participant->updated_at)->diffForHumans() }}</p></div><span class="teacher-badge {{ $isRecentlyActive ? 'status-open' : 'status-draft' }}">{{ $isRecentlyActive ? 'Activo recientemente' : 'Registrado' }}</span></div>
            @empty
                <div class="empty-state">Aún no han ingresado estudiantes.</div>
            @endforelse
        </div>
    </div>

    <div style="display:flex; justify-content:space-between; gap:10px; margin-top:16px; flex-wrap:wrap;">
        <a href="{{ route('teacher.courses.show', $room->course_id) }}" class="teacher-btn teacher-btn-muted">← Volver al curso</a>
        @if(in_array($room->status, ['closed','draft'], true))<form method="POST" action="{{ route('teacher.sessions.archive', $room) }}" onsubmit="return confirm('¿Archivar esta sesión?');">@csrf<button class="teacher-btn teacher-btn-danger">Archivar sesión</button></form>@endif
    </div>
</div>
@endsection
