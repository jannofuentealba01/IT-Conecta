@extends('layouts.app')
@section('content')
<style>
.hunt{max-width:760px;margin:auto}.hero{background:linear-gradient(135deg,var(--brand-green-dark),var(--brand-blue));color:var(--surface);border-radius:20px;padding:22px}.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:9px;margin-top:16px}.stat{background:color-mix(in srgb,var(--surface) 18%,transparent);border-radius:12px;padding:12px;text-align:center}.stat strong{display:block;font-size:23px}.notice,.message{margin:14px 0;padding:14px;border-radius:13px;background:var(--surface);border:1px solid var(--brand-green);color:var(--text-primary)}.warning{background:var(--warning-soft);border-color:var(--warning-orange);color:var(--text-primary)}.scan{width:100%;min-height:58px;border:0;border-radius:14px;background:var(--brand-green);color:var(--surface);font-size:17px;font-weight:900;cursor:pointer}.scan:hover,.photo:hover{background:var(--brand-green-dark)}.back{display:flex;justify-content:center;margin-top:13px;padding:13px;color:var(--brand-blue-dark)}.overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(17,24,39,.88);padding:14px;align-items:center;justify-content:center}.overlay.open{display:flex}.panel{background:var(--surface);border-radius:18px;padding:14px;width:min(520px,100%)}video{width:100%;max-height:62vh;border-radius:13px;background:var(--text-primary)}.actions{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}.actions button{min-height:48px;border:0;border-radius:10px;font-weight:800;cursor:pointer}.photo,.retry{background:var(--brand-green);color:var(--surface)}.torch{background:var(--warning-soft);color:var(--text-primary)}.close{background:var(--border);color:var(--text-primary)}#scanStatus{min-height:44px;color:var(--text-secondary);line-height:1.45}@media(max-width:540px){.stats{grid-template-columns:1fr 1fr}.stat:last-child{grid-column:1/-1}.actions{grid-template-columns:1fr}}
</style>
<main class="hunt"><div class="hero"><h1 style="margin:0">🧭 EcoBúsqueda</h1>
@if($hunt?->status === 'active')<div class="stats"><div class="stat">Encontradas<strong>{{ $progress['completed'] }}/{{ $hunt->activities->count() }}</strong></div><div class="stat">Puntos<strong>{{ $progress['points'] }}</strong></div><div class="stat">Tiempo<strong id="timer" data-ends="{{ $hunt->ends_at->toIso8601String() }}">--:--</strong></div></div>@elseif($hunt?->status === 'ready')<p>La actividad está preparada. Espera a que el profesor la inicie.</p>@else<p>Espera a que el profesor prepare la actividad.</p>@endif</div>
@if($hunt?->status === 'active')<div id="warning" class="notice warning" hidden>⏳ ¡Quedan 5 minutos! Continúa buscando misiones.</div><div class="notice">Busca los QR distribuidos por el colegio. Escanear no entrega puntos: primero debes realizar o declarar la acción y responder dos preguntas.</div><button id="openCamera" class="scan">📷 Abrir cámara</button>@elseif($hunt?->status === 'ready')<div class="notice">✅ La EcoBúsqueda está preparada, pero todavía no comienza. No es posible escanear ni sumar puntos hasta que el profesor la inicie. Esta pantalla se actualizará automáticamente.</div>@elseif($hunt)<div class="notice">El profesor todavía está configurando la EcoBúsqueda. Esta pantalla se actualizará automáticamente.</div>@else<div class="notice">No hay una EcoBúsqueda preparada para esta sala.</div>@endif
<a class="back" href="{{ route('student.dashboard') }}">← Volver al panel</a></main>
<div class="overlay" id="overlay" role="dialog" aria-modal="true" aria-labelledby="ecoScannerTitle" aria-hidden="true">
    <div class="panel">
        <h2 id="ecoScannerTitle">Escanear QR</h2>
        <video id="video" playsinline muted></video>
        <p id="scanStatus" role="status" aria-live="polite">Apunta la cámara al QR.</p>
        <div class="actions">
            <button type="button" class="photo" id="photo">Tomar foto</button>
            <button type="button" class="retry" id="retryCamera" hidden>Reintentar cámara</button>
            <button type="button" class="torch" id="torch" hidden>Encender linterna</button>
            <button type="button" class="close" id="close">Cerrar cámara</button>
        </div>
        <input id="file" type="file" accept="image/*" capture="environment" hidden>
    </div>
</div>
<script>
const ecoTimer=document.getElementById('timer');
if(ecoTimer){const end=new Date(ecoTimer.dataset.ends).getTime(),warning=document.getElementById('warning');const tick=()=>{const left=Math.max(0,Math.ceil((end-Date.now())/1000));ecoTimer.textContent=String(Math.floor(left/60)).padStart(2,'0')+':'+String(left%60).padStart(2,'0');if(left<=300&&left>0)warning.hidden=false;if(left===0)location.reload()};tick();setInterval(tick,1000)}else if({{ in_array($hunt?->status, ['draft', 'ready'], true) ? 'true' : 'false' }})setTimeout(()=>location.reload(),5000);
function initializeEcoHuntScanner(){const open=document.getElementById('openCamera');if(!open)return;window.ITConectaQrScanner.createQrScanner({overlay:document.getElementById('overlay'),video:document.getElementById('video'),status:document.getElementById('scanStatus'),fileInput:document.getElementById('file'),closeButton:document.getElementById('close'),photoButton:document.getElementById('photo'),retryButton:document.getElementById('retryCamera'),torchButton:document.getElementById('torch'),openButtons:[open],pathPrefix:'/student/eco-hunt/qr/',invalidQrMessage:'Este QR no pertenece a una EcoBúsqueda de IT Conecta.'})}
if(window.ITConectaQrScanner)initializeEcoHuntScanner();else window.addEventListener('it-conecta:frontend-ready',initializeEcoHuntScanner,{once:true});
</script>
@endsection
