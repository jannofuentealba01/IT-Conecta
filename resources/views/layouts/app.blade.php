<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel de Control | IT Conecta' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.color-tokens')

    <style>
        /* ESTILOS GLOBALES Y VARIABLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: var(--surface-muted);
            color: var(--text-primary);
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        /* NAVBAR / HEADER UNIFICADO */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: color-mix(in srgb, var(--surface) 94%, transparent);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border: 1px solid var(--border);
        }

        .brand-group {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-primary);
            transition: transform 0.2s ease;
        }

        .brand-group:hover {
            transform: scale(1.02);
        }

        .brand-group span {
            font-size: 30px;
        }

        .brand-group h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        /* Información del Usuario y Puntos */
        .user-badge {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-badge .student-identity-bar {
            margin: 0;
        }

        .user-info {
            background: var(--info-soft);
            color: var(--brand-blue-dark);
            padding: 8px 16px;
            border-radius: 99px;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--brand-blue-light);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .user-info .points-highlight {
            color: var(--brand-blue-dark);
            background: var(--surface);
            padding: 2px 10px;
            border-radius: 99px;
            font-size: 13px;
        }

        /* Botón de Logout */
        .logout-btn {
            text-decoration: none;
            background: var(--brand-blue);
            color: var(--surface);
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            min-height: 48px;
        }

        .logout-btn:hover {
            background: var(--brand-blue-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--focus-ring);
        }

        /* ALERTAS DE ÉXITO O ERROR DEL SISTEMA */
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-success {
            background-color: var(--positive-soft);
            border: 1px solid var(--brand-green);
            color: var(--brand-green-dark);
        }

        .alert-danger {
            background-color: var(--danger-soft);
            border: 1px solid var(--danger);
            color: var(--danger-dark);
        }

        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* CONTENEDOR DINÁMICO DE LAS VISTAS */
        .main-content {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* FOOTER */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            opacity: 0.7;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* RESPONSIVIDAD */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
                text-align: center;
            }
            .user-badge {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            .user-info, .logout-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="container">


<!-- NAVBAR UNIFICADA CON PUNTOS -->
    <header class="navbar">
        <!-- Logo e inicio -->
        <a href="{{ route('dashboard') }}" class="brand-group" title="Ir al Dashboard">
            <span>🌱</span>
            <h1>IT Conecta</h1>
        </a>

        <!-- Datos de sesión del participante -->
        @if (session()->has('participant_id'))
        <div class="user-badge">
            @include('student.partials.identity-bar')
        </div>
        @elseif (auth()->check())
        <div class="user-badge">
            <div class="user-info">
                <span>👤 {{ auth()->user()->name }}</span>
                <span class="points-highlight">Área docente</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn" style="border:0; cursor:pointer;">
                    Cerrar sesión
                </button>
            </form>
        </div>
        @endif
    </header>




    <!-- ALERTAS GLOBALES DE SESIÓN (ÉXITO / ERROR) -->
    @if (session('success'))
        <div class="alert alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- CONTENIDO DINÁMICO DE LA VISTA -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- FOOTER GENERAL -->
    <footer class="footer">
        IT Conecta © {{ date('Y') }} • Panel Escolar de Impacto Ambiental 🌍
    </footer>

</div>

</body>
</html>
