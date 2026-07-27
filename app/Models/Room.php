<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    // Permitir la asignación masiva de estos campos
    protected $fillable = [
        'code',
        'name',
    ];

    // Relación con participantes (aprovechando la estructura)
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}