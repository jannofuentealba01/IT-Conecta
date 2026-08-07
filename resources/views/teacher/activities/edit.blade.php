@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell" style="max-width:760px;"><div class="teacher-header"><div><p class="teacher-eyebrow">Catálogo</p><h1 class="teacher-title">Editar actividad</h1></div></div><form class="teacher-card" method="POST" action="{{ route('activities.update', $activity) }}">@include('teacher.activities._form')</form><a href="{{ route('teacher.activities.index') }}" class="teacher-btn teacher-btn-muted" style="margin-top:16px;">← Cancelar</a></div>
@endsection
