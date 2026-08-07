<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activities = [
            1 => [0.82, 'Si cierras la llave durante dos minutos cuando el agua no se está usando cada día de clases, ahorrarás aproximadamente 2.160 litros de agua al año y evitarás cerca de 0,82 kg de CO₂e, equivalente a recorrer 5 km en automóvil.'],
            2 => [1.77, 'Si reciclas correctamente una botella plástica cada día de clases, evitarás aproximadamente 1,77 kg de CO₂e al año, equivalente a cerca de 11 km recorridos en automóvil.'],
            3 => [12.79, 'Si apagas durante una hora las luces que no se están usando cada día de clases, evitarás aproximadamente 12,79 kg de CO₂e al año, equivalente a cerca de 79 km de viaje en automóvil.'],
            4 => [13.91, 'Si usas una botella reutilizable y evitas una botella PET cada día de clases, podrías evitar aproximadamente 13,91 kg de CO₂e al año, equivalente a cerca de 85 km en automóvil.'],
            5 => [22.17, 'Si separas 250 gramos de residuos reciclables cada día de clases, evitarás aproximadamente 22,17 kg de CO₂e al año, equivalente a cerca de 136 km de viaje en automóvil.'],
            6 => [175.74, 'Si caminas 3 km de ida y 3 km de regreso en lugar de viajar en automóvil durante el año escolar, evitarás aproximadamente 175,74 kg de CO₂e, equivalente a 1.080 km recorridos en automóvil.'],
            7 => [175.74, 'Si realizas en bicicleta un trayecto escolar diario de 6 km que antes hacías en automóvil, evitarás aproximadamente 175,74 kg de CO₂e al año, equivalente a 1.080 km recorridos en automóvil.'],
            8 => [60.00, 'Si plantas un árbol y lo cuidas para que crezca, podría capturar en promedio cerca de 60 kg de CO₂ al año durante sus primeros diez años. Esta proyección depende de la especie, el crecimiento y la supervivencia del árbol.'],
            9 => [2.42, 'Si utilizas dos hojas menos cada día de clases, evitarás aproximadamente 2,42 kg de CO₂e durante el año, equivalente a cerca de 15 km recorridos en automóvil.'],
            10 => [null, 'Tu campaña ayuda a que otras personas actúen. Esta misión entrega puntos de aprendizaje; la reducción de carbono se calculará mediante las acciones concretas que sus participantes realicen.'],
        ];

        foreach ($activities as $id => [$reduction, $message]) {
            DB::table('activities')->where('id', $id)->update([
                'annual_co2_reduction' => $reduction,
                'educational_message' => $message,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('activities')->whereBetween('id', [1, 10])->update([
            'annual_co2_reduction' => null,
            'educational_message' => 'Cada acción sostenida puede contribuir a reducir tu impacto ambiental.',
        ]);
    }
};
