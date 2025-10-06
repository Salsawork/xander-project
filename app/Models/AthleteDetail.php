<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SparringSchedule;

class AthleteDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'handicap',
        'experience_years',
        'specialty',
        'location',
        'bio',
        'price_per_session',
        'image',
    ];

    /**
     * Get the user that owns the athlete detail.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedules for the athlete.
     */
    public function schedules()
    {
        return $this->hasMany(SparringSchedule::class, 'athlete_id', 'user_id');
    }
}
