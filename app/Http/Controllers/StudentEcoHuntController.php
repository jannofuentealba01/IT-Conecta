<?php

namespace App\Http\Controllers;

use App\Models\EcoActivityProfile;
use App\Models\EcoHunt;
use App\Models\EcoHuntCompletion;
use App\Models\Participant;
use App\Services\EcoHuntService;
use DomainException;
use Illuminate\Http\Request;

class StudentEcoHuntController extends Controller
{
    public function index(Request $request, EcoHuntService $service)
    {
        $participant = $this->participant($request);
        $hunt = EcoHunt::with('activities.ecoProfile')->where('room_id', $participant->room_id)
            ->whereIn('status', [EcoHunt::STATUS_DRAFT, EcoHunt::STATUS_READY, EcoHunt::STATUS_ACTIVE, EcoHunt::STATUS_FINISHED])->latest()->first();
        if ($hunt) $hunt = $service->refresh($hunt);
        if ($hunt?->status === EcoHunt::STATUS_FINISHED) return redirect()->route('student.eco-hunt.results');

        $progress = $hunt ? $this->progress($hunt, $participant) : ['completed' => 0, 'points' => 0];
        return view('student.eco-hunt.index', compact('participant', 'hunt', 'progress'));
    }

    public function show(Request $request, string $token, EcoHuntService $service)
    {
        $participant = $this->participant($request);
        $profile = EcoActivityProfile::with('activity')->where('qr_token', $token)->where('is_active', true)->first();
        if (! $profile) return redirect()->route('student.eco-hunt.index')->with('error', 'Este QR no corresponde a una actividad válida.');

        $hunt = EcoHunt::where('room_id', $participant->room_id)
            ->whereIn('status', [EcoHunt::STATUS_DRAFT, EcoHunt::STATUS_READY, EcoHunt::STATUS_ACTIVE, EcoHunt::STATUS_FINISHED])
            ->whereHas('activities', fn ($query) => $query->where('activities.id', $profile->activity_id)->where('eco_hunt_activity.is_active', true))
            ->latest()->first();
        if (! $hunt) return redirect()->route('student.eco-hunt.index')->with('error', 'Este QR no pertenece a la actividad que estás realizando.');
        $hunt = $service->refresh($hunt);
        if ($hunt->status === EcoHunt::STATUS_FINISHED) return redirect()->route('student.eco-hunt.results');
        if ($hunt->status !== EcoHunt::STATUS_ACTIVE) return redirect()->route('student.eco-hunt.index')->with('error', 'La EcoBúsqueda está preparada, pero el profesor todavía no la inicia.');

        $alreadyCompleted = EcoHuntCompletion::where('eco_hunt_id', $hunt->id)->where('participant_id', $participant->id)
            ->where('activity_id', $profile->activity_id)->exists();
        return view('student.eco-hunt.show', compact('hunt', 'profile', 'alreadyCompleted'));
    }

    public function complete(Request $request, string $token, EcoHuntService $service)
    {
        $participant = $this->participant($request);
        $profile = EcoActivityProfile::with('activity')->where('qr_token', $token)->where('is_active', true)->first();
        if (! $profile) return redirect()->route('student.eco-hunt.index')->with('error', 'Este QR no corresponde a una actividad válida.');
        $hunt = EcoHunt::where('room_id', $participant->room_id)->where('status', EcoHunt::STATUS_ACTIVE)
            ->whereHas('activities', fn ($query) => $query->where('activities.id', $profile->activity_id)->where('eco_hunt_activity.is_active', true))
            ->latest()->first();
        if (! $hunt) return redirect()->route('student.eco-hunt.index')->with('error', 'La EcoBúsqueda no está activa o este QR no pertenece a ella.');

        $validated = $request->validate([
            'verification_answers' => ['required', 'array'],
            'verification_answers.q1' => ['required', 'string'],
            'verification_answers.q2' => ['required', 'string'],
        ]);
        try {
            $completion = $service->complete($participant, $hunt, $profile, $validated['verification_answers']);
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['verification' => $exception->getMessage()]);
        }

        return redirect()->route('student.eco-hunt.index')->with('success', "¡Misión completada! Sumaste {$completion->points_awarded} puntos.");
    }

    public function results(Request $request, EcoHuntService $service)
    {
        $participant = $this->participant($request);
        $hunt = EcoHunt::where('room_id', $participant->room_id)->latest()->firstOrFail();
        $hunt = $service->refresh($hunt);
        if ($hunt->status !== EcoHunt::STATUS_FINISHED) return redirect()->route('student.eco-hunt.index');
        $ranking = $service->ranking($hunt);
        return view('student.eco-hunt.results', compact('hunt', 'ranking', 'participant'));
    }

    private function participant(Request $request): Participant
    {
        return Participant::with('room')->findOrFail($request->session()->get('participant_id'));
    }

    private function progress(EcoHunt $hunt, Participant $participant): array
    {
        $query = EcoHuntCompletion::where('eco_hunt_id', $hunt->id)->where('participant_id', $participant->id);
        return ['completed' => $query->count(), 'points' => (int) $query->sum('points_awarded')];
    }
}
