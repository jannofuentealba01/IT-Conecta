<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $catalog = [
            1 => ['category' => 'Agua', 'instruction' => 'Busca una llave de agua que no se esté utilizando y comprueba que quede completamente cerrada.'],
            2 => ['category' => 'Residuos', 'instruction' => 'Busca una botella plástica y deposítala en el contenedor de reciclaje correspondiente.'],
            3 => ['category' => 'Energía', 'instruction' => 'Apaga las luces innecesarias del lugar indicado, sin afectar la seguridad de otras personas.'],
            4 => ['category' => 'Residuos', 'instruction' => 'Utiliza una botella reutilizable en lugar de una botella desechable.'],
            5 => ['category' => 'Residuos', 'instruction' => 'Separa correctamente los residuos disponibles en sus contenedores correspondientes.'],
            6 => ['category' => 'Transporte', 'instruction' => 'Identifica los beneficios de llegar caminando al liceo y registra esta acción cuando corresponda.'],
            7 => ['category' => 'Transporte', 'instruction' => 'Identifica los beneficios de usar bicicleta y registra esta acción cuando corresponda.'],
            8 => ['category' => 'Biodiversidad', 'instruction' => 'Participa en la plantación o cuidado de un árbol siguiendo las instrucciones del profesor.'],
            9 => ['category' => 'Consumo', 'instruction' => 'Evita una impresión innecesaria o reutiliza una hoja disponible.'],
            10 => ['category' => 'Educación', 'instruction' => 'Propón junto a tu grupo una acción concreta para una campaña ambiental escolar.'],
        ];

        foreach (DB::table('activities')->orderBy('id')->get() as $activity) {
            $level = match (true) {
                $activity->points <= 15 => 'low',
                $activity->points <= 30 => 'medium',
                $activity->points <= 60 => 'high',
                default => 'very_high',
            };

            $points = ['low' => 10, 'medium' => 20, 'high' => 35, 'very_high' => 50][$level];
            $defaults = $catalog[$activity->id] ?? ['category' => 'General', 'instruction' => $activity->description ?: $activity->name];

            DB::table('activities')->where('id', $activity->id)->update([
                'instructions' => $activity->instructions ?: $defaults['instruction'],
                'category' => $activity->category ?: $defaults['category'],
                'impact_level' => $level,
                'points' => $points,
                'annual_co2_reduction' => null,
                'educational_message' => $activity->educational_message ?: 'Cada acción sostenida puede contribuir a reducir tu impacto ambiental.',
            ]);
        }
    }

    public function down(): void
    {
        // La normalización no se revierte para evitar restaurar valores sin metodología.
    }
};
