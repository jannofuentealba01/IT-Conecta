<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | IT Conecta</title>
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
            background: var(--surface-muted);
            color: var(--text-primary);
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
            background: var(--brand-blue);
            color: var(--surface);
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
            background: var(--surface);
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
            min-height: 48px;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary { background:var(--brand-blue); color:var(--surface); }
        .btn-primary:hover { background:var(--brand-blue-dark); }
        .btn-positive { background:var(--brand-green); color:var(--surface); }
        .btn-positive:hover { background:var(--brand-green-dark); }
        .btn-game { background:var(--brand-purple); color:var(--surface); }
        .btn-game:hover { background:var(--brand-purple-dark); }

        .btn-secondary {
            background: var(--border);
            color: var(--text-primary);
        }

        .footprint-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            background: linear-gradient(135deg,var(--brand-blue),var(--brand-blue-light));
            color: var(--surface);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 24px rgba(6, 95, 70, .2);
            flex-wrap: wrap;
        }

        .footprint-low { background:var(--impact-low); box-shadow:0 10px 24px color-mix(in srgb,var(--impact-low) 28%,transparent); }
        .footprint-medium { background:var(--impact-medium); color:var(--text-primary); box-shadow:0 10px 24px color-mix(in srgb,var(--impact-medium) 32%,transparent); }
        .footprint-high { background:var(--impact-high); box-shadow:0 10px 24px color-mix(in srgb,var(--impact-high) 28%,transparent); }
        .footprint-level { display:inline-flex; margin-top:7px; padding:4px 9px; border-radius:999px; background:rgba(255,255,255,.2); font-size:12px; font-weight:850; }

        .footprint-summary strong { font-size: 27px; display: block; margin-top: 4px; }
        .footprint-summary a { color:var(--brand-blue-dark); background:var(--surface); padding:10px 14px; border-radius:9px; text-decoration:none; font-weight:750; }

        .progress-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:20px; }
        .progress-stat { background:var(--surface); border:1px solid var(--border); border-radius:13px; padding:14px; text-align:center; }
        .progress-stat strong { display:block; color:var(--brand-blue-dark); font-size:23px; margin-top:4px; }
        .exit-room { display:block;width:100%;min-height:48px;text-align:center;background:var(--danger-soft);color:var(--danger-dark);border:1px solid var(--danger);padding:12px;border-radius:12px;font-weight:600;font-size:14px;transition:all .2s;cursor:pointer; }
        .exit-room:hover { background:var(--danger);color:var(--surface); }
        .dashboard-alert { padding:13px 15px; border-radius:12px; margin-bottom:18px; font-size:14px; font-weight:700; }
        .dashboard-alert-error { background:var(--danger-soft); color:var(--danger-dark); border:1px solid var(--danger); }
        .dashboard-alert-success { background:var(--positive-soft); color:var(--brand-green-dark); border:1px solid var(--brand-green); }

        /* FOOTER */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            opacity: 0.6;
        }

        /* MOBILE */
        @media (max-width: 600px) {
            .container { display:flex; flex-direction:column; }
            .header {
                flex-direction: column;
                gap: 10px;
                order:0;
            }
            .footprint-summary { order:1; }
            .grid { order:2; grid-template-columns:1fr; }
            .grid .card { min-height:150px; }
            .activities-module { display:none; }
            .progress-stats { order:3; grid-template-columns:1fr; margin-top:20px; margin-bottom:0; }
            .room-exit-wrap { order:4; }
            .footer { order:5; }
            .footprint-summary a { width:100%; text-align:center; min-height:48px; display:flex; align-items:center; justify-content:center; }
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

    </div>

    @include('student.partials.identity-bar', ['participant' => $participant])

    @if(session('error'))<div class="dashboard-alert dashboard-alert-error">{{ session('error') }}</div>@endif
    @if(session('success'))<div class="dashboard-alert dashboard-alert-success">{{ session('success') }}</div>@endif

    <div class="progress-stats">
        <div class="progress-stat">Puntos por acciones<strong>{{ $actionPoints }}</strong></div>
        <div class="progress-stat">Puntos de aprendizaje<strong>{{ $learningPoints }}</strong></div>
        <div class="progress-stat">Actividades realizadas<strong>{{ $completedActivities }}</strong></div>
    </div>

    <div class="footprint-summary {{ $footprintClassification ? 'footprint-'.$footprintClassification['key'] : '' }}">
        <div>
            @if($footprint)
                <span>Tu huella inicial vigente</span>
                <strong>{{ number_format((float)$footprint->initial_kg_co2e_year, 2, ',', '.') }} kg CO₂e/año</strong>
                <span class="footprint-level">Huella {{ ['low'=>'baja','medium'=>'media','high'=>'alta'][$footprintClassification['key']] }}</span>
                <small>Calculada {{ $footprint->created_at->diffForHumans() }}</small>
            @else
                <span>Primer paso</span>
                <strong style="font-size:21px;">Calcula tu huella inicial</strong>
                <small>Necesitamos este valor para mostrar tu progreso.</small>
            @endif
        </div>
        <a href="{{ $footprint ? route('carbon.form') : route('carbon.form', ['new' => 1]) }}">{{ $footprint ? 'Ver resultado' : 'Comenzar cálculo' }}</a>
    </div>

    <!-- GRID PRINCIPAL (4 OPCIONES EN ORDEN EXACTO) -->
    <div class="grid">

        <!-- 1. HUELLA DE CARBONO -->
        <div class="card">
            <div>
                <h2>♻️ Huella de Carbono</h2>
                <p>Responde las preguntas y conoce tu impacto ambiental.</p>
            </div>
            <a href="{{ route('carbon.form') }}" class="btn-primary">{{ $footprint ? 'Ver mi huella' : 'Calcular huella' }}</a>
        </div>

        <!-- 2. ACTIVIDADES -->
        <div class="card">
            <div><h2>🧭 EcoBúsqueda</h2><p>Participa en la búsqueda temporizada y escanea los QR ambientales.</p></div>
            <a href="{{ route('student.eco-hunt.index') }}" class="btn-positive">Entrar a EcoBúsqueda</a>
        </div>

        <div class="card activities-module">
            <div>
                <h2>📋 Actividades</h2>
                <p>Busca los códigos QR y realiza las acciones ecológicas de tu sala.</p>
            </div>
            <a href="{{ route('activities.index') }}" class="btn-positive">Ver actividades</a>
        </div>

        <!-- 3. JUEGO DEL IMPOSTOR -->
        <div class="card">
            <div>
                <h2>🎭 Juego del Impostor</h2>
                <p>Participa en la ronda que inicia y controla tu profesor.</p>
            </div>
            <a href="{{ route('student.impostor.lobby') }}" class="btn-game">Entrar al juego</a>
        </div>

        <!-- 4. PUNTOS -->
        <div class="card">
            <div>
                <h2>⭐ Puntos</h2>
                <p>Consulta el puntaje ecológico y el ranking de usuarios.</p>
            </div>
            <a href="{{ route('ranking') }}" class="btn-primary">Ver puntos</a>
        </div>

    </div>

<!-- BOTÓN SALIR DE LA SALA -->
    <div class="room-exit-wrap" style="margin-top: 18px;">
        <form method="POST" action="{{ route('room.exit') }}" onsubmit="return confirm('¿Salir de la sala? Tendrás que volver a ingresar con el código.');">
           @csrf
        <button type="submit" class="exit-room">
            🚪 Salir de la Sala
        </button>
        </form>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        IT Conecta © {{ date('Y') }} • Panel de Control
    </div>

</div>

</body>



</html>
