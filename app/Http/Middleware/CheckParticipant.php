<?php

namespace App\Http\Middleware;

use App\Models\Participant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckParticipant
{
    /**
     * Revisa si el estudiante ingresó a una sala mediante su sesión.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $participantId = $request->session()->get('participant_id');

        if (! $participantId) {
            return redirect('/')->with('error', 'Debes ingresar el código de una sala para acceder.');
        }

        $participant = Participant::with('room.course')->find($participantId);

        if (! $participant || ! $participant->room || ! $participant->room->isOpen()) {
            if ($participant?->room?->status === 'open' && $participant->room->expires_at?->isPast()) {
                $participant->room->update(['status' => 'closed', 'closed_at' => now()]);
            }

            $request->session()->forget([
                'participant_id', 'participant_name', 'participant_course',
                'room_id', 'room_code',
            ]);

            return redirect('/')->with('error', 'La sesión terminó o ya no está disponible.');
        }

        if ((int) $request->session()->get('room_id') !== (int) $participant->room_id) {
            $request->session()->forget(['participant_id', 'room_id', 'room_code']);

            return redirect('/')->with('error', 'La sesión del estudiante no es válida. Ingresa nuevamente.');
        }

        if (! $participant->last_seen_at || $participant->last_seen_at->lt(now()->subMinute())) {
            $participant->update(['last_seen_at' => now()]);
        }

        $request->session()->put([
            'participant_name' => $participant->name,
            'participant_course' => $participant->room->course?->name ?? $participant->course,
            'room_code' => $participant->room->code,
        ]);

        return $next($request);
    }
}
