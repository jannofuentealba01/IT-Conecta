<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('carbon_footprints', 'current_marker')) {
            Schema::table('carbon_footprints', function (Blueprint $table) {
                $table->unsignedTinyInteger('current_marker')->nullable()->after('is_current');
            });
        }

        DB::table('carbon_footprints')->update(['is_current' => false, 'current_marker' => null]);

        DB::table('carbon_footprints')
            ->select('participant_id', DB::raw('MAX(id) as current_id'))
            ->groupBy('participant_id')
            ->orderBy('participant_id')
            ->each(function (object $footprint): void {
                DB::table('carbon_footprints')->where('id', $footprint->current_id)->update([
                    'is_current' => true,
                    'current_marker' => 1,
                ]);
            });

        Schema::table('carbon_footprints', function (Blueprint $table) {
            $table->unique(['participant_id', 'current_marker'], 'carbon_footprints_one_current');
        });
    }

    public function down(): void
    {
        Schema::table('carbon_footprints', function (Blueprint $table) {
            $table->dropUnique('carbon_footprints_one_current');
            $table->dropColumn('current_marker');
        });
    }
};
