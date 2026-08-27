@extends('layouts.app')

@section('content')
    @include('teacher.partials.styles')
    @php
        $isAdmin = $user->rol === 'admin';
        $areaLabel = $isAdmin ? 'Administración' : 'Área docente';
        $areaReturnLabel = $isAdmin ? 'administración' : 'área docente';
        $areaUrl = $isAdmin ? route('admin.teachers.index') : route('teacher.dashboard');
    @endphp
    <div class="teacher-shell profile-page" style="max-width:900px;">
        <x-breadcrumbs :items="[
            ['label' => $areaLabel, 'url' => $areaUrl],
            ['label' => 'Mi perfil'],
        ]" />

        <div class="teacher-header">
            <div>
                <p class="teacher-eyebrow">Cuenta · {{ $areaLabel }}</p>
                <h1 class="teacher-title">Mi perfil</h1>
                <p class="teacher-subtitle">Actualiza tus datos y administra de forma segura el acceso a tu cuenta.</p>
            </div>
            <span class="teacher-badge status-open">{{ $isAdmin ? 'Administrador' : 'Profesor' }}</span>
        </div>

        <div style="display:grid;gap:18px;">
            <section class="teacher-card">
                @include('profile.partials.update-profile-information-form')
            </section>

            <section class="teacher-card">
                @include('profile.partials.update-password-form')
            </section>

            <section class="teacher-card">
                @include('profile.partials.delete-user-form')
            </section>
        </div>

        <div style="margin-top:16px;">
            <a class="teacher-btn teacher-btn-muted" href="{{ $areaUrl }}">← Volver a {{ $areaReturnLabel }}</a>
        </div>
    </div>
@endsection
