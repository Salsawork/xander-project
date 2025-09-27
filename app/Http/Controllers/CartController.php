<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $cart = json_decode($request->cookie('cart') ?? '[]', true);
        $product = Product::findOrFail($request->id);

        $exists = collect($cart)->contains(fn($item) => $item['id'] === $product->id);

        if (!$exists) {
            $cart[] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'image' => $product->images[0] ?? null,
                'price' => $product->pricing,
            ];
        }

        return redirect()->back()->withCookie(cookie('cart', json_encode($cart), 60 * 24 * 7));
    }

    public function delete(Request $request)
    {
        $cart = json_decode($request->cookie('cart') ?? '[]', true);
        $cart = array_values(array_filter($cart, fn($item) => $item['id'] !== (int)$request->id));

        return redirect()->back()->withCookie(cookie('cart', json_encode($cart), 60 * 24 * 7));
    }
}
