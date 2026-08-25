<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TeacherHelpController extends Controller
{
    public function instructions(): View
    {
        return view('teacher.help.instructions');
    }

    public function faq(): View
    {
        return view('teacher.help.faq');
    }
}
