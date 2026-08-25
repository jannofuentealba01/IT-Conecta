<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherRoomController extends Controller
{
    public function create(int $course)
    {
        $course = $this->ownedCourse($course);

        return view('teacher.sessions.create', compact('course'));
    }

    public function store(Request $request, int $course)
    {
        $course = $this->ownedCourse($course);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:480'],
        ]);

        $room = Room::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'code' => $this->generateCode(),
            'name' => $validated['name'],
            'duration_minutes' => $validated['duration_minutes'],
            'status' => 'draft',
        ]);

        return redirect()->route('teacher.sessions.show', $room)
            ->with('success', 'Sesión preparada. Ábrela cuando los estudiantes estén listos.');
    }

    public function show(int $room)
    {
        $room = $this->ownedRoom($room);

        if ($room->status === 'open' && $room->expires_at?->isPast()) {
            $room->update(['status' => 'closed', 'closed_at' => now()]);
        }

        $room->load(['course', 'participants' => fn ($query) => $query->orderBy('name')]);
        $teacherHasFootprint = auth()->user()->carbonFootprints()->exists();

        return view('teacher.sessions.show', compact('room', 'teacherHasFootprint'));
    }

    public function open(int $room)
    {
        $room = $this->ownedRoom($room);

        if ($room->status !== 'draft') {
            return back()->with('error', 'Esta sesión no se puede abrir desde su estado actual.');
        }

        $opened = DB::transaction(function () use ($room): bool {
            Room::where('user_id', auth()->id())
                ->where('status', 'open')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'closed', 'closed_at' => now()]);

            $anotherOpenRoom = Room::where('user_id', auth()->id())
                ->where('status', 'open')
                ->where('id', '!=', $room->id)
                ->lockForUpdate()
                ->exists();

            if ($anotherOpenRoom) {
                return false;
            }

            $room->update([
                'status' => 'open',
                'opened_at' => now(),
                'closed_at' => null,
                'expires_at' => now()->addMinutes($room->duration_minutes),
            ]);

            return true;
        });

        if (! $opened) {
            return back()->with('error', 'Ya tienes otra sesión abierta. Ciérrala antes de abrir esta.');
        }

        return back()->with('success', 'Sesión abierta. Los estudiantes ya pueden ingresar.');
    }

    public function close(int $room)
    {
        $room = $this->ownedRoom($room);

        if ($room->status !== 'open') {
            return back()->with('error', 'La sesión no está abierta.');
        }

        $room->update(['status' => 'closed', 'closed_at' => now(), 'expires_at' => now()]);

        return back()->with('success', 'Sesión cerrada. El código dejó de aceptar ingresos y los resultados se conservaron.');
    }

    public function archive(int $room)
    {
        $room = $this->ownedRoom($room);

        if ($room->status === 'open') {
            return back()->with('error', 'Cierra la sesión antes de archivarla.');
        }

        $room->update(['status' => 'archived']);

        return redirect()->route('teacher.courses.show', $room->course_id)
            ->with('success', 'Sesión archivada. Sus registros permanecen disponibles.');
    }

    private function ownedCourse(int $id): Course
    {
        return Course::where('user_id', auth()->id())->where('is_active', true)->findOrFail($id);
    }

    private function ownedRoom(int $id): Room
    {
        return Room::where('user_id', auth()->id())->findOrFail($id);
    }

    private function generateCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (Room::where('code', $code)->exists());

        return $code;
    }
}
