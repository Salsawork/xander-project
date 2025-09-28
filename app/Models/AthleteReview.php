<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteReview extends Model
{
    protected $fillable = ['athlete_id', 'user_id', 'rating', 'comment'];

    public function athlete()
    {
        return $this->belongsTo(User::class, 'athlete_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
