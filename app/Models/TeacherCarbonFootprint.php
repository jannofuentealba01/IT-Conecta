<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherCarbonFootprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'initial_kg_co2e_year', 'answers',
        'calculator_version', 'is_current', 'current_marker',
    ];

    protected function casts(): array
    {
        return [
            'initial_kg_co2e_year' => 'decimal:2',
            'answers' => 'array',
            'is_current' => 'boolean',
            'current_marker' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
