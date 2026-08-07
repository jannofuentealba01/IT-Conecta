<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpostorVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'voter_id',
        'suspect_id',
    ];

    // Relación con la partida
    public function game()
    {
        return $this->belongsTo(ImpostorGame::class, 'game_id');
    }

    // Relación con el alumno que vota
    public function voter()
    {
        return $this->belongsTo(Participant::class, 'voter_id');
    }

    // Relación con el alumno acusado
    public function suspect()
    {
        return $this->belongsTo(Participant::class, 'suspect_id');
    }
}