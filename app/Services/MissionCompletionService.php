<?php

namespace App\Services;

use App\Models\ActivityCompletion;
use App\Models\Mission;
use App\Models\Participant;
use App\Models\PointTransaction;
use DomainException;
use Illuminate\Support\Facades\DB;

class MissionCompletionService
{
    public function complete(Participant $participant, Mission $mission, string $validationMethod = 'self_report'): array
    {
        return DB::transaction(function () use ($participant, $mission, $validationMethod): array {
            $participant = Participant::whereKey($participant->id)->lockForUpdate()->firstOrFail();
            $mission = Mission::with(['activity', 'room'])
                ->whereKey($mission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $mission->room_id !== (int) $participant->room_id || ! $mission->isAvailable()) {
                throw new DomainException('Esta misión no pertenece a tu sesión o ya no está disponible.');
            }

            if (! $participant->carbonFootprints()->where('is_current', true)->exists()) {
                throw new DomainException('Primero debes calcular tu huella de carbono inicial.');
            }

            $completionDate = today()->toDateString();
            $existing = ActivityCompletion::where('participant_id', $participant->id)
                ->where('activity_id', $mission->activity_id)
                ->whereDate('completion_date', $completionDate)
                ->first();

            if ($existing) {
                return ['completion' => $existing, 'created' => false];
            }

            $completion = ActivityCompletion::create([
                'participant_id' => $participant->id,
                'activity_id' => $mission->activity_id,
                'room_id' => $mission->room_id,
                'completion_date' => $completionDate,
                'points_awarded' => $mission->activity->points,
                'annual_co2_reduction_awarded' => $mission->activity->annual_co2_reduction,
                'validation_method' => $validationMethod,
                'validation_status' => 'approved',
                'completed_at' => now(),
            ]);

            PointTransaction::create([
                'participant_id' => $participant->id,
                'room_id' => $mission->room_id,
                'category' => PointTransaction::CATEGORY_ACTION,
                'source_type' => 'activity_completion',
                'source_id' => $completion->id,
                'source_key' => 'activity-completion-'.$completion->id,
                'points' => $completion->points_awarded,
                'description' => 'Actividad: '.$mission->activity->name,
            ]);

            return ['completion' => $completion, 'created' => true];
        }, 3);
    }
}
