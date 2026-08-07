<?php

namespace Tests\Unit;

use App\Models\Room;
use Tests\TestCase;

class RoomStateTest extends TestCase
{
    public function test_open_room_without_expiration_accepts_students(): void
    {
        $room = new Room(['status' => 'open']);

        $this->assertTrue($room->isOpen());
    }

    public function test_open_room_with_future_expiration_accepts_students(): void
    {
        $room = new Room(['status' => 'open', 'expires_at' => now()->addHour()]);

        $this->assertTrue($room->isOpen());
    }

    public function test_expired_room_rejects_students(): void
    {
        $room = new Room(['status' => 'open', 'expires_at' => now()->subMinute()]);

        $this->assertFalse($room->isOpen());
    }

    public function test_closed_room_rejects_students(): void
    {
        $room = new Room(['status' => 'closed']);

        $this->assertFalse($room->isOpen());
    }
}
