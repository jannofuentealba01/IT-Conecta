<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\ImpostorGame;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use App\Services\ImpostorGameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImpostorVotingTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('impostorScale')]
    public function test_impostor_quantity_scale(int $participants, int $expected): void
    {
        $this->assertSame($expected, app(ImpostorGameService::class)->impostorCountFor($participants));
    }

    public static function impostorScale(): array
    {
        return [
            [3, 1], [7, 1], [8, 2], [14, 2], [15, 3], [22, 3],
            [23, 4], [31, 4], [32, 5], [40, 5],
        ];
    }

    public function test_teacher_prepares_the_round_before_starting_the_timer(): void
    {
        Carbon::setTestNow('2026-08-21 09:00:00');
        [$teacher, $room, $participant] = $this->timedGameContext();

        $this->actingAs($teacher)
            ->post(route('teacher.impostor.start', $room))
            ->assertRedirect();

        $game = ImpostorGame::where('room_id', $room->id)->latest('id')->firstOrFail();
        $this->assertSame('waiting', $game->status);
        $this->assertNull($game->started_at);
        $this->assertNull($game->word);

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->get(route('student.impostor.show', $game))
            ->assertOk()
            ->assertSee('Esperando al profesor');

        $this->actingAs($teacher)
            ->post(route('teacher.impostor.launch', $game))
            ->assertRedirect(route('teacher.impostor.show', $game));

        $game->refresh();
        $this->assertSame('playing', $game->status);
        $this->assertTrue($game->started_at->equalTo(now()));
        $this->assertTrue($game->voting_at->equalTo(now()->addMinutes(4)));
        $this->assertTrue($game->closes_at->equalTo(now()->addMinutes(5)));
        $this->assertNotNull($game->word);
        Carbon::setTestNow();
    }

    public function test_start_assigns_three_distinct_impostors_for_fifteen_students(): void
    {
        $teacher = User::factory()->create();
        $course = Course::create(['user_id' => $teacher->id, 'name' => '3° Medio D', 'is_active' => true]);
        $room = Room::create([
            'user_id' => $teacher->id, 'course_id' => $course->id, 'code' => '654321',
            'name' => 'Sala múltiple', 'status' => 'open', 'duration_minutes' => 120,
            'opened_at' => now(), 'expires_at' => now()->addHour(),
        ]);

        foreach (range(1, 15) as $number) {
            $this->participant($room, "Estudiante $number", "estudiante-$number");
        }

        $game = app(ImpostorGameService::class)->start($room);

        $this->assertCount(3, $game->impostors);
        $this->assertCount(3, $game->impostors->pluck('id')->unique());
        $this->assertTrue($game->impostors->contains('id', $game->impostor_id));
    }

    public function test_student_vote_is_saved_and_teacher_can_finish_the_game(): void
    {
        $teacher = User::factory()->create();
        $course = Course::create([
            'user_id' => $teacher->id,
            'name' => '3° Medio D',
            'is_active' => true,
        ]);
        $room = Room::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'code' => '812364',
            'name' => 'Sala votación',
            'status' => 'open',
            'duration_minutes' => 120,
            'opened_at' => now(),
            'expires_at' => now()->addHour(),
        ]);
        $voter = $this->participant($room, 'Sofía', 'sofia');
        $suspect = $this->participant($room, 'Martín', 'martin');
        $this->participant($room, 'Elena', 'elena');
        $game = ImpostorGame::create([
            'room_id' => $room->id,
            'word' => 'Reciclaje',
            'status' => 'voting',
            'active_marker' => 1,
            'impostor_id' => $suspect->id,
            'started_at' => now()->subMinutes(4),
            'voting_at' => now(),
            'closes_at' => now()->addMinute(),
            'results_at' => now()->addSeconds(90),
        ]);

        $voteResponse = $this->withSession([
            'participant_id' => $voter->id,
            'room_id' => $room->id,
            'room_code' => $room->code,
        ])->post(route('student.impostor.vote', $game), [
            'suspect_id' => $suspect->id,
        ]);

        $voteResponse->assertRedirect();
        $voteResponse->assertSessionHas('success', 'Voto registrado.');
        $this->assertDatabaseHas('impostor_votes', [
            'game_id' => $game->id,
            'voter_id' => $voter->id,
            'suspect_id' => $suspect->id,
        ]);

        $game->update(['status' => 'closed']);

        $finishResponse = $this->actingAs($teacher)
            ->post(route('teacher.impostor.finish', $game));

        $finishResponse->assertRedirect(route('teacher.impostor.results', $game));
        $this->assertSame('finished', $game->fresh()->status);
        $this->assertNull($game->fresh()->active_marker);
    }

    public function test_teacher_vote_now_grants_exactly_one_minute_for_voting(): void
    {
        Carbon::setTestNow('2026-08-12 09:00:00');
        [$teacher, $room, $voter, $suspect] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'playing',
            'active_marker' => 1, 'impostor_id' => $suspect->id,
            'started_at' => now(), 'voting_at' => now()->addMinutes(4),
            'closes_at' => now()->addMinutes(5), 'results_at' => now()->addMinutes(5)->addSeconds(30),
        ]);

        Carbon::setTestNow('2026-08-12 09:01:15');
        $this->actingAs($teacher)->post(route('teacher.impostor.voting', $game))->assertRedirect();

        $game->refresh();
        $this->assertSame('voting', $game->status);
        $this->assertTrue($game->closes_at->equalTo(now()->addMinute()));
        $this->assertTrue($game->results_at->equalTo(now()->addSeconds(90)));
        Carbon::setTestNow();
    }

    public function test_student_state_reflects_teacher_early_voting_without_reloading_the_page(): void
    {
        Carbon::setTestNow('2026-08-12 09:00:00');
        [$teacher, $room, $voter, $suspect] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'playing',
            'active_marker' => 1, 'impostor_id' => $suspect->id,
            'started_at' => now(), 'voting_at' => now()->addMinutes(4),
            'closes_at' => now()->addMinutes(5), 'results_at' => now()->addMinutes(5)->addSeconds(30),
        ]);

        Carbon::setTestNow('2026-08-12 09:02:03');
        $this->actingAs($teacher)
            ->post(route('teacher.impostor.voting', $game))
            ->assertRedirect();

        $this->withSession(['participant_id' => $voter->id, 'room_id' => $room->id])
            ->getJson(route('student.impostor.state', $game))
            ->assertOk()
            ->assertJsonPath('status', 'voting')
            ->assertJsonPath('closes_at', now()->addMinute()->toIso8601String())
            ->assertJsonPath('results_at', now()->addSeconds(90)->toIso8601String());

        Carbon::setTestNow();
    }

    public function test_teacher_state_exposes_the_show_results_window_and_then_finishes_automatically(): void
    {
        Carbon::setTestNow('2026-08-12 09:30:00');
        [$teacher, $room, $voter, $suspect] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'voting',
            'active_marker' => 1, 'impostor_id' => $suspect->id,
            'started_at' => now()->subMinutes(5), 'voting_at' => now()->subMinute(),
            'closes_at' => now(), 'results_at' => now()->addSeconds(30),
        ]);

        $this->actingAs($teacher)
            ->getJson(route('teacher.impostor.state', $game))
            ->assertOk()
            ->assertJsonPath('status', 'closed')
            ->assertJsonPath('results_url', null);

        $this->actingAs($teacher)
            ->get(route('teacher.impostor.show', $game))
            ->assertOk()
            ->assertSee('id="teacherClosedControls"', false)
            ->assertSee('Mostrar resultados');

        Carbon::setTestNow('2026-08-12 09:30:30');
        $this->actingAs($teacher)
            ->getJson(route('teacher.impostor.state', $game))
            ->assertOk()
            ->assertJsonPath('status', 'finished')
            ->assertJsonPath('results_url', route('teacher.impostor.results', $game));

        Carbon::setTestNow();
    }

    public function test_student_cannot_read_the_state_of_a_game_from_another_room(): void
    {
        [, $room, $participant] = $this->timedGameContext();
        [, $otherRoom, , $otherParticipant] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $otherRoom->id,
            'word' => 'Reciclaje',
            'status' => 'playing',
            'active_marker' => 1,
            'impostor_id' => $otherParticipant->id,
            'started_at' => now(),
            'voting_at' => now()->addMinutes(4),
            'closes_at' => now()->addMinutes(5),
            'results_at' => now()->addMinutes(5)->addSeconds(30),
        ]);

        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->getJson(route('student.impostor.state', $game))
            ->assertNotFound();
    }

    public function test_teacher_can_only_show_results_during_the_thirty_second_window(): void
    {
        Carbon::setTestNow('2026-08-12 09:30:00');
        [$teacher, $room, $voter, $suspect] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'voting',
            'active_marker' => 1, 'impostor_id' => $suspect->id,
            'started_at' => now()->subMinutes(4), 'voting_at' => now(),
            'closes_at' => now()->addMinute(), 'results_at' => now()->addSeconds(90),
        ]);

        $this->actingAs($teacher)->post(route('teacher.impostor.finish', $game))
            ->assertSessionHas('error');
        $this->assertSame('voting', $game->fresh()->status);

        Carbon::setTestNow('2026-08-12 09:31:00');
        $this->actingAs($teacher)->post(route('teacher.impostor.finish', $game))
            ->assertRedirect(route('teacher.impostor.results', $game));
        $this->assertSame('finished', $game->fresh()->status);
        Carbon::setTestNow();
    }

    public function test_game_enters_voting_at_minute_four_and_closes_at_minute_five(): void
    {
        Carbon::setTestNow('2026-08-12 10:00:00');
        [$teacher, $room, $voter, $suspect] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'playing',
            'active_marker' => 1, 'impostor_id' => $suspect->id,
            'started_at' => now(), 'voting_at' => now()->addMinutes(4),
            'closes_at' => now()->addMinutes(5), 'results_at' => now()->addMinutes(5)->addSeconds(30),
        ]);

        Carbon::setTestNow('2026-08-12 10:04:00');
        $this->withSession(['participant_id' => $voter->id, 'room_id' => $room->id])
            ->get(route('student.impostor.show', $game))->assertOk();
        $this->assertSame('voting', $game->fresh()->status);

        Carbon::setTestNow('2026-08-12 10:05:00');
        $this->withSession(['participant_id' => $voter->id, 'room_id' => $room->id])
            ->post(route('student.impostor.vote', $game), ['suspect_id' => $suspect->id])
            ->assertSessionHas('error', 'La votación no está disponible.');
        $this->assertSame('closed', $game->fresh()->status);
        $this->assertDatabaseMissing('impostor_votes', ['game_id' => $game->id, 'voter_id' => $voter->id]);

        Carbon::setTestNow();
    }

    public function test_game_shows_results_automatically_thirty_seconds_after_closing(): void
    {
        Carbon::setTestNow('2026-08-12 11:00:00');
        [$teacher, $room, $voter, $suspect] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'closed',
            'active_marker' => 1, 'impostor_id' => $suspect->id,
            'started_at' => now()->subMinutes(5), 'voting_at' => now()->subMinute(),
            'closes_at' => now(), 'results_at' => now()->addSeconds(30),
        ]);

        Carbon::setTestNow('2026-08-12 11:00:30');
        $this->withSession(['participant_id' => $voter->id, 'room_id' => $room->id])
            ->get(route('student.impostor.show', $game))
            ->assertRedirect(route('student.impostor.results', $game));

        $this->assertSame('finished', $game->fresh()->status);
        $this->assertNull($game->fresh()->active_marker);
        Carbon::setTestNow();
    }

    public function test_expired_playing_game_closes_and_finishes_when_no_browser_synchronized_intermediate_phases(): void
    {
        Carbon::setTestNow('2026-08-21 11:00:00');
        [, $room, $participant, $impostor] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'playing',
            'active_marker' => 1, 'impostor_id' => $impostor->id,
            'started_at' => now(), 'voting_at' => now()->addMinutes(4),
            'closes_at' => now()->addMinutes(5), 'results_at' => now()->addMinutes(5)->addSeconds(30),
        ]);
        $game->impostors()->attach($impostor->id);

        Carbon::setTestNow('2026-08-21 11:05:31');
        $this->withSession(['participant_id' => $participant->id, 'room_id' => $room->id])
            ->get(route('student.impostor.show', $game))
            ->assertRedirect(route('student.impostor.results', $game));

        $this->assertSame('finished', $game->fresh()->status);
        $this->assertNull($game->fresh()->active_marker);
        Carbon::setTestNow();
    }

    public function test_student_state_recovers_after_a_prolonged_connection_interruption(): void
    {
        Carbon::setTestNow('2026-08-21 13:00:00');
        [, $room, $participant, $impostor] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id,
            'word' => 'Reciclaje',
            'status' => 'playing',
            'active_marker' => 1,
            'impostor_id' => $impostor->id,
            'started_at' => now(),
            'voting_at' => now()->addMinutes(4),
            'closes_at' => now()->addMinutes(5),
            'results_at' => now()->addMinutes(5)->addSeconds(30),
        ]);
        $game->impostors()->attach($impostor->id);

        // El teléfono vuelve a consultar cuando ya transcurrieron juego,
        // votación y espera de resultados sin ninguna sincronización intermedia.
        Carbon::setTestNow('2026-08-21 13:06:00');

        $this->withSession([
            'participant_id' => $participant->id,
            'room_id' => $room->id,
            'room_code' => $room->code,
        ])->getJson(route('student.impostor.state', $game))
            ->assertOk()
            ->assertJsonPath('status', 'finished')
            ->assertJsonPath('results_url', route('student.impostor.results', $game));

        $game->refresh();
        $this->assertSame('finished', $game->status);
        $this->assertNull($game->active_marker);
        Carbon::setTestNow();
    }

    public function test_student_can_open_results_when_the_round_ended_without_votes(): void
    {
        Carbon::setTestNow('2026-08-21 12:00:00');
        [$teacher, $room, $participant, $impostor] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'closed',
            'active_marker' => 1, 'impostor_id' => $impostor->id,
            'started_at' => now()->subMinutes(5), 'voting_at' => now()->subMinute(),
            'closes_at' => now(), 'results_at' => now(),
        ]);

        $session = ['participant_id' => $participant->id, 'room_id' => $room->id, 'room_code' => $room->code];
        $this->withSession($session)->get(route('student.impostor.show', $game))
            ->assertRedirect(route('student.impostor.results', $game));
        $this->withSession($session)->get(route('student.impostor.results', $game))
            ->assertOk()->assertSee('No se registraron votos en esta ronda.');
        $this->assertSame('finished', $game->fresh()->status);
        Carbon::setTestNow();
    }

    public function test_teacher_can_open_results_when_the_round_ended_without_votes(): void
    {
        Carbon::setTestNow('2026-08-21 12:00:00');
        [$teacher, $room, , $impostor] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id, 'word' => 'Reciclaje', 'status' => 'finished',
            'active_marker' => null, 'impostor_id' => $impostor->id,
            'started_at' => now()->subMinutes(5), 'voting_at' => now()->subMinute(),
            'closes_at' => now(), 'results_at' => now(),
        ]);

        $this->actingAs($teacher)->get(route('teacher.impostor.results', $game))
            ->assertOk()->assertSee('No se registraron votos en esta ronda.');
        Carbon::setTestNow();
    }

    public function test_incomplete_active_game_is_closed_and_hidden_from_students(): void
    {
        [, $room, $participant, $impostor] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id,
            'word' => 'Reciclaje',
            'status' => 'playing',
            'active_marker' => 1,
            'impostor_id' => $impostor->id,
        ]);

        $this->withSession([
            'participant_id' => $participant->id,
            'room_id' => $room->id,
            'room_code' => $room->code,
        ])->get(route('student.impostor.lobby'))
            ->assertOk()
            ->assertSee('Esperando al profesor');

        $game->refresh();
        $this->assertSame('finished', $game->status);
        $this->assertNull($game->active_marker);
    }

    public function test_teacher_prepare_replaces_an_incomplete_active_game_with_a_clean_waiting_round(): void
    {
        [, $room, , $impostor] = $this->timedGameContext();
        $invalidGame = ImpostorGame::create([
            'room_id' => $room->id,
            'word' => 'Reciclaje',
            'status' => 'playing',
            'active_marker' => 1,
            'impostor_id' => $impostor->id,
        ]);

        $newGame = app(ImpostorGameService::class)->prepare($room);

        $invalidGame->refresh();
        $this->assertSame('finished', $invalidGame->status);
        $this->assertNull($invalidGame->active_marker);
        $this->assertNotSame($invalidGame->id, $newGame->id);
        $this->assertSame('waiting', $newGame->status);
        $this->assertSame(1, (int) $newGame->active_marker);
        $this->assertNull($newGame->word);
        $this->assertNull($newGame->started_at);
    }

    public function test_active_game_in_a_closed_room_is_closed_and_not_available(): void
    {
        [, $room] = $this->timedGameContext();
        $game = ImpostorGame::create([
            'room_id' => $room->id,
            'status' => 'waiting',
            'active_marker' => 1,
        ]);
        $room->update(['status' => 'closed', 'closed_at' => now()]);

        $this->assertNull(app(ImpostorGameService::class)->activeGame($room->fresh()));

        $game->refresh();
        $this->assertSame('finished', $game->status);
        $this->assertNull($game->active_marker);
    }

    private function timedGameContext(): array
    {
        $teacher = User::factory()->create();
        $course = Course::create(['user_id' => $teacher->id, 'name' => '3° Medio D', 'is_active' => true]);
        $room = Room::create([
            'user_id' => $teacher->id, 'course_id' => $course->id, 'code' => (string) random_int(100000, 999999),
            'name' => 'Sala cronometrada', 'status' => 'open', 'duration_minutes' => 120,
            'opened_at' => now(), 'expires_at' => now()->addHour(),
        ]);
        $voter = $this->participant($room, 'Sofía', 'sofia');
        $suspect = $this->participant($room, 'Martín', 'martin');
        $this->participant($room, 'Elena', 'elena');

        return [$teacher, $room, $voter, $suspect];
    }

    private function participant(Room $room, string $name, string $normalizedName): Participant
    {
        return Participant::create([
            'room_id' => $room->id,
            'name' => $name,
            'normalized_name' => $normalizedName,
            'course' => '3° Medio D',
            'recovery_token' => str()->random(64),
            'joined_at' => now(),
        ]);
    }
}
