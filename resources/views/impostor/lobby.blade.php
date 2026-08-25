<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="refresh" content="5"><title>Esperando juego | IT Conecta</title>@vite(['resources/css/app.css', 'resources/js/app.js'])@include('partials.color-tokens')
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;padding:18px;display:grid;place-items:center;font-family:"Segoe UI",Arial,sans-serif;background:var(--surface-muted);color:var(--text-primary)}.card{width:min(520px,100%);background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:30px 22px;text-align:center;box-shadow:0 18px 40px rgba(17,24,39,.13)}.icon{font-size:55px}.pulse{width:12px;height:12px;border-radius:50%;background:var(--brand-purple);display:inline-block;animation:p 1.3s infinite}@keyframes p{50%{opacity:.25;transform:scale(.8)}}h1{margin:12px 0 8px;font-size:26px;color:var(--brand-purple-dark)}p{color:var(--text-secondary);line-height:1.5}.btn{min-height:52px;width:100%;display:flex;align-items:center;justify-content:center;border-radius:13px;background:var(--brand-purple);color:var(--surface);text-decoration:none;font-weight:800;margin-top:18px}.btn:hover{background:var(--brand-purple-dark)}
    </style>
</head>
<body><main class="card">@include('student.partials.identity-bar', ['participant' => $participant])<div class="icon">🎭</div><h1>Esperando al profesor</h1><p><span class="pulse"></span> Esta pantalla buscará una nueva ronda automáticamente.</p><a class="btn" href="{{ route('student.impostor.lobby') }}">Comprobar ahora</a><a class="btn" style="background:var(--surface-muted);color:var(--text-primary);border:1px solid var(--border)" href="{{ route('student.dashboard') }}">Volver al panel</a></main></body>
</html>
