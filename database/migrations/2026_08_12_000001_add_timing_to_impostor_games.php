<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impostor_games', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('impostor_id');
            $table->timestamp('voting_at')->nullable()->after('started_at');
            $table->timestamp('closes_at')->nullable()->after('voting_at');
            $table->timestamp('results_at')->nullable()->after('closes_at');
        });
    }

    public function down(): void
    {
        Schema::table('impostor_games', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'voting_at', 'closes_at', 'results_at']);
        });
    }
};
