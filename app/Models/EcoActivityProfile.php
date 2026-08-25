<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcoActivityProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['verification_questions' => 'array', 'is_active' => 'boolean'];
    }

    public function activity() { return $this->belongsTo(Activity::class); }
}
