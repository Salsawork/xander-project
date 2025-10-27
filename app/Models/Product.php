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
        'images'   => 'array',      // selalu jadi array PHP
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

    protected $appends = [
        'first_image_url',
        'image_urls',
    ];

    public function category()
    {
        // withDefault mencegah null error di Blade
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
     * Normalisasi single path/URL jadi URL absolut ke folder public/images/products
     * - Jika sudah http(s) → kembalikan apa adanya
     * - Jika string relatif/filename → jadikan asset('images/products/<filename>')
     */
    protected function normalizeImagePath(?string $path): ?string
    {
        if (!$path || !is_string($path)) return null;
        $path = trim($path);
        if ($path === '') return null;

        // Sudah URL absolut
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Ambil filename-nya saja (antisipasi path lain)
        $filename = basename($path);
        if ($filename === '' || $filename === '/' || $filename === '.') {
            return null;
        }

        // Jadikan URL absolut dari aplikasi (CMS)
        return asset('images/products/' . $filename);
    }

    /**
     * Array URL gambar absolut (sudah dinormalisasi)
     */
    public function getImageUrlsAttribute(): array
    {
        $out = [];
        $list = $this->images;

        // Antisipasi bila images di DB tersimpan sebagai JSON string
        if (is_string($list)) {
            $decoded = json_decode($list, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $list = $decoded;
            } else {
                $list = [$list];
            }
        }

        if (!is_array($list)) $list = [];

        foreach ($list as $item) {
            $url = $this->normalizeImagePath(is_string($item) ? $item : '');
            if ($url && !in_array($url, $out, true)) {
                $out[] = $url;
            }
        }

        // Jika kosong, coba dari accessor first image lama (fallback)
        if (empty($out)) {
            $fallback = $this->getFirstImageUrlAttribute();
            if ($fallback && stripos($fallback, 'placehold.co') === false) {
                $out[] = $fallback;
            }
        }

        // Jika tetap kosong → masukkan placeholder
        if (empty($out)) {
            $out[] = 'https://placehold.co/800x800?text=No+Image';
        }

        return array_values($out);
    }

    /**
     * URL gambar pertama (absolut). Fallback ke placeholder jika kosong.
     */
    public function getFirstImageUrlAttribute(): string
    {
        // Ambil elemen pertama dari image_urls
        $arr = $this->getImageUrlsAttribute();
        return $arr[0] ?? 'https://placehold.co/800x800?text=No+Image';
    }

    public function getHasStockAttribute(): bool
    {
        return (int) $this->stock > 0;
    }
}
