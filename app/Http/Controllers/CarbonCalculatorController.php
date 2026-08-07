<?php

namespace App\Http\Controllers;

use App\Models\CarbonFootprint;
use App\Models\Participant;
use App\Services\CarbonCalculator;
use App\Services\CarbonQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CarbonCalculatorController extends Controller
{
    private const MAX_CALCULATIONS = 2;

    public function showForm(Request $request, CarbonQuestionnaire $questionnaire, CarbonCalculator $calculator)
    {
        $participant = Participant::findOrFail($request->session()->get('participant_id'));
        $currentFootprint = $participant->carbonFootprints()->where('is_current', true)->latest()->first();
        $history = $participant->carbonFootprints()->latest()->take(5)->get();
        $history->each(function (CarbonFootprint $item) use ($calculator): void {
            $item->footprint_classification = $calculator->classification((float) $item->initial_kg_co2e_year);
        });
        $calculationCount = $participant->carbonFootprints()->count();
        $canCalculate = $calculationCount < self::MAX_CALCULATIONS;
        $attemptNumber = min($calculationCount + 1, self::MAX_CALCULATIONS);
        $classification = $currentFootprint
            ? $calculator->classification((float) $currentFootprint->initial_kg_co2e_year)
            : null;
        $showQuestionnaire = $canCalculate && (
            $request->boolean('new')
            || ! $currentFootprint
            || $request->session()->has('errors')
        );

        return view('carbon.calculator', [
            'questions' => $questionnaire->questionsForParticipant($participant->id, $attemptNumber),
            'currentFootprint' => $currentFootprint,
            'history' => $history,
            'classification' => $classification,
            'showQuestionnaire' => $showQuestionnaire,
            'canCalculate' => $canCalculate,
            'calculationCount' => $calculationCount,
            'maxCalculations' => self::MAX_CALCULATIONS,
        ]);
    }

    public function calculate(
        Request $request,
        CarbonQuestionnaire $questionnaire,
        CarbonCalculator $calculator
    ) {
        $answers = $request->validate($questionnaire->validationRules());
        $total = $calculator->calculate($answers);
        $participantId = (int) $request->session()->get('participant_id');

        $footprint = DB::transaction(function () use ($participantId, $answers, $total): CarbonFootprint {
            $participant = Participant::whereKey($participantId)->lockForUpdate()->firstOrFail();

            if ($participant->carbonFootprints()->count() >= self::MAX_CALCULATIONS) {
                throw ValidationException::withMessages([
                    'calculator' => 'Ya utilizaste los dos cálculos disponibles para esta sala.',
                ]);
            }

            CarbonFootprint::where('participant_id', $participantId)
                ->where('is_current', true)
                ->update(['is_current' => false, 'current_marker' => null]);

            return CarbonFootprint::create([
                'participant_id' => $participantId,
                'initial_kg_co2e_year' => $total,
                'answers' => $answers,
                'calculator_version' => CarbonQuestionnaire::VERSION,
                'is_current' => true,
                'current_marker' => 1,
            ]);
        });

        return redirect()->route('carbon.form')
            ->with('success', 'Tu huella inicial quedó guardada correctamente.')
            ->with('calculation_saved', $footprint->id);
    }
}
