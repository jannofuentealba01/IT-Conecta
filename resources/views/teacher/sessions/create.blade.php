@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell" style="max-width:700px;">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
        ['label' => $course->name, 'url' => route('teacher.courses.show', $course)],
        ['label' => 'Nueva sesión'],
    ]" />
    <div class="teacher-header"><div><p class="teacher-eyebrow">{{ $course->name }}</p><h1 class="teacher-title">Preparar nueva sesión</h1><p class="teacher-subtitle">El código se generará automáticamente.</p></div></div>
    <form class="teacher-card" method="POST" action="{{ route('teacher.sessions.store', $course) }}">
        @csrf
        <div class="teacher-form-group"><label for="name">Nombre de la sesión</label><input id="name" name="name" class="teacher-input" required maxlength="120" value="{{ old('name', 'Actividad ambiental '.now()->format('d/m/Y')) }}">@error('name')<div class="teacher-error">{{ $message }}</div>@enderror</div>
        <div class="teacher-form-group"><label for="duration_minutes">Duración de acceso</label><select id="duration_minutes" name="duration_minutes" class="teacher-input"><option value="60">1 hora</option><option value="120" selected>2 horas</option><option value="180">3 horas</option><option value="240">4 horas</option><option value="480">8 horas</option></select>@error('duration_minutes')<div class="teacher-error">{{ $message }}</div>@enderror</div>
        <p class="teacher-meta" style="margin-bottom:18px;">La sesión también puede cerrarse manualmente antes de que termine este período.</p>
        <button class="teacher-btn teacher-btn-primary">Crear sesión</button>
    </form>
    <a href="{{ route('teacher.courses.show', $course) }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Cancelar</a>
</div>
@endsection
