<?php

namespace Tests\Unit;

use App\Models\Mission;
use App\Models\Room;
use Tests\TestCase;

class MissionStateTest extends TestCase
{
    public function test_active_mission_in_open_room_is_available(): void
    {
        $mission = new Mission(['is_active' => true]);
        $mission->setRelation('room', new Room(['status' => 'open']));

        $this->assertTrue($mission->isAvailable());
    }

    public function test_mission_outside_its_time_window_is_not_available(): void
    {
        $mission = new Mission(['is_active' => true, 'closes_at' => now()->subMinute()]);
        $mission->setRelation('room', new Room(['status' => 'open']));

        $this->assertFalse($mission->isAvailable());
    }

    public function test_mission_in_closed_room_is_not_available(): void
    {
        $mission = new Mission(['is_active' => true]);
        $mission->setRelation('room', new Room(['status' => 'closed']));

        $this->assertFalse($mission->isAvailable());
    }
}
