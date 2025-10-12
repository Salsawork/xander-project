<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Xoco70\LaravelTournaments\Models\Tournament;

class Event extends Model
{
    protected $fillable = [
        'name',
        'image_url',
        'start_date',
        'end_date',
        'location',
        'price_ticket',
        'stock',
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
}
