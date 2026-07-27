<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | IT Conecta</title>

    <style>
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

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand span {
            font-size: 28px;
        }

        .brand h1 {
            font-size: 24px;
            font-weight: 800;
        }

        .logout {
            text-decoration: none;
            background: #065f46;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        /* CARDS */
        .card {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card h2 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .card p {
            font-size: 13px;
            opacity: 0.7;
            margin-bottom: 15px;
        }

        .card a {
            text-decoration: none;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-primary {
            background: #059669;
            color: white;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #065f46;
        }

        /* FOOTER */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            opacity: 0.6;
        }

        /* MOBILE */
        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>




<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="brand">
            <span>🌱</span>
            <h1>IT Conecta</h1>
        </div>

        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="logout">
            Cerrar sesión
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>

    <!-- GRID PRINCIPAL -->
    <div class="grid">

        <!-- ACTIVIDADES -->
        <div class="card">
            <div>
                <h2>📋 Actividades</h2>
                <p>Gestiona las actividades ecológicas del sistema.</p>
            </div>
            <a href="{{ route('activities.index') }}" class="btn-primary">Ver actividades</a>
        </div>

        <!-- CREAR ACTIVIDAD -->
        <div class="card">
            <div>
                <h2>➕ Nueva actividad</h2>
                <p>Agrega una nueva acción ecológica.</p>
            </div>
            <a href="{{ route('activities.create') }}" class="btn-primary">Crear</a>
        </div>

        <!-- QR -->
        <div class="card">
            <div>
                <h2>📷 Código QR</h2>
                <p>Escanea o genera códigos para registrar acciones.</p>
            </div>
            <a href="#" class="btn-secondary">Próximamente</a>
        </div>

        <!-- PUNTOS (SOLO CARD, SIN DATOS) -->
        <div class="card">
            <div>
                <h2>⭐ Puntos</h2>
                <p>Consulta el puntaje ecológico y el ranking de usuarios.</p>
            </div>
            <a href="{{ route('ranking') }}" class="btn-secondary">
                Ver puntos
            </a>
        </div>

        <!-- USUARIOS -->
        <div class="card">
            <div>
                <h2>👥 Usuarios</h2>
                <p>Administra alumnos y roles.</p>
            </div>
            <a href="#" class="btn-secondary">Gestionar</a>
        </div>

        <!-- REPORTES -->
<!-- REPORTES -->
        <div class="card">
            <div>
                <h2>♻️ Huella de carbono</h2>
                <p>Responde las preguntas y conoce tu impacto ambiental.</p>
            </div>
            <!-- AHORA APUNTA A LA RUTA DE LA CALCULADORA -->
            <a href="{{ route('carbon.form') }}" class="btn-primary">Calcular huella</a>
        </div>

    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="/">IT Conecta</a>

            @auth
                <span class="text-white">
                    👤 {{ Auth::user()->name }}
                </span>
            @endauth
        </div>
    </nav>

    <!-- FOOTER -->
    <div class="footer">
        IT Conecta © {{ date('Y') }} • Panel de Control
    </div>

</div>

</body>
</html>