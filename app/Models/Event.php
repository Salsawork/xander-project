<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Xoco70\LaravelTournaments\Models\Tournament;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'name',
        'image_url',
        'start_date',
        'end_date',
        'location',
        'price_ticket',
        'price_ticket_player',
        'stock',
        'player_slots',
        'game_types',
        'description',
        'total_prize_money',
        'champion_prize',
        'runner_up_prize',
        'third_place_prize',
        'match_style',
        'finals_format',
        'divisions',
        'social_media_handle',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_prize_money' => 'decimal:2',
        'champion_prize' => 'decimal:2',
        'runner_up_prize' => 'decimal:2',
        'third_place_prize' => 'decimal:2',
    ];

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'Upcoming');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'Ongoing');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'Ended');
    }

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function getSlugAttribute()
    {
        return str_replace(' ', '-', strtolower($this->name));
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class, 'event_id');
    }

    public function tickets()
    {
        return $this->hasMany(EventTicket::class, 'event_id');
    }

    /**
     * Refresh status semua event berdasarkan tanggal start/end HARI INI (timezone app).
     * - Upcoming  : today < start_date
     * - Ongoing   : start_date <= today <= end_date
     * - Ended     : today > end_date
     */
    public static function refreshStatuses(): void
    {
        $today = Carbon::today();

        // Ended: today > end_date
        static::whereDate('end_date', '<', $today)->where('status', '!=', 'Ended')->update(['status' => 'Ended']);

        // Ongoing: start_date <= today <= end_date
        static::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', '!=', 'Ongoing')
            ->update(['status' => 'Ongoing']);

        // Upcoming: today < start_date
        static::whereDate('start_date', '>', $today)
            ->where('status', '!=', 'Upcoming')
            ->update(['status' => 'Upcoming']);
    }
}
