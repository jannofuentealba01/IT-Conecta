<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Participant;
class RankingController extends Controller
{
    public function index()
    {
        $users = User::with('activities')->get()->map(function ($user) {
            $user->total_points = $user->activities->sum('points');
            return $user;
        })->sortByDesc('total_points');

        return view('ranking.index', compact('users'));
    }



    
public function complete(\App\Models\Activity $activity)
{
    // 1. Si el usuario está autenticado por correo (Admin/Profesor)
    if (auth()->check()) {
        $user = auth()->user();
        $user->activities()->syncWithoutDetaching([
            $activity->id => [
                'points_earned' => $activity->points,
                'co2_reduced'   => $activity->co2_impact,
            ]
        ]);
    } 
    // 2. Si es un alumno que ingresó con código de sala (Participant)
    elseif (session()->has('participant_id')) {
        $participant = Participant::find(session('participant_id'));

        if ($participant) {
            // Guardar la actividad realizada por el participante
            // Opción A: Si usas la relación de actividades
            if (method_exists($participant, 'activities')) {
                $participant->activities()->syncWithoutDetaching([
                    $activity->id => [
                        'points_earned' => $activity->points,
                        'co2_reduced'   => $activity->co2_impact,
                    ]
                ]);
            }
        }
    } else {
        return redirect()->back()->with('error', 'Debes estar en una sala para realizar actividades.');
    }

    return redirect()->back()->with('success', 
        "¡Excelente! Has completado la actividad y sumado {$activity->points} puntos."
    );
}
public function ranking()
{
    $points = 0;

    // 1. Obtener puntos del usuario o participante actual
    if (auth()->check()) {
        $user = auth()->user();
        $points = $user->activities ? $user->activities->sum('points') : 0;
    } elseif (session()->has('participant_id')) {
        $participant = \App\Models\Participant::find(session('participant_id'));
        if ($participant) {
            // Si usas la relación activities() o una columna points
            if (method_exists($participant, 'activities') && $participant->activities) {
                $points = $participant->activities->sum('points_earned');
            } else {
                $points = $participant->points ?? 0;
            }
        }
    }

    // 2. Obtener la lista general de ranking para la vista
    // (Si tienes la tabla Participants, podemos incluir sus puntajes)
    $ranking = \App\Models::class; // Variable base
    
    if (\Schema::hasTable('participants')) {
        $ranking = \App\Models\Participant::all()->map(function ($participant) {
            if (method_exists($participant, 'activities') && $participant->activities) {
                $participant->total_points = $participant->activities->sum('pivot.points_earned');
            } else {
                $participant->total_points = $participant->points ?? 0;
            }
            return $participant;
        })->sortByDesc('total_points')->values();
    } else {
        $ranking = \App\Models\User::with('activities')->get()->map(function ($user) {
            $user->total_points = $user->activities ? $user->activities->sum('points') : 0;
            return $user;
        })->sortByDesc('total_points')->values();
    }

    return view('ranking.index', compact('ranking', 'points'));
}

}