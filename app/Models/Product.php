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
        'stock',
        'sku',
        'images',
        'weight',
        'length',
        'breadth',
        'width',
        'pricing',
        'discount',
    ];

    protected $casts = [
        // pakai 'array' agar selalu dapat array PHP
        'images'   => 'array',
        'pricing'  => 'decimal:0',
        'discount' => 'decimal:2',
    ];

    protected $attributes = [
        'brand'     => 'Other',
        'condition' => 'new',
        'stock'     => 0,
        'weight'    => 0,
        'length'    => 0,
        'breadth'   => 0,
        'width'     => 0,
        'discount'  => 0,
    ];

    public function category()
    {
        // withDefault mencegah null error di Blade (name → "-")
        return $this->belongsTo(Categories::class, 'category_id')->withDefault([
            'name' => '-',
        ]);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('stock', 'price', 'subtotal', 'discount')
            ->withTimestamps();
    }

    /**
     * URL gambar pertama yang valid untuk tampilan.
     */
    public function getFirstImageUrlAttribute(): string
    {
        $img = is_array($this->images) && !empty($this->images) ? ($this->images[0] ?? null) : null;
        if (!$img) {
            return 'https://placehold.co/600x400';
        }

        if (preg_match('/^https?:\/\//i', $img)) {
            return $img; // sudah full URL
        }

        $filename = basename($img);
        // Selaraskan dengan tempat upload di controller (public/demo-xanders/images/products)
        return asset('demo-xanders/images/products/' . $filename);
    }

    public function getHasStockAttribute(): bool
    {
        return (int) $this->stock > 0;
    }
}
