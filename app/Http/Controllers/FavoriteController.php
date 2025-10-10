<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Tampilkan daftar venue favorit milik user.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $favorites = Favorite::with('venue')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('dash.user.favorite', compact('favorites'));
    }

    /**
     * Toggle favorite (add/remove) via AJAX/HTTP.
     */
    public function toggle(Venue $venue, Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login untuk menambahkan ke favorit.'
            ], 401);
        }

        $userId = Auth::id();

        $favorite = Favorite::where('venue_id', $venue->id)
            ->where('user_id', $userId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'success' => true,
                'message' => 'Venue dihapus dari favorit.',
                'action' => 'removed'
            ]);
        } else {
            Favorite::create([
                'venue_id' => $venue->id,
                'user_id'  => $userId,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Venue ditambahkan ke favorit.',
                'action' => 'added'
            ]);
        }
    }
}
