<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'orders';

    protected $fillable = [
        'id',
        'user_id',
        'order_number',
        'total',
        'payment_status',
        'delivery_status',
        'payment_method',
        'file',

    ];

    protected $casts = [
        'total' => 'decimal:2',
        'payment_status' => 'string',
        'delivery_status' => 'string'
    ];

    protected $attributes = [
        'total' => 0,
        'payment_status' => 'pending',
        'delivery_status' => 'pending'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'price', 'subtotal', 'discount')
            ->withTimestamps();
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the sparring sessions associated with the order.
     */
    public function orderSparrings()
    {
        return $this->hasMany(OrderSparring::class);
    }

    public function orderVenues()
    {
        return $this->hasMany(OrderVenue::class);
    }
}
