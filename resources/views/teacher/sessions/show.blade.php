@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<style>.module-eco{border-color:var(--brand-green)!important}.module-eco h2{color:var(--brand-green-dark)!important}.module-game{border-color:var(--brand-purple)!important}.module-game h2{color:var(--brand-purple-dark)!important}</style>
@php($statusLabels = ['draft'=>'Preparada','open'=>'Abierta','closed'=>'Cerrada','archived'=>'Archivada'])
<div class="teacher-shell">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
        ['label' => $room->course?->name ?? 'Curso', 'url' => route('teacher.courses.show', $room->course_id)],
        ['label' => $room->name],
    ]" />
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
                <form method="POST" action="{{ route('teacher.sessions.close', $room) }}" data-confirm-title="¿Cerrar la sesión?" data-confirm-text="Los estudiantes no podrán volver a ingresar, pero los registros permanecerán guardados." data-confirm-button="Sí, cerrar sesión" data-confirm-variant="danger">@csrf<button class="teacher-btn teacher-btn-danger-subtle">■ Cerrar sesión</button></form>
            @endif
        </div>
    </div>

    <div class="teacher-card" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; border-color:var(--brand-blue-light);">
        <div><h2 class="teacher-section-title teacher-section-title--brand">📊 Calcular mi huella</h2><p class="teacher-meta">Realiza el mismo cálculo que los estudiantes y conoce tu impacto anual. Tu resultado es personal y no se incluye en el reporte de la sala.</p></div>
        <a href="{{ route('teacher.carbon.form', $room) }}" class="teacher-btn teacher-btn-primary">{{ $teacherHasFootprint ? 'Ver mi huella' : 'Calcular huella' }}</a>
    </div>

    <div class="teacher-card module-eco" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
        <div><h2 class="teacher-section-title teacher-section-title--positive">🧭 EcoBúsqueda</h2><p class="teacher-meta">Prepara la búsqueda individual de 15 minutos con QR ambientales permanentes.</p></div>
        <a href="{{ route('teacher.eco-hunts.index', $room) }}" class="teacher-btn teacher-btn-positive">Preparar EcoBúsqueda</a>
    </div>

    <div class="teacher-card module-game" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
        <div><h2 class="teacher-section-title teacher-section-title--game">Juego del Impostor</h2><p class="teacher-meta">El profesor inicia y controla la ronda. Los premios se registran como aprendizaje y no reducen la huella.</p></div>
        <form method="POST" action="{{ route('teacher.impostor.start', $room) }}">@csrf<button class="teacher-btn teacher-btn-game" {{ $room->status !== 'open' ? 'disabled' : '' }}>Preparar o continuar juego</button></form>
    </div>

    <div class="teacher-card" style="margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
        <div><h2 class="teacher-section-title teacher-section-title--brand">Resultados de la sala</h2><p class="teacher-meta">Consulta huellas iniciales, puntos y actividades realizadas. El reporte permanece disponible al cerrar la sala.</p></div>
        <a href="{{ route('teacher.sessions.report', $room) }}" class="teacher-btn teacher-btn-secondary">Ver resultados</a>
    </div>

    <div class="teacher-card">
        <div class="teacher-header" style="margin-bottom:14px;"><div><h2 class="teacher-section-title teacher-section-title--brand">Estudiantes conectados</h2><p class="teacher-meta">{{ $room->participants->count() }} participantes registrados</p></div><a href="{{ route('teacher.sessions.show', $room) }}" class="teacher-btn teacher-btn-secondary">↻ Actualizar</a></div>
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
        @if(in_array($room->status, ['closed','draft'], true))<form method="POST" action="{{ route('teacher.sessions.archive', $room) }}" data-confirm-title="¿Archivar esta sesión?" data-confirm-text="Dejará de aparecer entre las sesiones activas. Sus registros se conservarán." data-confirm-button="Sí, archivar" data-confirm-variant="danger">@csrf<button class="teacher-btn teacher-btn-danger-subtle">Archivar sesión</button></form>@endif
    </div>
</div>
@endsection
