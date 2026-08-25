<?php

namespace App\Services;

use App\Models\ImpostorGame;
use App\Models\PointTransaction;
use App\Models\Room;
use DomainException;
use Illuminate\Support\Facades\DB;

class ImpostorGameService
{
    public const VOTING_AFTER_SECONDS = 240;
    public const CLOSE_AFTER_SECONDS = 300;
    public const RESULTS_AFTER_SECONDS = 330;

    private const WORDS = [
        'Reciclaje', 'Compostaje', 'Energía solar', 'Huella de carbono',
        'Biodiversidad', 'Reutilización', 'Eficiencia energética', 'Movilidad sostenible',
    ];

    public function prepare(Room $room): ImpostorGame
    {
        return DB::transaction(function () use ($room): ImpostorGame {
            $lockedRoom = Room::whereKey($room->id)->lockForUpdate()->firstOrFail();

            if (! $lockedRoom->isOpen()) {
                throw new DomainException('La sala debe estar abierta para preparar el juego.');
            }

            $activeGame = ImpostorGame::where('room_id', $lockedRoom->id)
                ->where('active_marker', 1)
                ->latest('id')
                ->first();

            if ($activeGame) {
                return $activeGame;
            }

            return ImpostorGame::create([
                'room_id' => $lockedRoom->id,
                'status' => 'waiting',
                'active_marker' => 1,
            ]);
        });
    }

    public function launch(ImpostorGame $game): ImpostorGame
    {
        return DB::transaction(function () use ($game): ImpostorGame {
            $game = ImpostorGame::lockForUpdate()->findOrFail($game->id);
            $room = Room::whereKey($game->room_id)->lockForUpdate()->firstOrFail();

            if ($game->status !== 'waiting') {
                return $game->load('impostors');
            }

            if (! $room->isOpen()) {
                throw new DomainException('La sala debe estar abierta para iniciar el juego.');
            }

            $participants = $room->participants()->get();
            if ($participants->count() < 3) {
                throw new DomainException('Se necesitan al menos 3 estudiantes para iniciar el juego.');
            }

            $startedAt = now();
            $impostors = $participants->random($this->impostorCountFor($participants->count()));

            $game->update([
                'word' => self::WORDS[array_rand(self::WORDS)],
                'status' => 'playing',
                'impostor_id' => $impostors->first()->id,
                'started_at' => $startedAt,
                'voting_at' => $startedAt->copy()->addSeconds(self::VOTING_AFTER_SECONDS),
                'closes_at' => $startedAt->copy()->addSeconds(self::CLOSE_AFTER_SECONDS),
                'results_at' => $startedAt->copy()->addSeconds(self::RESULTS_AFTER_SECONDS),
            ]);

            $game->impostors()->sync($impostors->pluck('id'));

            return $game->fresh('impostors');
        });
    }

    public function start(Room $room): ImpostorGame
    {
        return $this->launch($this->prepare($room));
    }

    public function impostorCountFor(int $participants): int
    {
        return match (true) {
            $participants <= 7 => 1,
            $participants <= 14 => 2,
            $participants <= 22 => 3,
            $participants <= 31 => 4,
            default => 5,
        };
    }

    public function synchronize(ImpostorGame $game): ImpostorGame
    {
        if ($game->status === 'finished' || ! $game->started_at) {
            return $game;
        }

        if ($game->results_at?->lessThanOrEqualTo(now())) {
            if (in_array($game->status, ['playing', 'voting'], true)) {
                $game->update(['status' => 'closed']);
                $game->refresh();
            }
            $this->finish($game, true);

            return $game->fresh();
        }

        if ($game->closes_at?->lessThanOrEqualTo(now()) && $game->status !== 'closed') {
            $game->update(['status' => 'closed']);
        } elseif ($game->voting_at?->lessThanOrEqualTo(now()) && $game->status === 'playing') {
            $game->update(['status' => 'voting']);
        }

        return $game->fresh();
    }

    public function finish(ImpostorGame $game, bool $automatic = false): array
    {
        return DB::transaction(function () use ($game, $automatic): array {
            $game = ImpostorGame::with(['votes', 'clues', 'room.participants', 'impostors'])
                ->lockForUpdate()
                ->findOrFail($game->id);

            if (! in_array($game->status, ['voting', 'closed', 'finished'], true)) {
                throw new DomainException('La partida debe estar en votación antes de finalizar.');
            }

            if ($game->votes->isEmpty() && ! $automatic) {
                throw new DomainException('Debe existir al menos un voto antes de mostrar los resultados.');
            }

            $impostorIds = $game->impostors->pluck('id')->map(fn ($id) => (int) $id);
            if ($impostorIds->isEmpty() && $game->impostor_id) {
                $impostorIds = collect([(int) $game->impostor_id]);
            }

            $voteCounts = $game->votes->countBy('suspect_id');
            $mostVotedIds = $voteCounts->sortDesc()->keys()->take($impostorIds->count())->map(fn ($id) => (int) $id);
            $caughtImpostorIds = $mostVotedIds->intersect($impostorIds)->values();
            $escapedImpostorIds = $impostorIds->diff($caughtImpostorIds)->values();
            $allImpostorsCaught = $impostorIds->isNotEmpty() && $escapedImpostorIds->isEmpty();

            if ($game->status !== 'finished') {
                foreach ($game->clues->unique('participant_id') as $clue) {
                    $this->award($game, (int) $clue->participant_id, 'participation', 5, 'Participación en Juego del Impostor');
                }

                foreach ($game->votes->whereIn('suspect_id', $impostorIds) as $vote) {
                        $this->award($game, (int) $vote->voter_id, 'correct-vote', 10, 'Identificó al impostor');
                }

                foreach ($escapedImpostorIds as $impostorId) {
                    $this->award($game, $impostorId, 'impostor-win', 20, 'No fue descubierto como impostor');
                }

                $game->update(['status' => 'finished', 'active_marker' => null]);
            }

            return [
                'game' => $game->fresh(['votes.voter', 'votes.suspect', 'clues.participant', 'room.participants', 'impostor', 'impostors']),
                'mostVotedIds' => $mostVotedIds,
                'caughtImpostorIds' => $caughtImpostorIds,
                'escapedImpostorIds' => $escapedImpostorIds,
                'allImpostorsCaught' => $allImpostorsCaught,
                'impostorCaught' => $allImpostorsCaught,
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
