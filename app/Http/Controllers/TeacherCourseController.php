<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherCourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('user_id', auth()->id())
            ->withCount(['rooms', 'rooms as participants_count' => fn ($query) => $query
                ->join('participants', 'participants.room_id', '=', 'rooms.id')])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('teacher.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('teacher.courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('courses')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'school_name' => ['nullable', 'string', 'max:150'],
        ]);

        $course = Course::create([
            ...$validated,
            'user_id' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->route('teacher.courses.show', $course)
            ->with('success', 'Curso creado correctamente. Ahora puedes crear su primera sesión.');
    }

    public function show(int $course)
    {
        $course = $this->ownedCourse($course);
        $course->load(['rooms' => fn ($query) => $query->withCount('participants')->latest()]);

        return view('teacher.courses.show', compact('course'));
    }

    public function edit(int $course)
    {
        $course = $this->ownedCourse($course);

        return view('teacher.courses.edit', compact('course'));
    }

    public function update(Request $request, int $course)
    {
        $course = $this->ownedCourse($course);
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('courses')->where(fn ($query) => $query->where('user_id', auth()->id()))->ignore($course->id),
            ],
            'school_name' => ['nullable', 'string', 'max:150'],
        ]);

        $course->update($validated);

        return redirect()->route('teacher.courses.show', $course)->with('success', 'Curso actualizado.');
    }

    public function archive(int $course)
    {
        $course = $this->ownedCourse($course);

        $course->rooms()
            ->where('status', 'open')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'closed', 'closed_at' => now()]);

        if ($course->rooms()->where('status', 'open')->exists()) {
            return back()->with('error', 'Cierra la sesión activa antes de archivar el curso.');
        }

        $course->update(['is_active' => false]);

        return redirect()->route('teacher.courses.index')->with('success', 'Curso archivado. Sus datos se conservaron.');
    }

    private function ownedCourse(int $id): Course
    {
        return Course::where('user_id', auth()->id())->findOrFail($id);
    }
}
