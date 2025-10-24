<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Categories;

class CategoryProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Categories::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // kamu bisa ganti ke paginate kalau mau
        $categories = $query->orderBy('created_at', 'desc')->get();

        return view('dash.admin.category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        Categories::create($validated);

        return redirect()->route('category.index')->with('success', 'categories created successfully!');
    }

    public function update(Request $request, Categories $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        ]);
    
        $category->update($validated);
    
        return redirect()->route('category.index')->with('success', 'Category updated successfully!');
    }
    

    public function destroy(Categories $category)
    {
        $category->delete();

        return back()->with('success', 'categories deleted successfully!');
    }
}
