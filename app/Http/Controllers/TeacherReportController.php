<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\RoomReportService;

class TeacherReportController extends Controller
{
    public function __invoke(int $room, RoomReportService $reportService)
    {
        $room = Room::where('user_id', auth()->id())->findOrFail($room);

        return view('teacher.reports.room', $reportService->build($room));
    }
}
