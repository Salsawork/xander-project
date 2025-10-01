<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::latest()->paginate(10); // ambil data voucher dengan pagination
       return view('dash.admin.promo.promo', compact('vouchers'));
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
            'discount_percentage'=> 'nullable|numeric|min:0|max:100',
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
        return view('dash.admin.promo.edit', compact('voucher'));
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
            'discount_percentage'=> 'nullable|numeric|min:0|max:100',
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
