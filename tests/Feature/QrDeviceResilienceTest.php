<?php

namespace Tests\Feature;

use Tests\TestCase;

class QrDeviceResilienceTest extends TestCase
{
    public function test_shared_scanner_handles_mobile_camera_and_permission_recovery(): void
    {
        $scanner = file_get_contents(resource_path('js/qr-scanner.js'));

        $this->assertStringContainsString("facingMode: { ideal: 'environment' }", $scanner);
        $this->assertStringContainsString('enumerateDevices()', $scanner);
        $this->assertStringContainsString("case 'NotAllowedError'", $scanner);
        $this->assertStringContainsString("case 'NotReadableError'", $scanner);
        $this->assertStringContainsString('window.isSecureContext', $scanner);
        $this->assertStringContainsString('HTTPS', $scanner);
        $this->assertStringContainsString('applyConstraints', $scanner);
        $this->assertStringContainsString('capabilities.torch', $scanner);
        $this->assertStringContainsString("inversionAttempts: 'attemptBoth'", $scanner);
        $this->assertStringContainsString("'visibilitychange'", $scanner);
        $this->assertStringContainsString("'pagehide'", $scanner);
        $this->assertStringContainsString('selectingPhoto', $scanner);
        $this->assertStringContainsString('requestId !== cameraRequest', $scanner);
        $this->assertStringContainsString("'createImageBitmap' in window", $scanner);
        $this->assertStringContainsString('new Image()', $scanner);
    }

    public function test_both_student_qr_views_use_the_resilient_shared_scanner(): void
    {
        $missions = file_get_contents(resource_path('views/student/missions/index.blade.php'));
        $ecoHunt = file_get_contents(resource_path('views/student/eco-hunt/index.blade.php'));

        foreach ([$missions, $ecoHunt] as $view) {
            $this->assertStringContainsString('ITConectaQrScanner.createQrScanner', $view);
            $this->assertStringContainsString('capture="environment"', $view);
            $this->assertStringContainsString('Reintentar cámara', $view);
            $this->assertStringContainsString('Encender linterna', $view);
            $this->assertStringContainsString('aria-live="polite"', $view);
            $this->assertStringNotContainsString('getUserMedia(', $view);
        }
    }

    public function test_frontend_exposes_the_shared_scanner_and_physical_checklist_exists(): void
    {
        $app = file_get_contents(resource_path('js/app.js'));
        $checklist = file_get_contents(base_path('docs/PRUEBA_FISICA_QR.md'));
        $instructions = file_get_contents(resource_path('views/teacher/help/instructions.blade.php'));
        $faq = file_get_contents(resource_path('views/teacher/help/faq.blade.php'));

        $this->assertStringContainsString("import { createQrScanner } from './qr-scanner'", $app);
        $this->assertStringContainsString('window.ITConectaQrScanner', $app);
        $this->assertStringContainsString('Teléfono Android', $checklist);
        $this->assertStringContainsString('iPhone', $checklist);
        $this->assertStringContainsString('Permiso rechazado', $checklist);
        $this->assertStringContainsString('Poca luz', $checklist);
        $this->assertStringContainsString('Cancelar fotografía', $checklist);
        $this->assertStringContainsString('cámara trasera', $instructions);
        $this->assertStringContainsString('Reintentar cámara', $instructions);
        $this->assertStringContainsString('requiere que el sitio use HTTPS', $faq);
        $this->assertStringContainsString('Tomar foto', $faq);
    }
}
