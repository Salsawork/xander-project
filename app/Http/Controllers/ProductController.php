<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Categories;
use App\Models\SparringSchedule;
use App\Models\Venue;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // ==== ADMIN: /dashboard/* → listing + paginate (6/hal) ====
        if ($request->is('dashboard*')) {
            // Eager-load category supaya aman & hemat query
            $query = Product::query()->with('category');

            // 🔹 Filter Level
            if ($request->has('level') && in_array($request->level, ['professional', 'beginner'])) {
                $query->where('level', $request->level);
            }

            // 🔹 Filter Harga
            if ($request->filter === 'under50') {
                $query->where('pricing', '<', 50000);
            }

            // 🔹 Filter Kategori
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }

            // 🔹 Filter Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('sku', 'like', '%' . $search . '%');
                });
            }

            // 🔹 Filter Status
            if ($request->has('status')) {
                if ($request->status === 'in-stock') {
                    $query->where('stock', '>', 0);
                } elseif ($request->status === 'out-of-stock') {
                    $query->where('stock', '=', 0);
                }
            }

            // Pagination konsisten: 6 item/hal
            $perPage  = 6;
            $products = $query->orderBy('created_at', 'desc')
                              ->paginate($perPage)
                              ->withQueryString();

            // Kategori untuk filter di view (dashboard)
            $categories = Categories::orderBy('name')->get();

            return view('dash.admin.product.index', compact('products', 'categories'));
        }

        // ==== PUBLIK (beranda): Top Picks 4 item saja ====
        // Strategi: terbaru & in-stock; bebas diubah ke "terlaris" dll.
        $products = Product::query()
            ->with('category')
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        // Track visit: HANYA untuk halaman publik (bukan /dashboard/*)
        try {
            $ipAddress = $request->ip();
            $visit = Visit::where('ip_address', $ipAddress)
                ->whereDate('visit_date', today())
                ->first();

            if (!$visit) {
                Visit::create([
                    'ip_address' => $ipAddress,
                    'visit'      => 1,
                    'visit_date' => now(),
                ]);
            } else {
                $visit->increment('visit');
            }
        } catch (\Throwable $e) {
            Log::warning('Skip visit tracking: ' . $e->getMessage());
        }

        // Kirim ke view landing. (Variabel $products = 4 item → Top Picks pasti 4)
        return view('landing', compact('products'));
    }

    public function filterByLevel(Request $request)
    {
        $query = Product::query()->with('category');

        // Catatan: di project kamu, chip "under50" & "cue-cases" diarahkan ke param "level".
        // Agar aman, tetap batasi 4 item untuk Top Picks.
        if ($request->has('level') && in_array($request->level, ['professional', 'beginner', 'under50', 'cue-cases'])) {
            // Default: filter berdasarkan 'level' saja (mengikuti struktur yang sudah ada)
            $query->where('level', $request->level);
        } else {
            $query->inRandomOrder();
        }

        // Selalu batasi ke 4 item untuk konsistensi Top Picks
        $products = $query->orderBy('created_at', 'desc')->take(4)->get();

        return view('landing', compact('products'));
    }

    public function create()
    {
        $categories = Categories::all();
        return view('dash.admin.product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // NB: name input file harus "images[]" multiple
        $validatedData = $request->validate([
            'name'        => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand'       => 'required|in:Mezz,Predator,Cuetec,Other',
            'condition'   => 'required|in:new,used',
            // menerima stock_qty dari form, map ke stock
            'stock_qty'   => 'required|integer|min:0',
            'sku'         => 'nullable|string|unique:products,sku',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'weight'      => 'required|integer|min:0',
            'length'      => 'required|integer|min:0',
            'breadth'     => 'required|integer|min:0',
            'width'       => 'required|integer|min:0',
            'pricing'     => 'required|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0|max:100'
        ]);

        // Map stock_qty -> stock (kolom DB)
        $validatedData['stock'] = (int) ($validatedData['stock_qty'] ?? 0);
        unset($validatedData['stock_qty']);

        // Upload images -> simpan array filename (tanpa path)
        $filenames = [];
        if ($request->hasFile('images')) {
            $filenames = $this->uploadImages($request->file('images'));
        }

        $validatedData['images']   = $filenames;          // array of filenames
        $validatedData['discount'] = $request->discount ?? 0;

        Product::create($validatedData);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(string $id)
    {
        $product    = Product::with('category')->findOrFail($id);
        $categories = Categories::all();
        return view('dash.admin.product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        Log::info('Request data:', $request->all());

        if ($request->discount === null) {
            $request->merge(['discount' => 0]);
        }

        $validatedData = $request->validate([
            'name'        => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand'       => 'required|in:Mezz,Predator,Cuetec,Other',
            'condition'   => 'required|in:new,used',
            // menerima stock_qty dari form, map ke stock
            'stock_qty'   => 'required|integer|min:0',
            'sku'         => 'nullable|string|unique:products,sku,' . $id,
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'weight'      => 'required|integer|min:0',
            'length'      => 'required|integer|min:0',
            'breadth'     => 'required|integer|min:0',
            'width'       => 'required|integer|min:0',
            'pricing'     => 'required|numeric|min:0',
            'discount'    => 'required|numeric|min:0|max:100'
        ]);

        // Map stock_qty -> stock (kolom DB)
        $validatedData['stock'] = (int) ($validatedData['stock_qty'] ?? 0);
        unset($validatedData['stock_qty']);

        $product = Product::findOrFail($id);

        // ==== Upload baru? ganti semua gambar ====
        if ($request->hasFile('images')) {
            // Hapus file lama di CMS & FE
            $this->deleteImages((array) ($product->images ?? []));
            // Upload baru
            $uploaded = $this->uploadImages($request->file('images'));
            $validatedData['images'] = $uploaded;
        } else {
            // Tidak upload → keep gambar lama
            $validatedData['images'] = $product->images ?? [];
        }

        Log::info('Data before update:', $product->toArray());

        try {
            $product->update($validatedData);
            Log::info('Data after update:', $product->fresh()->toArray());
            return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error updating product:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Hapus semua gambar terkait (array filename)
            if (!empty($product->images) && is_array($product->images)) {
                $this->deleteImages($product->images);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $product->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('products.index')->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    // Halaman listing publik /products – tetap paginate
    public function landing(Request $request)
    {
        $isMobile = $this->isMobile($request);
        $perPage  = $isMobile ? 4 : 8;

        $query = Product::query()
            ->with('category')
            ->when($request->search, fn($q) => $q->where(function ($sub) use ($request) {
                $s = $request->search;
                $sub->where('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%");
            }))
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->brand, fn($q) => $q->where('brand', $request->brand))
            ->when($request->condition, fn($q) => $q->where('condition', $request->condition))
            ->when($request->price_min, fn($q) => $q->where('pricing', '>=', $request->price_min))
            ->when($request->price_max, fn($q) => $q->where('pricing', '<=', $request->price_max))
            ->orderBy('created_at', 'desc');

        $products = $query->paginate($perPage)->withQueryString();

        $requestedPage = (int) $request->query('page', 1);
        $lastPage      = max(1, $products->lastPage());
        if ($requestedPage > $lastPage && $products->total() > 0) {
            return redirect()->to($request->fullUrlWithQuery(['page' => $lastPage]));
        }

        $categories = Categories::select('id', 'name')->orderBy('name')->get();
        $brands     = Product::select('brand')->whereNotNull('brand')->distinct()->pluck('brand')->filter()->values();
        $conditions = ['new' => 'New', 'used' => 'Used'];

        $cartProducts = collect();
        $cartVenues   = collect();
        $cartSparrings = collect();

        if (auth()->check()) {
            $userId = auth()->id();

            $cartProducts = CartItem::with('product')
                ->where('user_id', $userId)
                ->where('item_type', 'product')
                ->get()
                ->map(fn($item) => [
                    'cart_id'    => $item->id,
                    'product_id' => $item->product?->id,
                    'name'       => $item->product?->name,
                    'brand'      => $item->product?->brand,
                    'category'   => $item->product?->category?->name ?? '-',
                    'price'      => $item->product?->pricing,
                    'quantity'   => $item->quantity,
                    'total'      => $item->quantity * ($item->product?->pricing ?? 0),
                    'discount'   => $item->product?->discount ?? 0,
                    'images'     => $item->product?->images[0] ?? null,
                ]);

            $cartVenues = CartItem::with('venue')
                ->where('user_id', $userId)
                ->where('item_type', 'venue')
                ->get()
                ->map(fn($item) => [
                    'cart_id'  => $item->id,
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
                    'cart_id'       => $item->id,
                    'schedule_id'   => $item->sparringSchedule?->id,
                    'athlete_name'  => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'          => $item->date,
                    'start'         => $item->start,
                    'end'           => $item->end,
                    'price'         => $item->price,
                ]);
        }

        return view('public.product.index', compact(
            'products',
            'categories',
            'brands',
            'conditions',
            'cartProducts',
            'cartVenues',
            'cartSparrings'
        ));
    }

    public function detail(Request $request, int $id, ?string $slug = null)
    {
        $detail = Product::with('category')->findOrFail($id);

        $expectedSlug = Str::slug($detail->name ?? 'product');
        if ($slug !== $expectedSlug) {
            return redirect()->route('products.detail', ['id' => $id, 'slug' => $expectedSlug], 301);
        }

        $detailDiscountPercent = $this->normalizeDiscountPercent($detail->discount ?? 0);
        $detailHasDiscount     = $detailDiscountPercent > 0;
        $detailFinalPrice      = $this->finalPrice((float) ($detail->pricing ?? 0), $detailDiscountPercent);

        // ==== RELATED: tampilkan SEMUA produk selain produk ini (tanpa filter kategori, tanpa limit) ====
        $relatedProducts = Product::query()
            ->where('id', '!=', $detail->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $relatedPriceMap = [];
        foreach ($relatedProducts as $p) {
            $pct = $this->normalizeDiscountPercent($p->discount ?? 0);
            $relatedPriceMap[$p->id] = [
                'has_discount'     => $pct > 0,
                'discount_percent' => $pct,
                'final_price'      => $this->finalPrice((float) ($p->pricing ?? 0), $pct),
            ];
        }

        $cartProducts  = collect();
        $cartVenues    = collect();
        $cartSparrings = collect();

        if (auth()->check()) {
            $userId = auth()->id();

            $cartProducts = CartItem::with('product')
                ->where('user_id', $userId)
                ->where('item_type', 'product')
                ->get()
                ->map(fn($item) => [
                    'cart_id'    => $item->id,
                    'product_id' => $item->product?->id,
                    'name'       => $item->product?->name,
                    'brand'      => $item->product?->brand,
                    'category'   => $item->product?->category?->name ?? '-',
                    'price'      => $item->product?->pricing,
                    'quantity'   => $item->quantity,
                    'total'      => $item->quantity * ($item->product?->pricing ?? 0),
                    'discount'   => $item->product?->discount ?? 0,
                    'images'     => $item->product?->images[0] ?? null,
                ]);

            $cartVenues = CartItem::with('venue')
                ->where('user_id', $userId)
                ->where('item_type', 'venue')
                ->get()
                ->map(fn($item) => [
                    'cart_id'  => $item->id,
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
                    'cart_id'       => $item->id,
                    'schedule_id'   => $item->sparringSchedule?->id,
                    'athlete_name'  => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'          => $item->date,
                    'start'         => $item->start,
                    'end'           => $item->end,
                    'price'         => $item->price,
                ]);
        }

        return view('public.product.detail', compact(
            'detail',
            'detailHasDiscount',
            'detailDiscountPercent',
            'detailFinalPrice',
            'relatedProducts',
            'relatedPriceMap',
            'cartProducts',
            'cartVenues',
            'cartSparrings'
        ));
    }

    private function normalizeDiscountPercent($discount): float
    {
        $d = (float) ($discount ?? 0);
        if ($d <= 0) return 0.0;
        return $d <= 1 ? $d * 100.0 : $d;
    }

    private function finalPrice(float $pricing, float $discountPercent): int
    {
        if ($discountPercent <= 0) return (int) round($pricing, 0);
        $final = $pricing - ($pricing * ($discountPercent / 100.0));
        return (int) round($final, 0);
    }

    /**
     * Upload images ke:
     *  - CMS: public/images/products
     *  - FE : ../demo-xanders/images/products  (relative dari base_path)
     * dan hanya menyimpan nama file (filename) ke DB.
     */
    private function uploadImages(array $files): array
    {
        $cmsPath = public_path('images/products');
        $fePath  = base_path('../demo-xanders/images/products');

        if (!File::exists($cmsPath)) File::makeDirectory($cmsPath, 0755, true);
        if (!File::exists($fePath))  File::makeDirectory($fePath, 0755, true);

        $filenames = [];
        foreach ($files as $file) {
            if (!$file) continue;

            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '', Str::slug($origName));
            $filename = time() . '-' . $safeName . '.' . $file->getClientOriginalExtension();

            // Simpan ke CMS
            $file->move($cmsPath, $filename);

            // Copy ke FE (abaikan error)
            @copy($cmsPath . DIRECTORY_SEPARATOR . $filename, $fePath . DIRECTORY_SEPARATOR . $filename);

            $filenames[] = $filename; // simpan filename saja
        }

        return $filenames;
    }

    /**
     * Hapus images dari CMS (public/images/products) dan FE (../demo-xanders/images/products)
     */
    private function deleteImages(array $filenames): void
    {
        foreach ($filenames as $filename) {
            if (!$filename) continue;

            $cms = public_path('images/products/' . $filename);
            $fe  = base_path('../demo-xanders/images/products/' . $filename);

            if (File::exists($cms)) @File::delete($cms);
            if (File::exists($fe))  @File::delete($fe);
        }
    }

    private function isMobile(Request $request): bool
    {
        $ua = $request->header('User-Agent', '');
        return (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod|IEMobile|Opera Mini/i', $ua);
    }
}
