<?php

namespace App\Http\Controllers;

use App\Models\ImpostorGame;
use App\Models\Room;
use App\Services\ImpostorGameService;
use DomainException;

class TeacherImpostorController extends Controller
{
    public function start(int $room, ImpostorGameService $service)
    {
        $room = $this->ownedRoom($room);

        try {
            $game = $service->start($room);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('teacher.impostor.show', $game);
    }

    public function show(int $game)
    {
        $game = $this->ownedGame($game)->load(['room.participants', 'clues.participant', 'votes']);

        if ($game->status === 'finished') {
            return redirect()->route('teacher.impostor.results', $game);
        }

        return view('teacher.impostor.show', compact('game'));
    }

    public function voting(int $game)
    {
        $game = $this->ownedGame($game);

        if ($game->status !== 'playing') {
            return back()->with('error', 'La partida no está en la fase de pistas.');
        }

        $game->update(['status' => 'voting']);

        return back()->with('success', 'La votación comenzó. Los estudiantes ya pueden votar.');
    }

    public function finish(int $game, ImpostorGameService $service)
    {
        $game = $this->ownedGame($game);

        try {
            $service->finish($game);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('teacher.impostor.results', $game);
    }

    public function results(int $game, ImpostorGameService $service)
    {
        $game = $this->ownedGame($game);

        if ($game->status !== 'finished') {
            return redirect()->route('teacher.impostor.show', $game)
                ->with('error', 'Finaliza la ronda desde el panel docente antes de ver los resultados.');
        }

        try {
            $result = $service->finish($game);
        } catch (DomainException $exception) {
            return redirect()->route('teacher.impostor.show', $game)->with('error', $exception->getMessage());
        }

        return view('impostor.results', [...$result, 'teacherView' => true]);
    }

    private function ownedRoom(int $id): Room
    {
        return Room::where('user_id', auth()->id())->findOrFail($id);
    }

    private function ownedGame(int $id): ImpostorGame
    {
        return ImpostorGame::whereHas('room', fn ($query) => $query->where('user_id', auth()->id()))->findOrFail($id);
    }
}
