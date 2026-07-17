@extends('layouts.app')

@section('content')

<style>
.form-card {
    background: rgba(255, 255, 255, 0.95);
    padding: 35px 30px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
    max-width: 600px;
    margin: 40px auto;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.form-title {
    font-size: 22px;
    font-weight: 700;
    color: #065f46;
}

.btn-back {
    background: #e5e7eb;
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: #374151;
    font-size: 14px;
}

.form-group {
    margin-bottom: 18px;
}

.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    outline: none;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .form-group {
    flex: 1;
}

.btn-submit {
    width: 100%;
    background: #10b981;
    color: white;
    padding: 12px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}
</style>

<div class="form-card">

    <!-- HEADER -->
    <div class="form-header">
        <h2 class="form-title">✏️ Editar Actividad</h2>

        <a href="{{ route('activities.index') }}" class="btn-back">
            ⬅️ Volver
        </a>
    </div>

    <!-- FORM -->
    <form action="{{ route('activities.update', $activity->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">🏷️ Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ $activity->name }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">📝 Descripción</label>
            <textarea name="description" class="form-control" required>{{ $activity->description }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">⭐ Puntos</label>
                <input type="number" name="points" class="form-control" value="{{ $activity->points }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">🌱 CO2</label>
                <input type="number" step="0.01" name="co2_impact" class="form-control" value="{{ $activity->co2_impact }}" required>            </div>
        </div>

        <button class="btn-submit">
            💾 Actualizar Actividad
        </button>

    </form>

</div>

@endsection