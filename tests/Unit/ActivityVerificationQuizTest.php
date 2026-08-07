<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\ActivityVerificationQuiz;
use PHPUnit\Framework\TestCase;

class ActivityVerificationQuizTest extends TestCase
{
    public function test_every_catalog_activity_has_exactly_two_questions(): void
    {
        $quiz = new ActivityVerificationQuiz;

        foreach (range(1, 10) as $activityId) {
            $activity = new Activity;
            $activity->id = $activityId;
            $questions = $quiz->questions($activity);
            $this->assertCount(2, $questions);
            $this->assertSame(['q1', 'q2'], array_column($questions, 'id'));
        }
    }

    public function test_correct_answers_pass_and_an_incorrect_answer_fails(): void
    {
        $quiz = new ActivityVerificationQuiz;
        $activity = new Activity;
        $activity->id = 2;

        $this->assertTrue($quiz->passes($activity, ['q1' => 'bottle', 'q2' => 'recycling']));
        $this->assertFalse($quiz->passes($activity, ['q1' => 'paper', 'q2' => 'recycling']));
        $this->assertFalse($quiz->passes($activity, ['q1' => 'bottle']));
    }
}
