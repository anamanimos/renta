<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;

class WpMigrationController extends Controller
{
    /**
     * Menampilkan Halaman Pratinjau Sinkronisasi Database WP
     */
    public function index()
    {
        try {
            $wpCategories = DB::connection('wp_legacy')
                ->table('wpej_terms')
                ->join('wpej_term_taxonomy', 'wpej_terms.term_id', '=', 'wpej_term_taxonomy.term_id')
                ->where('wpej_term_taxonomy.taxonomy', 'product_cat')
                ->select('wpej_terms.term_id', 'wpej_terms.name', 'wpej_term_taxonomy.count')
                ->get();

            $wpProducts = DB::connection('wp_legacy')
                ->table('wpej_posts')
                ->where('post_type', 'product')
                ->whereIn('post_status', ['publish', 'draft'])
                ->select('ID', 'post_title', 'post_status')
                ->paginate(15);

            $totalWpCat = $wpCategories->count();
            $totalWpProd = $wpProducts->total();
            
            // Get already imported WP IDs to disable the import button if already imported
            $importedProductIds = Product::whereNotNull('wp_post_id')->pluck('wp_post_id')->toArray();

            $connectionStatus = 'connected';
        } catch (\Exception $e) {
            $wpCategories = collect([]);
            $wpProducts = null;
            $totalWpCat = 0;
            $totalWpProd = 0;
            $importedProductIds = [];
            $connectionStatus = 'error: ' . $e->getMessage();
        }

        return view('admin.wp-migration.index', compact('wpCategories', 'wpProducts', 'totalWpCat', 'totalWpProd', 'connectionStatus', 'importedProductIds'));
    }

    /**
     * API Pratinjau Detail Satu Produk dari WP
     */
    public function showProduct($id)
    {
        try {
            $wpProd = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $id)->first();
            if (!$wpProd) return response()->json(['error' => 'Product not found'], 404);

            $metaData = DB::connection('wp_legacy')->table('wpej_postmeta')->where('post_id', $id)
                ->whereIn('meta_key', ['_regular_price', '_sale_price', '_stock', '_thumbnail_id'])
                ->pluck('meta_value', 'meta_key')->toArray();

            $termRel = DB::connection('wp_legacy')->table('wpej_term_relationships')
                ->join('wpej_term_taxonomy', 'wpej_term_relationships.term_taxonomy_id', '=', 'wpej_term_taxonomy.term_taxonomy_id')
                ->join('wpej_terms', 'wpej_term_taxonomy.term_id', '=', 'wpej_terms.term_id')
                ->where('wpej_term_taxonomy.taxonomy', 'product_cat')
                ->where('wpej_term_relationships.object_id', $id)
                ->select('wpej_terms.name')
                ->first();

            $imageUrl = null;
            if (isset($metaData['_thumbnail_id'])) {
                $attachPost = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $metaData['_thumbnail_id'])->first();
                if ($attachPost) $imageUrl = $attachPost->guid;
            }

            return response()->json([
                'id' => $wpProd->ID,
                'name' => $wpProd->post_title,
                'status' => $wpProd->post_status,
                'price' => number_format($metaData['_regular_price'] ?? 0, 0, ',', '.'),
                'stock' => $metaData['_stock'] ?? 'N/A',
                'category' => $termRel ? $termRel->name : 'Tanpa Kategori',
                'image' => $imageUrl,
                'url' => 'https://rentaenterprise.com/product/' . $wpProd->post_name // Tautan web asli
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Impor Satu Produk beserta Pemetaan Kategori nya
     */
    public function importProduct(Request $request, $id)
    {
        try {
            $wpProd = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $id)->first();
            if (!$wpProd) return back()->with('error', 'Produk tidak ditemukan di WordPress.');

            $metaData = DB::connection('wp_legacy')->table('wpej_postmeta')->where('post_id', $id)
                ->pluck('meta_value', 'meta_key')->toArray();

            $basePrice = isset($metaData['_regular_price']) && is_numeric($metaData['_regular_price']) ? $metaData['_regular_price'] : 0;
            $promoPrice = isset($metaData['_sale_price']) && is_numeric($metaData['_sale_price']) ? $metaData['_sale_price'] : null;
            $stock = isset($metaData['_stock']) && is_numeric($metaData['_stock']) ? $metaData['_stock'] : 10;

            // Memetakan dan secara rekursif mengimpor kategori jika ada
            $termRel = DB::connection('wp_legacy')->table('wpej_term_relationships')
                ->join('wpej_term_taxonomy', 'wpej_term_relationships.term_taxonomy_id', '=', 'wpej_term_taxonomy.term_taxonomy_id')
                ->where('wpej_term_taxonomy.taxonomy', 'product_cat')
                ->where('wpej_term_relationships.object_id', $wpProd->ID)
                ->first();

            $categoryId = null;
            if ($termRel) {
                $categoryId = $this->importCategoryRecursive($termRel->term_id);
            }

            $imageUrl = null;
            if (isset($metaData['_thumbnail_id'])) {
                $attachPost = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $metaData['_thumbnail_id'])->first();
                if ($attachPost) $imageUrl = $attachPost->guid;
            }

            Product::updateOrCreate(
                ['wp_post_id' => $wpProd->ID],
                [
                    'category_id' => $categoryId,
                    'name' => $wpProd->post_title,
                    'slug' => urldecode($wpProd->post_name),
                    'description' => $wpProd->post_content,
                    'price_type' => 'rental_flat',
                    'price_per_day' => $basePrice,
                    'promo_price' => $promoPrice,
                    'stock_quantity' => $stock,
                    'image' => $imageUrl,
                    'is_active' => $wpProd->post_status === 'publish' ? 1 : 0,
                ]
            );

            return back()->with('success', "Produk '{$wpProd->post_title}' berhasil diimpor.");

        } catch (\Exception $e) {
            return back()->with('error', "Gagal mengimpor produk: " . $e->getMessage());
        }
    }

    /**
     * Helper Privat untuk Impor Kategori Beserta Induknya (Mencegah masalah Hierarki)
     */
    private function importCategoryRecursive($termId)
    {
        if (!$termId) return null;

        $wpCat = DB::connection('wp_legacy')->table('wpej_terms')
            ->join('wpej_term_taxonomy', 'wpej_terms.term_id', '=', 'wpej_term_taxonomy.term_id')
            ->where('wpej_term_taxonomy.taxonomy', 'product_cat')
            ->where('wpej_terms.term_id', $termId)
            ->select('wpej_terms.*', 'wpej_term_taxonomy.parent')
            ->first();

        if ($wpCat) {
            $parentId = null;
            // Jika punya induk, impor induknya dulu secara rekursif
            if ($wpCat->parent > 0) {
                $parentId = $this->importCategoryRecursive($wpCat->parent);
            }
            
            $cat = Category::updateOrCreate(
                ['id' => $wpCat->term_id],
                [
                    'name' => $wpCat->name,
                    'slug' => urldecode($wpCat->slug),
                    'icon' => 'fas fa-box', // Ikon Default
                    'parent_id' => $parentId
                ]
            );
            return $cat->id;
        }
        return null;
    }
}
