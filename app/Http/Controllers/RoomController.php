<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Participant;

class RoomController extends Controller
{
    /**
     * MÉTODO 1: Recibe el código enviado desde la bienvenida (welcome) y valida si existe la sala.
     */
    public function join(Request $request)
    {
        // Validamos que hayan escrito algo
        $request->validate([
            'code' => 'required|string',
        ]);

        // Buscamos si la sala existe en la base de datos
        $room = Room::where('code', $request->code)->first();

        // Si no existe la sala, volvemos atrás con un mensaje de error
        if (!$room) {
            return back()->with('error', 'El código de sala ingresado no existe. Verifica con tu profesor.');
        }

        // Si existe, lo redirigimos al formulario para ingresar Nombre y Curso
        return redirect()->route('room.form', $room->code);
    }

    /**
     * MÉTODO 2: Muestra la vista donde el alumno ingresa su Nombre y Curso (room/join.blade.php).
     */
    public function showJoinForm($code)
    {
        // Confirmamos que la sala sigue existiendo o lanzamos un error 404
        $room = Room::where('code', $code)->firstOrFail();

        // Retornamos la vista pasando el objeto $room y su $code
        return view('room.join', compact('room', 'code'));
    }

    /**
     * MÉTODO 3: Registra al participante en la BD y guarda sus datos en la SESIÓN de Laravel.
     */
    public function enter(Request $request, $code)
    {
        // 1. Buscamos la sala por el código
        $room = Room::where('code', $code)->firstOrFail();

        // 2. Validamos que el alumno haya ingresado su nombre y curso
        $request->validate([
            'name'   => 'required|string|max:100',
            'course' => 'required|string|max:50',
        ]);

        // 3. Creamos el registro del alumno temporal en la tabla `participants`
        $participant = Participant::create([
            'room_id' => $room->id,
            'name'    => $request->name,
            'course'  => $request->course,
        ]);

        // 4. GUARDAR EN SESIÓN (¡Punto clave!):
        // Como no usamos login convencional de email/password, guardamos estos datos 
        // en la sesión del navegador para saber quién está haciendo la actividad.
        session([
            'participant_id'   => $participant->id,
            'participant_name' => $participant->name,
            'participant_course' => $participant->course,
            'room_id'          => $room->id,
            'room_code'        => $room->code,
        ]);

        // 5. Redirigimos al Dashboard o inicio del sistema
        return redirect()->route('dashboard')->with('success', '¡Bienvenido(a) ' . $participant->name . '!');
    }
}