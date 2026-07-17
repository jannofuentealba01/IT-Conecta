@extends('layouts.app')

@section('content')
    <style>
        /* CONTENEDOR TARJETA PRINCIPAL */
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            max-width: 600px;
            margin: 0 auto; /* Centra el formulario en la pantalla */
        }

        /* ENCABEZADO */
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e6f4ea;
            padding-bottom: 15px;
        }

        .form-title {
            color: #0f766e;
            font-size: 22px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* BOTÓN VOLVER */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #f3f4f6;
            color: #4b5563;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.2s ease;
            border: 1px solid #e5e7eb;
        }

        .btn-back:hover {
            background-color: #e5e7eb;
            color: #1f2937;
            transform: translateX(-2px);
        }

        /* GRUPO DE INPUTS */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 700;
            color: #065f46;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* INPUTS Y TEXTAREAS */
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #a7f3d0;
            border-radius: 10px;
            font-size: 15px;
            color: #1f2937;
            background-color: #fcfdfd;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: #059669;
            background-color: #white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .form-control::placeholder {
            color: #9ca3af;
            font-size: 14px;
        }

        /* FILA PARA DOS CAMPOS CORTOS (Puntos y CO2) */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* BOTÓN ENVIAR FORMULARIO */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(5, 150, 105, 0.3);
            filter: brightness(1.05);
        }

        /* ERRORES DE VALIDACIÓN */
        .error-message {
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            margin-top: -4px;
        }

        /* ADAPTACIÓN MÓVIL */
        @media (max-width: 640px) {
            .form-card {
                padding: 25px 20px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>

    <div class="form-card">
        
        <!-- ENCABEZADO -->
        <div class="form-header">
            <h2 class="form-title">
                ➕ Nueva Actividad
            </h2>
            <a href="{{ route('activities.index') }}" class="btn-back">
                ⬅️ Volver
            </a>
        </div>

        <!-- FORMULARIO -->
        <form action="{{ route('activities.store') }}" method="POST">
            @csrf

            <!-- Campo Nombre -->
            <div class="form-group">
                <label for="name" class="form-label">🏷️ Nombre de la actividad</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="form-control" 
                       placeholder="Ej. Apagar luces innecesarias" 
                       value="{{ old('name') }}" 
                       required>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Campo Descripción -->
            <div class="form-group">
                <label for="description" class="form-label">📝 Descripción</label>
                <textarea name="description" 
                          id="description" 
                          rows="3" 
                          class="form-control" 
                          placeholder="Explica brevemente cómo realizar esta acción..." 
                          required>{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Fila para Puntos y CO2 -->
            <div class="form-row">
                <!-- Campo Puntos -->
                <div class="form-group">
                    <label for="points" class="form-label">⭐ Puntos otorgados</label>
                    <input type="number" 
                           name="points" 
                           id="points" 
                           class="form-control" 
                           placeholder="Ej. 10" 
                           value="{{ old('points') }}" 
                           min="1" 
                           required>
                    @error('points')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Campo CO2 -->
                <div class="form-group">
                    <label for="co2" class="form-label">🌱 CO2 Evitado (g)</label>
                    <input type="number" 
                           name="co2" 
                           id="co2" 
                           class="form-control" 
                           placeholder="Ej. 50" 
                           value="{{ old('co2') }}" 
                           min="0" 
                           required>
                    @error('co2')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Botón de guardar -->
            <button type="submit" class="btn-submit">
                💾 Guardar Actividad
            </button>
        </form>

    </div>




@endsection
