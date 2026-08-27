@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell" style="max-width:760px;"><x-breadcrumbs :items="[['label' => 'Área docente', 'url' => route('teacher.dashboard')], ['label' => 'Actividades', 'url' => route('teacher.activities.index')], ['label' => 'Nueva actividad']]" /><div class="teacher-header"><div><p class="teacher-eyebrow">Catálogo</p><h1 class="teacher-title">Nueva actividad</h1><p class="teacher-subtitle">Define una acción ambiental reutilizable; los puntos se asignarán según su nivel de impacto.</p></div></div><form class="teacher-card" method="POST" action="{{ route('activities.store') }}">@include('teacher.activities._form')</form><a href="{{ route('teacher.activities.index') }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Cancelar</a></div>
@endsection
