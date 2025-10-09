<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;

class SubscriberController extends Controller
{
    

    public function index(Request $request)
    {
        $query = Subscriber::query();

        // Jika ada parameter search, filter berdasarkan email
        if ($request->has('search') && $request->search != '') {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        // Urutkan data terbaru
        $subscribers = $query->orderBy('created_at', 'desc')->get();

        return view('dash.admin.subscriber', compact('subscribers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:subscribers,email',
        ]);

        Subscriber::create($validated);

        return back()->with('success', 'Thanks for subscribing to our newsletter!');
    }
}
