@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell" style="max-width:760px;"><x-breadcrumbs :items="[['label' => 'Área docente', 'url' => route('teacher.dashboard')], ['label' => 'Actividades', 'url' => route('teacher.activities.index')], ['label' => $activity->name], ['label' => 'Editar']]" /><div class="teacher-header"><div><p class="teacher-eyebrow">Catálogo</p><h1 class="teacher-title">Editar actividad</h1><p class="teacher-subtitle">Ajusta la información que verán los estudiantes cuando esta actividad vuelva a utilizarse.</p></div></div><form class="teacher-card" method="POST" action="{{ route('activities.update', $activity) }}">@include('teacher.activities._form')</form><a href="{{ route('teacher.activities.index') }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Cancelar</a></div>
@endsection
