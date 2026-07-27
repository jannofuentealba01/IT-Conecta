@extends('layouts.app')

@section('content')
<style>
    .calculator-card {
        background: rgba(255, 255, 255, 0.95);
        padding: 35px 30px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(10px);
        max-width: 800px;
        margin: 0 auto;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #e6f4ea;
        padding-bottom: 15px;
    }

    .result-box {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        padding: 20px;
        border-radius: 14px;
        text-align: center;
        margin-bottom: 25px;
        box-shadow: 0 10px 20px rgba(5, 150, 105, 0.2);
    }

    .grid-questions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .question-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .question-group label {
        font-size: 13.5px;
        font-weight: 700;
        color: #065f46;
        line-height: 1.3;
    }

    .form-select {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid #a7f3d0;
        border-radius: 10px;
        font-size: 14px;
        color: #1f2937;
        background-color: #fcfdfd;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .form-select:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    .btn-submit {
        width: 100%;
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        border: none;
        padding: 15px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 16px;
        margin-top: 30px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(5, 150, 105, 0.3);
    }

    .btn-back {
        text-decoration: none;
        background: #f3f4f6;
        color: #374151;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid #e5e7eb;
    }

    @media (max-width: 640px) {
        .grid-questions { grid-template-columns: 1fr; }
        .calculator-card { padding: 20px 15px; }
    }
</style>

<div class="calculator-card">
    <div class="section-header">
        <h2 style="color: #0f766e; font-size: 22px; font-weight: 800;">
            📊 Calculadora de Huella de Carbono
        </h2>
        <a href="{{ route('dashboard') }}" class="btn-back">⬅️ Volver</a>
    </div>

    <!-- RECUADRO DE RESULTADO -->
    @if (session('total_co2') !== null)
        <div class="result-box">
            <span style="font-size: 32px;">🌱</span>
            <h3 style="font-size: 26px; font-weight: 800; margin: 5px 0;">
                {{ session('total_co2') }} kg CO₂e/año
            </h3>
            <p style="font-size: 14.5px; opacity: 0.95;">
                {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- FORMULARIO DE PREGUNTAS REALES -->
    <form action="{{ route('carbon.calculate') }}" method="POST">
        @csrf

        <div class="grid-questions">
            
            <!-- P1 -->
            <div class="question-group">
                <label for="p1">1. ¿Cuál es el medio de transporte principal al liceo?</label>
                <select name="p1" id="p1" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="0.0">Caminando</option>
                    <option value="0.0">Bicicleta</option>
                    <option value="72.5">Transporte público</option>
                    <option value="390.5">Auto particular</option>
                </select>
            </div>

            <!-- P2 -->
            <div class="question-group">
                <label for="p2">2. ¿Cuánto tiempo tardas en llegar?</label>
                <select name="p2" id="p2" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="0.5">Menos de 10 minutos</option>
                    <option value="1.0">10-30 minutos</option>
                    <option value="2.0">Más de 30 minutos</option>
                </select>
            </div>

            <!-- P3 -->
            <div class="question-group">
                <label for="p3">3. ¿Cuántas horas usas tu computador, celular o consola diariamente?</label>
                <select name="p3" id="p3" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="13.5">Menos de 1 hora</option>
                    <option value="40.5">1-3 horas</option>
                    <option value="94.6">Más de 3 horas</option>
                </select>
            </div>

            <!-- P4 -->
            <div class="question-group">
                <label for="p4">4. Cuando sales de una habitación ¿apagas las luces?</label>
                <select name="p4" id="p4" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="0.0">Siempre apago luces</option>
                    <option value="18.0">A veces</option>
                    <option value="43.8">Nunca</option>
                </select>
            </div>

            <!-- P5 -->
            <div class="question-group">
                <label for="p5">5. ¿Cuánto dura normalmente tu ducha?</label>
                <select name="p5" id="p5" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="2.6">Menos de 5 minutos</option>
                    <option value="5.2">5-10 minutos</option>
                    <option value="10.5">Más de 10 minutos</option>
                </select>
            </div>

            <!-- P6 -->
            <div class="question-group">
                <label for="p6">6. ¿Cuántos días a la semana consumes carne?</label>
                <select name="p6" id="p6" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="110.0">0-1 días</option>
                    <option value="420.0">2-4 días</option>
                    <option value="890.0">Todos los días</option>
                </select>
            </div>

            <!-- P7 -->
            <div class="question-group">
                <label for="p7">7. ¿Reciclas en tu hogar?</label>
                <select name="p7" id="p7" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="15.4">Siempre</option>
                    <option value="160.8">A veces</option>
                    <option value="358.0">Nunca</option>
                </select>
            </div>

            <!-- P8 -->
            <div class="question-group">
                <label for="p8">8. ¿Qué productos retornables utilizas normalmente?</label>
                <select name="p8" id="p8" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="0.5">Botella reutilizable</option>
                    <option value="24.0">Ambas</option>
                    <option value="58.0">Botella desechable</option>
                </select>
            </div>

            <!-- P9 -->
            <div class="question-group">
                <label for="p9">9. ¿Con qué frecuencia compras ropa?</label>
                <select name="p9" id="p9" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="25.0">Solo cuando necesito</option>
                    <option value="115.0">Varias veces al año</option>
                    <option value="340.0">Muy frecuentemente</option>
                </select>
            </div>

            <!-- P10 -->
            <div class="question-group">
                <label for="p10">10. ¿Con qué frecuencia haces uso de papel en tus actividades diarias?</label>
                <select name="p10" id="p10" class="form-select" required>
                    <option value="" disabled selected>Selecciona una opción...</option>
                    <option value="2.1">Casi nunca</option>
                    <option value="18.5">A veces</option>
                    <option value="45.5">Frecuentemente</option>
                </select>
            </div>

        </div>

        <button type="submit" class="btn-submit">
            ⚡ Calcular Huella de Carbono
        </button>
    </form>
</div>
@endsection