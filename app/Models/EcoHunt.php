<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcoHunt extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_READY = 'ready';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FINISHED = 'finished';

    public const OPEN_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_READY,
        self::STATUS_ACTIVE,
    ];

    protected $fillable = ['room_id', 'name', 'status', 'duration_seconds', 'started_at', 'ends_at', 'finished_at', 'finished_by', 'reopen_count', 'initial_finished_at', 'reopened_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ends_at' => 'datetime', 'finished_at' => 'datetime', 'initial_finished_at' => 'datetime', 'reopened_at' => 'datetime'];
    }

    public function room() { return $this->belongsTo(Room::class); }
    public function activities() { return $this->belongsToMany(Activity::class, 'eco_hunt_activity')->withPivot('is_active')->withTimestamps(); }
    public function completions() { return $this->hasMany(EcoHuntCompletion::class); }
}
