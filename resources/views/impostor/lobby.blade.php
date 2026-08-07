<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="refresh" content="5"><title>Esperando juego | IT Conecta</title>
    <style>
        *{box-sizing:border-box} body{margin:0;min-height:100vh;padding:18px;display:grid;place-items:center;font-family:"Segoe UI",Arial,sans-serif;background:linear-gradient(145deg,#ecfdf5,#a7f3d0);color:#064e3b}.card{width:min(520px,100%);background:#fff;border-radius:22px;padding:30px 22px;text-align:center;box-shadow:0 18px 40px rgba(6,78,59,.13)}.icon{font-size:55px}.pulse{width:12px;height:12px;border-radius:50%;background:#10b981;display:inline-block;animation:p 1.3s infinite}@keyframes p{50%{opacity:.25;transform:scale(.8)}}h1{margin:12px 0 8px;font-size:26px}p{color:#64748b;line-height:1.5}.btn{min-height:52px;width:100%;display:flex;align-items:center;justify-content:center;border-radius:13px;background:#059669;color:#fff;text-decoration:none;font-weight:800;margin-top:18px}
    </style>
</head>
<body><main class="card"><div class="icon">🎭</div><h1>Esperando al profesor</h1><p><span class="pulse"></span> Esta pantalla buscará una nueva ronda automáticamente.</p><p>Sala: <strong>{{ $participant->room->code }}</strong></p><a class="btn" href="{{ route('student.impostor.lobby') }}">Comprobar ahora</a><a class="btn" style="background:#e2e8f0;color:#334155" href="{{ route('student.dashboard') }}">Volver al panel</a></main></body>
</html>
