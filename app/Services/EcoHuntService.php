<?php

namespace App\Services;

use App\Models\EcoActivityProfile;
use App\Models\EcoHunt;
use App\Models\EcoHuntCompletion;
use App\Models\Participant;
use App\Models\PointTransaction;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class EcoHuntService
{
    public function refresh(EcoHunt $hunt): EcoHunt
    {
        if ($hunt->status === EcoHunt::STATUS_ACTIVE && $hunt->ends_at?->lte(now())) {
            $hunt->update([
                'status' => EcoHunt::STATUS_FINISHED,
                'finished_at' => $hunt->ends_at,
                'finished_by' => 'automatic',
            ]);
            $hunt->refresh();
        }

        return $hunt;
    }

    public function start(EcoHunt $hunt): EcoHunt
    {
        return DB::transaction(function () use ($hunt): EcoHunt {
            $hunt = EcoHunt::whereKey($hunt->id)->lockForUpdate()->firstOrFail();
            if ($hunt->status !== EcoHunt::STATUS_READY) {
                throw new DomainException('Solo una EcoBúsqueda preparada puede iniciarse.');
            }
            if (! $hunt->activities()->wherePivot('is_active', true)->exists()) {
                throw new DomainException('Selecciona al menos una actividad antes de iniciar.');
            }

            $startedAt = now();
            $hunt->update([
                'status' => EcoHunt::STATUS_ACTIVE, 'started_at' => $startedAt,
                'ends_at' => $startedAt->copy()->addSeconds($hunt->duration_seconds),
                'finished_at' => null, 'finished_by' => null,
            ]);

            return $hunt->refresh();
        });
    }

    public function finish(EcoHunt $hunt): EcoHunt
    {
        return DB::transaction(function () use ($hunt): EcoHunt {
            $hunt = EcoHunt::whereKey($hunt->id)->lockForUpdate()->firstOrFail();
            $hunt = $this->refresh($hunt);
            if ($hunt->status === EcoHunt::STATUS_ACTIVE) {
                $hunt->update(['status' => EcoHunt::STATUS_FINISHED, 'finished_at' => now(), 'finished_by' => 'teacher']);
            }

            return $hunt->refresh();
        });
    }

    public function reopen(EcoHunt $hunt): EcoHunt
    {
        return DB::transaction(function () use ($hunt): EcoHunt {
            $hunt = EcoHunt::whereKey($hunt->id)->lockForUpdate()->firstOrFail();

            if ($hunt->status !== EcoHunt::STATUS_FINISHED) {
                throw new DomainException('Solo una EcoBúsqueda finalizada puede reabrirse.');
            }
            if ((int) $hunt->reopen_count >= 1) {
                throw new DomainException('Esta EcoBúsqueda ya utilizó su única reapertura.');
            }
            if ((int) EcoHunt::where('room_id', $hunt->room_id)->latest('id')->value('id') !== (int) $hunt->id) {
                throw new DomainException('Solo se puede reabrir la EcoBúsqueda más reciente de la sala.');
            }
            if (EcoHunt::where('room_id', $hunt->room_id)->whereKeyNot($hunt->id)->whereIn('status', EcoHunt::OPEN_STATUSES)->exists()) {
                throw new DomainException('Ya existe otra EcoBúsqueda preparada o activa en esta sala.');
            }
            if (! $hunt->room->isOpen()) {
                throw new DomainException('La sala debe estar abierta para reabrir la EcoBúsqueda.');
            }

            $reopenedAt = now();
            $hunt->update([
                'status' => EcoHunt::STATUS_ACTIVE,
                'reopen_count' => 1,
                'initial_finished_at' => $hunt->finished_at,
                'reopened_at' => $reopenedAt,
                'ends_at' => $reopenedAt->copy()->addMinutes(5),
                'finished_at' => null,
                'finished_by' => null,
            ]);

            return $hunt->refresh();
        });
    }

    public function complete(Participant $participant, EcoHunt $hunt, EcoActivityProfile $profile, array $answers): EcoHuntCompletion
    {
        $hunt = $this->refresh($hunt);
        if ($hunt->status !== EcoHunt::STATUS_ACTIVE) {
            throw new DomainException('La EcoBúsqueda no está activa o ya finalizó.');
        }

        return DB::transaction(function () use ($participant, $hunt, $profile, $answers): EcoHuntCompletion {
            $participant = Participant::whereKey($participant->id)->lockForUpdate()->firstOrFail();
            $hunt = EcoHunt::whereKey($hunt->id)->lockForUpdate()->firstOrFail();
            if ((int) $participant->room_id !== (int) $hunt->room_id || $hunt->status !== EcoHunt::STATUS_ACTIVE || $hunt->ends_at?->lte(now())) {
                throw new DomainException('La EcoBúsqueda no está activa o ya finalizó.');
            }
            if (! $profile->is_active || ! $profile->activity?->is_active || ! $hunt->activities()
                ->where('activities.id', $profile->activity_id)->wherePivot('is_active', true)->exists()) {
                throw new DomainException('Este QR no pertenece a la actividad que estás realizando.');
            }
            foreach ($profile->verification_questions as $question) {
                if (($answers[$question['id']] ?? null) !== $question['correct']) {
                    throw new DomainException('Una o más respuestas no coinciden con la actividad. Revísalas e inténtalo nuevamente.');
                }
            }

            try {
                $completion = EcoHuntCompletion::create([
                    'eco_hunt_id' => $hunt->id, 'room_id' => $hunt->room_id,
                    'participant_id' => $participant->id, 'activity_id' => $profile->activity_id,
                    'points_awarded' => $profile->game_points,
                    'verification_type' => $profile->activity_type === 'declared' ? 'declaration' : 'quiz',
                    'verification_answers' => $answers, 'completed_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if (EcoHuntCompletion::where('eco_hunt_id', $hunt->id)->where('participant_id', $participant->id)
                    ->where('activity_id', $profile->activity_id)->exists()) {
                    throw new DomainException('Ya completaste este QR en esta EcoBúsqueda.');
                }
                throw $exception;
            }

            PointTransaction::create([
                'participant_id' => $participant->id, 'room_id' => $hunt->room_id,
                'category' => PointTransaction::CATEGORY_ACTION, 'source_type' => 'eco_hunt_completion',
                'source_id' => $completion->id, 'source_key' => 'eco-hunt-completion-'.$completion->id,
                'points' => $profile->game_points, 'description' => 'EcoBúsqueda: '.$profile->activity->name,
            ]);

            return $completion;
        }, 3);
    }

    public function ranking(EcoHunt $hunt)
    {
        return Participant::where('participants.room_id', $hunt->room_id)
            ->leftJoin('eco_hunt_completions as ehc', function ($join) use ($hunt): void {
                $join->on('participants.id', '=', 'ehc.participant_id')->where('ehc.eco_hunt_id', '=', $hunt->id);
            })
            ->select('participants.id', 'participants.name')
            ->selectRaw('COALESCE(SUM(ehc.points_awarded), 0) as points')
            ->selectRaw('COUNT(ehc.id) as completed_count')
            ->selectRaw('MAX(ehc.completed_at) as score_reached_at')
            ->groupBy('participants.id', 'participants.name')
            ->orderByDesc('points')->orderBy('score_reached_at')->orderBy('participants.id')->get();
    }
}
