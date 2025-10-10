<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\BilliardSession;
use App\Models\CartItem;
use App\Models\PriceSchedule;
use App\Models\Table;
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


    public function detail(Request $request, $venueId)
    {
        try {
            $detail = Venue::findOrFail($venueId);
            $requestedTime = $request->query('date')
                ? \Carbon\Carbon::parse($request->query('date'))
                : now();

            $today = $requestedTime->format('l');
            $requestedDate = $requestedTime->format('Y-m-d');


            // Ambil semua schedule aktif untuk venue
            $schedules = PriceSchedule::where('venue_id', $detail->id)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get()
                ->filter(function ($item) use ($today) {
                    $days = json_decode($item->days, true) ?? [];
                    return in_array(strtolower($today), array_map('strtolower', $days));
                })
                ->map(fn($item) => [
                    'id'           => $item->id,
                    'venue_id'     => $item->venue_id,
                    'name'         => $item->name,
                    'price'        => number_format($item->price, 0, ',', '.'),
                    'days'         => json_decode($item->days, true) ?? [],
                    'start_time'   => \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                    'end_time'     => \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                    'time_category' => $item->time_category,
                    'schedule' => collect(
                        \Carbon\CarbonInterval::minutes(60)
                            ->toPeriod(
                                \Carbon\Carbon::parse($item->start_time),
                                \Carbon\Carbon::parse($item->end_time)->subHour()
                            )
                    )->map(fn($time) => [
                        'start' => $time->format('H:i'),
                        'end'   => $time->copy()->addHour()->format('H:i')
                    ])->values()->toArray(),
                    'tables_applicable' => $item->tables_applicable,
                    'date'         => $requestedDate,
                ]);

            return response()->json([
                'detail' => $detail,
                'schedules' => $schedules,
                'tables_applicable' => $schedules->pluck('tables_applicable')->flatten()->unique()->values()->toArray(),
                'requestedDate' => $requestedDate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve venue details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showDetail(Request $request, $venueId)
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
