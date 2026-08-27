@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<style>@media print { .navbar,.footer,.print-hide { display:none!important; } body{background:#fff!important}.teacher-card{box-shadow:none!important;border:0!important} }</style>
<div class="teacher-shell" style="max-width:650px; text-align:center;">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
        ['label' => $room->course?->name ?? 'Curso', 'url' => route('teacher.courses.show', $room->course_id)],
        ['label' => $room->name, 'url' => route('teacher.sessions.show', $room)],
        ['label' => 'Misiones QR', 'url' => route('teacher.missions.index', $room)],
        ['label' => $mission->activity->name],
    ]" />
    <div class="teacher-card">
        <p class="teacher-eyebrow">Misión ambiental · {{ $room->course?->name }}</p>
        <h1 class="teacher-title">{{ $mission->activity->name }}</h1>
        <p class="teacher-subtitle">Escanea para descubrir la instrucción</p>
        <div style="margin:28px auto; padding:18px; max-width:100%; background:#fff; display:inline-block; border:2px solid #d1fae5; border-radius:16px;">{!! QrCode::size(300)->generate($missionUrl) !!}</div>
        <p style="color:var(--brand-green-dark); font-weight:800;">⭐ {{ $mission->activity->points }} puntos · {{ $mission->activity->category }}</p>
        <p style="font-size:12px; color:#64748b; overflow-wrap:anywhere;">{{ $missionUrl }}</p>
        @if(in_array(request()->getHost(), ['127.0.0.1', 'localhost', '::1'], true))
            <div style="margin-top:15px; padding:13px; border-radius:11px; background:#fffbeb; color:#92400e; text-align:left; font-size:13px; line-height:1.5;"><strong>Atención para teléfonos:</strong> esta dirección solo funciona en este computador. Abre el panel docente usando la IP local del computador antes de imprimir el QR.</div>
        @endif
    </div>
    <div class="print-hide" style="display:flex; gap:10px; margin-top:16px;"><a href="{{ route('teacher.missions.index', $room) }}" class="teacher-btn teacher-btn-muted">← Volver</a><button onclick="window.print()" class="teacher-btn teacher-btn-primary">🖨️ Imprimir QR</button></div>
</div>
@endsection
