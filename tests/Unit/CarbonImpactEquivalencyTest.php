<?php

namespace Tests\Unit;

use App\Services\CarbonImpactEquivalency;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CarbonImpactEquivalencyTest extends TestCase
{
    #[DataProvider('bands')]
    public function test_it_assigns_the_six_internal_bands(float $footprint, string $expected): void
    {
        $this->assertSame($expected, (new CarbonImpactEquivalency)->band($footprint));
    }

    public static function bands(): array
    {
        return [
            [169.10, 'B1'], [384.54, 'B1'], [384.55, 'B2'], [599.99, 'B2'],
            [600, 'M1'], [899.99, 'M1'], [900, 'M2'], [1199.99, 'M2'],
            [1200, 'A1'], [1910.69, 'A1'], [1910.70, 'A2'], [2621.40, 'A2'],
        ];
    }

    public function test_the_fact_is_stable_for_the_same_student_and_footprint(): void
    {
        $service = new CarbonImpactEquivalency;

        $this->assertSame($service->for(2621.4, 40, 8), $service->for(2621.4, 40, 8));
    }

    public function test_every_fact_has_its_own_source(): void
    {
        $fact = (new CarbonImpactEquivalency)->for(850, 12, 3)['fact'];

        $this->assertNotEmpty($fact['text']);
        $this->assertNotEmpty($fact['source']);
        $this->assertNotEmpty($fact['references']);
        $this->assertStringStartsWith('https://', $fact['references'][0]['url']);
    }

    public function test_every_possible_fact_adds_a_real_world_comparison(): void
    {
        $service = new CarbonImpactEquivalency;
        $facts = [];

        foreach ([290.5, 500, 750, 1050, 1500, 2300] as $footprint) {
            foreach (range(1, 250) as $participantId) {
                $fact = $service->for($footprint, $participantId, 1)['fact']['text'];
                $facts[$fact] = $fact;
            }
        }

        $this->assertCount(28, $facts);

        foreach ($facts as $fact) {
            $this->assertMatchesRegularExpression(
                '/liceo|familia|hogar|árboles|estanques|balones|sacos/',
                $fact
            );
        }
    }
}
