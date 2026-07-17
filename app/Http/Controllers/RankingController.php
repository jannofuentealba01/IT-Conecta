<?php

namespace App\Http\Controllers;

use App\Models\User;

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
        $user = auth()->user();
        
        // Registra la actividad realizada en la tabla intermedia (pivote)
        $user->activities()->attach($activity->id);

        return redirect()->back()->with('success', "¡Excelente! Has completado la actividad y sumado {$activity->points} puntos.");
    }

public function ranking()
{
    $ranking = User::with('activities')->get()->map(function ($user) {
        $user->total_points = $user->activities->sum('points');
        return $user;
    })->sortByDesc('total_points')->values();

    $points = auth()->user()->activities->sum('points');

    return view('ranking.index', compact('ranking', 'points'));
}


}