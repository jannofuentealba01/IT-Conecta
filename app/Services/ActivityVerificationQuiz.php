<?php

namespace App\Services;

use App\Models\Activity;

class ActivityVerificationQuiz
{
    public function questions(Activity $activity): array
    {
        return $this->catalog()[$activity->id] ?? [
            ['id' => 'q1', 'label' => '¿Qué hiciste antes de confirmar esta misión?', 'options' => ['skip' => 'Solo leí el título', 'done' => 'Realicé la acción indicada', 'later' => 'Pensé hacerla después'], 'correct' => 'done'],
            ['id' => 'q2', 'label' => '¿Qué revisaste al terminar?', 'options' => ['instructions' => 'Que la acción cumpliera las instrucciones', 'points' => 'Solamente cuántos puntos entregaba', 'nothing' => 'No revisé nada'], 'correct' => 'instructions'],
        ];
    }

    public function passes(Activity $activity, array $answers): bool
    {
        foreach ($this->questions($activity) as $question) {
            if (($answers[$question['id']] ?? null) !== $question['correct']) {
                return false;
            }
        }

        return true;
    }

    private function catalog(): array
    {
        return [
            1 => [
                ['id' => 'q1', 'label' => '¿Cómo debía quedar la llave?', 'options' => ['open' => 'Un poco abierta', 'closed' => 'Completamente cerrada', 'running' => 'Con agua corriendo'], 'correct' => 'closed'],
                ['id' => 'q2', 'label' => '¿Qué comprobaste antes de terminar?', 'options' => ['drops' => 'Que no salieran gotas', 'color' => 'El color del lavamanos', 'temperature' => 'Que el agua estuviera caliente'], 'correct' => 'drops'],
            ],
            2 => [
                ['id' => 'q1', 'label' => '¿Qué residuo depositaste?', 'options' => ['paper' => 'Una hoja de papel', 'bottle' => 'Una botella plástica', 'food' => 'Restos de comida'], 'correct' => 'bottle'],
                ['id' => 'q2', 'label' => '¿Dónde debía quedar la botella?', 'options' => ['floor' => 'Junto al contenedor', 'trash' => 'En cualquier basurero', 'recycling' => 'Dentro del contenedor de reciclaje correspondiente'], 'correct' => 'recycling'],
            ],
            3 => [
                ['id' => 'q1', 'label' => '¿Qué apagaste en esta misión?', 'options' => ['all' => 'Todas las luces sin revisar', 'unused' => 'Las luces que no eran necesarias', 'devices' => 'Los teléfonos de otros estudiantes'], 'correct' => 'unused'],
                ['id' => 'q2', 'label' => '¿Qué debía mantenerse al realizarla?', 'options' => ['darkness' => 'El lugar completamente oscuro', 'safety' => 'La iluminación necesaria para la seguridad', 'doors' => 'Todas las puertas cerradas'], 'correct' => 'safety'],
            ],
            4 => [
                ['id' => 'q1', 'label' => '¿Qué objeto utilizaste?', 'options' => ['disposable' => 'Una botella desechable nueva', 'reusable' => 'Una botella reutilizable', 'cup' => 'Un vaso desechable'], 'correct' => 'reusable'],
                ['id' => 'q2', 'label' => '¿Qué consumo busca evitar esta acción?', 'options' => ['water' => 'Beber agua', 'pet' => 'Usar una botella PET desechable', 'washing' => 'Lavar la botella'], 'correct' => 'pet'],
            ],
            5 => [
                ['id' => 'q1', 'label' => '¿Qué hiciste antes de depositar cada residuo?', 'options' => ['mix' => 'Los mezclé', 'identify' => 'Identifiqué su material', 'hide' => 'Los oculté'], 'correct' => 'identify'],
                ['id' => 'q2', 'label' => '¿Dónde dejaste los residuos?', 'options' => ['corresponding' => 'En sus contenedores correspondientes', 'same' => 'Todos en el mismo contenedor', 'outside' => 'Fuera de los contenedores'], 'correct' => 'corresponding'],
            ],
            6 => [
                ['id' => 'q1', 'label' => '¿Qué medio de traslado representa esta misión?', 'options' => ['car' => 'Automóvil', 'walking' => 'Caminata', 'motorcycle' => 'Motocicleta'], 'correct' => 'walking'],
                ['id' => 'q2', 'label' => '¿Cuándo reduce emisiones esta acción?', 'options' => ['replace' => 'Cuando reemplaza un viaje motorizado', 'always' => 'Aunque no reemplace ningún viaje', 'indoors' => 'Cuando se camina dentro de la sala'], 'correct' => 'replace'],
            ],
            7 => [
                ['id' => 'q1', 'label' => '¿Qué medio de transporte corresponde a la misión?', 'options' => ['bus' => 'Bus', 'bicycle' => 'Bicicleta', 'car' => 'Automóvil'], 'correct' => 'bicycle'],
                ['id' => 'q2', 'label' => '¿Qué aspecto debes cuidar al usarlo?', 'options' => ['speed' => 'Ir lo más rápido posible', 'safety' => 'Usar elementos de seguridad y respetar las normas', 'phone' => 'Mirar el teléfono durante el trayecto'], 'correct' => 'safety'],
            ],
            8 => [
                ['id' => 'q1', 'label' => '¿Qué necesita el árbol después de plantarlo?', 'options' => ['abandon' => 'Quedar sin cuidados', 'care' => 'Riego y cuidados para sobrevivir', 'remove' => 'Ser retirado al día siguiente'], 'correct' => 'care'],
                ['id' => 'q2', 'label' => '¿Por qué importa que sobreviva y crezca?', 'options' => ['capture' => 'Porque así puede capturar carbono con el tiempo', 'points' => 'Solo porque entrega puntos', 'shade' => 'Porque cambia inmediatamente el clima mundial'], 'correct' => 'capture'],
            ],
            9 => [
                ['id' => 'q1', 'label' => '¿Qué acción realizaste con el papel?', 'options' => ['waste' => 'Usé hojas nuevas sin necesitarlas', 'reduce' => 'Evité una impresión o reutilicé una hoja', 'discard' => 'Boté una hoja limpia'], 'correct' => 'reduce'],
                ['id' => 'q2', 'label' => '¿Cómo puedes aprovechar mejor una hoja?', 'options' => ['one_side' => 'Usando solo una pequeña parte', 'both_sides' => 'Utilizando ambos lados cuando sea posible', 'tear' => 'Rompiéndola antes de usarla'], 'correct' => 'both_sides'],
            ],
            10 => [
                ['id' => 'q1', 'label' => '¿Qué debe proponer una campaña ambiental?', 'options' => ['concrete' => 'Una acción concreta que otras personas puedan realizar', 'points' => 'Solamente una forma de ganar puntos', 'unrelated' => 'Una actividad sin relación ambiental'], 'correct' => 'concrete'],
                ['id' => 'q2', 'label' => '¿Cómo se demuestra su impacto?', 'options' => ['poster' => 'Solo contando cuántos afiches tiene', 'actions' => 'Registrando las acciones concretas que consigue generar', 'automatic' => 'Asignándole CO₂ automáticamente'], 'correct' => 'actions'],
            ],
        ];
    }
}
