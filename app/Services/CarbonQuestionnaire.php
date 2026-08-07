<?php

namespace App\Services;

use Illuminate\Validation\Rule;

class CarbonQuestionnaire
{
    public const VERSION = '1.0-cl';

    /**
     * Devuelve las mismas preguntas y factores, pero con las alternativas en
     * un orden visual estable para cada estudiante e intento.
     */
    public function questionsForParticipant(int $participantId, int $attemptNumber): array
    {
        $questions = $this->questions();

        foreach ($questions as $questionKey => &$question) {
            $options = $question['options'];

            uksort($options, function (string $left, string $right) use ($participantId, $attemptNumber, $questionKey): int {
                $leftOrder = hash('sha256', "$participantId|$attemptNumber|$questionKey|$left");
                $rightOrder = hash('sha256', "$participantId|$attemptNumber|$questionKey|$right");

                return $leftOrder <=> $rightOrder;
            });

            $question['options'] = $options;
        }
        unset($question);

        return $questions;
    }

    public function questions(): array
    {
        return [
            'p1' => ['label' => '¿Cuál es el medio de transporte principal al liceo?', 'options' => [
                'walking' => ['label' => 'Caminando', 'factor' => 0.0],
                'bicycle' => ['label' => 'Bicicleta', 'factor' => 0.0],
                'public_transport' => ['label' => 'Transporte público', 'factor' => 72.5],
                'private_car' => ['label' => 'Auto particular', 'factor' => 390.5],
            ]],
            'p2' => ['label' => '¿Cuánto tiempo tardas en llegar?', 'options' => [
                'under_10' => ['label' => 'Menos de 10 minutos', 'factor' => 0.5],
                '10_to_30' => ['label' => '10-30 minutos', 'factor' => 1.0],
                'over_30' => ['label' => 'Más de 30 minutos', 'factor' => 2.0],
            ]],
            'p3' => ['label' => '¿Cuántas horas usas computador, celular o consola diariamente?', 'options' => [
                'under_1_hour' => ['label' => 'Menos de 1 hora', 'factor' => 13.5],
                '1_to_3_hours' => ['label' => '1-3 horas', 'factor' => 40.5],
                'over_3_hours' => ['label' => 'Más de 3 horas', 'factor' => 94.6],
            ]],
            'p4' => ['label' => 'Cuando sales de una habitación, ¿apagas las luces?', 'options' => [
                'always' => ['label' => 'Siempre', 'factor' => 0.0],
                'sometimes' => ['label' => 'A veces', 'factor' => 18.0],
                'never' => ['label' => 'Nunca', 'factor' => 43.8],
            ]],
            'p5' => ['label' => '¿Cuánto dura normalmente tu ducha?', 'options' => [
                'under_5' => ['label' => 'Menos de 5 minutos', 'factor' => 2.6],
                '5_to_10' => ['label' => '5-10 minutos', 'factor' => 5.2],
                'over_10' => ['label' => 'Más de 10 minutos', 'factor' => 10.5],
            ]],
            'p6' => ['label' => '¿Cuántos días a la semana consumes carne?', 'options' => [
                '0_to_1_days' => ['label' => '0-1 días', 'factor' => 110.0],
                '2_to_4_days' => ['label' => '2-4 días', 'factor' => 420.0],
                'every_day' => ['label' => 'Todos los días', 'factor' => 890.0],
            ]],
            'p7' => ['label' => '¿Reciclas en tu hogar?', 'options' => [
                'always' => ['label' => 'Siempre', 'factor' => 15.4],
                'sometimes' => ['label' => 'A veces', 'factor' => 160.8],
                'never' => ['label' => 'Nunca', 'factor' => 358.0],
            ]],
            'p8' => ['label' => '¿Qué tipo de botella utilizas normalmente?', 'options' => [
                'reusable' => ['label' => 'Botella reutilizable', 'factor' => 0.5],
                'both' => ['label' => 'Ambas', 'factor' => 24.0],
                'disposable' => ['label' => 'Botella desechable', 'factor' => 58.0],
            ]],
            'p9' => ['label' => '¿Con qué frecuencia compras ropa?', 'options' => [
                'only_needed' => ['label' => 'Solo cuando necesito', 'factor' => 25.0],
                'several_year' => ['label' => 'Varias veces al año', 'factor' => 115.0],
                'very_often' => ['label' => 'Muy frecuentemente', 'factor' => 340.0],
            ]],
            'p10' => ['label' => '¿Con qué frecuencia usas papel en tus actividades diarias?', 'options' => [
                'rarely' => ['label' => 'Casi nunca', 'factor' => 2.1],
                'sometimes' => ['label' => 'A veces', 'factor' => 18.5],
                'often' => ['label' => 'Frecuentemente', 'factor' => 45.5],
            ]],
        ];
    }

    public function validationRules(): array
    {
        $rules = [];

        foreach ($this->questions() as $key => $question) {
            $rules[$key] = ['required', 'string', Rule::in(array_keys($question['options']))];
        }

        return $rules;
    }

    public function factor(string $question, string $answer): float
    {
        return (float) $this->questions()[$question]['options'][$answer]['factor'];
    }
}
