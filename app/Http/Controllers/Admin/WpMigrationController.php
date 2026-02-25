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
    private function extractRnbData($postId)
    {
        $metaData = DB::connection('wp_legacy')->table('wpej_postmeta')->where('post_id', $postId)
            ->whereIn('meta_key', [
                'pricing_type', 'general_price', 'quantity', 
                'redq_day_ranges_cost', '_thumbnail_id'
            ])
            ->pluck('meta_value', 'meta_key')->toArray();

        $pricingType = $metaData['pricing_type'] ?? 'general_pricing';
        $quantity = isset($metaData['quantity']) && is_numeric($metaData['quantity']) ? $metaData['quantity'] : 10;
        
        $basePrice = 0;
        $tierPrice = null; // harga kenaikan harian
        $rentaPriceType = 'rental_flat';

        if ($pricingType === 'days_range' && isset($metaData['redq_day_ranges_cost'])) {
            $rentaPriceType = 'rental_tiered';
            $daysArray = @unserialize($metaData['redq_day_ranges_cost']);
            if (is_array($daysArray) && count($daysArray) > 0) {
                // Harga hari ke-1
                $day1 = collect($daysArray)->firstWhere('min_days', '1');
                $day2 = collect($daysArray)->firstWhere('min_days', '2');
                
                $basePrice = $day1 ? intval($day1['range_cost']) : 0;
                
                if ($day2) {
                    $tierPrice = intval($day2['range_cost']) - $basePrice; 
                }
            }
        } else {
            // General Pricing default RnB
            $basePrice = isset($metaData['general_price']) && is_numeric($metaData['general_price']) ? $metaData['general_price'] : 0;
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
            'image' => $imageUrl
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

            $priceText = number_format($rnbData['base_price'], 0, ',', '.');
            if ($rnbData['price_type'] == 'rental_tiered' && $rnbData['tier_price']) {
                $priceText .= " (Tiers " . number_format($rnbData['tier_price'], 0, ',', '.') . "/hari berikutnya)";
            }

            return response()->json([
                'id' => $wpProd->ID,
                'name' => $wpProd->post_title,
                'status' => $wpProd->post_status,
                'price' => $priceText,
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
