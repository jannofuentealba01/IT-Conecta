<?php

namespace App\Http\Controllers;

use App\Models\ImpostorClue;
use App\Models\ImpostorGame;
use App\Models\ImpostorVote;
use App\Models\Participant;
use App\Services\ImpostorGameService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentImpostorController extends Controller
{
    public function lobby(Request $request)
    {
        $participant = $this->participant($request);
        $game = ImpostorGame::where('room_id', $participant->room_id)
            ->where('active_marker', 1)
            ->latest('id')
            ->first();

        return $game
            ? redirect()->route('student.impostor.show', $game)
            : view('impostor.lobby', compact('participant'));
    }

    public function show(Request $request, int $game)
    {
        $participant = $this->participant($request);
        $service = app(ImpostorGameService::class);
        try {
            $game = $service->synchronize($this->gameForParticipant($game, $participant))
                ->load(['clues.participant', 'votes', 'room.participants', 'impostors']);
        } catch (DomainException $exception) {
            return redirect()->route('student.dashboard')->with(
                'error',
                'El estado del juego se está actualizando. Intenta ingresar nuevamente en unos segundos.'
            );
        }

        if ($game->status === 'waiting') {
            return view('impostor.lobby', compact('participant'));
        }

        if ($game->status === 'finished') {
            return redirect()->route('student.impostor.results', $game);
        }

        return view('impostor.play', [
            'game' => $game,
            'participant' => $participant,
            'hasClue' => $game->clues->contains('participant_id', $participant->id),
            'hasVoted' => $game->votes->contains('voter_id', $participant->id),
        ]);
    }

    public function clue(Request $request, int $game)
    {
        $participant = $this->participant($request);
        try {
            $game = app(ImpostorGameService::class)->synchronize($this->gameForParticipant($game, $participant));
        } catch (DomainException $exception) {
            return redirect()->route('student.dashboard')->with('error', 'No fue posible actualizar la partida. Intenta ingresar nuevamente.');
        }

        if ($game->status !== 'playing' || ! $game->room->isOpen()) {
            return back()->with('error', 'La fase de pistas ya terminó.');
        }

        $validated = $request->validate(['clue' => ['required', 'string', 'max:120']]);
        $clue = trim($validated['clue']);
        if ($clue === '') {
            return back()->withErrors(['clue' => 'Escribe una pista antes de enviarla.']);
        }

        $created = ImpostorClue::firstOrCreate(
            ['game_id' => $game->id, 'participant_id' => $participant->id],
            ['clue' => $clue]
        );

        return back()->with($created->wasRecentlyCreated ? 'success' : 'error', $created->wasRecentlyCreated ? 'Pista enviada.' : 'Ya enviaste tu pista en esta ronda.');
    }

    public function vote(Request $request, int $game)
    {
        $participant = $this->participant($request);
        try {
            $game = app(ImpostorGameService::class)->synchronize($this->gameForParticipant($game, $participant));
        } catch (DomainException $exception) {
            return redirect()->route('student.dashboard')->with('error', 'No fue posible actualizar la votación. Intenta ingresar nuevamente.');
        }

        if ($game->status !== 'voting' || ! $game->room->isOpen()) {
            return back()->with('error', 'La votación no está disponible.');
        }

        $validated = $request->validate([
            'suspect_id' => [
                'required', 'integer',
                Rule::exists('participants', 'id')->where(fn ($query) => $query->where('room_id', $game->room_id)),
                Rule::notIn([$participant->id]),
            ],
        ]);

        $vote = ImpostorVote::firstOrCreate(
            ['game_id' => $game->id, 'voter_id' => $participant->id],
            ['suspect_id' => $validated['suspect_id']]
        );

        return back()->with($vote->wasRecentlyCreated ? 'success' : 'error', $vote->wasRecentlyCreated ? 'Voto registrado.' : 'Ya votaste en esta ronda.');
    }

    public function results(Request $request, int $game, ImpostorGameService $service)
    {
        $participant = $this->participant($request);
        $game = $this->gameForParticipant($game, $participant);

        if ($game->status !== 'finished') {
            return redirect()->route('student.impostor.show', $game)->with('error', 'El profesor todavía no ha finalizado la ronda.');
        }

        try {
            $result = $service->finish($game);
        } catch (DomainException $exception) {
            return redirect()->route('student.impostor.show', $game)->with('error', $exception->getMessage());
        }

        return view('impostor.results', [...$result, 'teacherView' => false, 'participant' => $participant]);
    }

    private function participant(Request $request): Participant
    {
        return Participant::with('room')->findOrFail($request->session()->get('participant_id'));
    }

    private function gameForParticipant(int $id, Participant $participant): ImpostorGame
    {
        return ImpostorGame::where('room_id', $participant->room_id)->findOrFail($id);
    }
}
