<?php

namespace App\Http\Controllers;

use App\Services\GeneralRankingService;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function __invoke(Request $request, GeneralRankingService $rankingService)
    {
        $ranking = $rankingService->get();
        $participantId = (int) $request->session()->get('participant_id');
        $currentParticipant = $ranking->firstWhere('id', $participantId);

        return view('ranking.index', [
            'ranking' => $ranking,
            'currentParticipant' => $currentParticipant,
            'totalParticipants' => $ranking->count(),
        ]);
    }
}
