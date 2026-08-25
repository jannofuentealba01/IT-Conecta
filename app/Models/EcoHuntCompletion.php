<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcoHuntCompletion extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['verification_answers' => 'array', 'completed_at' => 'datetime']; }
    public function hunt() { return $this->belongsTo(EcoHunt::class, 'eco_hunt_id'); }
    public function participant() { return $this->belongsTo(Participant::class); }
    public function activity() { return $this->belongsTo(Activity::class); }
}
