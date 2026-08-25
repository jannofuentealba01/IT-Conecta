<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\TeacherCarbonFootprint;
use App\Services\CarbonCalculator;
use App\Services\CarbonImpactEquivalency;
use App\Services\CarbonQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherCarbonCalculatorController extends Controller
{
    private const MAX_CALCULATIONS = 2;

    public function showForm(Request $request, int $room, CarbonQuestionnaire $questionnaire, CarbonCalculator $calculator)
    {
        $room = $this->ownedRoom($room);
        $teacher = $request->user();
        $currentFootprint = $teacher->carbonFootprints()->where('is_current', true)->latest()->first();
        $history = $teacher->carbonFootprints()->latest()->take(5)->get();
        $history->each(fn (TeacherCarbonFootprint $item) => $item->footprint_classification = $calculator->classification((float) $item->initial_kg_co2e_year));
        $calculationCount = $teacher->carbonFootprints()->count();
        $canCalculate = $calculationCount < self::MAX_CALCULATIONS;
        $attemptNumber = min($calculationCount + 1, self::MAX_CALCULATIONS);
        $classification = $currentFootprint ? $calculator->classification((float) $currentFootprint->initial_kg_co2e_year) : null;
        $showQuestionnaire = $canCalculate && ($request->boolean('new') || ! $currentFootprint || $request->session()->has('errors'));

        return view('carbon.calculator', [
            'questions' => $questionnaire->questionsForParticipant($teacher->id, $attemptNumber),
            'currentFootprint' => $currentFootprint,
            'history' => $history,
            'classification' => $classification,
            'showQuestionnaire' => $showQuestionnaire,
            'canCalculate' => $canCalculate,
            'calculationCount' => $calculationCount,
            'maxCalculations' => self::MAX_CALCULATIONS,
            'calculateUrl' => route('teacher.carbon.calculate', $room),
            'formUrl' => route('teacher.carbon.form', $room),
            'impactUrl' => route('teacher.carbon.impact', $room),
            'panelUrl' => route('teacher.sessions.show', $room),
        ]);
    }

    public function calculate(Request $request, int $room, CarbonQuestionnaire $questionnaire, CarbonCalculator $calculator)
    {
        $room = $this->ownedRoom($room);
        $answers = $request->validate($questionnaire->validationRules());
        $total = $calculator->calculate($answers);
        $teacherId = (int) $request->user()->id;

        $footprint = DB::transaction(function () use ($teacherId, $answers, $total): TeacherCarbonFootprint {
            $teacher = \App\Models\User::whereKey($teacherId)->lockForUpdate()->firstOrFail();
            if ($teacher->carbonFootprints()->count() >= self::MAX_CALCULATIONS) {
                throw ValidationException::withMessages(['calculator' => 'Ya utilizaste los dos cálculos disponibles.']);
            }
            TeacherCarbonFootprint::where('user_id', $teacherId)->where('is_current', true)
                ->update(['is_current' => false, 'current_marker' => null]);

            return TeacherCarbonFootprint::create([
                'user_id' => $teacherId,
                'initial_kg_co2e_year' => $total,
                'answers' => $answers,
                'calculator_version' => CarbonQuestionnaire::VERSION,
                'is_current' => true,
                'current_marker' => 1,
            ]);
        });

        return redirect()->route('teacher.carbon.form', $room)
            ->with('success', 'Tu huella inicial quedó guardada correctamente.')
            ->with('calculation_saved', $footprint->id);
    }

    public function impact(Request $request, int $room, CarbonImpactEquivalency $equivalency)
    {
        $room = $this->ownedRoom($room);
        $footprint = $request->user()->carbonFootprints()->where('is_current', true)->latest()->first();
        if (! $footprint) {
            return redirect()->route('teacher.carbon.form', ['room' => $room, 'new' => 1]);
        }
        $impact = $equivalency->for((float) $footprint->initial_kg_co2e_year, $request->user()->id, $footprint->id);

        return view('carbon.impact', [
            'footprint' => $footprint,
            'impact' => $impact,
            'formUrl' => route('teacher.carbon.form', $room),
            'panelUrl' => route('teacher.sessions.show', $room),
        ]);
    }

    private function ownedRoom(int $id): Room
    {
        return Room::where('user_id', auth()->id())->findOrFail($id);
    }
}
