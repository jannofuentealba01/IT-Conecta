<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminTeacherApprovalController extends Controller
{
    public function index(): View
    {
        $pendingTeachers = User::where('rol', 'profesor')
            ->where('approval_status', 'pending')
            ->orderBy('created_at')
            ->get();
        $approvedTeachers = User::where('rol', 'profesor')
            ->where('approval_status', 'approved')
            ->latest('approved_at')
            ->latest('id')
            ->take(50)
            ->get();

        return view('admin.teachers.index', compact('pendingTeachers', 'approvedTeachers'));
    }

    public function approve(int $teacher): RedirectResponse
    {
        $teacher = User::where('rol', 'profesor')->findOrFail($teacher);

        if ($teacher->approval_status === 'approved') {
            return back()->with('error', 'La cuenta de este profesor ya estaba aprobada.');
        }

        $teacher->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Profesor aprobado. Ya puede iniciar sesión.');
    }
}
