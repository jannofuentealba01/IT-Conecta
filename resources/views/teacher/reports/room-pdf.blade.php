<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resultados - {{ $results['session']['name'] }}</title>
    <style>
        @page { margin: 24px 28px 34px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.35; }
        h1, h2, h3, p { margin: 0; }
        .header { margin-bottom: 14px; padding: 15px 17px; border-left: 7px solid #3B82F6; background: #F9FAFB; }
        .brand { color: #2563EB; font-size: 9px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin-top: 3px; font-size: 21px; }
        .session-meta { margin-top: 5px; color: #4B5563; }
        .summary { width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 5px; }
        .summary td { width: 25%; padding: 10px; border: 1px solid #E5E7EB; border-radius: 6px; background: #FFFFFF; vertical-align: top; }
        .summary-label { display: block; color: #4B5563; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .summary-value { display: block; margin-top: 4px; color: #2563EB; font-size: 16px; font-weight: bold; }
        .section { margin: 0 0 15px; page-break-inside: avoid; }
        .section.allow-break { page-break-inside: auto; }
        .page-break { page-break-before: always; }
        .section-title { margin-bottom: 7px; padding-bottom: 5px; border-bottom: 2px solid #3B82F6; color: #2563EB; font-size: 14px; }
        .section-title.green { border-color: #22C55E; color: #16A34A; }
        .section-title.purple { border-color: #8B5CF6; color: #7C3AED; }
        .section-meta { margin: -2px 0 8px; color: #4B5563; }
        table.data { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        table.data thead { display: table-header-group; }
        table.data tr { page-break-inside: avoid; }
        table.data th { padding: 5px 6px; color: #111827; background: #EAF2FE; border: 1px solid #D5E3F8; font-size: 7.5px; text-align: left; text-transform: uppercase; }
        table.data td { padding: 5px 6px; border: 1px solid #E5E7EB; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #F9FAFB; }
        .number { white-space: nowrap; font-weight: bold; }
        .muted { color: #4B5563; }
        .result { margin-bottom: 8px; padding: 9px 11px; border: 1px solid #C4B5FD; border-radius: 5px; background: #F5F3FF; }
        .result strong { color: #7C3AED; }
        .empty { padding: 12px; color: #4B5563; border: 1px solid #E5E7EB; background: #F9FAFB; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 10px; font-size: 7px; font-weight: bold; }
        .badge-low { color: #14532D; background: #DCFCE7; }
        .badge-medium { color: #111827; background: #FAD201; }
        .badge-high { color: #991B1B; background: #FEE2E2; }
        .badge-game { color: #6D28D9; background: #EDE9FE; }
        .badge-crew { color: #1D4ED8; background: #DBEAFE; }
        .note { margin-top: 8px; padding: 8px 10px; color: #4B5563; border: 1px solid #E5E7EB; background: #F9FAFB; }
    </style>
</head>
<body>
@php
    $formatDate = fn ($value, $format = 'd/m/Y H:i') => $value ? \Illuminate\Support\Carbon::parse($value)->format($format) : 'Sin registro';
    $levelLabels = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'];
    $experienceLabels = ['eco_hunt' => 'EcoBúsqueda', 'impostor_game' => 'Juego del Impostor'];
@endphp

<header class="header">
    <div class="brand">IT Conecta · Reporte docente</div>
    <h1>{{ $results['session']['name'] }}</h1>
    <p class="session-meta">
        {{ $results['session']['course'] ?? 'Curso no registrado' }}
        @if($results['session']['school']) · {{ $results['session']['school'] }} @endif
        · Estado: {{ $results['session']['status_label'] }}
        · Creada: {{ $formatDate($results['session']['created_at']) }}
        · Reporte: {{ $formatDate($results['generated_at']) }}
    </p>
</header>

<table class="summary">
    <tr>
        <td><span class="summary-label">Estudiantes</span><span class="summary-value">{{ $results['summary']['participants'] }}</span></td>
        <td><span class="summary-label">Huellas calculadas</span><span class="summary-value">{{ $results['summary']['footprints_calculated'] }}/{{ $results['summary']['participants'] }}</span></td>
        <td><span class="summary-label">Huella inicial promedio</span><span class="summary-value">{{ $results['summary']['average_footprint_kg_co2e_year'] !== null ? number_format($results['summary']['average_footprint_kg_co2e_year'], 0, ',', '.').' kg' : 'Sin datos' }}</span></td>
        <td><span class="summary-label">Actividades realizadas</span><span class="summary-value">{{ $results['summary']['completed_activities'] }}</span></td>
    </tr>
    <tr>
        <td><span class="summary-label">Puntos totales</span><span class="summary-value">{{ $results['summary']['total_points'] }}</span></td>
        <td><span class="summary-label">Puntos por acciones</span><span class="summary-value">{{ $results['summary']['action_points'] }}</span></td>
        <td><span class="summary-label">Puntos de aprendizaje</span><span class="summary-value">{{ $results['summary']['learning_points'] }}</span></td>
        <td><span class="summary-label">Reducción anual proyectada</span><span class="summary-value">{{ $results['summary']['projected_reduction_kg_co2e_year'] !== null ? number_format($results['summary']['projected_reduction_kg_co2e_year'], 2, ',', '.').' kg' : 'Pendiente' }}</span></td>
    </tr>
</table>

<section class="section allow-break">
    <h2 class="section-title">Resultados por estudiante</h2>
    @if(count($results['participants']))
        <table class="data">
            <thead><tr><th>Estudiante</th><th>Ingreso</th><th>Huella inicial</th><th>Clasificación</th><th>Puntos</th><th>Acción</th><th>Aprendizaje</th><th>Actividades</th><th>Reducción proyectada</th></tr></thead>
            <tbody>
            @foreach($results['participants'] as $participant)
                <tr>
                    <td><strong>{{ $participant['name'] }}</strong></td>
                    <td>{{ $formatDate($participant['joined_at']) }}</td>
                    <td class="number">{{ $participant['footprint_kg_co2e_year'] !== null ? number_format($participant['footprint_kg_co2e_year'], 2, ',', '.').' kg CO₂e/año' : 'No calculada' }}</td>
                    <td>@if($participant['footprint_level'])<span class="badge badge-{{ $participant['footprint_level'] }}">{{ $levelLabels[$participant['footprint_level']] ?? ucfirst($participant['footprint_level']) }}</span>@else<span class="muted">Sin datos</span>@endif</td>
                    <td class="number">{{ $participant['total_points'] }}</td>
                    <td class="number">{{ $participant['action_points'] }}</td>
                    <td class="number">{{ $participant['learning_points'] }}</td>
                    <td>{{ count($participant['completed_activities']) }}</td>
                    <td class="number">{{ $participant['projected_reduction_kg_co2e_year'] !== null ? number_format($participant['projected_reduction_kg_co2e_year'], 2, ',', '.').' kg CO₂e/año' : 'Pendiente' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">Aún no hay estudiantes registrados en esta sala.</div>
    @endif
</section>

@foreach($results['experiences'] as $experienceNumber => $experience)
    @if($experience['type'] === 'impostor_game')<div class="page-break"></div>@endif
    <section class="section allow-break">
        <h2 class="section-title {{ $experience['type'] === 'eco_hunt' ? 'green' : 'purple' }}">
            {{ $experienceLabels[$experience['type']] ?? $experience['name'] }}@if($experience['type'] === 'eco_hunt') · {{ $experience['name'] }}@endif
        </h2>
        <p class="section-meta">
            Inicio: {{ $formatDate($experience['started_at']) }}
            · Término: {{ $formatDate($experience['finished_at']) }}
            · Duración: {{ $experience['duration_seconds'] !== null ? gmdate('i:s', $experience['duration_seconds']) : 'Sin registro' }}
            · Estado: {{ $experience['status_label'] }}
            · Participantes: {{ $experience['participant_count'] }}
        </p>

        @if($experience['result'])
            <div class="result"><strong>Resultado: {{ $experience['result']['winner_label'] ?? 'Sin ganador' }}.</strong> {{ $experience['result']['summary'] }}</div>
        @endif

        @if(count($experience['ranking']))
            <table class="data">
                @if($experience['type'] === 'eco_hunt')
                    <thead><tr><th>Posición</th><th>Estudiante</th><th>Puntos</th><th>Actividades completadas</th></tr></thead>
                    <tbody>@foreach($experience['ranking'] as $entry)<tr><td>#{{ $entry['position'] }}</td><td><strong>{{ $entry['participant'] }}</strong></td><td class="number">{{ $entry['points'] }}</td><td>{{ $entry['completed_activities'] }}/{{ $experience['metrics']['selected_activities'] }}</td></tr>@endforeach</tbody>
                @else
                    <thead><tr><th>Posición</th><th>Estudiante</th><th>Rol</th><th>Pista</th><th>Votó por</th><th>Votos recibidos</th><th>Puntos</th></tr></thead>
                    <tbody>@foreach($experience['ranking'] as $entry)<tr><td>#{{ $entry['position'] }}</td><td><strong>{{ $entry['participant'] }}</strong></td><td><span class="badge {{ $entry['role'] === 'impostor' ? 'badge-game' : 'badge-crew' }}">{{ $entry['role'] === 'impostor' ? 'Impostor' : 'Tripulación' }}</span></td><td>{{ $entry['clue_submitted'] ? 'Entregada' : 'Sin pista' }}</td><td>{{ $entry['voted_for'] ?? 'Sin voto' }}</td><td>{{ $entry['votes_received'] }}</td><td class="number">{{ $entry['points'] }}</td></tr>@endforeach</tbody>
                @endif
            </table>
        @else
            <div class="empty">Esta experiencia todavía no registra resultados.</div>
        @endif
    </section>
@endforeach

@if($results['summary']['projected_reduction_kg_co2e_year'] === null)
    <div class="note"><strong>Nota:</strong> la reducción anual proyectada aparecerá cuando las actividades tengan factores reales configurados. Este reporte no inventa equivalencias ni reducciones.</div>
@endif

</body>
</html>
