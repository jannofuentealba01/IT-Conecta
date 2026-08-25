<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\Room;

class RoomReportService
{
    public function __construct(
        private readonly CarbonCalculator $carbonCalculator,
        private readonly EcoHuntService $ecoHuntService,
    ) {}

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
        $ecoHunts = $room->ecoHunts()->with(['activities', 'completions.activity'])->oldest()->get()
            ->map(function ($hunt) {
                $hunt = $this->ecoHuntService->refresh($hunt);
                $hunt->ranking = $this->ecoHuntService->ranking($hunt);
                $hunt->activity_stats = $hunt->completions->groupBy('activity_id')->map(function ($items) {
                    return (object) ['name' => $items->first()->activity?->name ?? 'Actividad eliminada', 'count' => $items->count()];
                })->sortByDesc('count')->values();
                if (! $hunt->started_at) {
                    $hunt->effective_seconds = null;
                } elseif ($hunt->reopened_at && $hunt->initial_finished_at) {
                    $firstSegment = $hunt->started_at->diffInSeconds($hunt->initial_finished_at);
                    $secondSegmentEnd = $hunt->finished_at ?? min(now(), $hunt->ends_at ?? now());
                    $hunt->effective_seconds = $firstSegment + $hunt->reopened_at->diffInSeconds($secondSegmentEnd);
                } else {
                    $hunt->effective_seconds = $hunt->started_at
                        ->diffInSeconds($hunt->finished_at ?? min(now(), $hunt->ends_at ?? now()));
                }
                return $hunt;
            });

        return [
            'room' => $room,
            'participants' => $participants,
            'ecoHunts' => $ecoHunts,
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
