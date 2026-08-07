<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_minutes')->default(120)->after('status');
        });

        DB::table('rooms')->where('status', 'active')->update(['status' => 'open']);

        $teacherId = DB::table('users')
            ->whereIn('rol', ['admin', 'profesor'])
            ->orderBy('id')
            ->value('id');

        if (! $teacherId) {
            return;
        }

        DB::table('rooms')->whereNull('user_id')->orderBy('id')->each(function (object $room) use ($teacherId): void {
            $courseName = $room->name ?: 'Curso histórico';
            $courseId = DB::table('courses')
                ->where('user_id', $teacherId)
                ->where('name', $courseName)
                ->value('id');

            if (! $courseId) {
                $courseId = DB::table('courses')->insertGetId([
                    'user_id' => $teacherId,
                    'name' => $courseName,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('rooms')->where('id', $room->id)->update([
                'user_id' => $teacherId,
                'course_id' => $courseId,
            ]);
        });
    }

    public function down(): void
    {
        DB::table('rooms')->where('status', 'open')->update(['status' => 'active']);

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};
