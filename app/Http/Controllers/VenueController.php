<?php

namespace App\Http\Controllers;

use App\Models\Venue;

class VenueController extends Controller
{
    public function index()
    {
        $venues = Venue::all();
        return view('public.venue.index', compact('venues'));
    }
} 