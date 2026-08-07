<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (! auth()->check()) {
            return redirect('/login');
        }
        if (! in_array(auth()->user()->rol, ['admin', 'profesor'], true)) {
            return redirect('/')->with('error', 'No autorizado');
        }

        if (auth()->user()->rol === 'profesor' && auth()->user()->approval_status !== 'approved') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta docente todavía está pendiente de aprobación.',
            ]);
        }

        return $next($request);
    }
}
