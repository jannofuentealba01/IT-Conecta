<?php

namespace App\Services;

use App\Models\ActivityCompletion;

class ActivityCompletionMessage
{
    public function build(ActivityCompletion $completion, ?float $initialFootprint): string
    {
        $message = (string) ($completion->activity?->educational_message
            ?: 'Completaste una acción ambiental.');
        $reduction = $completion->annual_co2_reduction_awarded;

        if ($reduction === null || (float) $reduction <= 0 || ! $initialFootprint || $initialFootprint <= 0) {
            return $message;
        }

        $percentage = ((float) $reduction / $initialFootprint) * 100;

        return $message.' Esto representa aproximadamente '
            .number_format($percentage, 2, ',', '.')
            .'% de tu huella inicial estimada.';
    }
}
