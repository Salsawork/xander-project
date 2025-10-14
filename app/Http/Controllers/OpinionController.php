<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opinion;
use App\Exports\OpinionsExport;
use Maatwebsite\Excel\Facades\Excel;

class OpinionController extends Controller
{
    /**
     * Tampilkan list opinion (mendukung ?search= optional).
     */
    public function index(Request $request)
    {
        $opinions = Opinion::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->where('email', 'like', "%{$s}%")
                       ->orWhere('subject', 'like', "%{$s}%")
                       ->orWhere('description', 'like', "%{$s}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dash.admin.opinion', compact('opinions'));
    }

    /**
     * Simpan opinion dari form publik.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'       => 'required|email',
            'subject'     => 'required|string',
            'description' => 'required|string',
        ]);

        Opinion::create($validated);

        return back()->with('success', 'Thanks for sharing your opinion!');
    }

    /**
     * Export Excel (ikutin query ?search= kalau ada).
     */
    public function export(Request $request)
    {
        $search = $request->get('search');

        // Bisa return instance export langsung (Responsable)
        return new OpinionsExport($search);

        // Atau, gunakan Excel::download (setara):
        // return Excel::download(new OpinionsExport($search), 'opinions_' . now()->format('Ymd_His') . '.xlsx');
    }
}
