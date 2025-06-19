<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'code',
        'type',
        'discount_percentage',
        'discount_amount',
        'minimum_purchase',
        'quota',
        'claimed',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'minimum_purchase' => 'decimal:2',
        'discount_amount' => 'decimal:2'
    ];

    // Relasi ke venue
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    // Cek apakah voucher masih bisa digunakan
    public function isUsable()
    {
        $now = now();
        return $this->is_active 
            && $this->claimed < $this->quota
            && $this->start_date <= $now
            && $this->end_date >= $now;
    }

    // Hitung potongan harga
    public function calculateDiscount($amount)
    {
        if ($this->type === 'percentage') {
            return $amount * ($this->discount_percentage / 100);
        } elseif ($this->type === 'fixed_amount') {
            return $this->discount_amount;
        } elseif ($this->type === 'free_time') {
            // Sesuaikan dengan logika free time
            return 0;
        }
        return 0;
    }
}