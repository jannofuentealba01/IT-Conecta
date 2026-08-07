<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\PointTransaction;
use Illuminate\Support\Collection;

class GeneralRankingService
{
    public function get(): Collection
    {
        $participants = Participant::query()
            ->with('room.course:id,name,school_name')
            ->withSum('pointTransactions as total_points', 'points')
            ->withSum([
                'pointTransactions as action_points' => fn ($query) => $query
                    ->where('category', PointTransaction::CATEGORY_ACTION),
            ], 'points')
            ->withSum([
                'pointTransactions as learning_points' => fn ($query) => $query
                    ->where('category', PointTransaction::CATEGORY_LEARNING),
            ], 'points')
            ->withCount('activityCompletions')
            ->get();

        return $this->rank($participants);
    }

    public function rank(Collection $participants): Collection
    {
        $ordered = $participants
            ->each(function (Participant $participant): void {
                $participant->total_points = (int) ($participant->total_points ?? 0);
                $participant->action_points = (int) ($participant->action_points ?? 0);
                $participant->learning_points = (int) ($participant->learning_points ?? 0);
                $participant->activity_completions_count = (int) ($participant->activity_completions_count ?? 0);
            })
            ->sort(function (Participant $left, Participant $right): int {
                return ($right->total_points <=> $left->total_points)
                    ?: ($right->action_points <=> $left->action_points)
                    ?: ($right->activity_completions_count <=> $left->activity_completions_count)
                    ?: (($left->joined_at?->getTimestamp() ?? PHP_INT_MAX) <=> ($right->joined_at?->getTimestamp() ?? PHP_INT_MAX))
                    ?: ($left->id <=> $right->id);
            })
            ->values();

        $previousPoints = null;
        $position = 0;

        return $ordered->map(function (Participant $participant, int $index) use (&$previousPoints, &$position) {
            if ($previousPoints === null || $participant->total_points !== $previousPoints) {
                $position = $index + 1;
                $previousPoints = $participant->total_points;
            }

            $participant->ranking_position = $position;

            return $participant;
        });
    }
}
