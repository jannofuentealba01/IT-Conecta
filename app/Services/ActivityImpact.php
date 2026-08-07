<?php

namespace App\Services;

use InvalidArgumentException;

class ActivityImpact
{
    public const POINTS = [
        'low' => 10,
        'medium' => 20,
        'high' => 35,
        'very_high' => 50,
    ];

    public function pointsFor(string $impactLevel): int
    {
        if (! array_key_exists($impactLevel, self::POINTS)) {
            throw new InvalidArgumentException("Nivel de impacto no válido: {$impactLevel}");
        }

        return self::POINTS[$impactLevel];
    }
}
