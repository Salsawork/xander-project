<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderSparring;
use App\Models\AthleteDetail;

class SparringSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'athlete_id',
        'date',
        'start_time',
        'end_time',
        'is_booked',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_booked' => 'boolean',
    ];

    /**
     * Get the athlete that owns the schedule.
     */
    public function athlete()
    {
        return $this->belongsTo(User::class, 'athlete_id');
    }

    /**
     * Get the athlete detail associated with the athlete.
     */
    public function athleteDetail()
    {
        return $this->hasOneThrough(
            AthleteDetail::class,
            User::class,
            'id', // Foreign key on users table...
            'user_id', // Foreign key on athlete_details table...
            'athlete_id', // Local key on sparring_schedules table...
            'id' // Local key on users table...
        );
    }

    /**
     * Get the order sparring associated with the schedule.
     */
    public function orderSparring()
    {
        return $this->hasOne(OrderSparring::class, 'schedule_id');
    }

    /**
     * Format time range for display
     */
    public function getTimeRangeAttribute()
    {
        return date('H:i', strtotime($this->start_time)) . '-' . date('H:i', strtotime($this->end_time));
    }
}
