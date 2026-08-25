<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherInstructionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_teacher_can_open_instructions_and_faq(): void
    {
        $teacher = User::factory()->create();

        $this->actingAs($teacher)->get(route('teacher.instructions'))
            ->assertOk()->assertSee('Cómo utilizar IT Conecta')->assertSee('Cerrar la sala');
        $this->actingAs($teacher)->get(route('teacher.instructions.faq'))
            ->assertOk()->assertSee('Preguntas frecuentes')->assertSee('¿Qué hace el botón “Votar ahora”?');
    }

    public function test_guest_can_not_open_teacher_help(): void
    {
        $this->get(route('teacher.instructions'))->assertRedirect(route('login'));
    }
}
