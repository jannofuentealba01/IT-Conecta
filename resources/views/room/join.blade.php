@extends('layouts.app')

@section('content')
<style>
    .join-card {
        background: rgba(255, 255, 255, 0.95);
        padding: 35px 30px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(10px);
        max-width: 450px;
        margin: 40px auto;
        text-align: center;
    }

    .room-badge {
        display: inline-block;
        background: var(--info-soft);
        color: var(--brand-blue-dark);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        text-align: left;
        margin-bottom: 18px;
    }

    .form-group label {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .form-input {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid #a7f3d0;
        border-radius: 10px;
        font-size: 15px;
        color: #1f2937;
        outline: none;
        box-sizing: border-box;
        transition: all 0.2s;
    }

    .form-input:focus {
        border-color: var(--brand-blue);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    .btn-enter {
        width: 100%;
        background: linear-gradient(135deg, var(--brand-blue), var(--brand-blue-dark));
        color: white;
        border: none;
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 16px;
        margin-top: 10px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
    }

    .btn-enter:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(5, 150, 105, 0.3);
    }
</style>

<div class="join-card">
    <div class="room-badge">
        📍 Sala: <strong>{{ $code }}</strong> @if($room->name) ({{ $room->name }}) @endif
    </div>

    <h2 style="color: var(--brand-blue-dark); font-size: 24px; font-weight: 800; margin-bottom: 8px;">
        👤 Completa tu Perfil
    </h2>
    <p style="color: #6b7280; font-size: 14px; margin-bottom: 25px;">
        Ingresa tu nombre para registrar tus respuestas y puntos en {{ $room->course?->name ?? $room->name ?? 'este curso' }}.
    </p>

    <!-- FORMULARIO DE REGISTRO DE PARTICIPANTE -->
    <form method="POST" action="{{ route('room.enter', $code) }}">
        @csrf

        <div class="form-group">
            <label for="name">Nombre</label>
            <input 
                type="text" 
                name="name" 
                id="name" 
                class="form-input" 
                placeholder="Ej: Alejandro Silva" 
                value="{{ old('name') }}"
                autocomplete="name"
                minlength="2"
                maxlength="100"
                required 
                autofocus
            >
            @error('name')
                <span style="color:#b91c1c; font-size:13px;">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn-enter">
            🚀 Entrar a la Sesión
        </button>
    </form>
</div>
@endsection
