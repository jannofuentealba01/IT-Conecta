<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\ActivityImpact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('teacher.activities.index', compact('activities'));
    }

    public function create()
    {
        return view('teacher.activities.create');
    }

    public function store(Request $request, ActivityImpact $impact)
    {
        $validated = $this->validateActivity($request);

        Activity::create([
            ...$validated,
            'user_id' => auth()->id(),
            'points' => $impact->pointsFor($validated['impact_level']),
            'co2_impact' => 0,
            'annual_co2_reduction' => null,
            'validation_type' => 'self_report',
            'frequency_days' => 1,
            'is_active' => true,
        ]);

        return redirect()->route('teacher.activities.index')->with('success', 'Actividad añadida al catálogo.');
    }

    public function edit(int $activity)
    {
        $activity = $this->editableActivity($activity);

        return view('teacher.activities.edit', compact('activity'));
    }

    public function update(Request $request, int $activity, ActivityImpact $impact)
    {
        $activity = $this->editableActivity($activity);
        $validated = $this->validateActivity($request);
        $activity->update([
            ...$validated,
            'points' => $impact->pointsFor($validated['impact_level']),
        ]);

        return redirect()->route('teacher.activities.index')->with('success', 'Actividad actualizada.');
    }

    public function destroy(int $activity)
    {
        $activity = $this->editableActivity($activity);
        $activity->update(['is_active' => false]);

        return redirect()->route('teacher.activities.index')
            ->with('success', 'Actividad desactivada. Sus registros históricos se conservaron.');
    }

    private function validateActivity(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'instructions' => ['required', 'string', 'max:700'],
            'category' => ['required', 'string', 'max:60'],
            'impact_level' => ['required', Rule::in(array_keys(ActivityImpact::POINTS))],
            'educational_message' => ['required', 'string', 'max:500'],
        ]);
    }

    private function editableActivity(int $id): Activity
    {
        return Activity::whereKey($id)
            ->where(function ($query) {
                $query->where('user_id', auth()->id());

                if (auth()->user()->rol === 'admin') {
                    $query->orWhereNull('user_id');
                }
            })
            ->firstOrFail();
    }
}
