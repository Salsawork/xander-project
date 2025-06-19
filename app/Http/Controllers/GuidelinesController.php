<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuidelinesController extends Controller
{
    public function index()
    {
        $guidelines = \App\Models\Guideline::orderBy('created_at', 'desc')->get();
        return view('public.guideline.index', compact('guidelines'));
    }

    public function category($category)
    {
        // Ubah slug ke nama kategori asli, misal: 'beginner' => 'Beginner'
        $categoryName = ucfirst(str_replace('-', ' ', $category));

        // Ambil data guideline yang kategori-nya sama (case-insensitive)
        $guidelines = \App\Models\Guideline::whereRaw('LOWER(category) = ?', [strtolower($categoryName)])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.guideline.category', compact('guidelines', 'categoryName'));
    }
    
    public function show($slug)
    {
        // Ambil data guideline berdasarkan slug
        $guideline = \App\Models\Guideline::where('slug', $slug)->firstOrFail();
        
        // Ambil beberapa guideline terkait untuk rekomendasi
        $relatedGuidelines = \App\Models\Guideline::where('id', '!=', $guideline->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();
            
        // Tambah jumlah view
        $guideline->increment('views_count');
        
        return view('public.guideline.show', compact('guideline', 'relatedGuidelines'));
    }
}
