<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><title>Resultados | IT Conecta</title>@vite(['resources/css/app.css', 'resources/js/app.js'])@include('partials.color-tokens')
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;padding:16px;font-family:"Segoe UI",Arial,sans-serif;background:var(--surface-muted);color:var(--text-primary)}.page{width:min(680px,100%);margin:auto}.hero,.card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:22px;margin-bottom:14px;box-shadow:0 12px 28px rgba(17,24,39,.1)}.hero{text-align:center;border-top:5px solid var(--brand-purple)}.hero .icon{font-size:54px}.hero h1{margin:8px 0;color:var(--brand-purple-dark)}.outcome{padding:14px;border-radius:13px;font-weight:850;background:{{ $allImpostorsCaught ? 'var(--positive-soft)' : 'var(--danger-soft)' }};color:{{ $allImpostorsCaught ? 'var(--brand-green-dark)' : 'var(--danger-dark)' }};margin-top:15px}.votes{display:grid;gap:8px}.vote-row{display:flex;justify-content:space-between;gap:10px;padding:12px;background:var(--surface-muted);border-radius:10px}.points{padding-left:20px;color:var(--text-secondary);line-height:1.8}.btn{width:100%;min-height:52px;display:flex;justify-content:center;align-items:center;border:0;border-radius:13px;background:var(--brand-purple);color:var(--surface);text-decoration:none;font-weight:850;margin-top:10px;cursor:pointer;font-size:15px}.btn:hover{background:var(--brand-purple-dark)}.secondary{background:var(--surface-muted);color:var(--text-primary);border:1px solid var(--border)}
    </style>
</head>
<body><main class="page">
    @if($teacherView)
        <x-breadcrumbs :items="[
            ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
            ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
            ['label' => $game->room->course?->name ?? 'Curso', 'url' => route('teacher.courses.show', $game->room->course_id)],
            ['label' => $game->room->name, 'url' => route('teacher.sessions.show', $game->room)],
            ['label' => 'Juego del Impostor'],
            ['label' => 'Resultados'],
        ]" />
    @endif
    @unless($teacherView)@include('student.partials.identity-bar', ['participant' => $participant])@endunless
    @php($displayImpostors = $game->impostors->isNotEmpty() ? $game->impostors : collect([$game->impostor])->filter())
    <section class="hero"><div class="icon">{{ $allImpostorsCaught ? '🎉' : '😈' }}</div><h1>{{ $allImpostorsCaught ? '¡Todos los impostores fueron descubiertos!' : 'Uno o más impostores escaparon' }}</h1><p>Los impostores eran <strong>{{ $displayImpostors->pluck('name')->join(', ') }}</strong>.</p><div class="outcome">Se descubrieron {{ $caughtImpostorIds->count() }} de {{ $displayImpostors->count() }} impostores. Los votos dirigidos a cualquier impostor recibieron su premio de aprendizaje.</div></section>
    <section class="card"><h2>Resultado de la votación</h2><div class="votes">@forelse($voteCounts->sortDesc() as $suspectId => $count)<div class="vote-row"><span>{{ $game->room->participants->firstWhere('id',(int)$suspectId)?->name ?? 'Estudiante' }}</span><strong>{{ $count }} {{ $count === 1 ? 'voto' : 'votos' }}</strong></div>@empty<p style="color:var(--text-secondary);margin:0">No se registraron votos en esta ronda.</p>@endforelse</div></section>
    <section class="card"><h2>Puntos de esta ronda</h2><ul class="points"><li>5 puntos por participar enviando una pista.</li><li>10 puntos adicionales por votar correctamente.</li><li>20 puntos para el impostor si no es descubierto.</li></ul><p style="color:var(--text-secondary);font-size:13px">Estos son puntos de aprendizaje: no descuentan kilogramos de la huella de carbono.</p></section>
    @if($teacherView)
        <form method="POST" action="{{ route('teacher.impostor.start',$game->room) }}">@csrf<button class="btn">Iniciar otra ronda</button></form><a class="btn secondary" href="{{ route('teacher.sessions.show',$game->room) }}">Volver a la sala</a>
    @else
        <a class="btn" href="{{ route('student.impostor.lobby') }}">Esperar otra ronda</a><a class="btn secondary" href="{{ route('student.dashboard') }}">Volver al panel</a>
    @endif
</main></body></html>
