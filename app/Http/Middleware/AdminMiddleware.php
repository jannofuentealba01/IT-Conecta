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

    if (!auth()->check()) {
        return redirect('/login');
    }
    if (auth()->user()->rol !== 'admin') {
        return redirect('/dashboard')->with('error', 'No autorizado');
    }

    return $next($request);
}
}
