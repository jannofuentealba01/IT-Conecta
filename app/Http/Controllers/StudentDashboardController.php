<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Services\CarbonCalculator;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request, CarbonCalculator $carbonCalculator)
    {
        $participant = Participant::with(['currentCarbonFootprint', 'room.course'])
            ->findOrFail($request->session()->get('participant_id'));
        $actionPoints = (int) $participant->pointTransactions()->where('category', 'action')->sum('points');
        $learningPoints = (int) $participant->pointTransactions()->where('category', 'learning')->sum('points');
        $footprint = $participant->currentCarbonFootprint;
        $footprintClassification = $footprint
            ? $carbonCalculator->classification((float) $footprint->initial_kg_co2e_year)
            : null;

        return view('dashboard', [
            'participant' => $participant,
            'footprint' => $footprint,
            'footprintClassification' => $footprintClassification,
            'actionPoints' => $actionPoints,
            'learningPoints' => $learningPoints,
            'totalPoints' => $actionPoints + $learningPoints,
            'completedActivities' => $participant->activityCompletions()->count(),
        ]);
    }
}
