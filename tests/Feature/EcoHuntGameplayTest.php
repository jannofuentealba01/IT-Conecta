<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\EcoActivityProfile;
use App\Models\EcoHunt;
use App\Models\EcoHuntCompletion;
use App\Models\Participant;
use App\Models\PointTransaction;
use App\Models\Room;
use App\Models\User;
use App\Services\EcoHuntService;
use Database\Seeders\EcoHuntActivitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EcoHuntGameplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EcoHuntActivitySeeder::class);
    }

    public function test_teacher_starts_and_finishes_hunt_early(): void
    {
        [$teacher, $room, $hunt] = $this->scenario();

        $this->actingAs($teacher)->post(route('teacher.eco-hunts.start', [$room, $hunt]))
            ->assertSessionHas('success');
        $hunt->refresh();
        $this->assertSame('active', $hunt->status);
        $this->assertEquals(900, $hunt->started_at->diffInSeconds($hunt->ends_at));

        $this->actingAs($teacher)->post(route('teacher.eco-hunts.finish', [$room, $hunt]))
            ->assertRedirect(route('teacher.eco-hunts.results', [$room, $hunt]));
        $this->assertSame('teacher', $hunt->refresh()->finished_by);
    }

    public function test_ready_hunt_keeps_students_waiting_and_rejects_qr_points(): void
    {
        [, $room, $hunt, $participant, $profile] = $this->scenario();
        $answers = collect($profile->verification_questions)
            ->mapWithKeys(fn ($question) => [$question['id'] => $question['correct']])
            ->all();

        $this->assertSame(EcoHunt::STATUS_READY, $hunt->status);

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->get(route('student.eco-hunt.index'))
            ->assertOk()
            ->assertSee('La actividad está preparada')
            ->assertDontSee('Abrir cámara');

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->get(route('student.eco-hunt.show', $profile->qr_token))
            ->assertRedirect(route('student.eco-hunt.index'))
            ->assertSessionHas('error');

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->post(route('student.eco-hunt.complete', $profile->qr_token), ['verification_answers' => $answers])
            ->assertRedirect(route('student.eco-hunt.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('eco_hunt_completions', 0);
        $this->assertDatabaseCount('point_transactions', 0);
    }

    public function test_teacher_can_reopen_a_finished_hunt_once_for_five_minutes_and_finish_early(): void
    {
        Carbon::setTestNow('2026-08-21 10:00:00');
        [$teacher, $room, $hunt] = $this->activeScenario();
        app(EcoHuntService::class)->finish($hunt);
        $firstFinishedAt = $hunt->refresh()->finished_at;

        Carbon::setTestNow('2026-08-21 10:10:00');
        $this->actingAs($teacher)
            ->post(route('teacher.eco-hunts.reopen', [$room, $hunt]))
            ->assertRedirect(route('teacher.eco-hunts.index', $room))
            ->assertSessionHas('success');

        $hunt->refresh();
        $this->assertSame('active', $hunt->status);
        $this->assertSame(1, (int) $hunt->reopen_count);
        $this->assertTrue($hunt->initial_finished_at->equalTo($firstFinishedAt));
        $this->assertTrue($hunt->reopened_at->equalTo(now()));
        $this->assertTrue($hunt->ends_at->equalTo(now()->addMinutes(5)));
        $this->assertNull($hunt->finished_at);

        Carbon::setTestNow('2026-08-21 10:12:00');
        $this->actingAs($teacher)->post(route('teacher.eco-hunts.finish', [$room, $hunt]));
        $this->assertSame('finished', $hunt->refresh()->status);

        $this->actingAs($teacher)
            ->post(route('teacher.eco-hunts.reopen', [$room, $hunt]))
            ->assertSessionHas('error', 'Esta EcoBúsqueda ya utilizó su única reapertura.');
        $this->assertSame('finished', $hunt->refresh()->status);
        Carbon::setTestNow();
    }

    public function test_valid_answers_award_server_points_once_and_ignore_client_points(): void
    {
        [, $room, $hunt, $participant, $profile] = $this->activeScenario();
        $answers = collect($profile->verification_questions)->mapWithKeys(fn ($q) => [$q['id'] => $q['correct']])->all();

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->post(route('student.eco-hunt.complete', $profile->qr_token), [
                'verification_answers' => $answers, 'points_awarded' => 9999,
            ])->assertRedirect(route('student.eco-hunt.index'));

        $this->assertDatabaseHas('eco_hunt_completions', [
            'eco_hunt_id' => $hunt->id, 'participant_id' => $participant->id,
            'activity_id' => $profile->activity_id, 'points_awarded' => $profile->game_points,
        ]);
        $this->assertDatabaseHas('point_transactions', [
            'participant_id' => $participant->id, 'source_type' => 'eco_hunt_completion',
            'points' => $profile->game_points,
        ]);

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->post(route('student.eco-hunt.complete', $profile->qr_token), ['verification_answers' => $answers]);
        $this->assertSame(1, EcoHuntCompletion::count());
        $this->assertSame(1, PointTransaction::where('source_type', 'eco_hunt_completion')->count());
    }

    public function test_wrong_answers_or_qr_outside_hunt_do_not_award_points(): void
    {
        [, $room, , $participant, $profile] = $this->activeScenario();
        $other = EcoActivityProfile::where('id', '!=', $profile->id)->firstOrFail();

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->post(route('student.eco-hunt.complete', $profile->qr_token), [
                'verification_answers' => ['q1' => 'wrong', 'q2' => 'wrong'],
            ])->assertSessionHasErrors('verification');
        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->get(route('student.eco-hunt.show', $other->qr_token))
            ->assertRedirect(route('student.eco-hunt.index'))->assertSessionHas('error');

        $this->assertDatabaseCount('eco_hunt_completions', 0);
    }

    public function test_expired_hunt_is_closed_by_server_and_rejects_completion(): void
    {
        [, $room, $hunt, $participant, $profile] = $this->activeScenario();
        $hunt->update(['started_at' => now()->subMinutes(16), 'ends_at' => now()->subSecond()]);
        $answers = collect($profile->verification_questions)->mapWithKeys(fn ($q) => [$q['id'] => $q['correct']])->all();

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->post(route('student.eco-hunt.complete', $profile->qr_token), ['verification_answers' => $answers]);

        $this->assertSame('finished', $hunt->refresh()->status);
        $this->assertSame('automatic', $hunt->finished_by);
        $this->assertDatabaseCount('eco_hunt_completions', 0);
    }

    public function test_ranking_is_only_visible_after_finish_and_breaks_tie_by_first_final_score(): void
    {
        [$teacher, $room, $hunt, $first, $profile] = $this->activeScenario();
        $second = Participant::create(['room_id' => $room->id, 'name' => 'Segundo', 'course' => '1° A']);
        foreach ([[$first, now()->subSeconds(10)], [$second, now()]] as [$participant, $time]) {
            EcoHuntCompletion::create([
                'eco_hunt_id' => $hunt->id, 'room_id' => $room->id, 'participant_id' => $participant->id,
                'activity_id' => $profile->activity_id, 'points_awarded' => $profile->game_points,
                'verification_type' => 'quiz', 'completed_at' => $time,
            ]);
        }

        $this->actingAs($teacher)->get(route('teacher.eco-hunts.results', [$room, $hunt]))
            ->assertRedirect(route('teacher.eco-hunts.index', $room));
        app(EcoHuntService::class)->finish($hunt);
        $ranking = app(EcoHuntService::class)->ranking($hunt);
        $this->assertSame([$first->id, $second->id], $ranking->pluck('id')->all());
    }

    public function test_room_report_includes_eco_hunt_results_and_timing(): void
    {
        [$teacher, $room, $hunt, $participant, $profile] = $this->activeScenario();
        EcoHuntCompletion::create([
            'eco_hunt_id' => $hunt->id, 'room_id' => $room->id, 'participant_id' => $participant->id,
            'activity_id' => $profile->activity_id, 'points_awarded' => $profile->game_points,
            'verification_type' => 'quiz', 'completed_at' => now(),
        ]);
        app(EcoHuntService::class)->finish($hunt);

        $this->actingAs($teacher)->get(route('teacher.sessions.report', $room))
            ->assertOk()->assertSee('EcoBúsqueda: EcoBúsqueda')->assertSee($profile->activity->name)
            ->assertSee('Finalización docente')->assertSee('QR registrados');
    }

    private function activeScenario(): array
    {
        [$teacher, $room, $hunt, $participant, $profile] = $this->scenario();
        app(EcoHuntService::class)->start($hunt);
        return [$teacher, $room, $hunt->refresh(), $participant, $profile];
    }

    private function scenario(): array
    {
        $teacher = User::factory()->create();
        $course = Course::create(['user_id' => $teacher->id, 'name' => '1° A', 'is_active' => true]);
        $room = Room::create(['user_id' => $teacher->id, 'course_id' => $course->id, 'code' => fake()->unique()->numerify('######'), 'name' => 'Sala', 'status' => 'open', 'duration_minutes' => 120, 'opened_at' => now(), 'expires_at' => now()->addHour()]);
        $participant = Participant::create(['room_id' => $room->id, 'name' => 'Primero', 'course' => '1° A']);
        $profile = EcoActivityProfile::with('activity')->firstOrFail();
        $hunt = EcoHunt::create([
            'room_id' => $room->id,
            'name' => 'EcoBúsqueda',
            'status' => EcoHunt::STATUS_READY,
            'duration_seconds' => 900,
        ]);
        $hunt->activities()->attach($profile->activity_id);
        return [$teacher, $room, $hunt, $participant, $profile];
    }
}
