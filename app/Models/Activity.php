<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Activity extends Model
{
    
    protected $fillable = [
        'name',
        'description',
        'points',
        'co2_impact'
    ];

        public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }


}

