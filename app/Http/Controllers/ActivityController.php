<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activities = Activity::all();
        return view('activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    return view('activities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Activity::create([
        'name' => $request->name,
        'description' => $request->description,
        'points' => $request->points,
        'co2_impact' => $request->co2_impact,
    ]);

    return redirect()->route('activities.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {

        return view('activities.edit', compact('activity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
    $activity->update([
        'name' => $request->name,
        'description' => $request->description,
        'points' => $request->points,
        'co2_impact' => $request->co2_impact,
    ]);

    return redirect()->route('activities.index');
    }

    /**
     * Remove the specified resource from storage.
     */



    public function destroy(Activity $activity)
    {
    $activity->delete();

    return redirect()->route('activities.index')
    ->with('success', 'Actividad eliminada correctamente 🗑️');
    }




    public function do($id)
    {
        $user = Auth::user();

        $user->activities()->syncWithoutDetaching([$id]);

        return redirect()->back()->with('success', 'Actividad registrada');
    }

    public function scan($id)
    {
        $user = Auth::user();

        // Evita duplicados
        $user->activities()->syncWithoutDetaching([$id]);

        return redirect()->route('ranking')
            ->with('success', 'Actividad registrada por QR 🚀');
    }


        public function showQr($id)
    {
        $activity = Activity::findOrFail($id);

        return view('activities.qr', compact('activity'));
    }


}
