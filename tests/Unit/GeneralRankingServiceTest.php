<?php

namespace Tests\Unit;

use App\Models\Participant;
use App\Services\GeneralRankingService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeneralRankingServiceTest extends TestCase
{
    #[Test]
    public function it_orders_by_total_points_and_assigns_shared_positions_to_ties(): void
    {
        $ranking = app(GeneralRankingService::class)->rank(collect([
            $this->participant(1, 20, 20, 1),
            $this->participant(2, 50, 35, 2),
            $this->participant(3, 50, 50, 1),
            $this->participant(4, 10, 10, 1),
        ]));

        $this->assertSame([3, 2, 1, 4], $ranking->pluck('id')->all());
        $this->assertSame([1, 1, 3, 4], $ranking->pluck('ranking_position')->all());
    }

    #[Test]
    public function it_uses_completed_activities_to_order_an_exact_points_tie(): void
    {
        $ranking = app(GeneralRankingService::class)->rank(collect([
            $this->participant(1, 30, 20, 1),
            $this->participant(2, 30, 20, 3),
        ]));

        $this->assertSame([2, 1], $ranking->pluck('id')->all());
        $this->assertSame([1, 1], $ranking->pluck('ranking_position')->all());
    }

    private function participant(int $id, int $total, int $action, int $completions): Participant
    {
        $participant = new Participant([
            'name' => "Estudiante {$id}",
            'joined_at' => Carbon::parse('2026-08-04 09:00:00')->addMinutes($id),
        ]);
        $participant->id = $id;
        $participant->total_points = $total;
        $participant->action_points = $action;
        $participant->learning_points = $total - $action;
        $participant->activity_completions_count = $completions;

        return $participant;
    }
}
