<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Course;
use App\Models\EcoActivityProfile;
use App\Models\EcoHunt;
use App\Models\EcoHuntCompletion;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\EcoHuntActivitySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherEcoHuntFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_has_twenty_classified_activities_and_permanent_tokens(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);

        $this->assertSame(20, EcoActivityProfile::count());
        $this->assertSame(10, EcoActivityProfile::where('activity_type', 'immediate')->count());
        $this->assertSame(10, EcoActivityProfile::where('activity_type', 'declared')->count());
        $this->assertSame(20, EcoActivityProfile::distinct()->count('qr_token'));

        $tokens = EcoActivityProfile::orderBy('activity_id')->pluck('qr_token')->all();
        $this->seed(EcoHuntActivitySeeder::class);

        $this->assertSame(20, EcoActivityProfile::count());
        $this->assertSame($tokens, EcoActivityProfile::orderBy('activity_id')->pluck('qr_token')->all());
    }

    public function test_teacher_can_prepare_an_individual_fifteen_minute_hunt(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);
        [$teacher, $room] = $this->teacherAndRoom();
        $activityIds = EcoActivityProfile::limit(4)->pluck('activity_id')->all();

        $this->actingAs($teacher)->post(route('teacher.eco-hunts.store', $room), [
            'name' => 'EcoBúsqueda 1° Medio',
            'activities' => $activityIds,
            'duration_seconds' => 60,
        ])->assertRedirect()->assertSessionHas('success');

        $hunt = EcoHunt::firstOrFail();
        $this->assertSame(EcoHunt::STATUS_READY, $hunt->status);
        $this->assertSame(900, $hunt->duration_seconds);
        $this->assertEqualsCanonicalizing($activityIds, $hunt->activities()->pluck('activities.id')->all());
    }

    public function test_teacher_sees_bulk_selection_controls_and_real_catalog_count(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);
        [$teacher, $room] = $this->teacherAndRoom();

        $this->actingAs($teacher)
            ->get(route('teacher.eco-hunts.index', $room))
            ->assertOk()
            ->assertSee('Seleccionar todas')
            ->assertSee('Deseleccionar todas')
            ->assertSee('id="totalCount">20</strong>', false)
            ->assertSee('id="selectAllActivities"', false)
            ->assertSee('id="clearAllActivities"', false);
    }

    public function test_sticky_bar_exposes_the_critical_action_for_each_hunt_state(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);
        [$teacher, $room] = $this->teacherAndRoom();
        $activity = EcoActivityProfile::firstOrFail()->activity;
        $hunt = EcoHunt::create([
            'room_id' => $room->id,
            'name' => 'Flujo sticky',
            'status' => EcoHunt::STATUS_READY,
            'duration_seconds' => 900,
        ]);
        $hunt->activities()->attach($activity->id);

        $this->actingAs($teacher)
            ->get(route('teacher.eco-hunts.index', $room))
            ->assertOk()
            ->assertSee('id="ecoStickyActions"', false)
            ->assertSee('sticky-action-bar--positive')
            ->assertSeeInOrder(['Guardar selección', 'Iniciar EcoBúsqueda'])
            ->assertSee('data-confirm-title="¿Iniciar EcoBúsqueda?"', false)
            ->assertSee('data-confirm-variant="positive"', false)
            ->assertDontSee('return confirm(', false)
            ->assertSee('Guarda los cambios antes de iniciar.');

        $hunt->update([
            'status' => EcoHunt::STATUS_ACTIVE,
            'started_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.eco-hunts.index', $room))
            ->assertOk()
            ->assertSee('EcoBúsqueda activa')
            ->assertSee('sticky-action-bar--danger')
            ->assertSee('data-confirm-title="¿Finalizar EcoBúsqueda?"', false)
            ->assertSee('data-confirm-variant="danger"', false)
            ->assertSee('Finalizar actividad')
            ->assertDontSee('Iniciar EcoBúsqueda');
    }

    public function test_teacher_cannot_prepare_hunt_with_an_activity_outside_catalog(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);
        [$teacher, $room] = $this->teacherAndRoom();
        $activity = Activity::create([
            'name' => 'Actividad privada', 'description' => 'No pertenece al catálogo EcoBúsqueda',
            'instructions' => 'Prueba', 'category' => 'Otro', 'impact_level' => 'low',
            'points' => 10, 'co2_impact' => 0, 'validation_type' => 'self_report',
            'frequency_days' => 1, 'is_active' => true,
        ]);

        $this->actingAs($teacher)->post(route('teacher.eco-hunts.store', $room), [
            'name' => 'Inválida', 'activities' => [$activity->id],
        ])->assertSessionHasErrors('activities.0');

        $this->assertDatabaseCount('eco_hunts', 0);
    }

    public function test_teacher_cannot_manage_another_teachers_room(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);
        [, $room] = $this->teacherAndRoom();
        $otherTeacher = User::factory()->create();

        $this->actingAs($otherTeacher)->get(route('teacher.eco-hunts.index', $room))->assertNotFound();
    }

    public function test_same_participant_cannot_complete_same_hunt_activity_twice(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);
        [, $room] = $this->teacherAndRoom();
        $activity = EcoActivityProfile::firstOrFail()->activity;
        $hunt = EcoHunt::create(['room_id' => $room->id, 'name' => 'Prueba']);
        $hunt->activities()->attach($activity->id);
        $participant = Participant::create(['room_id' => $room->id, 'name' => 'Ana', 'course' => '1° A']);

        $data = [
            'eco_hunt_id' => $hunt->id, 'room_id' => $room->id,
            'participant_id' => $participant->id, 'activity_id' => $activity->id,
            'points_awarded' => 20, 'verification_type' => 'quiz',
            'verification_answers' => ['q1' => 'done'], 'completed_at' => now(),
        ];
        EcoHuntCompletion::create($data);

        $this->expectException(QueryException::class);
        EcoHuntCompletion::create($data);
    }

    public function test_teacher_downloads_a_real_pdf_kit_with_selected_qr_only(): void
    {
        $this->seed(EcoHuntActivitySeeder::class);
        [$teacher, $room] = $this->teacherAndRoom();
        $profiles = EcoActivityProfile::limit(2)->get();
        $hunt = EcoHunt::create(['room_id' => $room->id, 'name' => 'Kit de prueba']);
        $hunt->activities()->attach($profiles->pluck('activity_id'));

        $response = $this->actingAs($teacher)->get(route('teacher.eco-hunts.kit', [$room, $hunt]));
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);

        if (env('ECO_HUNT_PDF_PREVIEW')) {
            $path = base_path('output/pdf/ecobusqueda-kit-preview.pdf');
            if (! is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
            file_put_contents($path, $content);
        }
    }

    private function teacherAndRoom(): array
    {
        $teacher = User::factory()->create();
        $course = Course::create(['user_id' => $teacher->id, 'name' => '1° A', 'is_active' => true]);
        $room = Room::create([
            'user_id' => $teacher->id, 'course_id' => $course->id,
            'code' => fake()->unique()->numerify('######'), 'name' => 'Sala de prueba',
            'status' => 'draft', 'duration_minutes' => 120,
        ]);

        return [$teacher, $room];
    }
}
