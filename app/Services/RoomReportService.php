<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\Room;

class RoomReportService
{
    public function __construct(private readonly CarbonCalculator $carbonCalculator) {}

    public function build(Room $room): array
    {
        $room->load('course');

        $participants = $room->participants()
            ->with(['currentCarbonFootprint', 'activityCompletions.activity'])
            ->withSum('pointTransactions as total_points', 'points')
            ->withSum([
                'pointTransactions as action_points' => fn ($query) => $query
                    ->where('category', PointTransaction::CATEGORY_ACTION),
            ], 'points')
            ->withSum([
                'pointTransactions as learning_points' => fn ($query) => $query
                    ->where('category', PointTransaction::CATEGORY_LEARNING),
            ], 'points')
            ->withSum('activityCompletions as projected_reduction', 'annual_co2_reduction_awarded')
            ->withCount('activityCompletions')
            ->orderBy('name')
            ->get()
            ->each(function ($participant): void {
                $participant->total_points = (int) ($participant->total_points ?? 0);
                $participant->action_points = (int) ($participant->action_points ?? 0);
                $participant->learning_points = (int) ($participant->learning_points ?? 0);
                $participant->activity_completions_count = (int) $participant->activity_completions_count;
                $participant->projected_reduction = $participant->projected_reduction !== null
                    ? (float) $participant->projected_reduction
                    : null;
                $participant->footprint_classification = $participant->currentCarbonFootprint
                    ? $this->carbonCalculator->classification((float) $participant->currentCarbonFootprint->initial_kg_co2e_year)
                    : null;
            });

        $footprints = $participants
            ->map(fn ($participant) => $participant->currentCarbonFootprint?->initial_kg_co2e_year)
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value);

        $averageFootprint = $footprints->isNotEmpty() ? round($footprints->avg(), 2) : null;

        return [
            'room' => $room,
            'participants' => $participants,
            'summary' => [
                'participants' => $participants->count(),
                'footprints_calculated' => $footprints->count(),
                'average_footprint' => $averageFootprint,
                'average_footprint_classification' => $averageFootprint !== null
                    ? $this->carbonCalculator->classification($averageFootprint)
                    : null,
                'total_points' => $participants->sum('total_points'),
                'action_points' => $participants->sum('action_points'),
                'learning_points' => $participants->sum('learning_points'),
                'completed_activities' => $participants->sum('activity_completions_count'),
                'quantified_reduction' => $participants->contains(fn ($participant) => $participant->projected_reduction !== null)
                    ? round($participants->sum('projected_reduction'), 2)
                    : null,
            ],
        ];
    }
}
