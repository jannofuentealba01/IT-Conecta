<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id', 'activity_id', 'room_id', 'completion_date',
        'points_awarded', 'annual_co2_reduction_awarded',
        'validation_method', 'validation_status', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completion_date' => 'date',
            'annual_co2_reduction_awarded' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
