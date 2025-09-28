<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'roles',
        'phone',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Get the athlete detail associated with the user.
     */
    public function athleteDetail()
    {
        return $this->hasOne(AthleteDetail::class);
    }

    /**
     * Get the sparring schedules for the user (if athlete).
     */
    public function sparringSchedules()
    {
        return $this->hasMany(SparringSchedule::class, 'athlete_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'athlete_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

}
