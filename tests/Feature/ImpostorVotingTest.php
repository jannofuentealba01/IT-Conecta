<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\ImpostorGame;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpostorVotingTest extends TestCase
{
    use RefreshDatabase;

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

        $finishResponse = $this->actingAs($teacher)
            ->post(route('teacher.impostor.finish', $game));

        $finishResponse->assertRedirect(route('teacher.impostor.results', $game));
        $this->assertSame('finished', $game->fresh()->status);
        $this->assertNull($game->fresh()->active_marker);
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
