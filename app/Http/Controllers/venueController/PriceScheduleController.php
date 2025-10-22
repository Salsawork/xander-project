<?php

namespace App\Http\Controllers\venueController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Venue;
use App\Models\Table;
use App\Models\PriceSchedule;
use Carbon\Carbon;
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
            'end_time'   => 'required|after:start_time',
            'time_category' => 'required',
            'days'   => 'required|array',
            'tables_applicable' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $validated['venue_id'] = Venue::where('user_id', Auth::id())->value('id');

        PriceSchedule::create($validated);

        return redirect()->route('venue.booking')->with('success', 'Price schedule created successfully.');
    }

    public function edit(PriceSchedule $priceSchedule)
    {
        $venueId = Venue::where('user_id', Auth::id())->value('id');
        $tables = Table::where('venue_id', $venueId)->get();

        return view('dash.venue.booking.edit-price-schedule', compact('priceSchedule', 'tables'));
    }

    public function update(Request $request, PriceSchedule $priceSchedule)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'price'  => 'required',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'time_category' => 'required',
            'days'   => 'required|array',
            'tables_applicable' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $venue = Venue::where('user_id', Auth::id())->firstOrFail();
        $validated['venue_id'] = $venue->id;

        $validated['start_time'] = Carbon::createFromFormat('H:i', $validated['start_time'])->format('H:i');
        $validated['end_time']   = Carbon::createFromFormat('H:i', $validated['end_time'])->format('H:i');

        $priceSchedule->fill($validated);

        $priceSchedule->save();

        $allSchedules = PriceSchedule::where('venue_id', $venue->id)
            ->where('is_active', true)
            ->get(['start_time', 'end_time']);

        if ($allSchedules->isNotEmpty()) {
            $earliestStart = $allSchedules->min('start_time');
            $latestEnd     = $allSchedules->max('end_time');

            $venue->update([
                'operating_hour' => $earliestStart,
                'closing_hour'   => $latestEnd,
            ]);
        }

        return redirect()
            ->route('venue.booking')
            ->with('success', 'Price schedule updated successfully and venue hours synchronized.');
    }

    public function destroy(PriceSchedule $priceSchedule)
    {
        $priceSchedule->delete();
        return redirect()->route('venue.booking')->with('success', 'Price schedule deleted successfully.');
    }
}
