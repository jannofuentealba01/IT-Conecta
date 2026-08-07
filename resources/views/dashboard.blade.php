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

        .btn-primary {
            background: #059669;
            color: white;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #065f46;
        }

        .footprint-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            background: linear-gradient(135deg, #065f46, #047857);
            color: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 24px rgba(6, 95, 70, .2);
            flex-wrap: wrap;
        }

        .footprint-low { background:linear-gradient(135deg,#166534,#16a34a); box-shadow:0 10px 24px rgba(22,101,52,.22); }
        .footprint-medium { background:linear-gradient(135deg,#92400e,#d97706); box-shadow:0 10px 24px rgba(146,64,14,.22); }
        .footprint-high { background:linear-gradient(135deg,#991b1b,#dc2626); box-shadow:0 10px 24px rgba(153,27,27,.22); }
        .footprint-level { display:inline-flex; margin-top:7px; padding:4px 9px; border-radius:999px; background:rgba(255,255,255,.2); font-size:12px; font-weight:850; }

        .footprint-summary strong { font-size: 27px; display: block; margin-top: 4px; }
        .footprint-summary a { color:#065f46; background:#fff; padding:10px 14px; border-radius:9px; text-decoration:none; font-weight:750; }

        .progress-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:20px; }
        .progress-stat { background:#fff; border:1px solid #d1fae5; border-radius:13px; padding:14px; text-align:center; }
        .progress-stat strong { display:block; color:#047857; font-size:23px; margin-top:4px; }

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
            .progress-stats { grid-template-columns:1fr; }
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

        <div class="user-info">👤 {{ $participant->name }} · {{ $participant->room->course?->name ?? $participant->course }}</div>
    </div>

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
            <div>
                <h2>📋 Actividades</h2>
                <p>Busca los códigos QR y realiza las acciones ecológicas de tu sala.</p>
            </div>
            <a href="{{ route('activities.index') }}" class="btn-primary">Ver actividades</a>
        </div>

        <!-- 3. JUEGO DEL IMPOSTOR -->
        <div class="card">
            <div>
                <h2>🎭 Juego del Impostor</h2>
                <p>Participa en la ronda que inicia y controla tu profesor.</p>
            </div>
            <a href="{{ route('student.impostor.lobby') }}" class="btn-primary">Entrar al juego</a>
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
    <div style="margin-top: 18px;">
        <form method="POST" action="{{ route('room.exit') }}">
           @csrf
        <button type="submit"
           style="display: block; width: 100%; min-height:48px; text-align: center; background: rgba(239, 68, 68, 0.15); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 12px; font-weight: 600; font-size: 14px; transition: all 0.2s; cursor:pointer;"
           onmouseover="this.style.background='rgba(239, 68, 68, 0.25)'; this.style.color='#ffffff';"
           onmouseout="this.style.background='rgba(239, 68, 68, 0.15)'; this.style.color='#b91c1c';">
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
