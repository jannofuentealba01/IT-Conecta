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
            background: var(--surface);
            color: var(--text-secondary);
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid var(--border);
            cursor: pointer;
            min-height: 48px;
        }

        .profile-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 10px 16px;
            border: 1px solid var(--brand-blue-light);
            border-radius: 10px;
            background: var(--surface);
            color: var(--brand-blue-dark);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .profile-btn:hover,
        .profile-btn[aria-current='page'] {
            background: var(--info-soft);
            border-color: var(--brand-blue);
        }

        .logout-btn:hover {
            background: var(--surface-muted);
            color: var(--text-primary);
            border-color: var(--brand-blue-light);
            transform: translateY(-1px);
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
            .user-info, .profile-btn, .logout-btn, .user-badge form {
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
        @if (auth()->check())
        <div class="user-badge">
            <div class="user-info">
                <span>👤 {{ auth()->user()->name }}</span>
                <span class="points-highlight">{{ auth()->user()->rol === 'admin' ? 'Administración' : 'Área docente' }}</span>
            </div>
            <a href="{{ route('profile.edit') }}" class="profile-btn" @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                Mi perfil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    Cerrar sesión
                </button>
            </form>
        </div>
        @elseif (session()->has('participant_id'))
        <div class="user-badge">
            @include('student.partials.identity-bar')
        </div>
        @endif
    </header>




    <!-- Feedback global centralizado: errores relevantes y éxitos importantes. -->
    <x-flash-feedback />

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
