<?php

namespace Tests\Unit;

use App\Services\CarbonCalculator;
use App\Services\CarbonQuestionnaire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CarbonCalculatorTest extends TestCase
{
    public function test_it_calculates_the_lowest_questionnaire_result(): void
    {
        $calculator = new CarbonCalculator(new CarbonQuestionnaire);

        $total = $calculator->calculate([
            'p1' => 'walking', 'p2' => 'under_10', 'p3' => 'under_1_hour',
            'p4' => 'always', 'p5' => 'under_5', 'p6' => '0_to_1_days',
            'p7' => 'always', 'p8' => 'reusable', 'p9' => 'only_needed', 'p10' => 'rarely',
        ]);

        $this->assertSame(169.1, $total);
    }

    public function test_it_calculates_the_highest_questionnaire_result(): void
    {
        $calculator = new CarbonCalculator(new CarbonQuestionnaire);

        $total = $calculator->calculate([
            'p1' => 'private_car', 'p2' => 'over_30', 'p3' => 'over_3_hours',
            'p4' => 'never', 'p5' => 'over_10', 'p6' => 'every_day',
            'p7' => 'never', 'p8' => 'disposable', 'p9' => 'very_often', 'p10' => 'often',
        ]);

        $this->assertSame(2621.4, $total);
    }

    public static function classifications(): array
    {
        return [[599.99, 'low'], [600.0, 'medium'], [1199.99, 'medium'], [1200.0, 'high']];
    }

    #[DataProvider('classifications')]
    public function test_it_classifies_boundary_values(float $total, string $expected): void
    {
        $calculator = new CarbonCalculator(new CarbonQuestionnaire);

        $this->assertSame($expected, $calculator->classification($total)['key']);
    }

    public function test_visual_option_randomization_preserves_every_option_and_factor(): void
    {
        $questionnaire = new CarbonQuestionnaire;
        $canonical = $questionnaire->questions();
        $randomized = $questionnaire->questionsForParticipant(37, 1);

        foreach ($canonical as $key => $question) {
            $this->assertEqualsCanonicalizing($question['options'], $randomized[$key]['options']);
        }
    }

    public function test_visual_option_order_is_stable_for_the_same_student_and_attempt(): void
    {
        $questionnaire = new CarbonQuestionnaire;

        $firstLoad = $questionnaire->questionsForParticipant(37, 2);
        $secondLoad = $questionnaire->questionsForParticipant(37, 2);

        $this->assertSame($firstLoad, $secondLoad);
    }

    public function test_different_students_do_not_always_receive_the_same_visual_order(): void
    {
        $questionnaire = new CarbonQuestionnaire;
        $firstStudent = $questionnaire->questionsForParticipant(37, 1);
        $secondStudent = $questionnaire->questionsForParticipant(38, 1);

        $firstOrders = array_map(fn (array $question) => array_keys($question['options']), $firstStudent);
        $secondOrders = array_map(fn (array $question) => array_keys($question['options']), $secondStudent);

        $this->assertNotSame($firstOrders, $secondOrders);
    }
}
