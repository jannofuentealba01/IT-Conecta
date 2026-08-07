<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impostor_clues', function (Blueprint $table) {
            $table->unique(['game_id', 'participant_id'], 'impostor_clues_player_unique');
        });

        Schema::table('impostor_votes', function (Blueprint $table) {
            $table->unique(['game_id', 'voter_id'], 'impostor_votes_voter_unique');
        });
    }

    public function down(): void
    {
        Schema::table('impostor_clues', function (Blueprint $table) {
            $table->dropUnique('impostor_clues_player_unique');
        });

        Schema::table('impostor_votes', function (Blueprint $table) {
            $table->dropUnique('impostor_votes_voter_unique');
        });
    }
};
