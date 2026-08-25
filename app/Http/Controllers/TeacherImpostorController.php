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
            $game = $service->prepare($room);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('teacher.impostor.show', $game);
    }

    public function launch(int $game, ImpostorGameService $service)
    {
        $game = $this->ownedGame($game);

        try {
            $game = $service->launch($game);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('teacher.impostor.show', $game)
            ->with('success', 'La partida comenzó. El reloj de 5 minutos ya está en marcha.');
    }

    public function show(int $game, ImpostorGameService $service)
    {
        $game = $service->synchronize($this->ownedGame($game));
        $game->load(['room.participants', 'clues.participant', 'votes', 'impostors']);

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

        $votingAt = now();
        $game->update([
            'status' => 'voting',
            'voting_at' => $votingAt,
            'closes_at' => $votingAt->copy()->addMinute(),
            'results_at' => $votingAt->copy()->addMinute()->addSeconds(30),
        ]);

        return back()->with('success', 'La votación comenzó. Los estudiantes ya pueden votar.');
    }

    public function finish(int $game, ImpostorGameService $service)
    {
        $game = $service->synchronize($this->ownedGame($game));

        if ($game->status !== 'closed') {
            return back()->with('error', 'El botón Mostrar resultados se habilita cuando termina el minuto de votación.');
        }

        try {
            $service->finish($game, true);
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
