<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable fields — sesuaikan dengan kolom yang ADA di tabel.
     */
    protected $fillable = [
        'name',
        'email',
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
     * Attribute casts.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * ===== Relationships (biarkan seperti sebelumnya jika dipakai) =====
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
        return $this->belongsToMany(Venue::class, 'favorites', 'user_id', 'venue_id')
            ->withTimestamps();
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }    /**
     * ====== Accessor: avatar_url (tanpa kolom DB) ======
     * Kita cari file di storage: public/avatars/{id}.(jpg|jpeg|png|webp)
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $id = $this->getKey();
        $exts = ['jpg', 'jpeg', 'png', 'webp'];
        foreach ($exts as $ext) {
            $path = "avatars/{$id}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                return Storage::url($path);
            }
        }
        return null;
    }
}
