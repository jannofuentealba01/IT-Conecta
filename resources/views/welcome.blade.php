<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoImpact</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        }

        

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f766e, #065f46, #022c22);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 20px;
        }

        /* Contenedor con Flexbox vertical para alineación perfecta */
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 450px;
        }

        /* Estructura del Título con Icono alineado */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .brand-header img, .brand-header .emoji {
            font-size: 48px;
        }

        h1 {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: -1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Tarjeta de Acceso */
        .card {
            background: rgba(255, 255, 255, 0.96);
            padding: 30px 25px;
            border-radius: 16px;
            width: 100%;
            max-width: 340px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .card h2 {
            color: #0f766e;
            margin-bottom: 20px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .card a {
            display: block;
            margin-top: 12px;
            padding: 14px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-login {
            background: #065f46;
            color: white;
        }

        .btn-register {
            background: #10b981;
            color: white;
        }

        .btn-dashboard {
            background: #1f2937;
            color: white;
        }

        /* Efectos Hover sutiles y modernos */
        .card a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            filter: brightness(1.1);
        }

        .card a:active {
            transform: translateY(0);
        }

        /* Footer estructurado y limpio */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            text-align: center;
            font-size: 12px;
            opacity: 0.8;
            line-height: 1.6;
        }

        .footer .tagline {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
            color: #34d399; /* Verde esmeralda claro */
        }

        .footer .system-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 8px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Cabecera de Marca (Alineación perfecta) -->
        <div class="brand-header">
            <!-- Si usas una imagen para el brote, ponla aquí. Si no, dejamos el emoji -->
            <span class="emoji">🌱</span>
            <h1>IT Conecta</h1>
        </div>

        <!-- Tarjeta Centralizada -->
        <div class="card">
            @if (Route::has('login'))
                @auth
                    <h2>¡Hola de nuevo!</h2>
                    <a href="{{ url('/dashboard') }}" class="btn-dashboard">Ir al Dashboard</a>
                @else
                    <h2>Accede a tu cuenta</h2>

                    <a href="{{ route('login') }}" class="btn-login">Iniciar Sesión</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-register">Registrarse</a>
                    @endif
                @endauth
            @endif
        </div>

        <!-- Footer Rediseñado -->
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