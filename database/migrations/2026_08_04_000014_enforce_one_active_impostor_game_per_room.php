<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impostor_games', function (Blueprint $table) {
            $table->unsignedTinyInteger('active_marker')->nullable()->after('status');
        });

        DB::table('impostor_games')
            ->whereIn('status', ['playing', 'voting'])
            ->select('room_id')
            ->distinct()
            ->pluck('room_id')
            ->each(function ($roomId): void {
                $keepId = DB::table('impostor_games')
                    ->where('room_id', $roomId)
                    ->whereIn('status', ['playing', 'voting'])
                    ->latest('id')
                    ->value('id');

                DB::table('impostor_games')
                    ->where('room_id', $roomId)
                    ->whereIn('status', ['playing', 'voting'])
                    ->where('id', '!=', $keepId)
                    ->update(['status' => 'finished', 'active_marker' => null]);

                DB::table('impostor_games')->where('id', $keepId)->update(['active_marker' => 1]);
            });

        Schema::table('impostor_games', function (Blueprint $table) {
            $table->unique(['room_id', 'active_marker'], 'impostor_games_one_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('impostor_games', function (Blueprint $table) {
            $table->dropUnique('impostor_games_one_active_unique');
            $table->dropColumn('active_marker');
        });
    }
};
