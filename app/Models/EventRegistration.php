<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id', 'user_id', 'bank_id', 'registration_number', 'bukti_payment', 'slot', 'price', 'total_payment', 'status',
    ];

    public function event() {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function ticket() {
        return $this->belongsTo(EventTicket::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
