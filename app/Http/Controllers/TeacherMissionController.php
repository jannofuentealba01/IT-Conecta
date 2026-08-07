<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Mission;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherMissionController extends Controller
{
    public function index(int $room)
    {
        $room = $this->ownedRoom($room);
        $activities = Activity::where('is_active', true)
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $assigned = Mission::where('room_id', $room->id)->get()->keyBy('activity_id');

        return view('teacher.missions.index', compact('room', 'activities', 'assigned'));
    }

    public function update(Request $request, int $room)
    {
        $room = $this->ownedRoom($room);
        $validated = $request->validate([
            'activities' => ['nullable', 'array'],
            'activities.*' => ['integer', 'distinct'],
        ]);
        $selectedIds = collect($validated['activities'] ?? [])->map(fn ($id) => (int) $id)->unique();
        $allowedIds = Activity::where('is_active', true)
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->whereIn('id', $selectedIds)
            ->pluck('id');

        if ($allowedIds->count() !== $selectedIds->count()) {
            return back()->with('error', 'Una de las actividades seleccionadas no está disponible.');
        }

        DB::transaction(function () use ($room, $allowedIds): void {
            Mission::where('room_id', $room->id)->whereNotIn('activity_id', $allowedIds)->delete();

            foreach ($allowedIds as $activityId) {
                Mission::firstOrCreate(
                    ['room_id' => $room->id, 'activity_id' => $activityId],
                    ['qr_token' => str()->random(64), 'is_active' => true]
                );
            }
        });

        return redirect()->route('teacher.missions.index', $room)
            ->with('success', 'Misiones de la sesión actualizadas.');
    }

    public function qr(int $room, int $mission)
    {
        $room = $this->ownedRoom($room);
        $mission = Mission::with('activity')->where('room_id', $room->id)->findOrFail($mission);
        $missionUrl = route('student.missions.show', $mission->qr_token);

        return view('teacher.missions.qr', compact('room', 'mission', 'missionUrl'));
    }

    private function ownedRoom(int $id): Room
    {
        return Room::with('course')->where('user_id', auth()->id())->findOrFail($id);
    }
}
