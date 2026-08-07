<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rooms')->where('status', 'draft')->update([
            'status' => 'active',
            'opened_at' => now(),
        ]);

        DB::table('participants')->orderBy('id')->each(function (object $participant): void {
            DB::table('participants')->where('id', $participant->id)->update([
                'normalized_name' => Str::lower(Str::ascii(trim(preg_replace('/\s+/', ' ', $participant->name)))),
                'recovery_token' => $participant->recovery_token ?: Str::random(64),
                'joined_at' => $participant->joined_at ?: $participant->created_at,
                'last_seen_at' => $participant->last_seen_at ?: $participant->updated_at,
            ]);

            if ((int) $participant->points !== 0) {
                DB::table('point_transactions')->insertOrIgnore([
                    'participant_id' => $participant->id,
                    'room_id' => $participant->room_id,
                    'category' => 'learning',
                    'source_type' => 'legacy_participant_points',
                    'source_id' => $participant->id,
                    'source_key' => 'legacy-participant-points-'.$participant->id,
                    'points' => (int) $participant->points,
                    'description' => 'Puntos previos a la unificación del sistema',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        DB::table('activity_participant')
            ->join('participants', 'participants.id', '=', 'activity_participant.participant_id')
            ->select('activity_participant.*', 'participants.room_id')
            ->orderBy('activity_participant.id')
            ->each(function (object $legacy): void {
                $completedAt = $legacy->created_at ?: now();
                $completionDate = date('Y-m-d', strtotime((string) $completedAt));

                DB::table('activity_completions')->insertOrIgnore([
                    'participant_id' => $legacy->participant_id,
                    'activity_id' => $legacy->activity_id,
                    'room_id' => $legacy->room_id,
                    'completion_date' => $completionDate,
                    'points_awarded' => (int) $legacy->points_earned,
                    // El valor histórico no tenía una metodología anual documentada.
                    'annual_co2_reduction_awarded' => null,
                    'validation_method' => 'legacy',
                    'validation_status' => 'approved',
                    'completed_at' => $completedAt,
                    'created_at' => $completedAt,
                    'updated_at' => $legacy->updated_at ?: $completedAt,
                ]);

                DB::table('point_transactions')->insertOrIgnore([
                    'participant_id' => $legacy->participant_id,
                    'room_id' => $legacy->room_id,
                    'category' => 'action',
                    'source_type' => 'legacy_activity_completion',
                    'source_id' => $legacy->id,
                    'source_key' => 'legacy-activity-completion-'.$legacy->id,
                    'points' => (int) $legacy->points_earned,
                    'description' => 'Actividad realizada antes de la unificación del sistema',
                    'created_at' => $completedAt,
                    'updated_at' => $legacy->updated_at ?: $completedAt,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('point_transactions')
            ->where('source_type', 'legacy_activity_completion')
            ->orWhere('source_type', 'legacy_participant_points')
            ->delete();

        DB::table('activity_completions')->where('validation_method', 'legacy')->delete();
    }
};
