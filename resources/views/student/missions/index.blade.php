@extends('layouts.app')
@section('content')
<style>
.mission-shell{max-width:850px;margin:0 auto}.mission-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:15px}.mission-card{background:var(--surface);border:1px solid var(--border);border-radius:17px;padding:20px;box-shadow:0 10px 25px rgba(17,24,39,.07);min-height:170px}.mission-card.done{background:var(--positive-soft);border-color:var(--brand-green)}.mission-card h2{color:var(--text-primary);font-size:18px;margin:8px 0}.mission-badge{display:inline-block;background:var(--positive-soft);color:var(--brand-green-dark);border-radius:999px;padding:5px 10px;font-size:12px;font-weight:800}.mission-points{color:var(--warning-orange);font-weight:800}.mission-note{background:var(--positive-soft);border:1px solid var(--brand-green);color:var(--text-primary);border-radius:13px;padding:14px;margin-bottom:18px;line-height:1.5}.camera-btn{display:flex;width:100%;min-height:48px;align-items:center;justify-content:center;border:0;border-radius:11px;background:var(--brand-green);color:var(--surface);font-weight:850;font-size:15px;cursor:pointer;touch-action:manipulation}.camera-btn:hover{background:var(--brand-green-dark)}.camera-btn:disabled{background:var(--border);color:var(--text-secondary);cursor:default}.mobile-back{display:flex;align-items:center;justify-content:center;min-height:52px;background:var(--brand-blue);color:var(--surface);text-decoration:none;text-align:center;padding:12px;border-radius:11px;font-weight:750;margin-top:18px;touch-action:manipulation}.scanner-overlay{position:fixed;inset:0;z-index:1000;display:none;align-items:center;justify-content:center;padding:15px;background:rgba(17,24,39,.82)}.scanner-overlay.open{display:flex}.scanner-panel{width:min(520px,100%);padding:18px;border-radius:18px;background:var(--surface);box-shadow:0 24px 60px rgba(0,0,0,.35)}.scanner-video-wrap{position:relative;overflow:hidden;border-radius:14px;background:var(--text-primary);aspect-ratio:3/4;max-height:65vh}.scanner-video{width:100%;height:100%;object-fit:cover}.scanner-target{position:absolute;inset:20%;border:3px solid var(--brand-green);border-radius:15px;box-shadow:0 0 0 999px rgba(0,0,0,.18)}.scanner-actions{display:grid;grid-template-columns:repeat(2,1fr);gap:9px;margin-top:12px}.scanner-action{min-height:46px;border:0;border-radius:10px;font-weight:800;cursor:pointer}.scanner-photo,.scanner-retry{background:var(--positive-soft);color:var(--brand-green-dark)}.scanner-torch{background:var(--warning-soft);color:var(--text-primary)}.scanner-close{background:var(--border);color:var(--text-primary)}.scanner-status{min-height:42px;margin:11px 0 0;color:var(--text-secondary);font-size:13px;line-height:1.45}@media(max-width:640px){.mission-grid{grid-template-columns:1fr}.mission-card{min-height:145px;padding:18px}.mission-shell h1{font-size:23px!important}.scanner-panel{padding:13px}.scanner-video-wrap{max-height:60vh}.scanner-actions{grid-template-columns:1fr}}
</style>
<div class="mission-shell">
    <h1 style="color:var(--brand-green-dark);font-size:26px;margin-bottom:7px;">🧭 Misiones ambientales</h1>
    <p style="color:var(--text-secondary);margin-bottom:18px;line-height:1.5;">Busca los códigos QR distribuidos por el lugar. Cada QR revelará la acción que debes realizar.</p>
    <div class="mission-note">📱 Escanea los QR con tu teléfono o tablet. Puedes completar cada actividad una vez al día.</div>
    <div class="mission-grid">
        @forelse($missions as $mission)
            @php($isCompleted = in_array($mission->activity_id, $completedActivityIds, true))
            <div class="mission-card {{ $isCompleted ? 'done' : '' }}">
                <span class="mission-badge">{{ $mission->activity->category }}</span>
                <h2>{{ $isCompleted ? '✅ Misión completada hoy' : '🔎 Misión disponible' }}</h2>
                <p style="color:var(--text-secondary);font-size:14px;line-height:1.4;">{{ $isCompleted ? 'Ya sumaste los puntos de esta actividad.' : 'Encuentra su QR para revelar la instrucción.' }}</p>
                <p class="mission-points">⭐ {{ $mission->activity->points }} puntos</p>
                <button type="button" class="camera-btn" @disabled($isCompleted)>{{ $isCompleted ? 'Misión completada' : '📷 Abrir cámara' }}</button>
            </div>
        @empty
            <div class="mission-card"><h2>No hay misiones disponibles</h2><p style="color:var(--text-secondary);">Espera a que el profesor seleccione las actividades de esta sesión.</p></div>
        @endforelse
    </div>
    <a href="{{ route('student.dashboard') }}" class="mobile-back">← Volver al panel</a>
</div>

<div class="scanner-overlay" id="scannerOverlay" role="dialog" aria-modal="true" aria-labelledby="scannerTitle" aria-hidden="true">
    <div class="scanner-panel">
        <h2 id="scannerTitle" style="margin:0 0 10px;color:var(--brand-green-dark);">Escanear código QR</h2>
        <div class="scanner-video-wrap">
            <video class="scanner-video" id="scannerVideo" playsinline muted></video>
            <div class="scanner-target" aria-hidden="true"></div>
        </div>
        <p class="scanner-status" id="scannerStatus" role="status" aria-live="polite">Apunta la cámara al código QR de una misión.</p>
        <div class="scanner-actions">
            <button type="button" class="scanner-action scanner-photo" id="scannerPhoto">Tomar foto</button>
            <button type="button" class="scanner-action scanner-retry" id="scannerRetry" hidden>Reintentar cámara</button>
            <button type="button" class="scanner-action scanner-torch" id="scannerTorch" hidden>Encender linterna</button>
            <button type="button" class="scanner-action scanner-close" id="scannerClose">Cerrar cámara</button>
        </div>
        <input type="file" id="scannerFile" accept="image/*" capture="environment" hidden>
    </div>
</div>

<script>
function initializeMissionScanner(){window.ITConectaQrScanner.createQrScanner({overlay:document.getElementById('scannerOverlay'),video:document.getElementById('scannerVideo'),status:document.getElementById('scannerStatus'),fileInput:document.getElementById('scannerFile'),closeButton:document.getElementById('scannerClose'),photoButton:document.getElementById('scannerPhoto'),retryButton:document.getElementById('scannerRetry'),torchButton:document.getElementById('scannerTorch'),openButtons:document.querySelectorAll('.camera-btn:not(:disabled)'),pathPrefix:'/student/missions/',invalidQrMessage:'Este QR no corresponde a una misión de IT Conecta.'})}
if(window.ITConectaQrScanner)initializeMissionScanner();else window.addEventListener('it-conecta:frontend-ready',initializeMissionScanner,{once:true});
</script>
@endsection
