@extends('layouts.app')

@section('content')
@include('teacher.partials.styles')
<style>
    .report-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .report-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
    .report-stat { background:var(--surface); border:1px solid var(--border); border-radius:15px; padding:17px; }
    .report-stat span { display:block; color:var(--text-secondary); font-size:12px; font-weight:750; }
    .report-stat strong { display:block; margin-top:7px; color:var(--brand-blue-dark); font-size:24px; overflow-wrap:anywhere; }
    .report-table-wrap { width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .report-table { width:100%; border-collapse:collapse; min-width:800px; }
    .report-table th { padding:12px; color:var(--brand-blue-dark); background:var(--info-soft); font-size:12px; text-align:left; text-transform:uppercase; }
    .report-table td { padding:14px 12px; border-bottom:1px solid var(--border); vertical-align:top; }
    .report-table tr:last-child td { border-bottom:0; }
    .student-title { color:var(--text-primary); font-weight:850; }
    .student-meta { color:var(--text-secondary); font-size:12px; margin-top:4px; }
    .score { color:var(--warning-orange); font-weight:900; font-size:18px; }
    .level { display:inline-flex; border-radius:99px; padding:4px 8px; font-size:11px; font-weight:850; }
    .level-low { color:var(--brand-green-dark); background:var(--positive-soft); }
    .level-medium { color:var(--text-primary); background:var(--impact-medium); }
    .level-high { color:var(--danger-dark); background:var(--danger-soft); }
    .footprint-number-low { color:var(--brand-green-dark); }
    .footprint-number-medium { color:var(--text-primary); }
    .footprint-number-high { color:var(--danger-dark); }
    .report-stat.footprint-low { background:var(--positive-soft); border-color:var(--impact-low); }
    .report-stat.footprint-medium { background:var(--impact-medium); border-color:var(--impact-medium); }
    .report-stat.footprint-high { background:var(--danger-soft); border-color:var(--impact-high); }
    .report-stat.footprint-low strong { color:var(--brand-green-dark); }
    .report-stat.footprint-medium strong { color:var(--text-primary); }
    .report-stat.footprint-high strong { color:var(--danger-dark); }
    .activity-list { margin:6px 0 0; padding-left:17px; color:var(--text-secondary); font-size:12px; }
    .report-note { padding:14px 16px; margin-bottom:18px; border-radius:13px; color:var(--text-secondary); background:var(--surface-muted); border:1px solid var(--border); font-size:13px; line-height:1.5; }
    .mobile-results { display:none; }
    @media (max-width:760px) {
        .report-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .report-table-wrap { display:none; }
        .mobile-results { display:grid; gap:12px; }
        .student-card { padding:16px; border:1px solid var(--border); border-radius:15px; background:var(--surface); }
        .student-card-head { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; }
        .student-metrics { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin-top:13px; }
        .student-metric { padding:10px; border-radius:10px; background:var(--surface-muted); color:var(--text-secondary); font-size:11px; }
        .student-metric strong { display:block; margin-top:4px; color:var(--text-primary); font-size:15px; }
        .student-metric.footprint-low { background:var(--positive-soft); }
        .student-metric.footprint-medium { background:var(--impact-medium); }
        .student-metric.footprint-high { background:var(--danger-soft); }
        .student-metric strong.footprint-number-low { color:var(--brand-green-dark); }
        .student-metric strong.footprint-number-medium { color:var(--text-primary); }
        .student-metric strong.footprint-number-high { color:var(--danger-dark); }
    }
    @media (max-width:420px) { .report-summary { grid-template-columns:1fr; } }
    @media print {
        body { padding:0; background:#fff; }
        .navbar,.footer,.report-actions { display:none !important; }
        .teacher-shell { max-width:none; }
        .teacher-card,.report-stat { box-shadow:none; break-inside:avoid; }
        .report-table-wrap { display:block !important; overflow:visible; }
        .report-table { min-width:0; font-size:10px; }
        .mobile-results { display:none !important; }
    }
</style>

@php
    $statusLabels = ['draft'=>'Preparada','open'=>'Abierta','closed'=>'Cerrada','archived'=>'Archivada'];
    $levelLabels = ['low'=>'Baja','medium'=>'Media','high'=>'Alta'];
@endphp

<div class="teacher-shell">
    <div class="teacher-header">
        <div>
            <p class="teacher-eyebrow">Reporte de resultados</p>
            <h1 class="teacher-title">{{ $room->name }}</h1>
            <p class="teacher-subtitle">{{ $room->course?->name ?? 'Sin curso' }}@if($room->course?->school_name) · {{ $room->course->school_name }}@endif · Código {{ $room->code }}</p>
        </div>
        <div class="report-actions">
            <span class="teacher-badge status-{{ $room->status }}">{{ $statusLabels[$room->status] ?? ucfirst($room->status) }}</span>
            <button type="button" class="teacher-btn teacher-btn-secondary" onclick="window.print()">Imprimir reporte</button>
        </div>
    </div>

    <div class="report-summary">
        <div class="report-stat"><span>Estudiantes registrados</span><strong>{{ $summary['participants'] }}</strong></div>
        <div class="report-stat"><span>Huellas calculadas</span><strong>{{ $summary['footprints_calculated'] }}/{{ $summary['participants'] }}</strong></div>
        <div class="report-stat {{ $summary['average_footprint_classification'] ? 'footprint-'.$summary['average_footprint_classification']['key'] : '' }}"><span>Huella inicial promedio</span><strong>{{ $summary['average_footprint'] !== null ? number_format($summary['average_footprint'], 0, ',', '.') : 'Sin datos' }}</strong><span>{{ $summary['average_footprint'] !== null ? 'kg CO₂e/año' : '' }}</span></div>
        <div class="report-stat"><span>Actividades realizadas</span><strong>{{ $summary['completed_activities'] }}</strong></div>
        <div class="report-stat"><span>Puntos totales</span><strong>{{ $summary['total_points'] }}</strong></div>
        <div class="report-stat"><span>Puntos por acciones</span><strong>{{ $summary['action_points'] }}</strong></div>
        <div class="report-stat"><span>Puntos de aprendizaje</span><strong>{{ $summary['learning_points'] }}</strong></div>
        <div class="report-stat"><span>Reducción anual proyectada</span><strong>{{ $summary['quantified_reduction'] !== null ? number_format($summary['quantified_reduction'], 2, ',', '.').' kg' : 'Pendiente' }}</strong></div>
    </div>

    @if($summary['quantified_reduction'] === null)
        <div class="report-note"><strong>Reducción de huella pendiente:</strong> las actividades todavía no tienen los valores reales de reducción anual. El sistema conserva todos los registros y mostrará este cálculo cuando se incorporen esos valores, sin inventar equivalencias.</div>
    @endif

    <section class="teacher-card">
        <div class="teacher-header" style="margin-bottom:14px;">
            <div><h2 style="margin:0 0 5px; font-size:19px;">Resultados por estudiante</h2><p class="teacher-meta">Información acumulada exclusivamente en esta sala.</p></div>
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead><tr><th>Estudiante</th><th>Huella inicial</th><th>Puntos</th><th>Actividades realizadas</th><th>Reducción proyectada</th></tr></thead>
                <tbody>
                @forelse($participants as $participant)
                    <tr>
                        <td><div class="student-title">{{ $participant->name }}</div><div class="student-meta">Ingresó {{ ($participant->joined_at ?? $participant->created_at)->format('d/m/Y H:i') }}</div></td>
                        <td>
                            @if($participant->currentCarbonFootprint)
                                <strong class="footprint-number-{{ $participant->footprint_classification['key'] }}">{{ number_format((float)$participant->currentCarbonFootprint->initial_kg_co2e_year, 2, ',', '.') }} kg CO₂e/año</strong><br>
                                <span class="level level-{{ $participant->footprint_classification['key'] }}">{{ $levelLabels[$participant->footprint_classification['key']] }}</span>
                            @else
                                <span class="student-meta">No calculada</span>
                            @endif
                        </td>
                        <td><span class="score">{{ $participant->total_points }} pts</span><div class="student-meta">{{ $participant->action_points }} acción · {{ $participant->learning_points }} aprendizaje</div></td>
                        <td>
                            <strong>{{ $participant->activity_completions_count }}</strong>
                            @if($participant->activityCompletions->isNotEmpty())
                                <ul class="activity-list">@foreach($participant->activityCompletions as $completion)<li>{{ $completion->activity?->name ?? 'Actividad eliminada' }} · {{ $completion->completed_at->format('d/m/Y') }}</li>@endforeach</ul>
                            @endif
                        </td>
                        <td>{{ $participant->projected_reduction !== null ? number_format($participant->projected_reduction, 2, ',', '.').' kg CO₂e/año' : 'Pendiente' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">Aún no hay estudiantes registrados en esta sala.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mobile-results">
            @forelse($participants as $participant)
                <article class="student-card">
                    <div class="student-card-head"><div><div class="student-title">{{ $participant->name }}</div><div class="student-meta">{{ $participant->activity_completions_count }} actividades</div></div><span class="score">{{ $participant->total_points }} pts</span></div>
                    <div class="student-metrics">
                        <div class="student-metric {{ $participant->footprint_classification ? 'footprint-'.$participant->footprint_classification['key'] : '' }}">Huella inicial<strong class="{{ $participant->footprint_classification ? 'footprint-number-'.$participant->footprint_classification['key'] : '' }}">{{ $participant->currentCarbonFootprint ? number_format((float)$participant->currentCarbonFootprint->initial_kg_co2e_year, 0, ',', '.').' kg/año' : 'No calculada' }}</strong></div>
                        <div class="student-metric">Puntos por acciones<strong>{{ $participant->action_points }}</strong></div>
                        <div class="student-metric">Puntos de aprendizaje<strong>{{ $participant->learning_points }}</strong></div>
                        <div class="student-metric">Reducción proyectada<strong>{{ $participant->projected_reduction !== null ? number_format($participant->projected_reduction, 2, ',', '.').' kg/año' : 'Pendiente' }}</strong></div>
                    </div>
                    @if($participant->activityCompletions->isNotEmpty())
                        <ul class="activity-list">@foreach($participant->activityCompletions as $completion)<li>{{ $completion->activity?->name ?? 'Actividad eliminada' }} · {{ $completion->completed_at->format('d/m/Y') }}</li>@endforeach</ul>
                    @endif
                </article>
            @empty
                <div class="empty-state">Aún no hay estudiantes registrados en esta sala.</div>
            @endforelse
        </div>
    </section>

    @foreach($ecoHunts as $hunt)
    <section class="teacher-card" style="margin-top:18px">
        <div class="teacher-header" style="margin-bottom:12px"><div><h2 style="margin:0 0 5px;font-size:19px">EcoBúsqueda: {{ $hunt->name }}</h2><p class="teacher-meta">{{ $hunt->started_at?->format('d/m/Y H:i') ?? 'No iniciada' }} · {{ $hunt->effective_seconds !== null ? gmdate('i:s',(int)$hunt->effective_seconds) : '--:--' }} de duración · {{ ['automatic'=>'Finalización automática','teacher'=>'Finalización docente'][$hunt->finished_by] ?? ['draft'=>'En configuración','ready'=>'Preparada','active'=>'Activa','finished'=>'Finalizada'][$hunt->status] ?? ucfirst($hunt->status) }}</p></div><span class="teacher-badge status-{{ $hunt->status==='active'?'open':'draft' }}">{{ $hunt->completions->count() }} QR registrados</span></div>
        @if($hunt->ranking->isNotEmpty())<div class="report-table-wrap"><table class="report-table" style="min-width:560px"><thead><tr><th>Posición</th><th>Estudiante</th><th>Puntos</th><th>QR encontrados</th></tr></thead><tbody>@foreach($hunt->ranking as $index=>$entry)<tr><td>#{{ $index+1 }}</td><td>{{ $entry->name }}</td><td class="score">{{ $entry->points }}</td><td>{{ $entry->completed_count }}/{{ $hunt->activities->count() }}</td></tr>@endforeach</tbody></table></div>@else<div class="empty-state">Esta EcoBúsqueda todavía no registra actividades completadas.</div>@endif
        @if($hunt->activity_stats->isNotEmpty())<div class="report-note" style="margin:14px 0 0"><strong>Actividad más encontrada:</strong> {{ $hunt->activity_stats->first()->name }} ({{ $hunt->activity_stats->first()->count }}) · <strong>Menos encontrada entre las registradas:</strong> {{ $hunt->activity_stats->last()->name }} ({{ $hunt->activity_stats->last()->count }})</div>@endif
    </section>
    @endforeach

    <div style="margin-top:16px;"><a href="{{ route('teacher.sessions.show', $room) }}" class="teacher-btn teacher-btn-muted">← Volver a la sala</a></div>
</div>
@endsection
