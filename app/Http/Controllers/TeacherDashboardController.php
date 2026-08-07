<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Room;

class TeacherDashboardController extends Controller
{
    public function __invoke()
    {
        $userId = auth()->id();

        Room::where('user_id', $userId)
            ->where('status', 'open')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'closed', 'closed_at' => now()]);

        $stats = [
            'courses' => Course::where('user_id', $userId)->where('is_active', true)->count(),
            'sessions' => Room::where('user_id', $userId)->count(),
            'open_sessions' => Room::where('user_id', $userId)->where('status', 'open')->count(),
            'participants' => Room::where('user_id', $userId)->withCount('participants')->get()->sum('participants_count'),
        ];

        $recentRooms = Room::where('user_id', $userId)
            ->with('course')
            ->withCount('participants')
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.dashboard', compact('stats', 'recentRooms'));
    }
}
