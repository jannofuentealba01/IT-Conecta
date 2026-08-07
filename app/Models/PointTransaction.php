<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    public const CATEGORY_ACTION = 'action';

    public const CATEGORY_LEARNING = 'learning';

    public const CATEGORY_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'participant_id', 'room_id', 'category', 'source_type',
        'source_id', 'source_key', 'points', 'description',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
