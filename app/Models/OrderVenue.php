<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVenue extends Model
{
    protected $fillable = [
        'order_id',
        'venue_id',
        'schedule_id',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function schedule()
    {
        return $this->belongsTo(VenueSchedule::class, 'schedule_id');
    }
}
