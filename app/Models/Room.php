<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    // Permitir la asignación masiva de estos campos
    protected $fillable = [
        'user_id',
        'course_id',
        'code',
        'name',
        'status',
        'duration_minutes',
        'opened_at',
        'closed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relación con participantes (aprovechando la estructura)
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function games()
    {
        return $this->hasMany(ImpostorGame::class);
    }

    public function ecoHunts() { return $this->hasMany(EcoHunt::class); }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'room_activity')
            ->withPivot(['qr_token', 'is_active', 'opens_at', 'closes_at'])
            ->withTimestamps();
    }

    public function activityCompletions()
    {
        return $this->hasMany(ActivityCompletion::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open'
            && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
