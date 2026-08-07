<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->after('name');
            $table->string('recovery_token', 64)->nullable()->unique()->after('course');
            $table->timestamp('joined_at')->nullable()->after('recovery_token');
            $table->timestamp('last_seen_at')->nullable()->after('joined_at');
            $table->index(['room_id', 'normalized_name']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->text('instructions')->nullable()->after('description');
            $table->string('category')->nullable()->after('instructions');
            $table->string('impact_level')->default('medium')->after('category');
            $table->decimal('annual_co2_reduction', 10, 2)->nullable()->after('co2_impact');
            $table->text('educational_message')->nullable()->after('annual_co2_reduction');
            $table->string('validation_type')->default('self_report')->after('educational_message');
            $table->unsignedSmallInteger('frequency_days')->default(1)->after('validation_type');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['room_id', 'normalized_name']);
            $table->dropUnique(['recovery_token']);
            $table->dropColumn(['normalized_name', 'recovery_token', 'joined_at', 'last_seen_at']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'instructions', 'category', 'impact_level', 'annual_co2_reduction',
                'educational_message', 'validation_type', 'frequency_days',
            ]);
        });
    }
};
