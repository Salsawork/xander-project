<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Venue;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Venue $venue, Request $request)
    {
        // Pastikan hanya user login yang bisa favorit
        if (!Auth::check()) {
            return response()->json([
                'status' => 'unauthenticated',
                'message' => 'Silakan login untuk menambahkan ke favorit.'
            ], 401);
        }

        $userId = Auth::id();

        $favorite = Favorite::where('venue_id', $venue->id)
            ->where('user_id', $userId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        } else {
            Favorite::create([
                'venue_id' => $venue->id,
                'user_id'  => $userId,
            ]);
            return response()->json(['status' => 'added']);
        }
    }
    
    
}
