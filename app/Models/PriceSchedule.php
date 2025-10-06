<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Venue;

class PriceSchedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'venue_id',
        'name',
        'start_time',
        'end_time',
        'days',
        'price',
        'is_active',
        'tables_applicable',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'tables_applicable' => 'array',
        'days' => 'array',
    ];

    /**
     * Get the venue that owns the price schedule.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
