<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BilliardSession extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'venue_id',
        'title',
        'session_code',
        'game_type',
        'skill_level',
        'price',
        'max_participants',
        'date',
        'start_time',
        'end_time',
        'promo_code',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'max_participants' => 'integer'
    ];

    /**
     * Get the venue that owns the session.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the participants for the session.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participants::class, 'session_id');
    }

    /**
     * Generate a unique session code.
     */
    public static function generateSessionCode(): string
    {
        return 'XB' . strtoupper(substr(uniqid(), -8));
    }

    /**
     * Scope a query to only include pending sessions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed sessions.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include cancelled sessions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if session still has available slots.
     */
    public function hasAvailableSlots(): bool
    {
        return $this->participants()->count() < $this->max_participants;
    }
}
