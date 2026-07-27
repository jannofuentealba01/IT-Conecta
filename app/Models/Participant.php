<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    // Permitir guardar de forma masiva estos campos desde el controlador
    protected $fillable = [
        'room_id',
        'name',
        'course',
    ];

    // Relación de pertenencia a una sala (Room)
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_participant')
                    ->withPivot('points_earned', 'co2_reduced')
                    ->withTimestamps();
    }


    
}