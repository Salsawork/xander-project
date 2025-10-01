<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\Guideline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuidelinesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    $query = Guideline::query();

    // Search
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%')
              ->orWhere('author_name', 'like', '%' . $request->search . '%');
        });
    }

    // Filter category
    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }

    // Urutkan terbaru dulu
    $guidelines = $query->orderBy('created_at', 'desc')->get();

    return view('dash.admin.guidelines.index', compact('guidelines'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dash.admin.guidelines.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'category' => 'required|string|in:BEGINNER,INTERMEDIATE,MASTER,GENERAL',
            'skill_level' => 'required|string',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'youtube_url' => 'nullable|string|url',
            'is_new' => 'boolean',
            'is_featured' => 'boolean',
            'reading_time_minutes' => 'nullable|integer',
            'author_name' => 'required|string|max:100',
        ]);

        $guideline = new Guideline();
        $guideline->title = $request->title;
        $guideline->slug = Str::slug($request->title);
        $guideline->description = $request->description;
        $guideline->content = $request->content;
        $guideline->category = $request->category;
        $guideline->skill_level = $request->skill_level;
        $guideline->tags = $request->tags;
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('guidelines', $imageName, 'public');
            $guideline->featured_image = $path;
        } else {
            $guideline->featured_image = null;
        }
        $guideline->youtube_url = $request->youtube_url;
        $guideline->is_new = $request->has('is_new');
        $guideline->is_featured = $request->has('is_featured');
        $guideline->reading_time_minutes = $request->reading_time_minutes ?? 5;
        $guideline->author_name = $request->author_name;
        $guideline->published_at = now();
        $guideline->status = 'published';
        $guideline->views_count = 0;
        $guideline->save();

        return redirect()->route('admin.guidelines.index')->with('success', 'Guideline berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guideline $guideline)
    {
        return view('dash.admin.guidelines.edit', compact('guideline'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guideline $guideline)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'category' => 'required|string|in:BEGINNER,INTERMEDIATE,MASTER,GENERAL',
            'skill_level' => 'required|string',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'youtube_url' => 'nullable|string|url',
            'reading_time_minutes' => 'nullable|integer',
            'author_name' => 'required|string|max:100',
        ]);

        $guideline->title = $request->title;
        // Update slug only if title changed
        if ($guideline->title != $request->title) {
            $guideline->slug = Str::slug($request->title);
        }
        $guideline->description = $request->description;
        $guideline->content = $request->content;
        $guideline->category = $request->category;
        $guideline->skill_level = $request->skill_level;
        $guideline->tags = $request->tags;
        $guideline->featured_image = $request->featured_image;
        $guideline->youtube_url = $request->youtube_url;
        $guideline->is_new = $request->has('is_new');
        $guideline->is_featured = $request->has('is_featured');
        $guideline->reading_time_minutes = $request->reading_time_minutes ?? 5;
        $guideline->author_name = $request->author_name;
        $guideline->save();

        return redirect()->route('admin.guidelines.index')->with('success', 'Guideline berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guideline $guideline)
    {
        $guideline->delete();
        return redirect()->route('admin.guidelines.index')->with('success', 'Guideline berhasil dihapus!');
    }
}