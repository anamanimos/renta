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
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');

            $wpCategories = DB::connection('wp_legacy')
                ->table('wpej_terms')
                ->join('wpej_term_taxonomy', 'wpej_terms.term_id', '=', 'wpej_term_taxonomy.term_id')
                ->where('wpej_term_taxonomy.taxonomy', 'product_cat')
                ->select('wpej_terms.term_id', 'wpej_terms.name', 'wpej_term_taxonomy.count')
                ->get();

            $query = DB::connection('wp_legacy')
                ->table('wpej_posts')
                ->where('post_type', 'product')
                ->whereIn('post_status', ['publish', 'draft'])
                ->select('ID', 'post_title', 'post_status');

            if ($search) {
                $query->where('post_title', 'like', '%' . $search . '%');
            }

            $wpProducts = $query->paginate(15)->appends(['search' => $search]);

            $totalWpCat = $wpCategories->count();
            
            // Re-query total without search for global stat
            $totalWpProd = DB::connection('wp_legacy')
                ->table('wpej_posts')
                ->where('post_type', 'product')
                ->whereIn('post_status', ['publish', 'draft'])
                ->count();
            
            $importedProductIds = Product::whereNotNull('wp_post_id')->pluck('wp_post_id')->toArray();

            $connectionStatus = 'connected';
        } catch (\Exception $e) {
            $search = '';
            $wpCategories = collect([]);
            $wpProducts = null;
            $totalWpCat = 0;
            $totalWpProd = 0;
            $importedProductIds = [];
            $connectionStatus = 'error: ' . $e->getMessage();
        }

        return view('admin.wp-migration.index', compact('wpCategories', 'wpProducts', 'totalWpCat', 'totalWpProd', 'connectionStatus', 'importedProductIds', 'search'));
    }

    /**
     * Mengekstrak Logika Harga dan Stok Khusus Plugin RnB
     */
    private function extractRnbData($productId)
    {
        // 1. Cek apakah Product terhubung dengan Inventory RnB terpisah
        $invRel = DB::connection('wp_legacy')->table('wpej_postmeta')
            ->where('post_id', $productId)
            ->where('meta_key', '_redq_product_inventory')
            ->first();

        // Target target meta, default ke product
        $targetId = $productId;
        if ($invRel && !empty($invRel->meta_value)) {
            $invArray = @unserialize($invRel->meta_value);
            if (is_array($invArray) && isset($invArray[0])) {
                $targetId = $invArray[0]; // Ambil Inventory ID
            }
        }

        // 2. Ambil MetaData dari Target ID (Inventory atau Product)
        $metaData = DB::connection('wp_legacy')->table('wpej_postmeta')->where('post_id', $targetId)
            ->whereIn('meta_key', [
                'pricing_type', 'general_price', 'quantity', 
                'redq_day_ranges_cost', 'redq_custom_pricing', 'redq_daily_pricing', '_thumbnail_id', '_price'
            ])
            ->pluck('meta_value', 'meta_key')->toArray();

        // 3. Setup Default
        $pricingType = $metaData['pricing_type'] ?? 'general_pricing';
        $quantity = isset($metaData['quantity']) && is_numeric($metaData['quantity']) ? $metaData['quantity'] : 10;
        
        $basePrice = isset($metaData['_price']) ? intval($metaData['_price']) : 0; // Fallback unit price
        $tierPrice = null; 
        $rentaPriceType = 'rental_flat';
        $priceDesc = '';

        // Kasus 1: Days Range (Biasanya Hari 1, Hari 2 dst terstruktur jelas)
        if ($pricingType === 'days_range' && isset($metaData['redq_day_ranges_cost'])) {
            $rentaPriceType = 'rental_tiered';
            $daysArray = @unserialize($metaData['redq_day_ranges_cost']);
            if (is_array($daysArray) && count($daysArray) > 0) {
                $day1 = collect($daysArray)->firstWhere('min_days', '1');
                $day2 = collect($daysArray)->firstWhere('min_days', '2');
                
                if ($day1) {
                    $basePrice = intval($day1['range_cost']);
                    $priceDesc = "Hari pertama: Rp " . number_format($basePrice, 0, ',', '.');
                }
                if ($day2) {
                    $tierPrice = intval($day2['range_cost']) - $basePrice; 
                    $priceDesc .= " | Hari berikutnya: Rp " . number_format($tierPrice, 0, ',', '.');
                }
            }
        } 
        // Kasus 2: Custom / Daily Pricing (Admin WP memakai slot Friday untuk Hari Ke-1, Saturday Hari Ke-2)
        elseif ($pricingType === 'custom_pricing' || $pricingType === 'daily_pricing') {
            $rentaPriceType = 'rental_tiered';
            $dailyArrayText = $metaData['redq_custom_pricing'] ?? ($metaData['redq_daily_pricing'] ?? null);
            $dailyArray = $dailyArrayText ? @unserialize($dailyArrayText) : [];
            
            if (is_array($dailyArray)) {
                // Sesuai temuan, 'friday' sering ditarik sebagai harga dasar, 'saturday' hari lanjutan
                $d1 = isset($dailyArray['friday']) ? intval($dailyArray['friday']) : 0;
                $d2 = isset($dailyArray['saturday']) ? intval($dailyArray['saturday']) : 0;
                
                if ($d1 > 0) {
                    $basePrice = $d1;
                    $priceDesc = "Hari pertama: Rp " . number_format($basePrice, 0, ',', '.');
                }
                if ($d2 > 0) {
                    $tierPrice = $d2; // Ini adalah harga satuan hari berikut via custom_pricing
                    $priceDesc .= " | Hari berikutnya: Rp " . number_format($tierPrice, 0, ',', '.');
                }
            }
        } 
        // Kasus 3: General Pricing RNB
        else {
            if (isset($metaData['general_price']) && is_numeric($metaData['general_price']) && $metaData['general_price'] > 0) {
                $basePrice = intval($metaData['general_price']);
            }
            $priceDesc = "Tarif Harga Tetap: Rp " . number_format($basePrice, 0, ',', '.') . " per hari";
        }

        // Thumbnail Image
        $imageUrl = null;
        if (isset($metaData['_thumbnail_id'])) {
            $attachPost = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $metaData['_thumbnail_id'])->first();
            if ($attachPost) $imageUrl = $attachPost->guid;
        }

        return [
            'price_type' => $rentaPriceType,
            'base_price' => $basePrice,
            'tier_price' => $tierPrice,
            'stock' => $quantity,
            'image' => $imageUrl,
            'description' => $priceDesc
        ];
    }

    /**
     * API Pratinjau Detail Satu Produk dari WP
     */
    public function showProduct($id)
    {
        try {
            $wpProd = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $id)->first();
            if (!$wpProd) return response()->json(['error' => 'Product not found'], 404);

            $rnbData = $this->extractRnbData($id);

            $termRel = DB::connection('wp_legacy')->table('wpej_term_relationships')
                ->join('wpej_term_taxonomy', 'wpej_term_relationships.term_taxonomy_id', '=', 'wpej_term_taxonomy.term_taxonomy_id')
                ->join('wpej_terms', 'wpej_term_taxonomy.term_id', '=', 'wpej_terms.term_id')
                ->where('wpej_term_taxonomy.taxonomy', 'product_cat')
                ->where('wpej_term_relationships.object_id', $id)
                ->select('wpej_terms.name')
                ->first();

            return response()->json([
                'id' => $wpProd->ID,
                'name' => $wpProd->post_title,
                'status' => $wpProd->post_status,
                'price' => $rnbData['description'],
                'price_type_label' => $rnbData['price_type'] == 'rental_tiered' ? 'Harga Bertingkat (RnB Day Based)' : 'Harga Flat Rata (RnB General)',
                'stock' => $rnbData['stock'],
                'category' => $termRel ? $termRel->name : 'Tanpa Kategori',
                'image' => $rnbData['image'],
                'url' => 'https://rentaenterprise.com/product/' . $wpProd->post_name
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

            $rnbData = $this->extractRnbData($id);

            // Memetakan kategori
            $termRel = DB::connection('wp_legacy')->table('wpej_term_relationships')
                ->join('wpej_term_taxonomy', 'wpej_term_relationships.term_taxonomy_id', '=', 'wpej_term_taxonomy.term_taxonomy_id')
                ->where('wpej_term_taxonomy.taxonomy', 'product_cat')
                ->where('wpej_term_relationships.object_id', $wpProd->ID)
                ->first();

            $categoryId = null;
            if ($termRel) {
                $categoryId = $this->importCategoryRecursive($termRel->term_id);
            }

            Product::updateOrCreate(
                ['wp_post_id' => $wpProd->ID],
                [
                    'category_id' => $categoryId,
                    'name' => $wpProd->post_title,
                    'slug' => urldecode($wpProd->post_name),
                    'description' => $wpProd->post_content,
                    'price_type' => $rnbData['price_type'],
                    'price_per_day' => $rnbData['base_price'],
                    'tier_price' => $rnbData['tier_price'],
                    'promo_price' => null, // Abaikan promo dr WP bila tidak selaras
                    'stock_quantity' => $rnbData['stock'],
                    'image' => $rnbData['image'],
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
