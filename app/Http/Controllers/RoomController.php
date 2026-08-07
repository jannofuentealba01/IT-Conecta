<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Room;
use App\Services\ParticipantIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class RoomController extends Controller
{
    /**
     * MÉTODO 1: Recibe el código enviado desde la bienvenida (welcome) y valida si existe la sala.
     */
    public function join(Request $request)
    {
        // Validamos que hayan escrito algo
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $code = strtoupper(trim($request->string('code')->toString()));
        $room = Room::where('code', $code)->first();

        if (! $room || ! $room->isOpen()) {
            if ($room?->status === 'open' && $room->expires_at?->isPast()) {
                $room->update(['status' => 'closed', 'closed_at' => now()]);
            }

            return back()->with('error', 'La sala no existe, está cerrada o su código expiró. Verifica con tu profesor.');
        }

        if ($participant = $this->knownParticipant($request, $room)) {
            $this->startStudentSession($request, $room, $participant);

            return redirect()->route('student.dashboard')
                ->with('success', '¡Bienvenido(a) nuevamente, '.$participant->name.'!');
        }

        // Si existe, lo redirigimos al formulario para ingresar Nombre y Curso
        return redirect()->route('room.form', $room->code);
    }

    /**
     * MÉTODO 2: Muestra la vista donde el alumno ingresa su Nombre y Curso (room/join.blade.php).
     */
    public function showJoinForm(Request $request, $code)
    {
        // Confirmamos que la sala sigue existiendo o lanzamos un error 404
        $room = Room::where('code', $code)->firstOrFail();

        if (! $room->isOpen()) {
            return redirect()->route('home')->with('error', 'Esta sala ya no está disponible.');
        }

        if ($participant = $this->knownParticipant($request, $room)) {
            $this->startStudentSession($request, $room, $participant);

            return redirect()->route('student.dashboard')
                ->with('success', '¡Bienvenido(a) nuevamente, '.$participant->name.'!');
        }

        // Retornamos la vista pasando el objeto $room y su $code
        return view('room.join', compact('room', 'code'));
    }

    /**
     * MÉTODO 3: Registra al participante en la BD y guarda sus datos en la SESIÓN de Laravel.
     */
    public function enter(Request $request, string $code, ParticipantIdentity $identity)
    {
        // 1. Buscamos la sala por el código
        $room = Room::where('code', $code)->firstOrFail();

        if (! $room->isOpen()) {
            return redirect()->route('home')->with('error', 'Esta sala ya no está disponible.');
        }

        // 2. El curso se obtiene desde la sala creada por el profesor.
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'not_regex:/[<>]/'],
        ]);

        $name = $identity->clean($request->string('name')->toString());
        $normalizedName = $identity->normalize($name);
        $cookieName = $this->participantCookieName($room);
        $recoveryToken = $request->cookie($cookieName);

        $participant = null;

        if ($recoveryToken) {
            $participant = Participant::where('room_id', $room->id)
                ->where('recovery_token', $recoveryToken)
                ->where('normalized_name', $normalizedName)
                ->first();
        }

        $participant ??= Participant::where('room_id', $room->id)
            ->where('normalized_name', $normalizedName)
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id')
            ->first();

        if (! $participant) {
            $participant = Participant::create([
                'room_id' => $room->id,
                'name' => $name,
                'normalized_name' => $normalizedName,
                'course' => $room->course?->name ?? $room->name ?? 'Sin curso',
                'recovery_token' => str()->random(64),
                'joined_at' => now(),
                'last_seen_at' => now(),
            ]);
        } else {
            $participant->update([
                'name' => $name,
                'normalized_name' => $normalizedName,
                'last_seen_at' => now(),
            ]);
        }

        $this->startStudentSession($request, $room, $participant);

        // 5. Redirigimos al Dashboard o inicio del sistema
        Cookie::queue(
            $cookieName,
            $participant->recovery_token,
            60 * 24 * 30,
            '/',
            null,
            app()->environment('production'),
            true,
            false,
            'lax'
        );

        return redirect()->route('student.dashboard')->with('success', '¡Bienvenido(a) '.$participant->name.'!');
    }

    private function knownParticipant(Request $request, Room $room): ?Participant
    {
        $recoveryToken = $request->cookie($this->participantCookieName($room));

        if (! is_string($recoveryToken) || strlen($recoveryToken) !== 64) {
            return null;
        }

        return Participant::where('room_id', $room->id)
            ->where('recovery_token', $recoveryToken)
            ->first();
    }

    private function startStudentSession(Request $request, Room $room, Participant $participant): void
    {
        $participant->update(['last_seen_at' => now()]);
        $request->session()->regenerate();
        $request->session()->put([
            'participant_id' => $participant->id,
            'participant_name' => $participant->name,
            'participant_course' => $room->course?->name ?? $participant->course,
            'room_id' => $room->id,
            'room_code' => $room->code,
        ]);
    }

    private function participantCookieName(Room $room): string
    {
        return 'it_conecta_participant_'.$room->id;
    }
}
