<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->date('completion_date');
            $table->unsignedInteger('points_awarded');
            $table->decimal('annual_co2_reduction_awarded', 10, 2)->nullable();
            $table->string('validation_method')->default('self_report');
            $table->string('validation_status')->default('approved');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(
                ['participant_id', 'activity_id', 'completion_date'],
                'activity_completions_daily_unique'
            );
            $table->index(['room_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_completions');
    }
};
