<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpostorGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'word',
        'status',
        'active_marker',
        'impostor_id',
    ];

    // Relación con la sala
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relación con el participante elegido como impostor
    public function impostor()
    {
        return $this->belongsTo(Participant::class, 'impostor_id');
    }

    // Relación con todas las pistas enviadas en esta partida
    public function clues()
    {
        return $this->hasMany(ImpostorClue::class, 'game_id');
    }

    // Relación con todos los votos realizados en esta partida
    public function votes()
    {
        return $this->hasMany(ImpostorVote::class, 'game_id');
    }
}
