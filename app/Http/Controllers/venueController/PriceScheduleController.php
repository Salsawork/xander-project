<?php

namespace App\Http\Controllers\venueController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Venue;
use App\Models\Table;
use App\Models\PriceSchedule;
use Illuminate\Support\Facades\Auth;

class PriceScheduleController extends Controller
{
    public function create()
    {
        $venueId = Venue::where('user_id', Auth::id())->value('id');
        $tables = Table::where('venue_id', $venueId)->get();

        return view('dash.venue.booking.create-price-schedule', compact('tables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'price'  => 'required',
            'start_time' => 'required',
            'end_time'   => 'required',
            'time_category' => 'required',
            'days'   => 'required|array',
            'tables_applicable' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $validated['venue_id'] = Venue::where('user_id', Auth::id())->value('id');

        PriceSchedule::create($validated);

        return redirect()->route('venue.booking')->with('success', 'Price schedule created successfully.');
    }

    public function destroy(PriceSchedule $priceSchedule)
    {
        $priceSchedule->delete();
        return redirect()->route('venue.booking')->with('success', 'Price schedule deleted successfully.');
    }
}
