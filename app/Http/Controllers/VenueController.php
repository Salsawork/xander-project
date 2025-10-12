<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\BilliardSession;
use App\Models\Booking;
use App\Models\CartItem;
use App\Models\PriceSchedule;
use App\Models\Table;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $favorites = collect(explode(',', $request->favorites ?? ''))
            ->filter()
            ->map(fn($id) => (int) $id)
            ->toArray();

        $venues = Venue::query()
            ->addSelect([
                'price' => PriceSchedule::select('price')
                    ->whereColumn('venue_id', 'venues.id')
                    ->where('is_active', true)
                    ->orderBy('price', 'asc')
                    ->limit(1)
            ])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('address', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            })
            ->when(!empty($favorites), function ($q) use ($favorites) {
                $ids = implode(',', $favorites);
                $q->orderByRaw("FIELD(id, $ids) DESC");
            })
            ->paginate(5)
            ->withQueryString();

        $cartProducts = collect();
        $cartVenues = collect();
        $cartSparrings = collect();

        if (auth()->check()) {
            $userId = auth()->id();

            $cartProducts = CartItem::with('product')
                ->where('user_id', $userId)
                ->where('item_type', 'product')
                ->get()
                ->map(fn($item) => [
                    'cart_id'  => $item->id,
                    'product_id' => $item->product?->id,
                    'name'     => $item->product?->name,
                    'brand'    => $item->product?->brand,
                    'category' => $item->product?->category?->name ?? '-',
                    'price'    => $item->product?->pricing,
                    'quantity' => $item->quantity,
                    'total'    => $item->quantity * ($item->product?->pricing ?? 0),
                    'discount' => $item->product?->discount ?? 0,
                    'images'    => $item->product?->images[0] ?? null,
                ]);

            $cartVenues = CartItem::with('venue')
                ->where('user_id', $userId)
                ->where('item_type', 'venue')
                ->get()
                ->map(fn($item) => [
                    'cart_id' => $item->id,
                    'venue_id' => $item->venue?->id,
                    'name'     => $item->venue?->name,
                    'address'  => $item->venue?->address ?? '-',
                    'date'     => $item->date,
                    'start'    => $item->start,
                    'end'      => $item->end,
                    'table'    => $item->table_number,
                    'price'    => $item->price,
                    'duration' => $item->start && $item->end
                        ? gmdate('H:i', strtotime($item->end) - strtotime($item->start))
                        : null,
                ]);

            $cartSparrings = CartItem::with(['sparringSchedule.athlete'])
                ->where('user_id', $userId)
                ->where('item_type', 'sparring')
                ->get()
                ->map(fn($item) => [
                    'cart_id'     => $item->id,
                    'schedule_id' => $item->sparringSchedule?->id,
                    'athlete_name' => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'        => $item->date,
                    'start'       => $item->start,
                    'end'         => $item->end,
                    'price'       => $item->price,
                ]);
        }

        $addresses = Venue::select('address')
            ->distinct()
            ->pluck('address');
        return view('public.venue.index', compact('venues', 'cartProducts', 'cartVenues', 'cartSparrings', 'addresses'));
    }

    // API schedules: /venues/{venueId}/price-schedules?date=YYYY-MM-DD
    public function priceSchedules(Request $request, $venueId)
    {
        try {
            $venue = Venue::findOrFail($venueId);
            $requestedTime = $request->query('date')
                ? \Carbon\Carbon::parse($request->query('date'))
                : now();

            $today = strtolower($requestedTime->format('l'));
            $requestedDate = $requestedTime->format('Y-m-d');

            $tables = \App\Models\Table::where('venue_id', $venueId)->get();
            $bookings = Booking::where('venue_id', $venueId)
                ->where('booking_date', $requestedDate)
                ->where('status', 'booked')
                ->get();

            $schedules = PriceSchedule::where('venue_id', $venueId)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get()
                ->filter(function ($item) use ($today) {
                    $days = is_string($item->days) ? json_decode($item->days, true) ?? [] : (array) $item->days;
                    return in_array($today, array_map('strtolower', $days));
                })
                ->map(function ($item) use ($requestedDate, $tables, $bookings) {
                    $tablesApplicable = $item->tables_applicable;

                    if (is_string($tablesApplicable)) {
                        $tablesApplicable = json_decode($tablesApplicable, true);
                    }

                    if (!is_array($tablesApplicable)) {
                        $tablesApplicable = [];
                    }

                    $slots = collect(
                        \Carbon\CarbonInterval::minutes(60)->toPeriod(
                            \Carbon\Carbon::parse($item->start_time),
                            \Carbon\Carbon::parse($item->end_time)->subHour()
                        )
                    )->map(function ($time) use ($tables, $bookings, $requestedDate, $tablesApplicable) {
                        $start = $time->format('H:i');
                        $end = $time->copy()->addHour()->format('H:i');

                        $tablesWithStatus = $tables->map(function ($table) use ($bookings, $requestedDate, $start, $end, $tablesApplicable) {
                            // Jika tablesApplicable kosong, anggap semua meja termasuk dalam jadwal ini
                            $isInApplicableList = empty($tablesApplicable) || in_array($table->table_number ?? $table->id, $tablesApplicable);

                            $isBooked = $bookings->contains(function ($b) use ($table, $start, $end) {
                                $bStart = \Carbon\Carbon::parse($b->start_time);
                                $bEnd   = \Carbon\Carbon::parse($b->end_time);
                                $sStart = \Carbon\Carbon::parse($start);
                                $sEnd   = \Carbon\Carbon::parse($end);

                                return $b->table_id == $table->id &&
                                    $sStart->lt($bEnd) && $sEnd->gt($bStart);
                            });

                            return [
                                'id' => $table->id,
                                'name' => $table->table_number ?? ('Table ' . $table->id),
                                'is_booked' => $isBooked || !$isInApplicableList,
                            ];
                        });

                        return [
                            'start' => $start,
                            'end' => $end,
                            'tables' => $tablesWithStatus,
                        ];
                    });

                    return [
                        'id' => $item->id,
                        'venue_id' => $item->venue_id,
                        'name' => $item->name,
                        'price' => $item->price,
                        'days' => is_string($item->days) ? json_decode($item->days, true) ?? [] : (array) $item->days,
                        'start_time' => \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                        'end_time' => \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                        'time_category' => $item->time_category,
                        'schedule' => $slots,
                        'date' => $requestedDate,
                    ];
                })
                ->values();

            return response()->json([
                'venue' => $venue,
                'requestedDate' => $requestedDate,
                'schedules' => $schedules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve venue details',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // DETAIL PAGE: /venues/{id}/{slug?}
    public function showDetail(Request $request, $venueId, $slug = null)
    {
        $detail = Venue::findOrFail($venueId);
        $tables = Table::where('venue_id', $detail->id)->get();

        $minPrice = PriceSchedule::where('venue_id', $detail->id)
            ->where('is_active', true)
            ->orderBy('price', 'asc')
            ->value('price');

        $cartProducts = collect();
        $cartVenues = collect();
        $cartSparrings = collect();

        if (auth()->check()) {
            $userId = auth()->id();

            $cartProducts = CartItem::with('product')
                ->where('user_id', $userId)
                ->where('item_type', 'product')
                ->get()
                ->map(fn($item) => [
                    'cart_id'  => $item->id,
                    'product_id' => $item->product?->id,
                    'name'     => $item->product?->name,
                    'brand'    => $item->product?->brand,
                    'category' => $item->product?->category?->name ?? '-',
                    'price'    => $item->product?->pricing,
                    'quantity' => $item->quantity,
                    'total'    => $item->quantity * ($item->product?->pricing ?? 0),
                    'discount' => $item->product?->discount ?? 0,
                    'images'    => $item->product?->images[0] ?? null,
                ]);

            $cartVenues = CartItem::with('venue')
                ->where('user_id', $userId)
                ->where('item_type', 'venue')
                ->get()
                ->map(fn($item) => [
                    'cart_id' => $item->id,
                    'venue_id' => $item->venue?->id,
                    'name'     => $item->venue?->name,
                    'address'  => $item->venue?->address ?? '-',
                    'date'     => $item->date,
                    'start'    => $item->start,
                    'end'      => $item->end,
                    'table'    => $item->table_number,
                    'price'    => $item->price,
                    'duration' => $item->start && $item->end
                        ? gmdate('H:i', strtotime($item->end) - strtotime($item->start))
                        : null,
                ]);

            $cartSparrings = CartItem::with(['sparringSchedule.athlete'])
                ->where('user_id', $userId)
                ->where('item_type', 'sparring')
                ->get()
                ->map(fn($item) => [
                    'cart_id'     => $item->id,
                    'schedule_id' => $item->sparringSchedule?->id,
                    'athlete_name' => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'        => $item->date,
                    'start'       => $item->start,
                    'end'         => $item->end,
                    'price'       => $item->price,
                ]);
        }

        return view('public.venue.detail', compact('detail', 'tables', 'minPrice', 'cartProducts', 'cartVenues', 'cartSparrings'));
    }
}
