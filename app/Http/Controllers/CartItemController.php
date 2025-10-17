<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\SparringSchedule;
use App\Models\User;
use App\Models\Venue;

class CartItemController extends Controller
{
    public function addProductToCart(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.'
            ], 401);
        }

        $user = auth()->user();
        $product = Product::findOrFail($request->id);

        $cartItem = CartItem::firstOrNew([
            'user_id'   => $user->id,
            'item_type' => 'product',
            'item_id'   => $product->id,
        ]);

        $cartItem->quantity = ($cartItem->exists ? $cartItem->quantity : 0) + (int)($request->quantity ?? 1);
        $cartItem->save();

        $cartCount = CartItem::where('user_id', $user->id)->sum('quantity');

        $cartProducts = CartItem::with('product')
            ->where('user_id', $user->id)
            ->where('item_type', 'product')
            ->get()
            ->filter(fn($item) => $item->product)
            ->map(function ($item) {
                $product = $item->product;

                return [
                    'cart_id'  => $item->id,
                    'id'       => $product->id,
                    'name'     => $product->name,
                    'price'    => $product->pricing,
                    'quantity' => $item->quantity,
                    // 👉 baca foto dari ../demo-xanders/images/products/
                    'image'    => $this->buildProductImageUrl($product->images ?? null),
                ];
            });

        return response()->json([
            'success'   => true,
            'message'   => 'Product added to cart',
            'cartCount' => $cartCount,
            'products'  => $cartProducts,
        ]);
    }

    public function addVenueToCart(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu untuk menambahkan venue ke keranjang.'
            ], 401);
        }

        $request->validate([
            'id'             => 'required|exists:venues,id',
            'date'           => 'required|date',
            'schedule.start' => 'required',
            'schedule.end'   => 'required',
            'price'          => 'required|numeric',
            'table'          => 'nullable|string',
        ]);

        $user  = auth()->user();
        $venue = Venue::findOrFail($request->id);

        $date      = date('Y-m-d', strtotime($request->date));
        $startTime = $request->input('schedule.start');
        $endTime   = $request->input('schedule.end');
        $price     = $request->input('price');
        $table     = $request->input('table');

        $exists = CartItem::where('user_id', $user->id)
            ->where('item_type', 'venue')
            ->where('item_id', $venue->id)
            ->where('date', $date)
            ->where('start', $startTime)
            ->where('table_number', $table)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Venue dengan waktu mulai {$startTime} pada {$date} untuk table {$table} sudah ada di keranjang.",
            ], 400);
        }

        CartItem::create([
            'user_id'      => $user->id,
            'item_type'    => 'venue',
            'item_id'      => $venue->id,
            'date'         => $date,
            'start'        => $startTime,
            'end'          => $endTime,
            'table_number' => $table,
            'price'        => $price,
        ]);

        $cartVenues = CartItem::with('venue')
            ->where('user_id', $user->id)
            ->where('item_type', 'venue')
            ->get()
            ->map(fn($item) => [
                'cart_id' => $item->id,
                'id'      => $item->venue->id,
                'name'    => $item->venue->name,
                'price'   => $item->price,
                'date'    => $item->date,
                'start'   => $item->start,
                'end'     => $item->end,
                'table'   => $item->table_number,
                // venue tetap membaca dari images/venue/ (sudah benar)
                'image'   => $this->buildVenueImageUrl($item->venue->image ?? null),
            ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Venue berhasil ditambahkan ke keranjang.',
            'cartCount' => $cartVenues->count(),
            'venues'    => $cartVenues,
        ]);
    }

    public function addSparringToCart(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu untuk menambahkan sparring ke keranjang.'
            ], 401);
        }

        $request->validate([
            'athlete_id'  => 'required|exists:users,id',
            'schedule_id' => 'required|exists:sparring_schedules,id',
        ]);

        $user    = auth()->user();
        $athlete = User::with('athleteDetail')->findOrFail($request->athlete_id);

        $schedule = SparringSchedule::where('id', $request->schedule_id)
            ->where('is_booked', false)
            ->firstOrFail();

        $exists = CartItem::where('user_id', $user->id)
            ->where('item_type', 'sparring')
            ->where('item_id', $schedule->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Sparring dengan jadwal ini sudah ada di keranjang Anda.'
            ], 400);
        }

        CartItem::create([
            'user_id'   => $user->id,
            'item_type' => 'sparring',
            'item_id'   => $schedule->id,
            'price'     => $athlete->athleteDetail->price_per_session,
            'date'      => $schedule->date,
            'start'     => $schedule->start_time,
            'end'       => $schedule->end_time,
            'quantity'  => 1,
        ]);

        $cartSparrings = CartItem::with(['sparringSchedule', 'sparringSchedule.athlete'])
            ->where('user_id', $user->id)
            ->where('item_type', 'sparring')
            ->get()
            ->map(function ($item) {
                $schedule = $item->sparringSchedule;
                $athlete  = $schedule->athlete ?? null;

                return [
                    'id'       => $schedule->id,
                    'athlete'  => $athlete ? $athlete->name : 'Unknown',
                    // bila ingin disamakan ke folder FE juga, bisa pakai helper mirip venue
                    'image'    => $athlete?->athleteDetail?->image,
                    'schedule' => date('d M Y', strtotime($schedule->date)) . ' ' .
                                  date('H:i', strtotime($schedule->start_time)) . '-' .
                                  date('H:i', strtotime($schedule->end_time)),
                    'price'    => $item->price,
                ];
            });

        return response()->json([
            'success'   => true,
            'message'   => 'Sparring berhasil ditambahkan ke keranjang.',
            'cartCount' => $cartSparrings->count(),
            'sparrings' => $cartSparrings,
        ]);
    }

    public function removeFromCart(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->back()->with('error', 'Silakan login terlebih dahulu.');
        }

        $request->validate([
            'id' => 'required|exists:cart_items,id',
        ]);

        $cartItem = CartItem::where('id', $request->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cartItem) {
            return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang Anda.');
        }

        $itemType = $cartItem->item_type;
        $cartItem->delete();

        $messages = [
            'product'  => 'Produk dihapus dari keranjang.',
            'venue'    => 'Venue dihapus dari keranjang.',
            'sparring' => 'Sesi sparring dihapus dari keranjang.',
        ];

        $message = $messages[$itemType] ?? 'Item dihapus dari keranjang.';

        return redirect()->back()->with('success', $message);
    }

    /* =======================
     * Helpers URL gambar
     * ======================= */

    /**
     * Normalisasi image produk ke https://demo-xanders.ptbmn.id/images/products/{filename}
     * Menerima: array string, JSON-string, atau string path.
     */
    private function buildProductImageUrl($images): string
    {
        $filename = null;

        // 1) jika string JSON array
        if (is_string($images) && str_starts_with($images, '[')) {
            $decoded = json_decode($images, true);
            if (is_array($decoded) && !empty($decoded)) {
                $first = $decoded[0];
                $filename = $this->extractFilename($first);
            }
        }
        // 2) jika array
        elseif (is_array($images) && !empty($images)) {
            $filename = $this->extractFilename($images[0]);
        }
        // 3) jika string path tunggal
        elseif (is_string($images) && $images !== '') {
            $filename = $this->extractFilename($images);
        }

        if (!$filename) {
            // fallback internal FE, bukan placeholder eksternal
            return url('images/products/default.png');
        }

        return url('images/products/' . $filename);
    }

    /**
     * Normalisasi image venue ke https://demo-xanders.ptbmn.id/images/venue/{filename}
     */
    private function buildVenueImageUrl($image): string
    {
        $filename = $this->extractFilename($image);
        if (!$filename) {
            return url('images/venue/default.png');
        }
        return url('images/venue/' . $filename);
    }

    /**
     * Ambil basename dari path/URL (serta rapikan slash).
     * Contoh:
     *  - 'uploads/abc.jpg'      -> 'abc.jpg'
     *  - '/storage/uploads/a.png'-> 'a.png'
     *  - 'http://.../x/y/z.webp' -> 'z.webp'
     */
    private function extractFilename($pathLike): ?string
    {
        if (empty($pathLike)) return null;

        $raw = str_replace('\\', '/', (string)$pathLike);

        // kalau JSON accidental lagi
        if (is_string($raw) && str_starts_with($raw, '[')) {
            $arr = json_decode($raw, true);
            if (is_array($arr) && !empty($arr)) {
                $raw = str_replace('\\', '/', (string)$arr[0]);
            }
        }

        // kalau URL penuh atau path panjang, ambil basename saja
        $base = basename($raw);
        return $base ?: null;
    }
}
