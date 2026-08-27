<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedIpRoomEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_forty_students_can_join_and_register_behind_the_same_school_ip(): void
    {
        $teacher = User::factory()->create();
        $course = Course::create([
            'user_id' => $teacher->id,
            'name' => '4° Medio A',
            'is_active' => true,
        ]);
        $room = Room::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'code' => '741852',
            'name' => 'Prueba red escolar',
            'status' => 'open',
            'duration_minutes' => 120,
            'opened_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        foreach (range(1, 40) as $number) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.40'])
                ->post(route('room.join'), ['code' => $room->code])
                ->assertRedirect(route('room.form', $room->code));

            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.40'])
                ->post(route('room.enter', $room->code), [
                    'name' => "Estudiante {$number}",
                ])
                ->assertRedirect(route('student.dashboard'));

            $this->flushSession();
        }

        $this->assertDatabaseCount('participants', 40);
        $this->assertSame(40, $room->participants()->count());
    }
}
