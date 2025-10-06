<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'venue_id',
        'opponent_id',
        'date',
        'time_start',
        'time_end',
        'payment_method',
        'total_amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'time_start' => 'datetime',
        'time_end' => 'datetime',
        'total_amount' => 'decimal:2',
    ];
    
    /**
     * Get the user that owns the match history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the venue where the match was played.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
    
    /**
     * Get the opponent user.
     */
    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }
    
    /**
     * Scope a query to only include pending matches.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include completed matches.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    /**
     * Scope a query to only include cancelled matches.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
