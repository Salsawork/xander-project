<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    protected $table = 'order_events';

    protected $fillable = [
        'order_number',
        'user_id',
        'event_id',
        'ticket_id',      // event_tickets.id
        'bank_id',        // mst_bank.id_bank
        'total_payment',
        'bukti_payment',
        'status',
        // ===== field baru =====
        'no_rekening',
    ];

    protected $casts = [
        'total_payment' => 'decimal:2',
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
        return $this->belongsTo(EventTicket::class, 'ticket_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id_bank');
    }
}
