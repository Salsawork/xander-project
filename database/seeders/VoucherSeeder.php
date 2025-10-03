<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        $vouchers = [
            // Voucher untuk venue_id 1
            [
                'venue_id' => 1,
                'name' => 'Diskon Awal Bulan',
                'code' => 'DISKON10',
                'type' => 'percentage',
                'discount_percentage' => 10,
                'discount_amount' => null,
                'minimum_purchase' => 100000,
                'quota' => 50,
                'claimed' => 0,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonth(),
                'is_active' => true
            ],
            [
                'venue_id' => 1,
                'name' => 'Potongan Langsung',
                'code' => 'POTONG20K',
                'type' => 'fixed_amount',
                'discount_percentage' => null,
                'discount_amount' => 20000,
                'minimum_purchase' => 150000,
                'quota' => 30,
                'claimed' => 0,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(2),
                'is_active' => true
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}