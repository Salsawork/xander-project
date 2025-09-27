<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'brand',
        'level',
        'condition',
        'quantity',
        'sku',
        'images',
        'weight',
        'length',
        'breadth',
        'width',
        'pricing',
        'discount'
    ];

    protected $casts = [
        'images' => 'json',
        'pricing' => 'decimal:0',
        'discount' => 'decimal:2'
    ];

    protected $attributes = [
        'brand' => 'Other',
        'condition' => 'new',
        'quantity' => 0,
        'weight' => 0,
        'length' => 0,
        'breadth' => 0,
        'width' => 0,
        'discount' => 0
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'price', 'subtotal', 'discount')
            ->withTimestamps();
    }
}
