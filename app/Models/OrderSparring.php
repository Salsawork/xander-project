<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSparring extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'athlete_id',
        'schedule_id',
        'price',
    ];

    /**
     * Get the order that owns the sparring.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the athlete for this sparring session.
     */
    public function athlete()
    {
        return $this->belongsTo(User::class, 'athlete_id');
    }

    /**
     * Get the schedule for this sparring session.
     */
    public function schedule()
    {
        return $this->belongsTo(SparringSchedule::class, 'schedule_id');
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
            'athlete_id', // Local key on order_sparrings table...
            'id' // Local key on users table...
        );
    }
}
