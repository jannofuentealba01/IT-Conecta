<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentSessionController extends Controller
{
    public function destroy(Request $request)
    {
        $request->session()->forget([
            'participant_id', 'participant_name', 'participant_course',
            'room_id', 'room_code',
        ]);
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Has salido de la sala. Podrás volver a ingresar mientras siga abierta.');
    }
}
