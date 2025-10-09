<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opinion;

class OpinionController extends Controller
{
    public function index(Request $request)
    {
        $opinions = Opinion::orderBy('created_at', 'desc')->get();
        return view('dash.admin.opinion', compact('opinions'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'required',
            'description' => 'required',
        ]);

        Opinion::create($validated);

        return back()->with('success', 'Thanks for subscribing to our newsletter!');
    }
}
