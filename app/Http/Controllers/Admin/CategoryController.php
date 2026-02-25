<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with(['children' => function($q) {
            $q->withCount('products')->orderBy('name', 'asc');
        }])->withCount('products')
          ->whereNull('parent_id')
          ->orderBy('name', 'asc')
          ->get();
          
        $allCategories = Category::orderBy('name', 'asc')->get();

        return view('admin.categories.index', compact('categories', 'allCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // View create ada dalam modal/sidebar atau bisa via route view terpisah
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:100'
        ]);

        $validated['slug'] = Str::slug($request->name);
        Category::create($validated);

        return back()->with('success', 'Kategori Peralatan baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:100'
        ]);

        // Cegah kategori jadi parent dari dirinya sendiri
        if ($validated['parent_id'] == $id) {
            return back()->with('error', 'Kategori tidak bisa menjadi induk untuk dirinya sendiri.');
        }

        $validated['slug'] = Str::slug($request->name);
        $category->update($validated);

        return back()->with('success', 'Kategori Peralatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        
        // Mencegah penghapusan jika ada relasi Produk untuk memelihara integritas rentetan Transaksi
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Gagal Menghapus: Terdapat Peralatan yang menggunakan Label Kategori ini.');
        }

        $category->delete();
        return back()->with('success', 'Kategori Peralatan berhasil dihapus dari sistem.');
    }
}
