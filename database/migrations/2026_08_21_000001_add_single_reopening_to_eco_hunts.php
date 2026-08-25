<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eco_hunts', function (Blueprint $table): void {
            $table->unsignedTinyInteger('reopen_count')->default(0)->after('finished_by');
            $table->timestamp('initial_finished_at')->nullable()->after('reopen_count');
            $table->timestamp('reopened_at')->nullable()->after('initial_finished_at');
        });
    }

    public function down(): void
    {
        Schema::table('eco_hunts', function (Blueprint $table): void {
            $table->dropColumn(['reopen_count', 'initial_finished_at', 'reopened_at']);
        });
    }
};
