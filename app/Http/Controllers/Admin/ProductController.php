<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.form', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_type' => 'required|in:rental_flat,rental_tiered,sell_once',
            'price_per_day' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'tier_price' => 'nullable|numeric|min:0',
            'tier_promo_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $validated['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadedFileUrl = cloudinary()->upload($file->getRealPath(), [
                'folder' => 'renta/products'
            ])->getSecurePath();
            $validated['image'] = $uploadedFileUrl;
        }

        $product = Product::create($validated);

        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $variantData) {
                $vPriceType = ($product->price_type === 'rental_tiered') ? 'custom_pricing' : 'general_pricing';
                $product->variants()->create([
                    'name' => $variantData['name'],
                    'price_type' => $vPriceType,
                    'price_per_day' => $variantData['price_per_day'],
                    'tier_price' => $variantData['tier_price'] ?? null,
                    'stock_quantity' => $variantData['stock_quantity'],
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan ke katalog.');
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
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('admin.products.form', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_type' => 'required|in:rental_flat,rental_tiered,sell_once',
            'price_per_day' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'tier_price' => 'nullable|numeric|min:0',
            'tier_promo_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $validated['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadedFileUrl = cloudinary()->upload($file->getRealPath(), [
                'folder' => 'renta/products'
            ])->getSecurePath();
            $validated['image'] = $uploadedFileUrl;
        }

        $product->update($validated);

        if ($request->has('variants') && is_array($request->variants)) {
            $activeVariantIds = [];
            foreach ($request->variants as $variantData) {
                $vPriceType = ($product->price_type === 'rental_tiered') ? 'custom_pricing' : 'general_pricing';
                
                if (isset($variantData['id']) && is_numeric($variantData['id'])) {
                    $variant = $product->variants()->updateOrCreate(
                        ['id' => $variantData['id']],
                        [
                            'name' => $variantData['name'],
                            'price_type' => $vPriceType,
                            'price_per_day' => $variantData['price_per_day'],
                            'tier_price' => $variantData['tier_price'] ?? null,
                            'stock_quantity' => $variantData['stock_quantity'],
                        ]
                    );
                } else {
                    $variant = $product->variants()->create([
                        'name' => $variantData['name'],
                        'price_type' => $vPriceType,
                        'price_per_day' => $variantData['price_per_day'],
                        'tier_price' => $variantData['tier_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'],
                    ]);
                }
                $activeVariantIds[] = $variant->id;
            }
            // Hapus varian yang dihapus oleh admin dari view
            $product->variants()->whereNotIn('id', $activeVariantIds)->delete();
        } else {
            // Hapus semua jika tidak ada varian yang dikirim
            $product->variants()->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Informasi produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus dari sistem.');
    }
}
