<?php

namespace App\Services;

use App\Models\ImpostorGame;
use App\Models\PointTransaction;
use App\Models\Room;
use DomainException;
use Illuminate\Support\Facades\DB;

class ImpostorGameService
{
    private const WORDS = [
        'Reciclaje', 'Compostaje', 'Energía solar', 'Huella de carbono',
        'Biodiversidad', 'Reutilización', 'Eficiencia energética', 'Movilidad sostenible',
    ];

    public function start(Room $room): ImpostorGame
    {
        return DB::transaction(function () use ($room): ImpostorGame {
            $lockedRoom = Room::whereKey($room->id)->lockForUpdate()->firstOrFail();

            if (! $lockedRoom->isOpen()) {
                throw new DomainException('La sala debe estar abierta para iniciar el juego.');
            }

            $activeGame = ImpostorGame::where('room_id', $lockedRoom->id)
                ->where('active_marker', 1)
                ->latest('id')
                ->first();

            if ($activeGame) {
                return $activeGame;
            }

            $participants = $lockedRoom->participants()->get();
            if ($participants->count() < 3) {
                throw new DomainException('Se necesitan al menos 3 estudiantes para iniciar el juego.');
            }

            return ImpostorGame::create([
                'room_id' => $lockedRoom->id,
                'word' => self::WORDS[array_rand(self::WORDS)],
                'status' => 'playing',
                'active_marker' => 1,
                'impostor_id' => $participants->random()->id,
            ]);
        });
    }

    public function finish(ImpostorGame $game): array
    {
        return DB::transaction(function () use ($game): array {
            $game = ImpostorGame::with(['votes', 'clues', 'room.participants'])
                ->lockForUpdate()
                ->findOrFail($game->id);

            if (! in_array($game->status, ['voting', 'finished'], true)) {
                throw new DomainException('La partida debe estar en votación antes de finalizar.');
            }

            if ($game->votes->isEmpty()) {
                throw new DomainException('Debe existir al menos un voto antes de mostrar los resultados.');
            }

            $voteCounts = $game->votes->countBy('suspect_id');
            $highestVotes = $voteCounts->max();
            $mostVotedIds = $voteCounts->filter(fn ($count) => $count === $highestVotes)->keys()->map(fn ($id) => (int) $id);
            $impostorCaught = $mostVotedIds->count() === 1 && $mostVotedIds->first() === (int) $game->impostor_id;

            if ($game->status !== 'finished') {
                foreach ($game->clues->unique('participant_id') as $clue) {
                    $this->award($game, (int) $clue->participant_id, 'participation', 5, 'Participación en Juego del Impostor');
                }

                if ($impostorCaught) {
                    foreach ($game->votes->where('suspect_id', $game->impostor_id) as $vote) {
                        $this->award($game, (int) $vote->voter_id, 'correct-vote', 10, 'Identificó al impostor');
                    }
                } else {
                    $this->award($game, (int) $game->impostor_id, 'impostor-win', 20, 'Ganó como impostor');
                }

                $game->update(['status' => 'finished', 'active_marker' => null]);
            }

            return [
                'game' => $game->fresh(['votes.voter', 'votes.suspect', 'clues.participant', 'room.participants', 'impostor']),
                'mostVotedIds' => $mostVotedIds,
                'impostorCaught' => $impostorCaught,
                'voteCounts' => $voteCounts,
            ];
        });
    }

    private function award(ImpostorGame $game, int $participantId, string $reason, int $points, string $description): void
    {
        PointTransaction::firstOrCreate(
            ['source_key' => "impostor-game-{$game->id}-{$reason}-participant-{$participantId}"],
            [
                'participant_id' => $participantId,
                'room_id' => $game->room_id,
                'category' => PointTransaction::CATEGORY_LEARNING,
                'source_type' => 'impostor_game',
                'source_id' => $game->id,
                'points' => $points,
                'description' => $description,
            ]
        );
    }
}
