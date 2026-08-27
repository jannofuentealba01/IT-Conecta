@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell" style="max-width:700px;">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
        ['label' => 'Crear curso'],
    ]" />
    <div class="teacher-header"><div><p class="teacher-eyebrow">Nuevo registro</p><h1 class="teacher-title">Crear curso</h1><p class="teacher-subtitle">El estudiante no tendrá que escribir este dato al ingresar.</p></div></div>
    <form class="teacher-card" method="POST" action="{{ route('teacher.courses.store') }}">@include('teacher.courses._form')</form>
    <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Cancelar</a>
</div>
@endsection
