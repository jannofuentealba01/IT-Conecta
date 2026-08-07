<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('status')->default('draft')->after('name');
            $table->timestamp('opened_at')->nullable()->after('status');
            $table->timestamp('closed_at')->nullable()->after('opened_at');
            $table->timestamp('expires_at')->nullable()->after('closed_at');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['status', 'expires_at']);
            $table->dropConstrainedForeignId('course_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['status', 'opened_at', 'closed_at', 'expires_at']);
        });
    }
};
