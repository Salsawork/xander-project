<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * Menampilkan daftar berita di dashboard admin
     */
    public function index()
    {
        $news = News::orderBy('published_at', 'desc')->get();
        return view('dash.admin.comunity.index', compact('news'));
    }

    /**
     * Menampilkan form untuk membuat berita baru
     */
    public function create()
    {
        return view('dash.admin.comunity.create');
    }

    /**
     * Menyimpan berita baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'published_at' => 'required|date',
            'image_url' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image_url')) {
            $file = $request->file('image_url');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('uploads', $fileName, 'public');
            $data['image_url'] = $fileName;
        }

        // Set checkbox values
        $data['is_featured'] = $request->has('is_featured');
        $data['is_popular'] = $request->has('is_popular');

        News::create($data);

        return redirect()->route('comunity.index')->with('success', 'Berita berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit berita
     */
    public function edit(News $news)
    {
        return view('dash.admin.comunity.edit', compact('news'));
    }

    /**
     * Menyimpan perubahan pada berita
     */
    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'published_at' => 'required|date',
            'image_url' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image_url')) {
            // Delete old image if exists
            if ($news->image_url && !str_starts_with($news->image_url, 'http://') && !str_starts_with($news->image_url, 'https://') && Storage::disk('public')->exists('uploads/' . $news->image_url)) {
                Storage::disk('public')->delete('uploads/' . $news->image_url);
            }
            
            $file = $request->file('image_url');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('uploads', $fileName, 'public');
            $data['image_url'] = $fileName;
        }

        // Set checkbox values
        $data['is_featured'] = $request->has('is_featured');
        $data['is_popular'] = $request->has('is_popular');

        $news->update($data);

        return redirect()->route('comunity.index')->with('success', 'Berita berhasil diperbarui!');
    }

    /**
     * Menghapus berita dari database
     */
    public function destroy(News $news)
    {
        // Delete image if exists
        if ($news->image_url && !str_starts_with($news->image_url, 'http://') && !str_starts_with($news->image_url, 'https://') && Storage::disk('public')->exists('uploads/' . $news->image_url)) {
            Storage::disk('public')->delete('uploads/' . $news->image_url);
        }
        
        $news->delete();
        
        return redirect()->route('comunity.index')->with('success', 'Berita berhasil dihapus!');
    }
}