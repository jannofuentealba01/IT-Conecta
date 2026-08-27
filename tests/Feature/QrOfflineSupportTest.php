<?php

namespace Tests\Feature;

use Tests\TestCase;

class QrOfflineSupportTest extends TestCase
{
    public function test_qr_decoder_is_bundled_with_the_application(): void
    {
        $package = json_decode(file_get_contents(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
        $javascript = file_get_contents(resource_path('js/app.js'));

        $this->assertSame('^1.4.0', $package['dependencies']['jsqr'] ?? null);
        $this->assertStringContainsString("import jsQR from 'jsqr';", $javascript);
        $this->assertStringContainsString('window.jsQR = jsQR;', $javascript);
    }

    public function test_student_qr_views_do_not_load_the_decoder_from_an_external_cdn(): void
    {
        $views = [
            resource_path('views/student/missions/index.blade.php'),
            resource_path('views/student/eco-hunt/index.blade.php'),
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($view);
            $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/jsqr', $contents);
            $this->assertStringNotContainsString('unpkg.com/jsqr', $contents);
        }
    }
}
