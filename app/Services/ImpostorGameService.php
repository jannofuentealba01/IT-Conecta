<?php

namespace App\Services;

use App\Models\ImpostorGame;
use App\Models\PointTransaction;
use App\Models\Room;
use Carbon\CarbonInterface;
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
            $this->normalizeActiveGames($lockedRoom);

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

    public function activeGame(Room $room): ?ImpostorGame
    {
        return DB::transaction(function () use ($room): ?ImpostorGame {
            $lockedRoom = Room::whereKey($room->id)->lockForUpdate()->firstOrFail();
            $this->normalizeActiveGames($lockedRoom);

            if (! $lockedRoom->isOpen()) {
                return null;
            }

            $game = ImpostorGame::where('room_id', $lockedRoom->id)
                ->where('active_marker', 1)
                ->latest('id')
                ->first();

            if (! $game) {
                return null;
            }

            $game = $this->synchronize($game);

            return (int) $game->active_marker === 1 ? $game : null;
        });
    }

    public function launch(ImpostorGame $game): ImpostorGame
    {
        return DB::transaction(function () use ($game): ImpostorGame {
            $game = ImpostorGame::lockForUpdate()->findOrFail($game->id);
            $room = Room::whereKey($game->room_id)->lockForUpdate()->firstOrFail();

            if ((int) $game->active_marker !== 1 || ! $this->isConsistentActiveGame($game, $room)) {
                $this->deactivateInconsistentGame($game);

                throw new DomainException('La partida anterior ya no está disponible. Prepara una nueva ronda.');
            }

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
        if ($game->status === 'finished') {
            if ($game->active_marker !== null) {
                $game->update(['active_marker' => null]);
            }

            return $game;
        }

        $game->loadMissing('room');
        if ((int) $game->active_marker !== 1 || ! $this->isConsistentActiveGame($game, $game->room)) {
            return $this->deactivateInconsistentGame($game);
        }

        if ($game->status === 'waiting') {
            return $game;
        }

        if ($game->results_at?->lessThanOrEqualTo(now())) {
            if (in_array($game->status, ['playing', 'voting'], true)) {
                $game->update(['status' => 'closed']);
            }
            $this->finish($game, true, false);

            return $game->fresh();
        }

        if ($game->closes_at?->lessThanOrEqualTo(now()) && $game->status !== 'closed') {
            $game->update(['status' => 'closed']);
        } elseif ($game->voting_at?->lessThanOrEqualTo(now()) && $game->status === 'playing') {
            $game->update(['status' => 'voting']);
        }

        return $game;
    }

    public function hasCompleteRoundData(ImpostorGame $game): bool
    {
        if (! is_string($game->word) || trim($game->word) === '' || ! $game->impostor_id) {
            return false;
        }

        if (! $game->started_at || ! $game->voting_at || ! $game->closes_at || ! $game->results_at) {
            return false;
        }

        return $game->started_at->lessThanOrEqualTo($game->voting_at)
            && $game->voting_at->lessThanOrEqualTo($game->closes_at)
            && $game->closes_at->lessThanOrEqualTo($game->results_at);
    }

    public function state(ImpostorGame $game): array
    {
        return [
            'status' => $game->status,
            'server_now' => now()->toIso8601String(),
            'voting_at' => $game->voting_at?->toIso8601String(),
            'closes_at' => $game->closes_at?->toIso8601String(),
            'results_at' => $game->results_at?->toIso8601String(),
        ];
    }

    public function finish(ImpostorGame $game, bool $automatic = false, bool $includeDetails = true): array
    {
        return DB::transaction(function () use ($game, $automatic, $includeDetails): array {
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
                $timestamp = now();
                $awards = collect();

                foreach ($game->clues->unique('participant_id') as $clue) {
                    $awards->push($this->awardData(
                        $game,
                        (int) $clue->participant_id,
                        'participation',
                        5,
                        'Participación en Juego del Impostor',
                        $timestamp
                    ));
                }

                foreach ($game->votes->whereIn('suspect_id', $impostorIds) as $vote) {
                    $awards->push($this->awardData(
                        $game,
                        (int) $vote->voter_id,
                        'correct-vote',
                        10,
                        'Identificó al impostor',
                        $timestamp
                    ));
                }

                foreach ($escapedImpostorIds as $impostorId) {
                    $awards->push($this->awardData(
                        $game,
                        $impostorId,
                        'impostor-win',
                        20,
                        'No fue descubierto como impostor',
                        $timestamp
                    ));
                }

                if ($awards->isNotEmpty()) {
                    PointTransaction::query()->insertOrIgnore(
                        $awards->unique('source_key')->values()->all()
                    );
                }

                $game->update(['status' => 'finished', 'active_marker' => null]);
            }

            return [
                'game' => $includeDetails
                    ? $game->fresh(['votes.voter', 'votes.suspect', 'clues.participant', 'room.participants', 'impostor', 'impostors'])
                    : $game->fresh(),
                'mostVotedIds' => $mostVotedIds,
                'caughtImpostorIds' => $caughtImpostorIds,
                'escapedImpostorIds' => $escapedImpostorIds,
                'allImpostorsCaught' => $allImpostorsCaught,
                'impostorCaught' => $allImpostorsCaught,
                'voteCounts' => $voteCounts,
            ];
        });
    }

    private function awardData(
        ImpostorGame $game,
        int $participantId,
        string $reason,
        int $points,
        string $description,
        CarbonInterface $timestamp
    ): array {
        return [
            'participant_id' => $participantId,
            'room_id' => $game->room_id,
            'category' => PointTransaction::CATEGORY_LEARNING,
            'source_type' => 'impostor_game',
            'source_id' => $game->id,
            'source_key' => "impostor-game-{$game->id}-{$reason}-participant-{$participantId}",
            'points' => $points,
            'description' => $description,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    private function normalizeActiveGames(Room $room): void
    {
        ImpostorGame::where('room_id', $room->id)
            ->where('active_marker', 1)
            ->lockForUpdate()
            ->get()
            ->each(function (ImpostorGame $game) use ($room): void {
                if (! $this->isConsistentActiveGame($game, $room)) {
                    $this->deactivateInconsistentGame($game);
                }
            });
    }

    private function isConsistentActiveGame(ImpostorGame $game, ?Room $room): bool
    {
        if (! $room?->isOpen() || $game->status === 'finished') {
            return false;
        }

        if ($game->status === 'waiting') {
            return $game->word === null
                && $game->impostor_id === null
                && $game->started_at === null
                && $game->voting_at === null
                && $game->closes_at === null
                && $game->results_at === null;
        }

        return in_array($game->status, ['playing', 'voting', 'closed'], true)
            && $this->hasCompleteRoundData($game);
    }

    private function deactivateInconsistentGame(ImpostorGame $game): ImpostorGame
    {
        $game->update([
            'status' => 'finished',
            'active_marker' => null,
        ]);

        return $game->fresh();
    }
}
