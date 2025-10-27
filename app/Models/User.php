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
        'status_player',
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
        if (!empty($this->photo_profile)) {
            return asset('images/avatars/' . $this->photo_profile);
            // return 'https://demo-xanders.ptbmn.id/images/avatars/' . $this->photo_profile;
        }
        return null;
    }
    

    /**
     * ====== Tambahan relasi untuk kolom "Event Diikuti" ======
     * Relasi ke semua bracket berdasarkan nama pemain (player_name -> users.name)
     * Catatan: relasi ini mengandalkan kesesuaian NAME. Untuk skema ideal, tambahkan user_id di tabel brackets.
     */
    public function bracketsByName()
    {
        return $this->hasMany(Bracket::class, 'player_name', 'name');
    }

    /**
     * Bracket terbaru yang berkaitan dengan user (berdasarkan ID terbesar).
     */
    public function latestBracket()
    {
        return $this->hasOne(Bracket::class, 'player_name', 'name')->latestOfMany('id');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'user_id');
    }
}
