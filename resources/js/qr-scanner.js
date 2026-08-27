function cameraErrorMessage(error) {
    if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        return 'La cámara en vivo requiere una conexión segura (HTTPS). Puedes usar “Tomar foto” para leer el QR.';
    }

    switch (error?.name) {
        case 'NotAllowedError':
        case 'SecurityError':
            return 'El permiso de cámara está bloqueado. Habilítalo en los ajustes del navegador y pulsa “Reintentar cámara”, o usa “Tomar foto”.';
        case 'NotFoundError':
        case 'OverconstrainedError':
            return 'No se encontró una cámara compatible. Puedes continuar usando “Tomar foto”.';
        case 'NotReadableError':
            return 'La cámara está siendo utilizada por otra aplicación. Ciérrala y pulsa “Reintentar cámara”.';
        case 'AbortError':
            return 'La apertura de la cámara fue cancelada. Puedes reintentarlo o tomar una foto.';
        default:
            return 'No se pudo abrir la cámara. Pulsa “Reintentar cámara” o utiliza “Tomar foto”.';
    }
}

async function preferredRearCameraStream() {
    const constraints = {
        audio: false,
        video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1280 },
            height: { ideal: 720 },
        },
    };
    const initialStream = await navigator.mediaDevices.getUserMedia(constraints);
    const initialTrack = initialStream.getVideoTracks()[0];
    const settings = initialTrack?.getSettings?.() ?? {};

    if (settings.facingMode === 'environment' || !navigator.mediaDevices.enumerateDevices) {
        return initialStream;
    }

    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const rearCamera = devices.find((device) =>
            device.kind === 'videoinput'
            && /back|rear|environment|trasera|posterior/i.test(device.label)
            && device.deviceId !== settings.deviceId
        );

        if (!rearCamera) return initialStream;

        const rearStream = await navigator.mediaDevices.getUserMedia({
            audio: false,
            video: {
                deviceId: { exact: rearCamera.deviceId },
                width: { ideal: 1280 },
                height: { ideal: 720 },
            },
        });
        initialStream.getTracks().forEach((track) => track.stop());

        return rearStream;
    } catch (_) {
        return initialStream;
    }
}

async function imageSourceFromFile(file) {
    if ('createImageBitmap' in window) {
        const bitmap = await createImageBitmap(file);

        return { source: bitmap, release: () => bitmap.close() };
    }

    const objectUrl = URL.createObjectURL(file);
    const image = new Image();
    image.src = objectUrl;
    await new Promise((resolve, reject) => {
        image.onload = resolve;
        image.onerror = reject;
    });

    return { source: image, release: () => URL.revokeObjectURL(objectUrl) };
}

export function createQrScanner(options) {
    const overlay = options.overlay;
    const video = options.video;
    const status = options.status;
    const fileInput = options.fileInput;
    const closeButton = options.closeButton;
    const photoButton = options.photoButton;
    const retryButton = options.retryButton;
    const torchButton = options.torchButton;
    const openButtons = [...options.openButtons];
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });
    let detector = null;
    let stream = null;
    let scanning = false;
    let opening = false;
    let navigating = false;
    let torchEnabled = false;
    let previousFocus = null;
    let cameraRequest = 0;
    let selectingPhoto = false;

    function setStatus(message) {
        status.textContent = message;
    }

    function initializeDetector() {
        if (detector || !('BarcodeDetector' in window)) return;

        try {
            detector = new BarcodeDetector({ formats: ['qr_code'] });
        } catch (_) {
            detector = null;
        }
    }

    function stopTracks() {
        scanning = false;
        torchEnabled = false;
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
        video.srcObject = null;
        if (torchButton) {
            torchButton.hidden = true;
            torchButton.textContent = 'Encender linterna';
        }
    }

    function closeScanner() {
        cameraRequest += 1;
        stopTracks();
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        previousFocus?.focus?.();
    }

    function openTarget(rawValue) {
        try {
            const target = new URL(rawValue, window.location.origin);
            if (target.origin !== window.location.origin || !target.pathname.startsWith(options.pathPrefix)) {
                setStatus(options.invalidQrMessage);
                return false;
            }

            navigating = true;
            closeScanner();
            window.location.assign(target.href);
            return true;
        } catch (_) {
            setStatus('No fue posible interpretar este código QR. Intenta con otro código.');
            return false;
        }
    }

    async function detectFrom(source) {
        initializeDetector();

        if (detector) {
            try {
                const codes = await detector.detect(source);
                if (codes[0]?.rawValue) return openTarget(codes[0].rawValue);
            } catch (_) {
                // jsQR se utiliza como respaldo local.
            }
        }

        if (!window.jsQR || !context) return false;

        const width = source.videoWidth || source.naturalWidth || source.width;
        const height = source.videoHeight || source.naturalHeight || source.height;
        if (!width || !height) return false;

        const scale = Math.min(1, 960 / Math.max(width, height));
        const scanWidth = Math.round(width * scale);
        const scanHeight = Math.round(height * scale);
        canvas.width = scanWidth;
        canvas.height = scanHeight;
        context.drawImage(source, 0, 0, scanWidth, scanHeight);
        const imageData = context.getImageData(0, 0, scanWidth, scanHeight);
        const code = window.jsQR(imageData.data, scanWidth, scanHeight, { inversionAttempts: 'attemptBoth' });

        return code?.data ? openTarget(code.data) : false;
    }

    async function scanFrame() {
        if (!scanning || navigating) return;
        if (video.readyState >= 2 && await detectFrom(video)) return;
        window.setTimeout(scanFrame, 250);
    }

    function configureTorch() {
        const track = stream?.getVideoTracks()[0];
        let capabilities = {};
        try {
            capabilities = track?.getCapabilities?.() ?? {};
        } catch (_) {}

        if (torchButton) torchButton.hidden = !capabilities.torch;
    }

    async function openCamera() {
        if (opening) return;
        opening = true;
        const requestId = ++cameraRequest;
        previousFocus = document.activeElement;
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        retryButton.hidden = true;
        setStatus('Solicitando acceso a la cámara trasera…');
        stopTracks();

        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            setStatus(cameraErrorMessage());
            opening = false;
            photoButton.focus();
            return;
        }

        try {
            const openedStream = await preferredRearCameraStream();
            if (requestId !== cameraRequest || !overlay.classList.contains('open')) {
                openedStream.getTracks().forEach((track) => track.stop());
                return;
            }
            stream = openedStream;
            video.srcObject = stream;
            await video.play();
            configureTorch();
            scanning = true;
            setStatus('Apunta al QR y mantén el teléfono estable. Si hay poca luz, usa la linterna o toma una foto.');
            scanFrame();
        } catch (error) {
            if (requestId !== cameraRequest || !overlay.classList.contains('open')) return;
            stopTracks();
            retryButton.hidden = false;
            setStatus(cameraErrorMessage(error));
            photoButton.focus();
        } finally {
            opening = false;
        }
    }

    async function toggleTorch() {
        const track = stream?.getVideoTracks()[0];
        if (!track) return;

        try {
            torchEnabled = !torchEnabled;
            await track.applyConstraints({ advanced: [{ torch: torchEnabled }] });
            torchButton.textContent = torchEnabled ? 'Apagar linterna' : 'Encender linterna';
        } catch (_) {
            torchEnabled = false;
            torchButton.hidden = true;
            setStatus('La linterna no está disponible en esta cámara. Acércate al QR o utiliza “Tomar foto”.');
        }
    }

    async function processFile() {
        selectingPhoto = false;
        const file = fileInput.files?.[0];
        if (!file) return;

        initializeDetector();
        if (!detector && !window.jsQR) {
            setStatus('El lector QR no está disponible. Recarga la aplicación e inténtalo nuevamente.');
            fileInput.value = '';
            return;
        }

        setStatus('Analizando la fotografía…');
        let imageResource = null;
        try {
            imageResource = await imageSourceFromFile(file);
            const found = await detectFrom(imageResource.source);
            if (!found) setStatus('No se encontró un QR válido. Busca mejor iluminación, acércate y evita reflejos.');
        } catch (_) {
            setStatus('No fue posible leer esta fotografía. Toma una nueva imagen e inténtalo otra vez.');
        } finally {
            imageResource?.release();
            fileInput.value = '';
        }
    }

    function selectPhoto() {
        stopTracks();
        selectingPhoto = true;
        setStatus('Abriendo la cámara o galería del dispositivo…');
        fileInput.click();
    }

    openButtons.forEach((button) => button.addEventListener('click', openCamera));
    closeButton.addEventListener('click', closeScanner);
    photoButton.addEventListener('click', selectPhoto);
    retryButton.addEventListener('click', openCamera);
    torchButton?.addEventListener('click', toggleTorch);
    fileInput.addEventListener('change', processFile);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) closeScanner();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay.classList.contains('open')) closeScanner();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && !selectingPhoto && overlay.classList.contains('open')) closeScanner();
    });
    window.addEventListener('focus', () => {
        window.setTimeout(() => {
            if (!selectingPhoto) return;
            selectingPhoto = false;
            setStatus('No se seleccionó una fotografía. Puedes tomar una nueva o reintentar la cámara en vivo.');
            if (window.isSecureContext && navigator.mediaDevices?.getUserMedia) retryButton.hidden = false;
        }, 300);
    });
    window.addEventListener('pagehide', stopTracks);

    overlay.setAttribute('aria-hidden', 'true');

    return { openCamera, closeScanner };
}
