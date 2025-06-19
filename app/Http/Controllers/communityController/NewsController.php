<?php

namespace App\Http\Controllers\CommunityController;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of the news.
     */
    public function index(Request $request)
    {
        // Filter berdasarkan kategori jika ada
        $query = News::query();
        
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        // Filter berdasarkan pencarian jika ada
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        // Ambil semua berita dengan pagination
        $allNews = $query->latest('published_at')->paginate(9);
        
        // Ambil berita featured untuk slider
        $featuredNews = News::where('is_featured', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        // Ambil berita popular
        $popularNews = News::where('is_popular', true)
            ->latest('published_at')
            ->take(4)
            ->get();
            
        // Berita terbaru untuk sidebar
        $recentNews = News::latest('published_at')
            ->take(5)
            ->get();

        // Ambil semua kategori
        $categories = News::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
            
        // Hitung jumlah berita per kategori
        $categoryCount = [];
        foreach ($categories as $category) {
            $categoryCount[$category] = News::where('category', $category)->count();
        }

        // Jika request dari halaman utama community
        if ($request->route()->getName() === 'community.index') {
            return view('dash.community.index', compact(
                'featuredNews',
                'popularNews',
                'recentNews',
                'categories',
                'categoryCount'
            ));
        }
        
        // Jika request dari halaman news
        return view('dash.community.news', compact(
            'allNews',
            'featuredNews',
            'popularNews',
            'recentNews',
            'categories',
            'categoryCount'
        ));
    }

    /**
     * Display the specified news.
     */
    public function show(News $news)
    {
        // Ambil berita terkait berdasarkan kategori
        $relatedNews = News::where('category', $news->category)
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->take(3)
            ->get();
            
        // Berita terbaru untuk sidebar
        $recentNews = News::latest('published_at')
            ->where('id', '!=', $news->id)
            ->take(5)
            ->get();
            
        // Ambil semua kategori
        $categories = News::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
            
        // Hitung jumlah berita per kategori
        $categoryCount = [];
        foreach ($categories as $category) {
            $categoryCount[$category] = News::where('category', $category)->count();
        }

        return view('dash.community.show', compact(
            'news', 
            'relatedNews',
            'recentNews',
            'categories',
            'categoryCount'
        ));
    }
}