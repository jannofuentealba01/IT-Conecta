<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();
        if ($user->rol === 'profesor' && $user->approval_status !== 'approved') {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta docente todavía está pendiente de aprobación por el administrador.',
            ]);
        }

        $request->session()->forget([
            'participant_id',
            'participant_name',
            'participant_course',
            'room_id',
            'room_code',
        ]);
        $request->session()->regenerate();

        $destination = $user->rol === 'admin'
            ? route('admin.teachers.index', absolute: false)
            : route('teacher.dashboard', absolute: false);

        // Cada rol vuelve siempre a su propia área. Así una URL protegida
        // visitada antes del acceso no puede enviar al usuario al panel ajeno.
        $request->session()->forget('url.intended');

        return redirect($destination);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
