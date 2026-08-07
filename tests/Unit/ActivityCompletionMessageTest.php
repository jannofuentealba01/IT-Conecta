<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\ActivityCompletion;
use App\Services\ActivityCompletionMessage;
use PHPUnit\Framework\TestCase;

class ActivityCompletionMessageTest extends TestCase
{
    public function test_it_appends_the_percentage_of_the_students_initial_footprint(): void
    {
        $activity = new Activity(['educational_message' => 'Mensaje ambiental.']);
        $completion = new ActivityCompletion(['annual_co2_reduction_awarded' => 12.79]);
        $completion->setRelation('activity', $activity);

        $message = (new ActivityCompletionMessage)->build($completion, 2500);

        $this->assertStringContainsString('Mensaje ambiental.', $message);
        $this->assertStringContainsString('0,51%', $message);
    }

    public function test_learning_activity_does_not_claim_a_carbon_percentage(): void
    {
        $activity = new Activity(['educational_message' => 'Actividad de aprendizaje.']);
        $completion = new ActivityCompletion(['annual_co2_reduction_awarded' => null]);
        $completion->setRelation('activity', $activity);

        $message = (new ActivityCompletionMessage)->build($completion, 2500);

        $this->assertSame('Actividad de aprendizaje.', $message);
    }
}
