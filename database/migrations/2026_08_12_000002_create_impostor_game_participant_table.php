<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impostor_game_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('impostor_games')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['game_id', 'participant_id'], 'impostor_game_participant_unique');
        });

        DB::table('impostor_games')->whereNotNull('impostor_id')->orderBy('id')->each(function ($game): void {
            DB::table('impostor_game_participant')->insertOrIgnore([
                'game_id' => $game->id,
                'participant_id' => $game->impostor_id,
                'created_at' => $game->created_at,
                'updated_at' => $game->updated_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impostor_game_participant');
    }
};
