<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><title>Resultados | IT Conecta</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;padding:16px;font-family:"Segoe UI",Arial,sans-serif;background:linear-gradient(145deg,#ecfdf5,#a7f3d0);color:#064e3b}.page{width:min(680px,100%);margin:auto}.hero,.card{background:#fff;border-radius:20px;padding:22px;margin-bottom:14px;box-shadow:0 12px 28px rgba(6,78,59,.1)}.hero{text-align:center}.hero .icon{font-size:54px}.hero h1{margin:8px 0}.outcome{padding:14px;border-radius:13px;font-weight:850;background:{{ $impostorCaught ? '#dcfce7' : '#fee2e2' }};color:{{ $impostorCaught ? '#166534' : '#991b1b' }};margin-top:15px}.votes{display:grid;gap:8px}.vote-row{display:flex;justify-content:space-between;gap:10px;padding:12px;background:#f8fafc;border-radius:10px}.points{padding-left:20px;color:#475569;line-height:1.8}.btn{width:100%;min-height:52px;display:flex;justify-content:center;align-items:center;border:0;border-radius:13px;background:#059669;color:#fff;text-decoration:none;font-weight:850;margin-top:10px;cursor:pointer;font-size:15px}.secondary{background:#e2e8f0;color:#334155}
    </style>
</head>
<body><main class="page">
    <section class="hero"><div class="icon">{{ $impostorCaught ? '🎉' : '😈' }}</div><h1>{{ $impostorCaught ? '¡Impostor descubierto!' : 'El impostor escapó' }}</h1><p>El impostor era <strong>{{ $game->impostor?->name }}</strong>.</p><div class="outcome">{{ $impostorCaught ? 'Los votos acertados recibieron su premio de aprendizaje.' : 'El impostor ganó la ronda y recibió su premio de aprendizaje.' }}</div></section>
    <section class="card"><h2>Resultado de la votación</h2><div class="votes">@foreach($voteCounts->sortDesc() as $suspectId => $count)<div class="vote-row"><span>{{ $game->room->participants->firstWhere('id',(int)$suspectId)?->name ?? 'Estudiante' }}</span><strong>{{ $count }} {{ $count === 1 ? 'voto' : 'votos' }}</strong></div>@endforeach</div></section>
    <section class="card"><h2>Puntos de esta ronda</h2><ul class="points"><li>5 puntos por participar enviando una pista.</li><li>10 puntos adicionales por votar correctamente.</li><li>20 puntos para el impostor si no es descubierto.</li></ul><p style="color:#64748b;font-size:13px">Estos son puntos de aprendizaje: no descuentan kilogramos de la huella de carbono.</p></section>
    @if($teacherView)
        <form method="POST" action="{{ route('teacher.impostor.start',$game->room) }}">@csrf<button class="btn">Iniciar otra ronda</button></form><a class="btn secondary" href="{{ route('teacher.sessions.show',$game->room) }}">Volver a la sala</a>
    @else
        <a class="btn" href="{{ route('student.impostor.lobby') }}">Esperar otra ronda</a><a class="btn secondary" href="{{ route('student.dashboard') }}">Volver al panel</a>
    @endif
</main></body></html>
