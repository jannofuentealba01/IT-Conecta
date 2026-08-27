@extends('layouts.app')
@section('content')
@include('teacher.partials.styles')
<style>
    .eco-summary{background:linear-gradient(135deg,var(--brand-green-dark),var(--brand-blue));color:var(--surface);border-radius:18px;padding:21px;margin-bottom:18px}.eco-summary p{margin:0;opacity:.9}.eco-selection-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 0 4px;flex-wrap:wrap}.eco-selection-tools{display:flex;gap:8px;flex-wrap:wrap}.eco-selection-tools .teacher-btn{min-height:42px;padding:8px 13px}.eco-type{margin:20px 0 10px}.eco-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px}.eco-option{display:block;border:1px solid var(--border);border-radius:14px;padding:15px;background:var(--surface);cursor:pointer}.eco-option:has(input:checked){border:2px solid var(--brand-green);background:var(--positive-soft)}.eco-top{display:flex;gap:10px;align-items:start}.eco-icon{font-size:24px}.eco-name{font-weight:850;color:var(--text-primary)}.eco-meta{font-size:12px;color:var(--text-secondary);margin-top:5px;line-height:1.45}.eco-points{display:inline-block;margin-top:8px;padding:4px 8px;border-radius:99px;background:var(--warning-soft);color:var(--text-primary);font-size:12px;font-weight:850}.eco-unsaved{display:block;margin-top:3px;color:var(--warning-orange);font-size:12px;font-weight:750}@media(max-width:640px){.eco-selection-toolbar,.eco-selection-tools{flex-direction:column;align-items:stretch}.eco-selection-tools .teacher-btn,.sticky-action-bar .teacher-btn{width:100%}}
</style>
<div class="teacher-shell">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Cursos', 'url' => route('teacher.courses.index')],
        ['label' => $room->course?->name ?? 'Curso', 'url' => route('teacher.courses.show', $room->course_id)],
        ['label' => $room->name, 'url' => route('teacher.sessions.show', $room)],
        ['label' => 'EcoBúsqueda'],
    ]" />
    <div class="teacher-header"><div><p class="teacher-eyebrow">Sala {{ $room->code }}</p><h1 class="teacher-title">EcoBúsqueda</h1><p class="teacher-subtitle">Prepara, inicia y controla la búsqueda individual de QR.</p></div><a class="teacher-btn teacher-btn-muted" href="{{ route('teacher.sessions.show',$room) }}">← Volver a la sala</a></div>
    <div class="eco-summary"><h2 class="teacher-section-title teacher-section-title--inverse">{{ $hunt?->name ?? 'Nueva EcoBúsqueda' }}</h2><p>Modalidad individual · 15 minutos fijos · QR permanentes · ranking solo al finalizar</p></div>
    @if($hunt)<div style="margin:-7px 0 18px;text-align:right"><a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.eco-hunts.kit',[$room,$hunt]) }}">⬇ Descargar kit PDF</a></div>@endif
    @if($hunt?->status === 'ready')
        <div class="teacher-card" style="margin-bottom:18px;text-align:center;border-color:var(--brand-green)"><p class="teacher-eyebrow">Preparada y esperando inicio</p><h2 class="teacher-section-title teacher-section-title--positive">Los estudiantes todavía no pueden sumar puntos</h2><p class="teacher-meta">Puedes revisar la selección y descargar el kit. El tiempo comenzará únicamente cuando pulses “Iniciar EcoBúsqueda”.</p></div>
    @elseif($hunt?->status === 'active')
        <div class="teacher-card" style="margin-bottom:18px;text-align:center"><p class="teacher-eyebrow">Actividad en curso</p><div id="ecoTimer" data-ends="{{ $hunt->ends_at->toIso8601String() }}" style="font-size:42px;font-weight:900;color:var(--brand-green-dark)">--:--</div><p class="teacher-meta">Los estudiantes ya pueden buscar y escanear los QR. La acción para finalizar permanecerá visible al recorrer la lista.</p></div>
    @elseif($hunt?->status === 'finished')
        <div class="teacher-card" style="margin-bottom:18px;text-align:center"><h2 class="teacher-section-title teacher-section-title--positive">EcoBúsqueda finalizada</h2><p class="teacher-meta">Ya no se aceptan puntos.</p><div style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap"><a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.eco-hunts.results',[$room,$hunt]) }}">Ver resultados</a>@if((int)$hunt->reopen_count === 0 && $room->isOpen())<form method="POST" action="{{ route('teacher.eco-hunts.reopen',[$room,$hunt]) }}" data-confirm-title="¿Reabrir por 5 minutos?" data-confirm-text="Esta extensión excepcional puede utilizarse una sola vez." data-confirm-button="Sí, reabrir" data-confirm-variant="positive">@csrf<button class="teacher-btn teacher-btn-positive">↻ Reabrir por 5 minutos</button></form>@elseif((int)$hunt->reopen_count >= 1)<span class="teacher-badge status-draft">Reapertura utilizada</span>@endif</div></div>
    @endif
    @php($selected = collect(old('activities', $hunt?->activities->pluck('id')->all() ?? $profiles->pluck('activity_id')->all()))->map(fn($id)=>(int)$id))
    @php($configurationEditable = !$hunt || $hunt->status === 'ready')
    <form id="ecoConfigurationForm" method="POST" action="{{ $hunt ? route('teacher.eco-hunts.update',[$room,$hunt]) : route('teacher.eco-hunts.store',$room) }}">
        @csrf @if($hunt) @method('PUT') @endif
        <div class="teacher-card"><div class="teacher-form-group"><label for="name">Nombre de la actividad</label><input class="teacher-input" id="name" name="name" maxlength="100" value="{{ old('name',$hunt?->name ?? 'EcoBúsqueda') }}" required @disabled(!$configurationEditable)></div>
        @if($configurationEditable)
            <div class="eco-selection-toolbar">
                <p class="teacher-meta">Elige las acciones que estarán disponibles durante esta búsqueda.</p>
                <div class="eco-selection-tools" aria-label="Selección masiva de actividades">
                    <button type="button" id="selectAllActivities" class="teacher-btn teacher-btn-secondary">✓ Seleccionar todas</button>
                    <button type="button" id="clearAllActivities" class="teacher-btn teacher-btn-muted">Deseleccionar todas</button>
                </div>
            </div>
        @endif
        @error('activities')<div class="teacher-error">{{ $message }}</div>@enderror
        @foreach(['immediate'=>'⚡ Acción inmediata y comprobable','declared'=>'🕐 Acción previa declarada'] as $type => $label)
            <h2 class="teacher-section-title teacher-section-title--positive eco-type">{{ $label }}</h2><div class="eco-list">
            @foreach($profiles->where('activity_type',$type) as $profile)
                <label class="eco-option"><div class="eco-top"><input type="checkbox" name="activities[]" value="{{ $profile->activity_id }}" @checked($selected->contains($profile->activity_id)) @disabled(!$configurationEditable)><span class="eco-icon">{{ $profile->icon }}</span><div><div class="eco-name">{{ $profile->activity->name }}</div><div class="eco-meta">{{ $profile->activity->instructions }}</div><div class="eco-meta"><strong>Ubicación sugerida:</strong> {{ $profile->location_suggestion }} · Confianza {{ $profile->impact_confidence }}</div><span class="eco-points">{{ $profile->game_points }} puntos</span></div></div></label>
            @endforeach
            </div>
        @endforeach
        </div>
    </form>
    @if(!$hunt || in_array($hunt->status, ['ready', 'active'], true))
        <x-sticky-action-bar id="ecoStickyActions" :variant="$hunt?->status === 'active' ? 'danger' : 'positive'" label="Control principal de EcoBúsqueda">
            <x-slot:summary>
                @if($hunt?->status === 'active')
                    <strong>EcoBúsqueda activa</strong>
                    <span class="teacher-meta">Los QR están entregando puntos.</span>
                @else
                    <span id="selectionSummary" aria-live="polite"><strong id="selectedCount">{{ $selected->count() }}</strong> de <strong id="totalCount">{{ $profiles->count() }}</strong> actividades seleccionadas</span>
                    @if($hunt?->status === 'ready')<span id="unsavedSelectionNotice" class="eco-unsaved" hidden>Guarda los cambios antes de iniciar.</span>@endif
                @endif
            </x-slot:summary>
            <x-slot:actions>
                @if(!$hunt)
                    <button id="saveHuntSelection" type="submit" form="ecoConfigurationForm" class="teacher-btn teacher-btn-positive">Preparar EcoBúsqueda</button>
                @elseif($hunt->status === 'ready')
                    <button id="saveHuntSelection" type="submit" form="ecoConfigurationForm" class="teacher-btn teacher-btn-secondary">Guardar selección</button>
                    <form method="POST" action="{{ route('teacher.eco-hunts.start',[$room,$hunt]) }}" data-confirm-title="¿Iniciar EcoBúsqueda?" data-confirm-text="El reloj de 15 minutos comenzará inmediatamente y los estudiantes podrán sumar puntos." data-confirm-button="Sí, iniciar" data-confirm-variant="positive">@csrf<button id="startEcoHunt" class="teacher-btn teacher-btn-positive">▶ Iniciar EcoBúsqueda</button></form>
                @elseif($hunt->status === 'active')
                    <form method="POST" action="{{ route('teacher.eco-hunts.finish',[$room,$hunt]) }}" data-confirm-title="¿Finalizar EcoBúsqueda?" data-confirm-text="Los estudiantes dejarán de sumar puntos y se habilitarán los resultados." data-confirm-button="Sí, finalizar" data-confirm-variant="danger">@csrf<button class="teacher-btn teacher-btn-danger">Finalizar actividad</button></form>
                @endif
            </x-slot:actions>
        </x-sticky-action-bar>
    @endif
</div>
<script>const boxes=[...document.querySelectorAll('input[name="activities[]"]')],count=document.getElementById('selectedCount'),total=document.getElementById('totalCount'),save=document.getElementById('saveHuntSelection'),start=document.getElementById('startEcoHunt'),unsaved=document.getElementById('unsavedSelectionNotice'),selectAll=document.getElementById('selectAllActivities'),clearAll=document.getElementById('clearAllActivities'),nameInput=document.getElementById('name'),selectionKey=()=>boxes.filter(box=>box.checked).map(box=>box.value).sort().join('|'),initialSelection=selectionKey(),initialName=nameInput?.value??'';function refresh(){const selected=boxes.filter(box=>box.checked).length,dirty=!!start&&(selectionKey()!==initialSelection||(nameInput?.value??'')!==initialName);if(count)count.textContent=selected;if(total)total.textContent=boxes.length;if(save)save.disabled=selected===0;if(start)start.disabled=dirty||selected===0;if(unsaved)unsaved.hidden=!dirty}function setAll(checked){boxes.filter(box=>!box.disabled).forEach(box=>box.checked=checked);refresh()}boxes.forEach(box=>box.addEventListener('change',refresh));nameInput?.addEventListener('input',refresh);selectAll?.addEventListener('click',()=>setAll(true));clearAll?.addEventListener('click',()=>setAll(false));refresh();const timer=document.getElementById('ecoTimer');if(timer){const end=new Date(timer.dataset.ends).getTime();const tick=()=>{const left=Math.max(0,Math.ceil((end-Date.now())/1000));timer.textContent=String(Math.floor(left/60)).padStart(2,'0')+':'+String(left%60).padStart(2,'0');if(left===0)location.reload()};tick();setInterval(tick,1000)}</script>
@endsection
