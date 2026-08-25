<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Conecta - EcoImpact</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.color-tokens')

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg,var(--brand-blue),var(--brand-blue-dark),var(--text-primary));
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 20px;
            padding-bottom: 100px; /* Espacio para el footer */
        }

        /* Contenedor Principal */
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 420px;
        }

        /* Estructura del Título */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .brand-header .emoji {
            font-size: 42px;
        }

        h1 {
            font-size: 38px;
            font-weight: 800;
            letter-spacing: -1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Tarjeta de Ingreso a Sala */
        .card {
            background: rgba(255, 255, 255, 0.96);
            padding: 28px 24px;
            border-radius: 20px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .card h2 {
            color: var(--brand-blue-dark);
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .card .subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* Formulario de Código */
        .code-input {
            width: 100%;
            padding: 14px;
            font-size: 22px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 4px;
            border: 2px solid var(--brand-blue-light);
            border-radius: 12px;
            outline: none;
            text-transform: uppercase;
            color: var(--text-primary);
            background: var(--surface-muted);
            transition: all 0.2s ease;
            margin-bottom: 16px;
        }

        .code-input:focus {
            border-color: var(--brand-blue);
            background: var(--surface);
            box-shadow: 0 0 0 4px var(--focus-ring);
        }

        /* Botón Principal de Sala */
        .btn-submit {
            width: 100%;
            background: var(--brand-blue);
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.4);
            filter: brightness(1.05);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Mensajes de Alerta */
        .alert-error {
            background: var(--danger-soft);
            border: 1px solid var(--danger);
            color: var(--danger-dark);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
        }

        .alert-success {
            background: var(--positive-soft);
            border: 1px solid var(--brand-green);
            color: var(--brand-green-dark);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
        }

        /* Sección Separada de Autenticación (Abajo) */
        .auth-actions {
            margin-top: 24px;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .auth-actions p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
        }

        .auth-buttons-group {
            display: flex;
            gap: 12px;
            width: 100%;
            justify-content: center;
        }

        .btn-auth-secondary {
            display: inline-block;
            width: 100%;
            padding: 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(5px);
        }

        .btn-auth-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .btn-dashboard {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--brand-blue);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .btn-dashboard:hover {
            background: var(--brand-blue-dark);
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            text-align: center;
            font-size: 12px;
            opacity: 0.85;
            line-height: 1.5;
            pointer-events: none;
        }

        .footer .tagline {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 4px;
            color: var(--brand-blue-light);
        }

        .footer .system-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 6px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--brand-green);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--brand-green);
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Cabecera de Marca -->
        <div class="brand-header">
            <span class="emoji">🌱</span>
            <h1>IT Conecta</h1>
        </div>

        <!-- Tarjeta Principal: Ingreso a Sala -->
        <div class="card">
            @if (Route::has('login'))
                @auth
                    <h2>¡Hola de nuevo!</h2>
                    <p class="subtitle">Ya has iniciado sesión en la plataforma.</p>
                    <a href="{{ url('/dashboard') }}" class="btn-dashboard">Ir al Dashboard</a>
                @else
                    <h2>🎮 Únete a una Sala</h2>
                    <p class="subtitle">Ingresa el código que te dio tu profesor para comenzar.</p>

                    <!-- Mensajes de Error -->
                    @if (session('success'))
                        <div class="alert-success">✅ {{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert-error">
                            ❌ {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">
                            ⚠️ {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Formulario de Código -->
                    <form method="POST" action="{{ route('room.join') }}">
                        @csrf
                        <input
                            type="text"
                            name="code"
                            placeholder="Ej: 123456"
                            required
                            maxlength="10"
                            class="code-input"
                            autocomplete="off"
                        >

                        <button type="submit" class="btn-submit">
                            🚀 Ingresar a la Sala
                        </button>
                    </form>
                @endauth
            @endif
        </div>

        <!-- Sección Separada para Registro / Inicio de Sesión de Profesores -->
        @if (Route::has('login') && !auth()->check())
            <div class="auth-actions">
                <p>¿Eres docente o deseas administrar?</p>
                <div class="auth-buttons-group">
                    <a href="{{ route('login') }}" class="btn-auth-secondary">Iniciar Sesión</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-auth-secondary">Registrarse</a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="tagline">⚡️ Escanea. Suma. Reduce.</div>
            
            <div class="system-status">
                <span class="status-dot"></span>
                <span>Plataforma Activa</span>
            </div>
            
            <p>© {{ date('Y') }} IT Conecta • Acción Climática Escolar</p>
        </div>
    </div>

</body>
</html>
