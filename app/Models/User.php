<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'approval_status',
        'approved_at',
        'approved_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
        ];
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'user_activities')
            ->withPivot('points_earned', 'co2_reduced')
            ->withTimestamps();
    }

    public function totalPoints()
    {
        return $this->activities()->sum('user_activities.points_earned');
    }

    public function totalCO2()
    {
        return $this->activities()->sum('user_activities.co2_reduced');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function carbonFootprints()
    {
        return $this->hasMany(TeacherCarbonFootprint::class);
    }

    public function createdActivities()
    {
        return $this->hasMany(Activity::class);
    }
}
