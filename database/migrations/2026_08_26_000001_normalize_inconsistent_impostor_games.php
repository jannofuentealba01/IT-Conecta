<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rooms = DB::table('rooms')
            ->select(['id', 'status', 'expires_at'])
            ->get()
            ->keyBy('id');

        DB::table('impostor_games')
            ->where('active_marker', 1)
            ->orderBy('id')
            ->get()
            ->each(function (object $game) use ($rooms): void {
                $room = $rooms->get($game->room_id);

                if (! $this->isConsistent($game, $room)) {
                    DB::table('impostor_games')
                        ->where('id', $game->id)
                        ->update([
                            'status' => 'finished',
                            'active_marker' => null,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // La normalización conserva las partidas como historial y no es reversible.
    }

    private function isConsistent(object $game, ?object $room): bool
    {
        if (! $room || $room->status !== 'open') {
            return false;
        }

        if ($room->expires_at && Carbon::parse($room->expires_at)->lessThanOrEqualTo(now())) {
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

        if (! in_array($game->status, ['playing', 'voting', 'closed'], true)) {
            return false;
        }

        if (! is_string($game->word) || trim($game->word) === '' || ! $game->impostor_id) {
            return false;
        }

        if (! $game->started_at || ! $game->voting_at || ! $game->closes_at || ! $game->results_at) {
            return false;
        }

        $startedAt = Carbon::parse($game->started_at);
        $votingAt = Carbon::parse($game->voting_at);
        $closesAt = Carbon::parse($game->closes_at);
        $resultsAt = Carbon::parse($game->results_at);

        return $startedAt->lessThanOrEqualTo($votingAt)
            && $votingAt->lessThanOrEqualTo($closesAt)
            && $closesAt->lessThanOrEqualTo($resultsAt);
    }
};
