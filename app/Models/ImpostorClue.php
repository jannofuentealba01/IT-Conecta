<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpostorClue extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'participant_id',
        'clue',
    ];

    // Relación con la partida
    public function game()
    {
        return $this->belongsTo(ImpostorGame::class, 'game_id');
    }

    // Relación con el alumno que envió la pista
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }
}