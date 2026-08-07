<?php

namespace Tests\Unit;

use App\Services\ActivityImpact;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ActivityImpactTest extends TestCase
{
    public static function levels(): array
    {
        return [
            'bajo' => ['low', 10],
            'medio' => ['medium', 20],
            'alto' => ['high', 35],
            'muy alto' => ['very_high', 50],
        ];
    }

    #[DataProvider('levels')]
    public function test_it_assigns_points_proportionally_to_impact(string $level, int $points): void
    {
        $this->assertSame($points, (new ActivityImpact)->pointsFor($level));
    }

    public function test_it_rejects_unknown_impact_levels(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ActivityImpact)->pointsFor('unknown');
    }
}
