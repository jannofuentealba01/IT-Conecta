<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eco_hunts', function (Blueprint $table): void {
            $table->enum('status', ['draft', 'ready', 'active', 'finished'])
                ->default('draft')
                ->change();
        });

        // En la versión anterior, "draft" significaba que la búsqueda ya
        // estaba guardada y esperando al profesor; conceptualmente es ready.
        DB::table('eco_hunts')->where('status', 'draft')->update(['status' => 'ready']);
    }

    public function down(): void
    {
        DB::table('eco_hunts')->where('status', 'ready')->update(['status' => 'draft']);

        Schema::table('eco_hunts', function (Blueprint $table): void {
            $table->enum('status', ['draft', 'active', 'finished'])
                ->default('draft')
                ->change();
        });
    }
};
