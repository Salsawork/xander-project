<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

class Bracket extends Model
{
    protected $fillable = [
        'event_id',
        'player_name',
        'round',
        'position',
        'next_match_position',
        'is_winner'
    ];

    protected $casts = [
        'is_winner' => 'boolean',
        'round' => 'integer',
        'position' => 'integer',
        'next_match_position' => 'integer',
    ];

    /**
     * Relasi ke model Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope untuk pemain berdasarkan event
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope untuk round tertentu
     */
    public function scopeForRound($query, $round)
    {
        return $query->where('round', $round);
    }

    /**
     * Scope untuk pemenang
     */
    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }
}
