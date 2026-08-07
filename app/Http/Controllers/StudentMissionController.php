<?php

namespace App\Http\Controllers;

use App\Models\ActivityCompletion;
use App\Models\Mission;
use App\Models\Participant;
use App\Services\MissionCompletionService;
use App\Services\ActivityCompletionMessage;
use App\Services\ActivityVerificationQuiz;
use DomainException;
use Illuminate\Http\Request;

class StudentMissionController extends Controller
{
    public function index(Request $request)
    {
        $missions = Mission::with(['activity', 'room'])
            ->where('room_id', $request->session()->get('room_id'))
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->filter->isAvailable();
        $completedActivityIds = ActivityCompletion::where('participant_id', $request->session()->get('participant_id'))
            ->whereDate('completion_date', today())
            ->pluck('activity_id')
            ->all();

        return view('student.missions.index', compact('missions', 'completedActivityIds'));
    }

    public function show(Request $request, string $token, ActivityVerificationQuiz $verificationQuiz)
    {
        $mission = Mission::with(['activity', 'room.course'])
            ->where('qr_token', $token)
            ->where('room_id', $request->session()->get('room_id'))
            ->firstOrFail();

        if (! $mission->isAvailable()) {
            return redirect()->route('activities.index')->with('error', 'Esta misión ya no está disponible.');
        }

        $participant = Participant::findOrFail($request->session()->get('participant_id'));
        $hasFootprint = $participant->carbonFootprints()->where('is_current', true)->exists();
        $alreadyCompleted = ActivityCompletion::where('participant_id', $participant->id)
            ->where('activity_id', $mission->activity_id)
            ->whereDate('completion_date', today())
            ->exists();

        $verificationQuestions = $verificationQuiz->questions($mission->activity);

        return view('student.missions.show', compact(
            'mission', 'hasFootprint', 'alreadyCompleted', 'verificationQuestions'
        ));
    }

    public function complete(
        Request $request,
        string $token,
        MissionCompletionService $completionService,
        ActivityCompletionMessage $completionMessage,
        ActivityVerificationQuiz $verificationQuiz
    ) {
        $mission = Mission::with('activity')->where('qr_token', $token)
            ->where('room_id', $request->session()->get('room_id'))
            ->firstOrFail();
        $participant = Participant::findOrFail($request->session()->get('participant_id'));

        $answers = $request->validate([
            'verification_answers' => ['required', 'array'],
            'verification_answers.q1' => ['required', 'string'],
            'verification_answers.q2' => ['required', 'string'],
        ], [
            'verification_answers.required' => 'Responde las dos preguntas antes de finalizar.',
            'verification_answers.q1.required' => 'Responde la primera pregunta.',
            'verification_answers.q2.required' => 'Responde la segunda pregunta.',
        ]);

        if (! $verificationQuiz->passes($mission->activity, $answers['verification_answers'])) {
            return back()->withInput()->withErrors([
                'verification' => 'Una o más respuestas no coinciden con la misión. Revisa lo que hiciste e inténtalo nuevamente.',
            ]);
        }

        try {
            $result = $completionService->complete($participant, $mission, 'quiz');
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (! $result['created']) {
            return back()->with('error', 'Esta actividad ya fue registrada hoy. Podrás repetirla otro día.');
        }

        $completion = $result['completion']->load('activity');
        $initialFootprint = $participant->carbonFootprints()
            ->where('is_current', true)
            ->value('initial_kg_co2e_year');

        return back()
            ->with('success', "¡Misión completada! Sumaste {$completion->points_awarded} puntos ecológicos.")
            ->with('completion_message', $completionMessage->build(
                $completion,
                $initialFootprint !== null ? (float) $initialFootprint : null
            ));
    }
}
