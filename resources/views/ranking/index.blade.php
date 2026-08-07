<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ranking general | IT Conecta</title>
    <style>
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:18px; font-family:"Segoe UI",-apple-system,BlinkMacSystemFont,Arial,sans-serif; color:#064e3b; background:linear-gradient(145deg,#ecfdf5,#d1fae5); }
        .page { width:min(900px,100%); margin:0 auto; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .brand { font-size:20px; font-weight:850; }
        .back { min-height:48px; display:inline-flex; align-items:center; justify-content:center; padding:0 16px; border-radius:12px; background:#fff; color:#065f46; text-decoration:none; font-weight:750; border:1px solid #a7f3d0; }
        .hero { color:#fff; background:linear-gradient(135deg,#065f46,#059669); padding:22px; border-radius:20px; box-shadow:0 14px 32px rgba(6,95,70,.2); }
        .hero h1 { margin:0 0 7px; font-size:clamp(24px,5vw,34px); }
        .hero p { margin:0; line-height:1.45; opacity:.9; }
        .my-position { display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:14px; margin-top:18px; padding:15px; border-radius:15px; background:rgba(255,255,255,.16); }
        .position-number { width:55px; height:55px; display:grid; place-items:center; border-radius:50%; background:#fff; color:#047857; font-size:22px; font-weight:900; }
        .my-position small,.my-position strong { display:block; }
        .my-position strong { margin-top:3px; }
        .my-points { font-size:22px; font-weight:900; text-align:right; }
        .my-points small { font-size:11px; font-weight:650; }
        .podium { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin:18px 0; }
        .podium-card { min-width:0; padding:16px 10px; border-radius:16px; text-align:center; background:#fff; border:2px solid transparent; box-shadow:0 8px 18px rgba(6,78,59,.08); }
        .podium-card.current { border-color:#10b981; }
        .medal { font-size:30px; }
        .podium-name { margin:7px 0 4px; font-weight:850; overflow-wrap:anywhere; }
        .podium-course { min-height:32px; color:#64748b; font-size:12px; }
        .podium-points { margin-top:8px; color:#d97706; font-weight:900; }
        .ranking-list { overflow:hidden; border-radius:18px; background:#fff; box-shadow:0 10px 24px rgba(6,78,59,.08); }
        .list-title { margin:0; padding:18px; font-size:18px; border-bottom:1px solid #d1fae5; }
        .rank-row { display:grid; grid-template-columns:48px minmax(0,1fr) auto; gap:12px; align-items:center; padding:15px 18px; border-bottom:1px solid #ecfdf5; }
        .rank-row:last-child { border-bottom:0; }
        .rank-row.current { background:#ecfdf5; box-shadow:inset 4px 0 #10b981; }
        .rank-number { font-size:18px; font-weight:900; text-align:center; color:#047857; }
        .student-name { font-weight:850; overflow-wrap:anywhere; }
        .student-name .you { display:inline-block; margin-left:5px; padding:2px 7px; border-radius:99px; color:#047857; background:#d1fae5; font-size:11px; vertical-align:2px; }
        .student-context,.breakdown { color:#64748b; font-size:12px; line-height:1.4; }
        .row-points { color:#d97706; font-size:19px; font-weight:900; text-align:right; }
        .row-points small { display:block; color:#64748b; font-size:11px; font-weight:600; }
        .empty { padding:28px; text-align:center; color:#64748b; }
        .note { margin:15px 3px 0; color:#64748b; font-size:12px; line-height:1.5; }
        @media (max-width:620px) {
            body { padding:12px; }
            .topbar { align-items:stretch; }
            .brand { display:flex; align-items:center; }
            .back { padding:0 12px; }
            .hero { padding:18px; }
            .my-position { grid-template-columns:auto 1fr; }
            .my-points { grid-column:1 / -1; padding-top:10px; border-top:1px solid rgba(255,255,255,.25); text-align:left; }
            .podium { gap:7px; }
            .podium-card { padding:13px 6px; }
            .medal { font-size:25px; }
            .rank-row { grid-template-columns:38px minmax(0,1fr); padding:14px 12px; gap:9px; }
            .row-points { grid-column:2; text-align:left; font-size:17px; }
        }
    </style>
</head>
<body>
<main class="page">
    <nav class="topbar" aria-label="Navegación">
        <div class="brand">🌱 IT Conecta</div>
        <a class="back" href="{{ route('student.dashboard') }}">← Volver</a>
    </nav>

    <section class="hero">
        <h1>🏆 Ranking general</h1>
        <p>Reúne los puntos de todos los estudiantes y conserva los resultados aunque una sala haya terminado.</p>

        @if($currentParticipant)
            <div class="my-position">
                <div class="position-number">#{{ $currentParticipant->ranking_position }}</div>
                <div>
                    <small>Tu posición entre {{ $totalParticipants }} participantes</small>
                    <strong>{{ $currentParticipant->name }}</strong>
                </div>
                <div class="my-points">
                    {{ $currentParticipant->total_points }} pts
                    <small>{{ $currentParticipant->action_points }} de acciones · {{ $currentParticipant->learning_points }} de aprendizaje</small>
                </div>
            </div>
        @endif
    </section>

    @if($ranking->isNotEmpty())
        <section class="podium" aria-label="Primeros lugares">
            @foreach($ranking->take(3) as $student)
                <article class="podium-card {{ $student->id === $currentParticipant?->id ? 'current' : '' }}">
                    <div class="medal">{{ match($student->ranking_position) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => '#'.$student->ranking_position } }}</div>
                    <div class="podium-name">{{ $student->name }}</div>
                    <div class="podium-course">{{ $student->room?->course?->name ?? $student->course ?? 'Curso sin registrar' }}</div>
                    <div class="podium-points">{{ $student->total_points }} pts</div>
                </article>
            @endforeach
        </section>
    @endif

    <section class="ranking-list">
        <h2 class="list-title">Clasificación completa</h2>
        @forelse($ranking as $student)
            <article class="rank-row {{ $student->id === $currentParticipant?->id ? 'current' : '' }}">
                <div class="rank-number">#{{ $student->ranking_position }}</div>
                <div>
                    <div class="student-name">
                        {{ $student->name }}
                        @if($student->id === $currentParticipant?->id)<span class="you">Tú</span>@endif
                    </div>
                    <div class="student-context">
                        {{ $student->room?->course?->name ?? $student->course ?? 'Curso sin registrar' }}
                        @if($student->room?->course?->school_name) · {{ $student->room->course->school_name }} @endif
                    </div>
                    <div class="breakdown">{{ $student->action_points }} acción · {{ $student->learning_points }} aprendizaje · {{ $student->activity_completions_count }} actividades</div>
                </div>
                <div class="row-points">{{ $student->total_points }} pts<small>Puntaje total</small></div>
            </article>
        @empty
            <div class="empty">Todavía no hay estudiantes en el ranking.</div>
        @endforelse
    </section>

    <p class="note">Los empates comparten posición. Para ordenar estudiantes empatados se priorizan los puntos por acciones ambientales y luego las actividades completadas.</p>
</main>
</body>
</html>
