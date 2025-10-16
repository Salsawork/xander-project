<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class NewsController extends Controller
{
    /**
     * Menampilkan daftar berita di dashboard admin
     */
    public function index(Request $request)
    {
        $news = News::when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $category = $request->input('category');
                if ($category) {
                    $query->where('category', $category);
                }
            })
            ->orderBy('published_at', 'desc')
            ->get();

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
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'category'     => 'nullable|string|max:100',
            'published_at' => 'required|date',
            'image_url'    => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $data = [
            'title'        => $request->input('title'),
            'content'      => $request->input('content'),
            'category'     => $request->input('category'),
            'published_at' => $request->input('published_at'),
            // checkbox → boolean
            'is_featured'  => $request->boolean('is_featured'),
            'is_popular'   => $request->boolean('is_popular'),
        ];

        // Upload gambar (opsional) → simpan filename saja
        if ($request->hasFile('image_url')) {
            $data['image_url'] = $this->uploadFile($request->file('image_url'));
        }

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
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'category'     => 'nullable|string|max:100',
            'published_at' => 'required|date',
            'image_url'    => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $data = [
            'title'        => $request->input('title'),
            'content'      => $request->input('content'),
            'category'     => $request->input('category'),
            'published_at' => $request->input('published_at'),
            'is_featured'  => $request->boolean('is_featured'),
            'is_popular'   => $request->boolean('is_popular'),
        ];

        // Ganti gambar jika upload baru
        if ($request->hasFile('image_url')) {
            // Hapus file lama di CMS & FE (jika ada)
            $this->deleteFile($news->image_url);
            // Upload baru
            $data['image_url'] = $this->uploadFile($request->file('image_url'));
        }

        $news->update($data);

        return redirect()->route('comunity.index')->with('success', 'Berita berhasil diperbarui!');
    }

    /**
     * Menghapus berita dari database
     */
    public function destroy(News $news)
    {
        // Hapus file gambar lama di CMS & FE
        $this->deleteFile($news->image_url);

        $news->delete();

        return redirect()->route('comunity.index')->with('success', 'Berita berhasil dihapus!');
    }

    /* ============================================================
     | Helpers upload/delete ala BannerController
     | Target: CMS => public/demo-xanders/images/community
     |         FE  => ../demo-xanders/images/community
     * ============================================================*/

    /**
     * Upload file ke CMS & salin ke FE. Return: filename aman (tanpa path).
     */
    private function uploadFile($file): string
    {
        // Nama file aman
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName     = preg_replace('/[^a-zA-Z0-9-_]/', '', Str::slug($originalName));
        $filename     = time() . '-' . $safeName . '.' . $file->getClientOriginalExtension();

        // Path CMS & FE
        $cmsPath = public_path('demo-xanders/images/community');
        $fePath  = base_path('../demo-xanders/images/community');

        // Pastikan folder ada
        if (!File::exists($cmsPath)) {
            File::makeDirectory($cmsPath, 0755, true);
        }
        if (!File::exists($fePath)) {
            File::makeDirectory($fePath, 0755, true);
        }

        // Simpan di CMS
        $file->move($cmsPath, $filename);

        // Copy juga ke FE (abaikan error agar tidak fatal di shared hosting)
        @copy($cmsPath . DIRECTORY_SEPARATOR . $filename, $fePath . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    /**
     * Hapus file di CMS & FE (jika ada).
     */
    private function deleteFile(?string $filename): void
    {
        if (!$filename) return;

        $cms = public_path('demo-xanders/images/community/' . $filename);
        $fe  = base_path('../demo-xanders/images/community/' . $filename);

        if (File::exists($cms)) {
            @File::delete($cms);
        }
        if (File::exists($fe)) {
            @File::delete($fe);
        }
    }
}
