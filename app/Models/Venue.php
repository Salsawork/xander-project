<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Carbon\Carbon;

class Venue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'description',
        'date',
        'operating_hour',
        'closing_hour',
        'rating',
        'price',
        'images',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'operating_hour' => 'datetime:H:i',
        'closing_hour' => 'datetime:H:i',
        'rating' => 'decimal:2',
        'images'   => 'json',
    ];

    /**
     * Get the user that owns the venue.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priceSchedules()
    {
        return $this->hasMany(PriceSchedule::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'bookings')
            ->withPivot(['price', 'table_id', 'user_id', 'booking_date', 'status', 'start_time', 'end_time'])
            ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function getOperatingHourAttribute($value)
    {
        return $value ? Carbon::createFromFormat('H:i:s', $value) : null;
    }

    public function getClosingHourAttribute($value)
    {
        return $value ? Carbon::createFromFormat('H:i:s', $value) : null;
    }
}
