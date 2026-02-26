<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
     * Mengekstrak Logika Harga dan Stok Khusus Plugin RnB (Dengan Dukungan Multi-Varian)
     */
    private function extractRnbData($productId)
    {
        // 1. Meta Primer Produk (WooCommerce Dasar)
        $prodMeta = DB::connection('wp_legacy')->table('wpej_postmeta')->where('post_id', $productId)
            ->whereIn('meta_key', ['_price', '_regular_price', '_thumbnail_id', '_stock', '_redq_product_inventory', 'pricing_type', 'rnb_settings_for_display'])
            ->pluck('meta_value', 'meta_key')->toArray();

        // 2. Cek apakah ada relasi ke Inventory RnB (Bisa lebih dari 1)
        $targetIds = [$productId];
        $isRnb = false;

        if (isset($prodMeta['_redq_product_inventory']) && !empty($prodMeta['_redq_product_inventory'])) {
            $invArray = @unserialize($prodMeta['_redq_product_inventory']);
            if (is_array($invArray) && count($invArray) > 0) {
                $targetIds = array_values($invArray); // Ambil Semua Inventory ID
                $isRnb = true;
            }
        } elseif (isset($prodMeta['pricing_type']) || isset($prodMeta['rnb_settings_for_display'])) {
            $isRnb = true;
        }

        $variants = [];
        $baseFallbackPrice = isset($prodMeta['_price']) ? intval($prodMeta['_price']) : (isset($prodMeta['_regular_price']) ? intval($prodMeta['_regular_price']) : 0);
        $baseFallbackStock = isset($prodMeta['_stock']) && is_numeric($prodMeta['_stock']) ? intval($prodMeta['_stock']) : 10;

        foreach ($targetIds as $targetId) {
            $targetPost = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $targetId)->first();
            $metaData = DB::connection('wp_legacy')->table('wpej_postmeta')->where('post_id', $targetId)
                ->whereIn('meta_key', [
                    'pricing_type', 'general_price', 'quantity', 
                    'redq_day_ranges_cost', 'redq_custom_pricing', 'redq_daily_pricing'
                ])
                ->pluck('meta_value', 'meta_key')->toArray();

            $basePrice = $baseFallbackPrice;
            $tierPrice = null; 
            $pricingType = $metaData['pricing_type'] ?? null;
            $quantity = $baseFallbackStock;
            
            $rentaPriceType = 'beli_putus';
            $priceTypeLabel = 'Beli Putus (WooCommerce)';
            $priceDesc = "Harga Beli: Rp " . number_format($basePrice, 0, ',', '.');
            
            if ($isRnb) {
                $quantity = isset($metaData['quantity']) && is_numeric($metaData['quantity']) ? $metaData['quantity'] : $baseFallbackStock;
                
                if ($pricingType === 'custom_pricing' || $pricingType === 'days_range' || $pricingType === 'daily_pricing') {
                    $rentaPriceType = 'custom_pricing';
                    $priceTypeLabel = 'Harga Sewa Berbeda per Hari (RnB Custom/Days)';
                    
                    $dailyArrayText = $metaData['redq_custom_pricing'] ?? ($metaData['redq_daily_pricing'] ?? null);
                    if ($pricingType === 'days_range' && isset($metaData['redq_day_ranges_cost'])) {
                        $daysArray = @unserialize($metaData['redq_day_ranges_cost']);
                        if (is_array($daysArray) && count($daysArray) > 0) {
                            $day1 = collect($daysArray)->firstWhere('min_days', '1');
                            $day2 = collect($daysArray)->firstWhere('min_days', '2');
                            if ($day1) $basePrice = intval($day1['range_cost']);
                            if ($day2) $tierPrice = intval($day2['range_cost']) - $basePrice;
                        }
                    } elseif ($dailyArrayText) {
                        $dailyArray = @unserialize($dailyArrayText);
                        if (is_array($dailyArray)) {
                            $d1 = isset($dailyArray['friday']) ? intval($dailyArray['friday']) : 0;
                            $d2 = isset($dailyArray['saturday']) ? intval($dailyArray['saturday']) : 0;
                            if ($d1 > 0) $basePrice = $d1;
                            if ($d2 > 0) $tierPrice = $d2;
                        }
                    }
                    
                    $priceDesc = "Hari pertama: Rp " . number_format($basePrice, 0, ',', '.');
                    if ($tierPrice) {
                        $priceDesc .= " | Hari berikutnya: Rp " . number_format($tierPrice, 0, ',', '.');
                    }
                } else {
                    $rentaPriceType = 'general_pricing';
                    $priceTypeLabel = 'Harga Sewa Tetap (RnB General)';
                    
                    if (isset($metaData['general_price']) && is_numeric($metaData['general_price']) && $metaData['general_price'] > 0) {
                        $basePrice = intval($metaData['general_price']);
                    }
                    $priceDesc = "Tarif Harga Tetap: Rp " . number_format($basePrice, 0, ',', '.') . " per hari";
                }
            }

            $variants[] = [
                'wp_inventory_id' => $targetId,
                'name' => $targetPost ? $targetPost->post_title : 'Varian',
                'price_type' => $rentaPriceType,
                'price_type_label' => $priceTypeLabel,
                'base_price' => $basePrice,
                'tier_price' => $tierPrice,
                'stock' => $quantity,
                'description' => $priceDesc
            ];
        }

        // 4. Thumbnail Image (Selalu dari ID Produk aslinya)
        $imageUrl = null;
        
        if (isset($prodMeta['_thumbnail_id'])) {
            $attachPost = DB::connection('wp_legacy')->table('wpej_posts')->where('ID', $prodMeta['_thumbnail_id'])->first();
            if ($attachPost) {
                // Untuk Pratinjau UI & URL Sumber Unduhan
                $imageUrl = str_replace('.test', '.com', $attachPost->guid);
            }
        }

        $mainVariant = $variants[0] ?? [];

        return [
            'price_type' => $mainVariant['price_type'] ?? 'beli_putus',
            'price_type_label' => $mainVariant['price_type_label'] ?? 'Beli Putus',
            'base_price' => $mainVariant['base_price'] ?? 0,
            'tier_price' => $mainVariant['tier_price'] ?? null,
            'stock' => $mainVariant['stock'] ?? 10,
            'description' => $mainVariant['description'] ?? '',
            'image_url' => $imageUrl,
            'variants' => $variants
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
                
            $descriptionText = strip_tags($wpProd->post_content);
            $descriptionText = (mb_strlen($descriptionText) > 200) ? mb_substr($descriptionText, 0, 200) . '...' : ($descriptionText ?: 'Tanpa deskripsi.');

            return response()->json([
                'id' => $wpProd->ID,
                'name' => $wpProd->post_title,
                'status' => $wpProd->post_status,
                'description' => $descriptionText,
                'price' => $rnbData['base_price'],
                'tier_price' => $rnbData['tier_price'] ?? null,
                'price_type_label' => $rnbData['price_type_label'],
                'stock' => $rnbData['stock'],
                'category' => $termRel ? $termRel->name : 'Tanpa Kategori',
                'image' => $rnbData['image_url'],
                'variants' => $rnbData['variants'],
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

            $mappedPriceType = 'rental_flat';
            if ($rnbData['price_type'] == 'beli_putus') $mappedPriceType = 'sell_once';
            elseif ($rnbData['price_type'] == 'custom_pricing') $mappedPriceType = 'rental_tiered';

            $finalImageUrl = null;
            if (!empty($rnbData['image_url'])) {
                $existingProduct = Product::where('wp_post_id', $wpProd->ID)->first();
                // Hemat API: Jika produk sudah ada dan memegang URL rekaman Cloudinary, cukup setorkan yang lama kembali
                if ($existingProduct && Str::contains($existingProduct->image, 'res.cloudinary.com')) {
                    $finalImageUrl = $existingProduct->image;
                } else {
                    try {
                        // Bypass Cloudflare Hotlink Protection dengan mendownload gambar via perantara backend 
                        $imgResponse = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                            ->withHeaders([
                                'Referer' => 'https://rentaenterprise.com/',
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                            ])->get($rnbData['image_url']);
                            
                        if ($imgResponse->successful()) {
                            $tmpPath = sys_get_temp_dir() . '/' . uniqid('renta_wp_') . '.png';
                            file_put_contents($tmpPath, $imgResponse->body());
                            
                            $cloudinary = new \Cloudinary\Cloudinary(config('cloudinary.cloud_url'));
                            $uploadApi = new \Cloudinary\Api\Upload\UploadApi($cloudinary->configuration);
                            
                            $uploaded = $uploadApi->upload($tmpPath, [
                                'folder' => 'renta/products'
                            ]);
                            
                            $finalImageUrl = $uploaded['secure_url'] ?? $rnbData['image_url'];
                            if (file_exists($tmpPath)) @unlink($tmpPath);
                        } else {
                            throw new \Exception("Gagal ditarik (HTTP " . $imgResponse->status() . ")");
                        }
                    } catch (\Exception $e) {
                        // Opsi darurat
                        \Illuminate\Support\Facades\Log::error('Cloudinary Migrate Error: ' . $e->getMessage());
                        $finalImageUrl = $rnbData['image_url'];
                    }
                }
            }

            $product = Product::updateOrCreate(
                ['wp_post_id' => $wpProd->ID],
                [
                    'category_id' => $categoryId,
                    'name' => $wpProd->post_title,
                    'slug' => urldecode($wpProd->post_name),
                    'description' => $wpProd->post_content,
                    'price_type' => $mappedPriceType,
                    'price_per_day' => $rnbData['base_price'],
                    'tier_price' => $rnbData['tier_price'],
                    'promo_price' => null, // Abaikan promo dr WP bila tidak selaras
                    'stock_quantity' => $rnbData['stock'],
                    'image' => $finalImageUrl,
                    'is_active' => $wpProd->post_status === 'publish' ? 1 : 0,
                ]
            );

            // Import Variants
            if (isset($rnbData['variants']) && is_array($rnbData['variants'])) {
                foreach ($rnbData['variants'] as $v) {
                    \App\Models\ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id, 
                            'wp_inventory_id' => $v['wp_inventory_id']
                        ],
                        [
                            'name' => $v['name'],
                            'price_type' => $v['price_type'],
                            'price_per_day' => $v['base_price'],
                            'tier_price' => $v['tier_price'],
                            'stock_quantity' => $v['stock'],
                        ]
                    );
                }
            }

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
                    'slug' => \Illuminate\Support\Str::slug(urldecode($wpCat->slug)) . '-' . $wpCat->term_id,
                    'icon' => 'fas fa-box', // Ikon Default
                    'parent_id' => $parentId
                ]
            );

            return $cat->id;
        }
        return null;
    }
}
