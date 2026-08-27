<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TeacherBreadcrumbsTest extends TestCase
{
    use RefreshDatabase;

    public function test_breadcrumb_component_links_ancestors_but_not_the_current_page(): void
    {
        $this->blade('<x-breadcrumbs :items="$items" />', [
            'items' => [
                ['label' => 'Área docente', 'url' => '/teacher/dashboard'],
                ['label' => 'Página actual', 'url' => '/ruta-que-no-debe-enlazarse'],
            ],
        ])->assertSee('aria-label="Migas de pan"', false)
            ->assertSee('<a href="/teacher/dashboard">Área docente</a>', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('<span>Página actual</span>', false)
            ->assertDontSee('href="/ruta-que-no-debe-enlazarse"', false);
    }

    public function test_session_breadcrumb_shows_its_complete_teacher_hierarchy(): void
    {
        $teacher = User::factory()->create();
        $course = Course::create([
            'user_id' => $teacher->id,
            'name' => '4° Medio D',
            'is_active' => true,
        ]);
        $room = Room::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'code' => '654321',
            'name' => 'Sala de ciencias',
            'status' => 'draft',
            'duration_minutes' => 120,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.sessions.show', $room))
            ->assertOk()
            ->assertSee('aria-label="Migas de pan"', false)
            ->assertSeeInOrder(['Área docente', 'Cursos', '4° Medio D', 'Sala de ciencias']);
    }

    public function test_hierarchical_teacher_views_use_the_shared_component(): void
    {
        $views = [
            'teacher/courses/index.blade.php',
            'teacher/courses/create.blade.php',
            'teacher/courses/edit.blade.php',
            'teacher/courses/show.blade.php',
            'teacher/sessions/create.blade.php',
            'teacher/sessions/show.blade.php',
            'teacher/activities/index.blade.php',
            'teacher/activities/create.blade.php',
            'teacher/activities/edit.blade.php',
            'teacher/eco-hunts/index.blade.php',
            'teacher/eco-hunts/results.blade.php',
            'teacher/reports/room.blade.php',
        ];

        foreach ($views as $view) {
            $this->assertStringContainsString(
                '<x-breadcrumbs',
                File::get(resource_path("views/{$view}")),
                "La vista {$view} no utiliza las migas de pan compartidas."
            );
        }

        $this->assertStringNotContainsString(
            '<x-breadcrumbs',
            File::get(resource_path('views/teacher/dashboard.blade.php'))
        );
    }

    public function test_breadcrumb_styles_prevent_mobile_overflow(): void
    {
        $styles = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('overflow-x:auto', $styles);
        $this->assertStringContainsString('text-overflow:ellipsis', $styles);
        $this->assertStringContainsString(':nth-last-child(-n + 2)', $styles);
    }
}
