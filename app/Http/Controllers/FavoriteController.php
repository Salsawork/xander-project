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
        $guestId = $request->session()->getId();

        $favorite = Favorite::where('venue_id', $venue->id)
            ->where('guest_id', $guestId)
            ->first();

        if ($favorite) {
            Favorite::where('venue_id', $venue->id)
                ->where('guest_id', $guestId)
                ->delete();

            return response()->json(['status' => 'removed']);
        } else {
            Favorite::insert([
                'venue_id'  => $venue->id,
                'guest_id'  => $guestId,
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);

            return response()->json(['status' => 'added']);
        }
    }

}
