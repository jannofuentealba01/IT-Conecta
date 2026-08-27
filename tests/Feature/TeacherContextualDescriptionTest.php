<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TeacherContextualDescriptionTest extends TestCase
{
    public function test_each_teacher_screen_has_at_most_one_contextual_subtitle(): void
    {
        $files = collect(File::allFiles(resource_path('views/teacher')))
            ->merge(File::allFiles(resource_path('views/admin')))
            ->reject(fn ($file) => str_ends_with($file->getFilename(), '-pdf.blade.php'));

        foreach ($files as $file) {
            $contents = File::get($file->getPathname());

            if (! str_contains($contents, '<h1')) {
                continue;
            }

            $this->assertSame(
                1,
                substr_count($contents, 'class="teacher-subtitle"'),
                "La pantalla {$file->getPathname()} debe tener una sola descripción contextual."
            );
        }
    }

    public function test_form_screens_explain_the_effect_of_the_action_briefly(): void
    {
        $createActivity = File::get(resource_path('views/teacher/activities/create.blade.php'));
        $editActivity = File::get(resource_path('views/teacher/activities/edit.blade.php'));
        $editCourse = File::get(resource_path('views/teacher/courses/edit.blade.php'));

        $this->assertStringContainsString('acción ambiental reutilizable', $createActivity);
        $this->assertStringContainsString('cuando esta actividad vuelva a utilizarse', $editActivity);
        $this->assertStringContainsString('sin solicitar estos datos nuevamente', $editCourse);
    }
}
