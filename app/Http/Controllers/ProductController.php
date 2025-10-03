<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{

    // public function index(Request $request)
    // {
    //     $products = Product::when($request->has('search'), function ($query) use ($request) {
    //         $search = $request->input('search');
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', '%' . $search . '%')
    //                 ->orWhere('description', 'like', '%' . $search . '%')
    //                 ->orWhere('sku', 'like', '%' . $search . '%');
    //         });
    //     })->when($request->has('category'), function ($query) use ($request) {
    //         $categoryId = $request->input('category');
    //         if ($categoryId) {
    //             $query->where('category_id', $categoryId);
    //         }
    //     })->when($request->has('status'), function ($query) use ($request) {
    //         $status = $request->input('status');
    //         if ($status == 'in-stock') {
    //             $query->where('quantity', '>', 0);
    //         } elseif ($status == 'out-of-stock') {
    //             $query->where('quantity', 0);
    //         }
    //     })->orderBy('created_at', 'desc')->get();

    //     $categories = \App\Models\Category::all();

    //     return view('dash.admin.product.index', compact('products', 'categories'));
    // }

    public function index(Request $request)
{
    $query = Product::query();

    // 🔹 Filter Level
    if ($request->has('level') && in_array($request->level, ['professional', 'beginner'])) {
        $query->where('level', $request->level);
    }

    // 🔹 Filter Harga
    if ($request->filter === 'under50') {
        $query->where('pricing', '<', 50000);
    }

    // 🔹 Filter Kategori
    if ($request->has('category') && $request->category) {
        $query->where('category_id', $request->category);
    }

    // 🔹 Filter Search
    if ($request->has('search') && $request->search) {
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
            $query->where('quantity', '>', 0);
        } elseif ($request->status === 'out-of-stock') {
            $query->where('quantity', '=', 0);
        }
    }

    // 🔹 Kalau tidak ada filter → tampilkan random 4 (untuk landing)
    if (!$request->hasAny(['level', 'filter', 'category', 'search', 'status'])) {
        $products = $query->inRandomOrder()->limit(4)->get();
    } else {
        $products = $query->orderBy('created_at', 'desc')->paginate(10);
    }

    // 🔹 Track the visit (hanya sekali per hari per IP)
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

    // 🔹 Ambil kategori untuk filter di view (dashboard)
    $categories = \App\Models\Category::all();

    // 🔹 Pilih view sesuai kebutuhan
    if ($request->is('dashboard*')) {
        return view('dash.admin.product.index', compact('products', 'categories'));
    }

    return view('landing', compact('products'));
}


    public function filterByLevel(Request $request)
    {
        $query = Product::query();

        // semua filter lewat field level
        if ($request->has('level') && in_array($request->level, ['professional', 'beginner', 'under50', 'cue-cases'])) {
            $query->where('level', $request->level);
        } else {
            // kalau gak ada filter, tampil random 4
            $query->inRandomOrder()->limit(4);
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        return view('landing', compact('products'));
    }


    public function create()
    {
        $categories = Category::all();
        return view('dash.admin.product.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|in:Mezz,Predator,Cuetec,Other',
            'condition' => 'required|in:new,used',
            'quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'weight' => 'required|integer|min:0',
            'length' => 'required|integer|min:0',
            'breadth' => 'required|integer|min:0',
            'width' => 'required|integer|min:0',
            'pricing' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100'
        ]);
        Product::create($validatedData);
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('dash.admin.product.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Debugging: Cek data yang dikirim
        Log::info('Request data:', $request->all());

        // Pastikan discount tidak null
        if ($request->discount === null) {
            $request->merge(['discount' => 0]);
        }

        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|in:Mezz,Predator,Cuetec,Other',
            'condition' => 'required|in:new,used',
            'quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $id,
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'weight' => 'required|integer|min:0',
            'length' => 'required|integer|min:0',
            'breadth' => 'required|integer|min:0',
            'width' => 'required|integer|min:0',
            'pricing' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100'
        ]);

        // Debugging: Cek data yang lolos validasi
        Log::info('Validated data:', $validatedData);

        $product = Product::findOrFail($id);

        // Handle file uploads if any
        if ($request->hasFile('images')) {
            $uploadedImages = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('uploads', $imageName, 'public');
                $uploadedImages[] = asset('storage/uploads/' . $imageName);
            }

            // Merge with existing images if any
            $existingImages = json_decode($product->images ?? '[]', true);
            $validatedData['images'] = array_merge($existingImages, $uploadedImages);
        } else {
            // Keep existing images or set empty array if null
            $validatedData['images'] = $product->images ?? [];
        }

        // Debugging: Cek data sebelum update
        Log::info('Data before update:', $product->toArray());

        try {
            $product->update($validatedData);
            // Debugging: Cek data setelah update
            Log::info('Data after update:', $product->fresh()->toArray());
            return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            // Debugging: Cek jika ada error
            Log::error('Error updating product:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Hapus gambar jika ada
            if ($product->gambar && file_exists(public_path('images/products/' . $product->gambar))) {
                unlink(public_path('images/products/' . $product->gambar));
            }

            // Matiin foreign key checks sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Hapus produk
            $product->delete();

            // Nyalain lagi foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('products.index')->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function landing(Request $request)
    {
        $products = Product::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->brand, fn($q) => $q->where('brand', $request->brand))
            ->when($request->condition, fn($q) => $q->where('condition', $request->condition))
            ->when($request->price_min, fn($q) => $q->where('pricing', '>=', $request->price_min))
            ->when($request->price_max, fn($q) => $q->where('pricing', '<=', $request->price_max))
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $cartProducts = json_decode($request->cookie('cartProduct') ?? '[]', true);
        $cartVenues = json_decode($request->cookie('cartVenues') ?? '[]', true);
        $cartSparrings = json_decode($request->cookie('cartsparring') ?? '[]', true);

        return view('public.product.index', compact('products', 'cartProducts', 'cartVenues', 'cartSparrings'));
    }

    public function detail(Request $request, $product)
    {
        $detail = Product::findOrFail($product);
        $cartProducts = json_decode($request->cookie('cartProduct') ?? '[]', true);
        $cartVenues = json_decode($request->cookie('cartVenues') ?? '[]', true);
        $cartSparrings = json_decode($request->cookie('cartsparring') ?? '[]', true);

        return view('public.product.detail', compact('detail', 'cartProducts', 'cartVenues', 'cartSparrings'));
    }
}
