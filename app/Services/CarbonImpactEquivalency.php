<?php

namespace App\Services;

class CarbonImpactEquivalency
{
    private const CAR_KG_PER_KM = 0.1822;
    private const ELECTRICITY_KG_PER_KWH = 0.2466;
    private const HOUSEHOLD_KWH_MONTH = 180.0;
    private const TREE_KG_PER_YEAR = 60.0;
    private const GASOLINE_KG_PER_LITRE = 2.3477;
    private const DIESEL_KG_PER_LITRE = 2.6893;
    private const PROPANE_KG_PER_15KG_CYLINDER = 45.47;
    private const COAL_KG_CO2_PER_KG = 1.984;

    public function for(float $footprint, int $participantId, int $footprintId): array
    {
        $classification = $this->classification($footprint);
        $band = $this->band($footprint);
        $facts = $this->facts($footprint);

        // La elección parece aleatoria entre estudiantes, pero permanece estable al recargar.
        $index = (int) (sprintf('%u', crc32("$participantId|$footprintId|$band")) % count($facts[$band]));

        return [
            'classification' => $classification,
            'band' => $band,
            'fact' => $facts[$band][$index],
        ];
    }

    public function band(float $footprint): string
    {
        return match (true) {
            $footprint <= 384.54 => 'B1',
            $footprint < 600 => 'B2',
            $footprint < 900 => 'M1',
            $footprint < 1200 => 'M2',
            $footprint <= 1910.69 => 'A1',
            default => 'A2',
        };
    }

    private function classification(float $footprint): string
    {
        return match (true) {
            $footprint < 600 => 'low',
            $footprint < 1200 => 'medium',
            default => 'high',
        };
    }

    private function facts(float $footprint): array
    {
        $km = $this->integer($footprint / self::CAR_KG_PER_KM);
        $kwh = $this->integer($footprint / self::ELECTRICITY_KG_PER_KWH);
        $householdMonthsValue = ($footprint / self::ELECTRICITY_KG_PER_KWH) / self::HOUSEHOLD_KWH_MONTH;
        $households = $this->integer($householdMonthsValue);
        $householdDuration = $this->householdDuration($householdMonthsValue);
        $trees = $this->integer($footprint / self::TREE_KG_PER_YEAR);
        $gasolineValue = $footprint / self::GASOLINE_KG_PER_LITRE;
        $gasoline = $this->integer($gasolineValue);
        $gasolineTanks = $this->decimal($gasolineValue / 50);
        $dieselValue = $footprint / self::DIESEL_KG_PER_LITRE;
        $diesel = $this->integer($dieselValue);
        $dieselTanks = $this->decimal($dieselValue / 50);
        $propane = $this->decimal($footprint / self::PROPANE_KG_PER_15KG_CYLINDER);
        $coalValue = $footprint / self::COAL_KG_CO2_PER_KG;
        $coal = $this->integer($coalValue);
        $coalSacks = $this->decimal($coalValue / 25);
        $schoolTrips = $this->integer(($footprint / self::CAR_KG_PER_KM) / 10);

        $car = $this->fact(
            "Tu huella equivale a recorrer aproximadamente $km km en automóvil a gasolina. Para imaginarlo: serían cerca de $schoolTrips viajes de ida y vuelta al liceo si cada recorrido completo fuera de 10 km.",
            'Programa HuellaChile: factor para un vehículo liviano a gasolina. El trayecto escolar de 10 km es un supuesto educativo indicado en la comparación.',
            'https://huellachile.mma.gob.cl/wp-content/uploads/2026/04/PPT-HuellaChile-Webinar-Eventos-14042026.pdf'
        );
        $electricity = $this->fact(
            "Tu huella equivale a las emisiones de aproximadamente $kwh kWh de electricidad: lo que una familia chilena promedio consume durante $householdDuration.",
            'Programa HuellaChile: factor de emisión 2025 del SEN. SEC: consumo residencial promedio de 180 kWh al mes.',
            [
                ['label' => 'Factor de emisión 2025 (HuellaChile)', 'url' => 'https://huellachile.mma.gob.cl/wp-content/uploads/2026/04/PPT-HuellaChile-Webinar-Eventos-14042026.pdf'],
                ['label' => 'Consumo residencial promedio (SEC)', 'url' => 'https://www.sec.cl/limite-de-invierno/?view_full_site=true'],
            ]
        );
        $homes = $this->fact(
            "En términos cotidianos, las emisiones de tu huella equivalen a generar la electricidad que aproximadamente $households hogares chilenos consumen en un mes.",
            'Programa HuellaChile (factor de emisión 2025 del SEN) y SEC (consumo residencial promedio de 180 kWh al mes).',
            [
                ['label' => 'Factor de emisión 2025 (HuellaChile)', 'url' => 'https://huellachile.mma.gob.cl/wp-content/uploads/2026/04/PPT-HuellaChile-Webinar-Eventos-14042026.pdf'],
                ['label' => 'Consumo residencial promedio (SEC)', 'url' => 'https://www.sec.cl/limite-de-invierno/?view_full_site=true'],
            ]
        );
        $homeYears = $this->fact(
            "Tu huella equivale a las emisiones asociadas a mantener un hogar chileno promedio con electricidad durante $householdDuration.",
            'Programa HuellaChile (factor de emisión 2025 del SEN) y SEC (consumo residencial promedio).',
            [
                ['label' => 'Factor de emisión 2025 (HuellaChile)', 'url' => 'https://huellachile.mma.gob.cl/wp-content/uploads/2026/04/PPT-HuellaChile-Webinar-Eventos-14042026.pdf'],
                ['label' => 'Consumo residencial promedio (SEC)', 'url' => 'https://www.sec.cl/limite-de-invierno/?view_full_site=true'],
            ]
        );
        $tree = $this->fact(
            "Para retirar de la atmósfera una cantidad similar a tu huella, se necesitarían aproximadamente $trees árboles urbanos creciendo y capturando carbono durante todo un año.",
            'EPA: captura media estimada de CO₂ de árboles urbanos bajo supuestos de crecimiento y supervivencia.',
            'https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos'
        );
        $gas = $this->fact(
            "Tu huella equivale al CO₂ de quemar aproximadamente $gasoline litros de gasolina: cerca de $gasolineTanks estanques completos de automóvil de 50 litros.",
            'EPA: emisiones de CO₂ por galón de gasolina. El estanque de 50 litros es un supuesto educativo indicado en la comparación.',
            'https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos'
        );
        $dieselFact = $this->fact(
            "Tu huella equivale al CO₂ de quemar aproximadamente $diesel litros de diésel: cerca de $dieselTanks estanques de vehículo de 50 litros.",
            'EPA: emisiones de CO₂ por galón de diésel. El estanque de 50 litros es un supuesto educativo indicado en la comparación.',
            'https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos'
        );
        $propaneFact = $this->fact(
            "Tu huella representa aproximadamente el CO₂ liberado al consumir $propane balones domésticos de gas de 15 kg.",
            'EPA: contenido de carbono y emisiones del propano; equivalencia ajustada proporcionalmente a balones domésticos de 15 kg.',
            'https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos'
        );
        $coalFact = $this->fact(
            "Tu huella equivale a quemar aproximadamente $coal kg de carbón; imagina cerca de $coalSacks sacos de 25 kg cada uno.",
            'EPA: emisiones aproximadas por libra de carbón. Los sacos de 25 kg son una representación educativa del peso calculado.',
            'https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos'
        );

        return [
            'B1' => [$car, $electricity, $tree, $gas],
            'B2' => [$homes, $tree, $gas, $dieselFact],
            'M1' => [$car, $homes, $tree, $propaneFact],
            'M2' => [$electricity, $homeYears, $tree, $coalFact],
            'A1' => [$car, $homes, $tree, $gas, $dieselFact],
            'A2' => [$car, $electricity, $homes, $homeYears, $tree, $gas, $coalFact],
        ];
    }

    private function fact(string $text, string $source, string|array $url): array
    {
        $references = is_array($url)
            ? $url
            : [['label' => 'Consultar referencia', 'url' => $url]];

        return compact('text', 'source', 'references');
    }

    private function integer(float $value): string
    {
        return number_format(round($value), 0, ',', '.');
    }

    private function decimal(float $value): string
    {
        return number_format($value, 1, ',', '.');
    }

    private function householdDuration(float $months): string
    {
        if ($months < 12) {
            return $this->decimal($months).' meses';
        }

        return $this->decimal($months / 12).' años';
    }
}
