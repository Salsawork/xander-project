<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    protected $table = 'order_events';
    protected $fillable = 
    [
        'order_id',
        'event_id',
        'user_id',
        'ticket_id',
        'bank_id',
        'total_payment',
        'bukti_payment',
        'status',
        'type',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
