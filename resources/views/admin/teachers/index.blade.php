@extends('layouts.app')

@section('content')
@include('teacher.partials.styles')
<div class="teacher-shell">
    <div class="teacher-header">
        <div>
            <p class="teacher-eyebrow">Administración</p>
            <h1 class="teacher-title">Aprobación de profesores</h1>
            <p class="teacher-subtitle">Revisa las solicitudes antes de permitir el acceso al área docente.</p>
        </div>
    </div>

    <div class="teacher-grid" style="margin-bottom:20px;">
        <div class="teacher-stat">Solicitudes pendientes<strong>{{ $pendingTeachers->count() }}</strong></div>
        <div class="teacher-stat">Profesores aprobados mostrados<strong>{{ $approvedTeachers->count() }}</strong></div>
    </div>

    <section class="teacher-card" style="margin-bottom:20px;">
        <h2 class="teacher-section-title teacher-section-title--brand" style="margin-bottom:14px;">Solicitudes pendientes</h2>
        <div class="teacher-list">
            @forelse($pendingTeachers as $teacher)
                <div class="teacher-row">
                    <div>
                        <h3>{{ $teacher->name }}</h3>
                        <p class="teacher-meta">{{ $teacher->email }} · Solicitud {{ $teacher->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.teachers.approve', $teacher) }}">
                        @csrf
                        <button class="teacher-btn teacher-btn-positive" type="submit">Aprobar profesor</button>
                    </form>
                </div>
            @empty
                <div class="empty-state">No hay solicitudes pendientes.</div>
            @endforelse
        </div>
    </section>

    <section class="teacher-card">
        <h2 class="teacher-section-title teacher-section-title--brand" style="margin-bottom:14px;">Profesores aprobados</h2>
        <div class="teacher-list">
            @forelse($approvedTeachers as $teacher)
                <div class="teacher-row">
                    <div>
                        <h3>{{ $teacher->name }}</h3>
                        <p class="teacher-meta">{{ $teacher->email }}</p>
                    </div>
                    <span class="teacher-badge status-open">Aprobado</span>
                </div>
            @empty
                <div class="empty-state">Todavía no hay profesores aprobados.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
