<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TeacherTypographyHierarchyTest extends TestCase
{
    public function test_teacher_typography_defines_a_shared_visual_scale(): void
    {
        $styles = File::get(resource_path('views/teacher/partials/styles.blade.php'));

        $this->assertStringContainsString('.teacher-title {', $styles);
        $this->assertStringContainsString('.teacher-section-title {', $styles);
        $this->assertStringContainsString('.teacher-card-title {', $styles);
        $this->assertStringContainsString('.teacher-body {', $styles);
        $this->assertStringContainsString('.teacher-meta {', $styles);
    }

    public function test_teacher_screen_headings_do_not_define_arbitrary_font_sizes_inline(): void
    {
        $files = collect(File::allFiles(resource_path('views/teacher')))
            ->merge(File::allFiles(resource_path('views/admin')))
            ->reject(fn ($file) => str_ends_with($file->getFilename(), '-pdf.blade.php'));

        foreach ($files as $file) {
            $contents = File::get($file->getPathname());

            $this->assertDoesNotMatchRegularExpression(
                '/<h[12]\b[^>]*style="[^"]*font-size/i',
                $contents,
                "Se encontró un tamaño de encabezado local en {$file->getPathname()}."
            );
        }
    }

    public function test_instruction_steps_are_card_titles_below_their_flow_section(): void
    {
        $instructions = File::get(resource_path('views/teacher/help/instructions.blade.php'));

        $this->assertDoesNotMatchRegularExpression(
            '/<article class="help-step"[^\r\n]*<h2/i',
            $instructions
        );
        $this->assertSame(29, substr_count($instructions, '<article class="help-step"><div><h3>'));
    }
}
