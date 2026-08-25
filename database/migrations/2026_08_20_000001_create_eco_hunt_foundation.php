<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eco_activity_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->enum('activity_type', ['immediate', 'declared']);
            $table->string('icon', 20);
            $table->string('location_suggestion');
            $table->json('verification_questions');
            $table->string('standard_unit');
            $table->decimal('standard_quantity', 12, 6)->nullable();
            $table->text('baseline_scenario')->nullable();
            $table->text('action_scenario')->nullable();
            $table->decimal('emission_factor', 14, 8)->nullable();
            $table->string('emission_factor_unit')->nullable();
            $table->string('factor_source')->nullable();
            $table->string('factor_version')->nullable();
            $table->decimal('avoided_co2e_standard', 14, 8)->nullable();
            $table->enum('impact_confidence', ['A', 'B', 'C']);
            $table->unsignedTinyInteger('game_points');
            $table->string('qr_token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('eco_hunts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('EcoBúsqueda');
            $table->enum('status', ['draft', 'active', 'finished'])->default('draft');
            $table->unsignedSmallInteger('duration_seconds')->default(900);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->enum('finished_by', ['automatic', 'teacher'])->nullable();
            $table->timestamps();
            $table->index(['room_id', 'status']);
        });

        Schema::create('eco_hunt_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eco_hunt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['eco_hunt_id', 'activity_id'], 'eco_hunt_activity_unique');
        });

        Schema::create('eco_hunt_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eco_hunt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('points_awarded');
            $table->string('verification_type');
            $table->json('verification_answers')->nullable();
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->unique(['eco_hunt_id', 'participant_id', 'activity_id'], 'eco_hunt_completion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eco_hunt_completions');
        Schema::dropIfExists('eco_hunt_activity');
        Schema::dropIfExists('eco_hunts');
        Schema::dropIfExists('eco_activity_profiles');
    }
};
