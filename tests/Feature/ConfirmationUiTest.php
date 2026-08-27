<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfirmationUiTest extends TestCase
{
    public function test_flash_feedback_is_exposed_safely_to_the_centralized_interface(): void
    {
        $this->withSession([
            'error' => 'La sala "4° Medio" ya está cerrada.',
        ])->get('/')
            ->assertOk()
            ->assertSee('data-flash-feedback', false)
            ->assertSee('data-flash-error="La sala &quot;4° Medio&quot; ya está cerrada."', false)
            ->assertDontSee('class="alert-error">❌', false);
    }

    public function test_production_views_and_scripts_do_not_use_native_browser_dialogs(): void
    {
        $files = collect(File::allFiles(resource_path('views')))
            ->merge(File::allFiles(resource_path('js')));

        foreach ($files as $file) {
            $contents = File::get($file->getPathname());

            $this->assertDoesNotMatchRegularExpression(
                '/(?<![A-Za-z])(?:window\.)?(?:alert|confirm)\s*\(/',
                $contents,
                "Se encontró un diálogo nativo en {$file->getPathname()}."
            );
        }
    }
}
