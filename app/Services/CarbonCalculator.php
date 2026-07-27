<?php

namespace App\Services;

class CarbonCalculator
{
    public function calculate(array $answers)
    {
        // transporte (P1 * P2)
        $transporte = ($answers['p1'] ?? 0) * ($answers['p2'] ?? 0);

        // energía
        $energia = ($answers['p3'] ?? 0) + ($answers['p4'] ?? 0);

        // agua
        $agua = $answers['p5'] ?? 0;

        // alimentación
        $alimentacion = $answers['p6'] ?? 0;

        // residuos
        $residuos = ($answers['p7'] ?? 0) + ($answers['p8'] ?? 0);

        // consumo
        $consumo = ($answers['p9'] ?? 0) + ($answers['p10'] ?? 0);

        // total
        return $transporte + $energia + $agua + $alimentacion + $residuos + $consumo;
    }
}