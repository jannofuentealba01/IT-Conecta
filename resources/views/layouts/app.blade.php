<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel de Control | IT Conecta' }}</title>

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
            background: linear-gradient(135deg, #ecfdf5, #d1fae5, #a7f3d0);
            color: #064e3b;
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
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .brand-group {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #064e3b;
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

        .user-info {
            background: #d1fae5;
            color: #065f46;
            padding: 8px 16px;
            border-radius: 99px;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #a7f3d0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .user-info .points-highlight {
            color: #047857;
            background: white;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: 13px;
        }

        /* Botón de Logout */
        .logout-btn {
            text-decoration: none;
            background: #065f46;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 95, 70, 0.2);
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
            background-color: #ecfdf5;
            border: 1px solid #10b981;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #ef4444;
            color: #991b1b;
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
            color: #065f46;
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

        <!-- Datos de sesión y puntos -->
        @auth
        <div class="user-badge">
            <div class="user-info">
                <span>👤 {{ Auth::user()->name }}</span>
                <span class="points-highlight">⭐ {{ Auth::user()->total_points ?? 0 }} puntos</span>
            </div>

            <!-- Botón de Logout -->
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="logout-btn">
                Cerrar sesión
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
        @endauth
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