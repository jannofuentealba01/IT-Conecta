<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\EcoActivityProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcoHuntActivitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalog() as $item) {
            $activity = Activity::updateOrCreate(
                ['user_id' => null, 'name' => $item['name']],
                [
                    'description' => $item['message'], 'instructions' => $item['instruction'],
                    'category' => $item['category'], 'impact_level' => $this->level($item['points']),
                    'points' => $item['points'], 'co2_impact' => 0, 'annual_co2_reduction' => null,
                    'educational_message' => $item['message'], 'validation_type' => $item['type'] === 'immediate' ? 'quiz' : 'declaration',
                    'frequency_days' => 1, 'is_active' => true,
                ]
            );

            $profile = EcoActivityProfile::firstOrNew(['activity_id' => $activity->id]);
            $profile->fill([
                'slug' => $item['slug'], 'activity_type' => $item['type'], 'icon' => $item['icon'],
                'location_suggestion' => $item['location'], 'verification_questions' => $this->questions($item),
                'standard_unit' => $item['unit'], 'standard_quantity' => $item['quantity'],
                'baseline_scenario' => $item['baseline'], 'action_scenario' => $item['action'],
                'emission_factor' => $item['factor'], 'emission_factor_unit' => $item['factor_unit'],
                'factor_source' => $item['source'], 'factor_version' => $item['version'],
                'avoided_co2e_standard' => $item['avoided'], 'impact_confidence' => $item['confidence'],
                'game_points' => $item['points'], 'is_active' => true,
            ]);
            $profile->qr_token ??= Str::random(48);
            $profile->save();
        }
    }

    private function questions(array $item): array
    {
        if ($item['type'] === 'declared') {
            return [
                ['id' => 'q1', 'label' => '¿Realizaste esta acción hoy?', 'options' => ['yes' => 'Sí, la realicé hoy', 'no' => 'No la realicé'], 'correct' => 'yes'],
                ['id' => 'q2', 'label' => '¿Tu respuesta representa lo que realmente hiciste?', 'options' => ['honest' => 'Sí, respondí con honestidad', 'points' => 'No, solo quiero los puntos'], 'correct' => 'honest'],
            ];
        }

        return [
            ['id' => 'q1', 'label' => '¿Qué hiciste al encontrar esta misión?', 'options' => ['done' => $item['check'], 'read' => 'Solo leí la instrucción', 'later' => 'Decidí hacerlo después'], 'correct' => 'done'],
            ['id' => 'q2', 'label' => '¿La realizaste respetando la instrucción y la seguridad?', 'options' => ['safe' => 'Sí, de forma segura', 'unsafe' => 'No revisé la seguridad'], 'correct' => 'safe'],
        ];
    }

    private function level(int $points): string
    {
        return match (true) { $points <= 15 => 'low', $points <= 25 => 'medium', $points <= 40 => 'high', default => 'very_high' };
    }

    private function catalog(): array
    {
        $hc = 'Programa HuellaChile — Factores de emisión 2025, Organizaciones y Eventos, versión resumen';
        $rows = [
            ['cerrar-llave','Cerrar la llave del agua','immediate','💧','Agua',20,'Observa una llave sin uso y ciérrala completamente sin interrumpir a otra persona.','Cerré completamente una llave que no estaba siendo utilizada.','Lavamanos o baños','litro de agua evitado',2,'Llave desperdiciando agua','Llave cerrada','El agua potable requiere captación, tratamiento y distribución.',0.36218,'kgCO2e/m³',$hc,'2025',0.00072436,'A'],
            ['reciclar-pet','Reciclar una botella plástica','immediate','♻️','Residuos',25,'Deposita una botella PET limpia en el contenedor correspondiente.','Deposité la botella dentro del contenedor PET.','Punto limpio', 'botella PET',0.02,'PET enviado a disposición final','PET enviado a reciclaje','Separar correctamente permite recuperar materiales.',3.86390,'kgCO2e/kg material',$hc,'2025',null,'B'],
            ['apagar-luces','Apagar luces innecesarias','immediate','💡','Energía',25,'Apaga solo luces innecesarias, manteniendo una iluminación segura.','Apagué únicamente iluminación que no se necesitaba.','Sector de iluminación','hora de 40 W evitada',1,'Iluminación encendida sin necesidad','Iluminación apagada', 'Ahorrar electricidad reduce emisiones asociadas a la red.',0.246725,'kgCO2e/kWh',$hc,'2025',0.009869,'A'],
            ['separar-residuos','Separar residuos correctamente','immediate','🗂️','Residuos',40,'Identifica los materiales y déjalos en sus contenedores correctos.','Separé los residuos según su material.','Estación de reciclaje','kg de residuo separado',0.25,'Residuo mezclado en disposición municipal','Residuo separado para valorización','Separar es el primer paso para recuperar residuos.',0.497242,'kgCO2e/kg disposición',$hc,'2025',null,'B'],
            ['desconectar-equipos','Desconectar equipos sin uso','immediate','🔌','Energía',20,'Desconecta de forma segura un cargador o equipo propio que no esté en uso.','Desconecté un equipo propio y sin uso.','Zona de carga segura','hora de equipo en espera evitada',1,'Equipo conectado sin uso','Equipo desconectado','Evitar consumos innecesarios mejora la eficiencia energética.',0.246725,'kgCO2e/kWh',$hc,'2025',null,'A'],
            ['luz-natural','Aprovechar la luz natural','immediate','☀️','Energía',25,'Si hay luz natural suficiente, apaga una luz artificial innecesaria.','Aproveché la luz natural sin dejar el lugar oscuro.','Sala con ventanas','hora de 40 W evitada',1,'Luz artificial encendida','Uso de luz natural','La luz natural puede reducir el consumo eléctrico.',0.246725,'kgCO2e/kWh',$hc,'2025',0.009869,'A'],
            ['doble-cara','Utilizar ambas caras de una hoja','immediate','📄','Papel',25,'Reutiliza una hoja disponible escribiendo en su cara libre.','Utilicé la cara libre de una hoja.','Biblioteca o sala','hoja A4 evitada',1,'Uso de una hoja nueva','Reutilización de una cara libre','Aprovechar cada hoja reduce la demanda de papel.',1.34508,'kgCO2e/kg papel',$hc,'2025',null,'A'],
            ['residuo-electronico','Gestionar un residuo electrónico','immediate','🔋','Residuos',40,'Identifica un residuo electrónico autorizado y deposítalo solo en el punto indicado.','Usé el punto autorizado para residuos electrónicos.','Punto e-waste supervisado','residuo electrónico gestionado',1,'Disposición incorrecta','Entrega en punto autorizado','Los residuos electrónicos requieren gestión especializada.',null,null,'Sin factor específico cerrado; puntaje pedagógico provisional','educativo',null,'C'],
            ['recoger-reciclable','Recoger un residuo reciclable','immediate','🧤','Residuos',15,'Recoge de forma segura un residuo limpio y déjalo en su contenedor.','Recogí un residuo seguro y lo deposité correctamente.','Patio o pasillo seguro','residuo dispuesto correctamente',1,'Residuo fuera del contenedor','Residuo en contenedor correcto','Mantener los espacios limpios evita que materiales recuperables se pierdan.',null,null,'Puntaje pedagógico sin reducción atribuible','educativo',null,'C'],
            ['riego-eficiente','Regar con el agua necesaria','immediate','🌿','Agua',10,'Riega únicamente si está autorizado y usa solo el agua necesaria.','Regué sin dejar agua corriendo innecesariamente.','Jardín supervisado','litro de agua evitado',2,'Riego con exceso de agua','Riego eficiente','Usar solo el agua necesaria protege un recurso limitado.',0.36218,'kgCO2e/m³',$hc,'2025',0.00072436,'A'],
            ['botella-reutilizable','Usar botella reutilizable','declared','🧴','Consumo',45,'Declara esta misión solo si hoy trajiste y utilizaste una botella reutilizable.','Usé mi botella reutilizable hoy.','Comedor o patio','botella PET evitada',1,'Uso de botella PET desechable','Uso de botella reutilizable','Reutilizar evita fabricar y desechar envases de un solo uso.',3.86390,'kgCO2e/kg PET',$hc,'2025',null,'B'],
            ['caminar','Llegar caminando al liceo','declared','🚶','Transporte',50,'Declara Sí solo si hoy caminaste y reemplazaste un viaje motorizado al liceo.','Llegué caminando hoy.','Acceso principal','km de automóvil sustituido',1,'Viaje en vehículo a gasolina','Caminata','Caminar no genera emisiones directas de transporte.',0.16272,'kgCO2e/km vehículo gasolina',$hc,'2025',0.16272,'A'],
            ['bicicleta','Usar bicicleta como transporte','declared','🚲','Transporte',50,'Declara Sí solo si hoy llegaste en bicicleta reemplazando un viaje motorizado.','Llegué en bicicleta hoy.','Bicicletero','km de automóvil sustituido',1,'Viaje en vehículo a gasolina','Viaje en bicicleta','La bicicleta evita emisiones directas de un viaje motorizado.',0.16272,'kgCO2e/km vehículo gasolina',$hc,'2025',0.16272,'A'],
            ['reducir-papel','Reducir el uso de papel','declared','📝','Papel',35,'Declara Sí solo si hoy evitaste una impresión o reutilizaste papel.','Reduje mi uso de papel hoy.','Biblioteca o impresora','hoja A4 evitada',2,'Uso de hojas nuevas','Impresión evitada o papel reutilizado','Reducir papel disminuye la demanda de material nuevo.',1.34508,'kgCO2e/kg papel',$hc,'2025',null,'A'],
            ['compartir-transporte','Compartir transporte','declared','🚗','Transporte',50,'Declara Sí solo si hoy compartiste un vehículo al liceo con otra persona.','Compartí transporte hoy.','Acceso o estacionamiento','viaje compartido',1,'Viaje individual','Viaje compartido','Compartir un trayecto reparte su impacto entre más personas.',0.16272,'kgCO2e/km vehículo gasolina',$hc,'2025',null,'B'],
            ['ducha-corta','Reducir el tiempo de ducha','declared','🚿','Agua',45,'Declara Sí solo si hoy redujiste conscientemente el tiempo de tu ducha.','Tomé una ducha más corta hoy.','Sector informativo, no dentro de baños','minuto de ducha evitado',1,'Ducha habitual','Ducha un minuto más corta','Una ducha más corta ahorra agua y, según el sistema, energía para calentarla.',0.36218,'kgCO2e/m³ agua',$hc,'2025',null,'B'],
            ['evitar-desperdicio','Evitar desperdiciar alimentos','declared','🍎','Alimentos',45,'Declara Sí solo si hoy serviste o consumiste tus alimentos sin desperdiciarlos.','Evité desperdiciar alimentos hoy.','Comedor','porción de alimento no desperdiciada',1,'Alimento desechado','Alimento consumido o conservado','Evitar desperdicios aprovecha los recursos usados para producir alimentos.',null,null,'Sin factor único: depende del alimento','educativo provisional',null,'B'],
            ['reutilizar-materiales','Reutilizar materiales escolares','declared','🎒','Consumo',40,'Declara Sí solo si hoy reutilizaste un material escolar en lugar de desecharlo.','Reutilicé un material escolar hoy.','Sala o biblioteca','material escolar reutilizado',1,'Material desechado y reemplazado','Material reutilizado','Alargar la vida útil evita consumo y residuos.',null,null,'Sin factor único: depende del material','educativo provisional',null,'B'],
            ['evitar-plastico','Evitar plástico de un solo uso','declared','🥄','Consumo',35,'Declara Sí solo si hoy rechazaste un producto plástico desechable.','Evité un plástico de un solo uso hoy.','Comedor o kiosco','plástico desechable evitado',1,'Uso de plástico desechable','Alternativa reutilizable o sin plástico','Evitar desechables reduce la demanda de plástico nuevo.',3.35428,'kgCO2e/kg plástico rígido',$hc,'2025',null,'B'],
            ['menos-envases','Preferir productos con menos envases','declared','📦','Consumo',35,'Declara Sí solo si hoy elegiste un producto con menos embalaje.','Elegí un producto con menos envases hoy.','Kiosco o comedor','envase evitado',1,'Producto con más embalaje','Producto con menos embalaje','Elegir menos envases previene residuos desde el origen.',null,null,'Factores HuellaChile varían según material','2025',null,'B'],
        ];

        return array_map(fn ($r) => array_combine(['slug','name','type','icon','category','points','instruction','check','location','unit','quantity','baseline','action','message','factor','factor_unit','source','version','avoided','confidence'], $r), $rows);
    }
}
