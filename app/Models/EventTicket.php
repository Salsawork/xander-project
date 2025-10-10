<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicket extends Model
{
    protected $table = 'event_tickets';

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'stock',
        'description',
    ];
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
