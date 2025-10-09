<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi mass assignment.
     */
    protected $fillable = [
        'name',
        'email',
        'photo_profile',
        'password',
        'roles',
        'phone',
        'otp_code',
        'status',
        'firstname',
        'lastname',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast attributes.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * ===== Relationships (opsional, sesuai kebutuhan) =====
     */
    public function athleteDetail()
    {
        return $this->hasOne(AthleteDetail::class);
    }

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

    public function favoriteVenues()
    {
        return $this->belongsToMany(Venue::class, 'favorites', 'user_id', 'venue_id')->withTimestamps();
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Accessor: avatar_url dari kolom photo_profile (URL penuh).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return !empty($this->photo_profile) ? asset($this->photo_profile) : null;
    }
}
