<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\Venue;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $now = now();

        // Update voucher kadaluarsa jadi inactive
        Voucher::where('end_date', '<', $now)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Ambil status dari request
        $status = $request->get('status', 'all');

        // Base query
        if ($status === 'all') {
            $query = Voucher::with('venue'); // semua voucher tanpa filter
        } elseif ($status === 'ended') {
            $query = Voucher::with('venue')->where('end_date', '<', $now);
        } elseif ($status === 'inactive') {
            $query = Voucher::with('venue')->where('is_active', false);
        } else {
            // default: hanya ambil voucher yang aktif
            $query = Voucher::with('venue')->where('is_active', true);
        }

        // Filter status spesifik
        if ($status === 'ongoing') {
            $query->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now);
        } elseif ($status === 'upcoming') {
            $query->where('start_date', '>', $now);
        }

        // 🔍 Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('venue', function ($venueQuery) use ($search) {
                        $venueQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Custom order untuk all → ongoing, upcoming, ended, inactive
        if ($status === 'all') {
            $query->orderByRaw("
            CASE
                WHEN is_active = 1 AND start_date <= ? AND end_date >= ? THEN 1  -- ongoing
                WHEN is_active = 1 AND start_date > ? THEN 2                      -- upcoming
                WHEN end_date < ? THEN 3                                          -- ended
                WHEN is_active = 0 THEN 4                                         -- inactive
                ELSE 5
            END, created_at DESC
        ", [$now, $now, $now, $now]);
        } else {
            $query->latest();
        }

        // Paginate
        $vouchers = $query->paginate(10);

        // Tambah status ke setiap voucher
        $vouchers->getCollection()->transform(function ($voucher) use ($now) {
            if ($voucher->end_date < $now) {
                $voucher->status = 'ended';
            } elseif (!$voucher->is_active) {
                $voucher->status = 'inactive';
            } elseif ($voucher->start_date > $now) {
                $voucher->status = 'upcoming';
            } else {
                $voucher->status = 'ongoing';
            }
            return $voucher;
        });


        // Hitung jumlah untuk badge
        $counts = [
            'all'      => Voucher::count(),
            'ongoing'  => Voucher::where('is_active', true)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->count(),
            'upcoming' => Voucher::where('is_active', true)->where('start_date', '>', $now)->count(),
            'ended'    => Voucher::where('end_date', '<', $now)->count(),
            'inactive' => Voucher::where('is_active', false)->count(),
        ];

        return view('dash.admin.promo.promo', compact('vouchers', 'counts', 'status'));
    }







    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $venues = \App\Models\Venue::all();
        return view('dash.admin.promo.create', compact('venues'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'venue_id'           => 'required|exists:venues,id',
            'name'               => 'required|string|max:255',
            'code'               => 'required|string|max:50|unique:vouchers,code',
            'type'               => 'required|string|in:percentage,fixed_amount,free_time',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount'    => 'nullable|numeric|min:0',
            'minimum_purchase'   => 'nullable|numeric|min:0',
            'quota'              => 'required|integer|min:1',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'is_active'          => 'boolean',
        ]);

        Voucher::create($request->all());

        return redirect()->route('promo.index')->with('success', 'Voucher created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $voucher = Voucher::findOrFail($id);
        return view('promo.show', compact('voucher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $voucher = Voucher::findOrFail($id);
        $venues = Venue::all(); // ambil semua venue

        return view('dash.admin.promo.edit', compact('voucher', 'venues'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);

        $request->validate([
            'venue_id'           => 'required|exists:venues,id',
            'name'               => 'required|string|max:255',
            'code'               => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'type'               => 'required|string|in:percentage,fixed_amount,free_time',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount'    => 'nullable|numeric|min:0',
            'minimum_purchase'   => 'nullable|numeric|min:0',
            'quota'              => 'required|integer|min:1',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'is_active'          => 'boolean',
        ]);

        $voucher->update($request->all());

        return redirect()->route('promo.index')->with('success', 'Voucher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();

        return redirect()->route('promo.index')->with('success', 'Voucher deleted successfully.');
    }
}
