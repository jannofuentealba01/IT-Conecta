<?php

namespace App\Services;

class CarbonCalculator
{
    public function __construct(private readonly CarbonQuestionnaire $questionnaire) {}

    public function calculate(array $answers): float
    {
        $transport = $this->questionnaire->factor('p1', $answers['p1'])
            * $this->questionnaire->factor('p2', $answers['p2']);

        $otherSources = 0.0;
        foreach (range(3, 10) as $number) {
            $key = 'p'.$number;
            $otherSources += $this->questionnaire->factor($key, $answers[$key]);
        }

        return round($transport + $otherSources, 2);
    }

    public function classification(float $total): array
    {
        return match (true) {
            $total < 600 => ['key' => 'low', 'icon' => '🌱', 'message' => 'Muy bien, tienes hábitos sustentables.'],
            $total < 1200 => ['key' => 'medium', 'icon' => '⚖️', 'message' => 'Vas bien, pero todavía puedes mejorar.'],
            default => ['key' => 'high', 'icon' => '🔥', 'message' => 'Tu impacto estimado es alto; trabajaremos para reducirlo.'],
        };
    }
}
