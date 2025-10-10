<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'quantity',
        'date',
        'start',
        'end',
        'table_number',
        'price',
        'schedule',
    ];


    /**
     * Get the product associated with the cart item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /**
     * Get the venue associated with the cart item.
     */
    public function venue()
    {
        return $this->belongsTo(Venue::class, 'item_id');
    }

    /**
     * Get the sparring schedule associated with the cart item.
     */
    public function sparringSchedule()
    {
        return $this->belongsTo(SparringSchedule::class, 'item_id');
    }
}
