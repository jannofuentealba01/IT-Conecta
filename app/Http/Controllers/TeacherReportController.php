<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\RoomReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class TeacherReportController extends Controller
{
    public function __invoke(int $room, RoomReportService $reportService)
    {
        $room = $this->ownedRoom($room);

        return view('teacher.reports.room', $reportService->build($room));
    }

    public function download(int $room, RoomReportService $reportService)
    {
        $room = $this->ownedRoom($room);
        $results = $reportService->build($room)['exportResults'];
        $roomSlug = Str::slug($room->name) ?: 'sala';
        $filename = 'resultados-'.$roomSlug.'-'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('teacher.reports.room-pdf', compact('results'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function ownedRoom(int $id): Room
    {
        return Room::where('user_id', auth()->id())->findOrFail($id);
    }
}
