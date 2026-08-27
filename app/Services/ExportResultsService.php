<?php

namespace App\Services;

use App\Models\ImpostorGame;
use App\Models\PointTransaction;
use App\Models\Room;
use Illuminate\Support\Collection;

class ExportResultsService
{
    public const SCHEMA_VERSION = 1;

    public function build(Room $room, Collection $participants, Collection $ecoHunts, array $summary): array
    {
        $room->loadMissing('course');

        $games = $room->games()
            ->with(['impostors', 'impostor', 'clues.participant', 'votes.voter', 'votes.suspect'])
            ->oldest()
            ->get();

        $gameTransactions = PointTransaction::query()
            ->with('participant')
            ->where('room_id', $room->id)
            ->where('source_type', 'impostor_game')
            ->get()
            ->groupBy('source_id');

        $experiences = $ecoHunts
            ->map(fn ($hunt) => $this->ecoHunt($hunt))
            ->concat($games->map(fn (ImpostorGame $game) => $this->impostorGame(
                $game,
                $participants,
                $gameTransactions->get($game->id, collect())
            )))
            ->sortBy(fn (array $experience) => $experience['started_at'] ?? $experience['created_at'])
            ->values()
            ->all();

        return [
            'schema' => 'it-conecta-results',
            'schema_version' => self::SCHEMA_VERSION,
            'generated_at' => now()->toIso8601String(),
            'session' => [
                'name' => $room->name,
                'course' => $room->course?->name,
                'school' => $room->course?->school_name,
                'status' => $room->status,
                'status_label' => $this->roomStatus($room->status),
                'created_at' => $this->date($room->created_at),
                'opened_at' => $this->date($room->opened_at),
                'closed_at' => $this->date($room->closed_at),
                'planned_duration_minutes' => (int) $room->duration_minutes,
            ],
            'summary' => [
                'participants' => (int) $summary['participants'],
                'footprints_calculated' => (int) $summary['footprints_calculated'],
                'average_footprint_kg_co2e_year' => $summary['average_footprint'],
                'average_footprint_level' => $summary['average_footprint_classification']['key'] ?? null,
                'total_points' => (int) $summary['total_points'],
                'action_points' => (int) $summary['action_points'],
                'learning_points' => (int) $summary['learning_points'],
                'completed_activities' => (int) $summary['completed_activities'],
                'projected_reduction_kg_co2e_year' => $summary['quantified_reduction'],
                'eco_hunts' => $ecoHunts->count(),
                'impostor_games' => $games->count(),
            ],
            'participants' => $participants->map(fn ($participant) => $this->participant($participant))->values()->all(),
            'experiences' => $experiences,
        ];
    }

    private function participant($participant): array
    {
        $regularActivities = $participant->activityCompletions->map(fn ($completion) => [
            'type' => 'environmental_activity',
            'name' => $completion->activity?->name ?? 'Actividad eliminada',
            'completed_at' => $this->date($completion->completed_at),
            'points' => (int) $completion->points_awarded,
            'projected_reduction_kg_co2e_year' => $completion->annual_co2_reduction_awarded !== null
                ? (float) $completion->annual_co2_reduction_awarded
                : null,
        ]);

        $ecoActivities = $participant->ecoHuntCompletions->map(fn ($completion) => [
            'type' => 'eco_hunt_activity',
            'name' => $completion->activity?->name ?? 'Actividad eliminada',
            'completed_at' => $this->date($completion->completed_at),
            'points' => (int) $completion->points_awarded,
            'projected_reduction_kg_co2e_year' => null,
        ]);

        return [
            'name' => $participant->name,
            'joined_at' => $this->date($participant->joined_at ?? $participant->created_at),
            'footprint_kg_co2e_year' => $participant->currentCarbonFootprint
                ? (float) $participant->currentCarbonFootprint->initial_kg_co2e_year
                : null,
            'footprint_level' => $participant->footprint_classification['key'] ?? null,
            'total_points' => (int) $participant->total_points,
            'action_points' => (int) $participant->action_points,
            'learning_points' => (int) $participant->learning_points,
            'projected_reduction_kg_co2e_year' => $participant->projected_reduction,
            'completed_activities' => $regularActivities->concat($ecoActivities)
                ->sortBy('completed_at')
                ->values()
                ->all(),
        ];
    }

    private function ecoHunt($hunt): array
    {
        $ranking = $hunt->ranking->values()->map(fn ($entry, int $index) => [
            'position' => $index + 1,
            'participant' => $entry->name,
            'points' => (int) $entry->points,
            'completed_activities' => (int) $entry->completed_count,
        ]);
        $winner = $ranking->first(fn (array $entry) => $entry['points'] > 0);

        return [
            'type' => 'eco_hunt',
            'name' => $hunt->name,
            'status' => $hunt->status,
            'status_label' => $this->ecoStatus($hunt->status),
            'created_at' => $this->date($hunt->created_at),
            'started_at' => $this->date($hunt->started_at),
            'finished_at' => $this->date($hunt->finished_at),
            'duration_seconds' => $hunt->effective_seconds !== null ? (int) $hunt->effective_seconds : null,
            'participant_count' => $ranking->count(),
            'result' => [
                'winner_label' => $winner ? $winner['participant'] : null,
                'summary' => $winner
                    ? "{$winner['participant']} obtuvo {$winner['points']} puntos."
                    : 'La experiencia todavía no registra un puntaje ganador.',
            ],
            'metrics' => [
                'selected_activities' => $hunt->activities->count(),
                'completed_records' => $hunt->completions->count(),
                'reopened' => (int) $hunt->reopen_count > 0,
                'finished_by' => $hunt->finished_by,
            ],
            'ranking' => $ranking->all(),
        ];
    }

    private function impostorGame(ImpostorGame $game, Collection $participants, Collection $transactions): array
    {
        $impostorIds = $game->impostors->pluck('id')->map(fn ($id) => (int) $id);
        if ($impostorIds->isEmpty() && $game->impostor_id) {
            $impostorIds = collect([(int) $game->impostor_id]);
        }

        $voteCounts = $game->votes->countBy('suspect_id');
        $mostVotedIds = $voteCounts->sortDesc()->keys()->take($impostorIds->count())->map(fn ($id) => (int) $id);
        $caughtIds = $mostVotedIds->intersect($impostorIds)->values();
        $escapedIds = $impostorIds->diff($caughtIds)->values();
        $allCaught = $impostorIds->isNotEmpty() && $escapedIds->isEmpty();
        $pointsByParticipant = $transactions->groupBy('participant_id')->map->sum('points');
        $votesByVoter = $game->votes->keyBy('voter_id');
        $clueParticipantIds = $game->clues->pluck('participant_id')->map(fn ($id) => (int) $id);

        $ranking = $participants->map(function ($participant) use (
            $pointsByParticipant,
            $votesByVoter,
            $clueParticipantIds,
            $impostorIds,
            $voteCounts
        ): array {
            $vote = $votesByVoter->get($participant->id);

            return [
                'participant' => $participant->name,
                'points' => (int) $pointsByParticipant->get($participant->id, 0),
                'role' => $impostorIds->contains((int) $participant->id) ? 'impostor' : 'crew',
                'clue_submitted' => $clueParticipantIds->contains((int) $participant->id),
                'voted_for' => $vote?->suspect?->name,
                'votes_received' => (int) $voteCounts->get($participant->id, 0),
            ];
        })->sort(function (array $left, array $right): int {
            return ($right['points'] <=> $left['points'])
                ?: strcasecmp($left['participant'], $right['participant']);
        })->values();

        $previousPoints = null;
        $position = 0;
        $ranking = $ranking->map(function (array $entry, int $index) use (&$previousPoints, &$position): array {
            if ($previousPoints === null || $entry['points'] !== $previousPoints) {
                $position = $index + 1;
                $previousPoints = $entry['points'];
            }

            return ['position' => $position, ...$entry];
        });

        $impostorNames = $participants->whereIn('id', $impostorIds)->pluck('name')->values();
        $caughtNames = $participants->whereIn('id', $caughtIds)->pluck('name')->values();
        $escapedNames = $participants->whereIn('id', $escapedIds)->pluck('name')->values();
        $hasResult = $game->status === 'finished';

        return [
            'type' => 'impostor_game',
            'name' => 'Juego del Impostor',
            'status' => $game->status,
            'status_label' => $this->impostorStatus($game->status),
            'created_at' => $this->date($game->created_at),
            'started_at' => $this->date($game->started_at),
            'finished_at' => $hasResult ? $this->date($game->updated_at) : null,
            'duration_seconds' => $game->started_at && $game->closes_at
                ? (int) $game->started_at->diffInSeconds($game->closes_at)
                : null,
            'participant_count' => $participants->count(),
            'result' => $hasResult ? [
                'winner_label' => $allCaught ? 'Tripulación' : 'Impostores',
                'winner_names' => $allCaught ? [] : $escapedNames->all(),
                'summary' => $allCaught
                    ? 'La tripulación descubrió a todos los impostores.'
                    : 'Uno o más impostores escaparon de la votación.',
                'impostors' => $impostorNames->all(),
                'caught_impostors' => $caughtNames->all(),
                'escaped_impostors' => $escapedNames->all(),
            ] : null,
            'metrics' => [
                'word' => $game->word,
                'clues_submitted' => $game->clues->unique('participant_id')->count(),
                'votes_submitted' => $game->votes->unique('voter_id')->count(),
                'impostor_count' => $impostorIds->count(),
            ],
            'ranking' => $ranking->all(),
        ];
    }

    private function date($value): ?string
    {
        return $value?->toIso8601String();
    }

    private function roomStatus(string $status): string
    {
        return ['draft' => 'Preparada', 'open' => 'Abierta', 'closed' => 'Cerrada', 'archived' => 'Archivada'][$status]
            ?? ucfirst($status);
    }

    private function ecoStatus(string $status): string
    {
        return ['draft' => 'En configuración', 'ready' => 'Preparada', 'active' => 'Activa', 'finished' => 'Finalizada'][$status]
            ?? ucfirst($status);
    }

    private function impostorStatus(string $status): string
    {
        return ['waiting' => 'Esperando inicio', 'playing' => 'Fase de pistas', 'voting' => 'Votación', 'closed' => 'Votación cerrada', 'finished' => 'Finalizada'][$status]
            ?? ucfirst($status);
    }
}
