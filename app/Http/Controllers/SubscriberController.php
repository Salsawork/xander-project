<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubscribersExport;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscriber::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        // kamu bisa ganti ke paginate kalau mau
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

    /**
     * Export Excel (ikut filter ?search=... jika ada)
     */
    public function export(Request $request)
    {
        $filename = 'subscribers_' . now()->format('Ymd_His') . '.xlsx';
        $search   = $request->get('search');

        return Excel::download(new SubscribersExport($search), $filename);
    }
}
