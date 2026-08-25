<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'instructions',
        'category',
        'impact_level',
        'points',
        'co2_impact',
        'annual_co2_reduction',
        'educational_message',
        'validation_type',
        'frequency_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'co2_impact' => 'decimal:2',
            'annual_co2_reduction' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_activity')
            ->withPivot(['qr_token', 'is_active', 'opens_at', 'closes_at'])
            ->withTimestamps();
    }

    public function completions()
    {
        return $this->hasMany(ActivityCompletion::class);
    }

    public function ecoProfile() { return $this->hasOne(EcoActivityProfile::class); }
    public function ecoHunts() { return $this->belongsToMany(EcoHunt::class, 'eco_hunt_activity')->withPivot('is_active')->withTimestamps(); }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_activities')
            ->withPivot('points_earned', 'co2_reduced')
            ->withTimestamps();
    }
}
