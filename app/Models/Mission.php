<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $table = 'room_activity';

    protected $fillable = [
        'room_id', 'activity_id', 'qr_token', 'is_active', 'opens_at', 'closes_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_active
            && (! $this->opens_at || $this->opens_at->isPast())
            && (! $this->closes_at || $this->closes_at->isFuture())
            && $this->room?->isOpen();
    }
}
