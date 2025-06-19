<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participants extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'status',
        'payment_method',
        'payment_status'
    ];

    /**
     * Get the session that the participant joined.
     */
    public function billiardSession(): BelongsTo
    {
        return $this->belongsTo(billiardSession::class, 'session_id');
    }

    /**
     * Get the user that is participating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope a query to only include registered participants.
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }
    
    /**
     * Scope a query to only include attending participants.
     */
    public function scopeAttending($query)
    {
        return $query->where('status', 'attending');
    }
    
    /**
     * Scope a query to only include cancelled participants.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
    
    /**
     * Scope a query to only include participants with pending payment.
     */
    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'pending');
    }
    
    /**
     * Scope a query to only include participants with paid payment.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
    
    /**
     * Scope a query to only include participants with refunded payment.
     */
    public function scopeRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }
}
