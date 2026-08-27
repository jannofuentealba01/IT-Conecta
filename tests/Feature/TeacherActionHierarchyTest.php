<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TeacherActionHierarchyTest extends TestCase
{
    public function test_teacher_buttons_define_shared_semantic_variants(): void
    {
        $styles = File::get(resource_path('views/teacher/partials/styles.blade.php'));

        $this->assertStringContainsString('.teacher-btn-primary { background:var(--brand-blue)', $styles);
        $this->assertStringContainsString('.teacher-btn-positive { background:var(--brand-green)', $styles);
        $this->assertStringContainsString('.teacher-btn-game { background:var(--brand-purple)', $styles);
        $this->assertStringContainsString('.teacher-btn-secondary { background:var(--surface)', $styles);
        $this->assertStringContainsString('.teacher-btn-danger { background:var(--danger)', $styles);
        $this->assertStringContainsString('.teacher-btn-danger-subtle { background:var(--surface)', $styles);
    }

    public function test_teacher_modules_use_the_variant_that_matches_their_function(): void
    {
        $session = File::get(resource_path('views/teacher/sessions/show.blade.php'));
        $impostor = File::get(resource_path('views/teacher/impostor/show.blade.php'));

        $this->assertStringContainsString('teacher-btn-positive">Preparar EcoBúsqueda', $session);
        $this->assertStringContainsString('teacher-btn-game', $session);
        $this->assertStringContainsString('teacher-btn-danger-subtle">■ Cerrar sesión', $session);
        $this->assertStringContainsString('teacher-btn-game', $impostor);
        $this->assertStringNotContainsString('.teacher-game .teacher-btn-primary', $impostor);
    }
}
