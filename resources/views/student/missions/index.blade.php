@extends('layouts.app')
@section('content')
<style>
.mission-shell{max-width:850px;margin:0 auto}.mission-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:15px}.mission-card{background:#fff;border:1px solid #d1fae5;border-radius:17px;padding:20px;box-shadow:0 10px 25px rgba(6,78,59,.07);min-height:170px}.mission-card.done{background:#f0fdf4;border-color:#4ade80}.mission-card h2{color:#065f46;font-size:18px;margin:8px 0}.mission-badge{display:inline-block;background:#ecfdf5;color:#047857;border-radius:999px;padding:5px 10px;font-size:12px;font-weight:800}.mission-points{color:#d97706;font-weight:800}.mission-note{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:13px;padding:14px;margin-bottom:18px;line-height:1.5}.camera-btn{display:flex;width:100%;min-height:48px;align-items:center;justify-content:center;border:0;border-radius:11px;background:#047857;color:#fff;font-weight:850;font-size:15px;cursor:pointer;touch-action:manipulation}.camera-btn:disabled{background:#9ca3af;cursor:default}.mobile-back{display:flex;align-items:center;justify-content:center;min-height:52px;background:#059669;color:#fff;text-decoration:none;text-align:center;padding:12px;border-radius:11px;font-weight:750;margin-top:18px;touch-action:manipulation}.scanner-overlay{position:fixed;inset:0;z-index:1000;display:none;align-items:center;justify-content:center;padding:15px;background:rgba(15,23,42,.82)}.scanner-overlay.open{display:flex}.scanner-panel{width:min(520px,100%);padding:18px;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(0,0,0,.35)}.scanner-video-wrap{position:relative;overflow:hidden;border-radius:14px;background:#111827;aspect-ratio:3/4;max-height:65vh}.scanner-video{width:100%;height:100%;object-fit:cover}.scanner-target{position:absolute;inset:20%;border:3px solid #34d399;border-radius:15px;box-shadow:0 0 0 999px rgba(0,0,0,.18)}.scanner-actions{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:12px}.scanner-action{min-height:46px;border:0;border-radius:10px;font-weight:800;cursor:pointer}.scanner-photo{background:#ecfdf5;color:#047857}.scanner-close{background:#e5e7eb;color:#374151}.scanner-status{min-height:42px;margin:11px 0 0;color:#475569;font-size:13px;line-height:1.45}@media(max-width:640px){.mission-grid{grid-template-columns:1fr}.mission-card{min-height:145px;padding:18px}.mission-shell h1{font-size:23px!important}.scanner-panel{padding:13px}.scanner-video-wrap{max-height:60vh}}
</style>
<div class="mission-shell">
    <h1 style="color:#064e3b;font-size:26px;margin-bottom:7px;">🧭 Misiones ambientales</h1>
    <p style="color:#6b7280;margin-bottom:18px;line-height:1.5;">Busca los códigos QR distribuidos por el lugar. Cada QR revelará la acción que debes realizar.</p>
    <div class="mission-note">📱 Escanea los QR con tu teléfono o tablet. Puedes completar cada actividad una vez al día.</div>
    <div class="mission-grid">
        @forelse($missions as $mission)
            @php($isCompleted = in_array($mission->activity_id, $completedActivityIds, true))
            <div class="mission-card {{ $isCompleted ? 'done' : '' }}">
                <span class="mission-badge">{{ $mission->activity->category }}</span>
                <h2>{{ $isCompleted ? '✅ Misión completada hoy' : '🔎 Misión disponible' }}</h2>
                <p style="color:#6b7280;font-size:14px;line-height:1.4;">{{ $isCompleted ? 'Ya sumaste los puntos de esta actividad.' : 'Encuentra su QR para revelar la instrucción.' }}</p>
                <p class="mission-points">⭐ {{ $mission->activity->points }} puntos</p>
                <button type="button" class="camera-btn" @disabled($isCompleted)>{{ $isCompleted ? 'Misión completada' : '📷 Abrir cámara' }}</button>
            </div>
        @empty
            <div class="mission-card"><h2>No hay misiones disponibles</h2><p style="color:#6b7280;">Espera a que el profesor seleccione las actividades de esta sesión.</p></div>
        @endforelse
    </div>
    <a href="{{ route('student.dashboard') }}" class="mobile-back">← Volver al panel</a>
</div>

<div class="scanner-overlay" id="scannerOverlay" role="dialog" aria-modal="true" aria-labelledby="scannerTitle">
    <div class="scanner-panel">
        <h2 id="scannerTitle" style="margin:0 0 10px;color:#064e3b;">Escanear código QR</h2>
        <div class="scanner-video-wrap">
            <video class="scanner-video" id="scannerVideo" playsinline muted></video>
            <div class="scanner-target" aria-hidden="true"></div>
        </div>
        <p class="scanner-status" id="scannerStatus">Apunta la cámara al código QR de una misión.</p>
        <div class="scanner-actions">
            <button type="button" class="scanner-action scanner-photo" id="scannerPhoto">Tomar foto</button>
            <button type="button" class="scanner-action scanner-close" id="scannerClose">Cerrar cámara</button>
        </div>
        <input type="file" id="scannerFile" accept="image/*" capture="environment" hidden>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
(() => {
    const overlay = document.getElementById('scannerOverlay');
    const video = document.getElementById('scannerVideo');
    const status = document.getElementById('scannerStatus');
    const fileInput = document.getElementById('scannerFile');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });
    let stream = null;
    let scanning = false;
    let detector = null;

    const stopCamera = () => {
        scanning = false;
        stream?.getTracks().forEach(track => track.stop());
        stream = null;
        video.srcObject = null;
        overlay.classList.remove('open');
    };

    const openMission = rawValue => {
        try {
            const target = new URL(rawValue, window.location.origin);
            if (target.origin !== window.location.origin || !target.pathname.includes('/student/missions/')) {
                status.textContent = 'Este QR no corresponde a una misión de IT Conecta.';
                return false;
            }
            stopCamera();
            window.location.assign(target.href);
            return true;
        } catch (_) {
            status.textContent = 'No fue posible interpretar este código QR.';
            return false;
        }
    };

    const detectFrom = async source => {
        if (detector) {
            try {
                const codes = await detector.detect(source);
                if (codes.length > 0) return openMission(codes[0].rawValue);
            } catch (_) {}
        }

        if (window.jsQR && context) {
            const width = source.videoWidth || source.width;
            const height = source.videoHeight || source.height;
            if (!width || !height) return false;
            canvas.width = width;
            canvas.height = height;
            context.drawImage(source, 0, 0, width, height);
            const imageData = context.getImageData(0, 0, width, height);
            const code = window.jsQR(imageData.data, width, height, { inversionAttempts: 'dontInvert' });
            if (code?.data) return openMission(code.data);
        }

        return false;
    };

    const scanFrame = async () => {
        if (!scanning) return;
        if (video.readyState >= 2 && await detectFrom(video)) return;
        window.setTimeout(scanFrame, 250);
    };

    const openCamera = async () => {
        overlay.classList.add('open');
        status.textContent = 'Apunta la cámara al código QR de una misión.';

        if ('BarcodeDetector' in window) detector = new BarcodeDetector({ formats: ['qr_code'] });

        if (!navigator.mediaDevices?.getUserMedia) {
            status.textContent = 'Abriendo la cámara del dispositivo para tomar una foto del QR.';
            fileInput.click();
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            video.srcObject = stream;
            await video.play();
            scanning = true;
            scanFrame();
        } catch (_) {
            status.textContent = 'No se pudo abrir la cámara. Revisa el permiso del navegador o pulsa “Tomar foto”.';
        }
    };

    document.querySelectorAll('.camera-btn:not(:disabled)').forEach(button => button.addEventListener('click', openCamera));
    document.getElementById('scannerClose').addEventListener('click', stopCamera);
    document.getElementById('scannerPhoto').addEventListener('click', () => fileInput.click());
    overlay.addEventListener('click', event => { if (event.target === overlay) stopCamera(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') stopCamera(); });

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files?.[0];
        if (!file) return;
        if (!('BarcodeDetector' in window) && !window.jsQR) {
            status.textContent = 'No se pudo cargar el lector QR. Revisa la conexión e inténtalo nuevamente.';
            return;
        }
        if (!detector && 'BarcodeDetector' in window) detector = new BarcodeDetector({ formats: ['qr_code'] });
        const image = await createImageBitmap(file);
        const found = await detectFrom(image);
        image.close();
        if (!found) status.textContent = 'No se encontró un QR válido en la imagen. Intenta acercarte y tomar otra foto.';
        fileInput.value = '';
    });
})();
</script>
@endsection
