<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRoomReentryTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_device_enters_with_room_code_without_asking_for_name(): void
    {
        [$room, $participant] = $this->openRoomWithParticipant();
        $cookieName = 'it_conecta_participant_'.$room->id;

        $response = $this->withCookie($cookieName, $participant->recovery_token)
            ->post(route('room.join'), ['code' => $room->code]);

        $response->assertRedirect(route('student.dashboard'));
        $response->assertSessionHas('participant_id', $participant->id);
        $response->assertSessionHas('room_id', $room->id);
    }

    public function test_unknown_device_is_still_sent_to_the_name_form(): void
    {
        [$room] = $this->openRoomWithParticipant();

        $response = $this->withCookie('it_conecta_participant_'.$room->id, str_repeat('x', 64))
            ->post(route('room.join'), ['code' => $room->code]);

        $response->assertRedirect(route('room.form', $room->code));
        $response->assertSessionMissing('participant_id');
    }

    private function openRoomWithParticipant(): array
    {
        $room = Room::create([
            'code' => '483921',
            'name' => 'Prueba de reingreso',
            'status' => 'open',
            'duration_minutes' => 120,
            'opened_at' => now(),
            'expires_at' => now()->addHour(),
        ]);
        $participant = Participant::create([
            'room_id' => $room->id,
            'name' => 'Sofía',
            'normalized_name' => 'sofia',
            'course' => '3° Medio D',
            'recovery_token' => str_repeat('a', 64),
            'joined_at' => now(),
        ]);

        return [$room, $participant];
    }
}
