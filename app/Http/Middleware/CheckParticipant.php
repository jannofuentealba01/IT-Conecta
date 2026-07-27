<?php

namespace App\Http\Middleware;

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
        // Si no existe 'participant_id' en la sesión, lo redirige a la bienvenida
        if (!session()->has('participant_id')) {
            return redirect('/')->with('error', 'Debes ingresar el código de una sala para acceder.');
        }

        return $next($request);
    }
}