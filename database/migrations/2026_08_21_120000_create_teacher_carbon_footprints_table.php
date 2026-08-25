<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_carbon_footprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('initial_kg_co2e_year', 12, 2);
            $table->json('answers');
            $table->string('calculator_version')->default('1.0');
            $table->boolean('is_current')->default(true);
            $table->unsignedTinyInteger('current_marker')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_current']);
            $table->unique(['user_id', 'current_marker'], 'teacher_footprints_one_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_carbon_footprints');
    }
};
