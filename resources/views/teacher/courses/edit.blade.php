@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell" style="max-width:700px;">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
        ['label' => $course->name, 'url' => route('teacher.courses.show', $course)],
        ['label' => 'Editar'],
    ]" />
    <div class="teacher-header"><div><p class="teacher-eyebrow">Configuración</p><h1 class="teacher-title">Editar {{ $course->name }}</h1><p class="teacher-subtitle">Actualiza el nombre o colegio sin solicitar estos datos nuevamente a los estudiantes.</p></div></div>
    <form class="teacher-card" method="POST" action="{{ route('teacher.courses.update', $course) }}">@include('teacher.courses._form')</form>
    <a href="{{ route('teacher.courses.show', $course) }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Cancelar</a>
</div>
@endsection
