<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class MigrateWpCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:import-catalog {--fresh : Kosongkan tabel produk dan kategori sebelum import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Products and Categories from legacy WordPress database to new Laravel schema.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai Proses Migrasi Katalog dari WordPress...');

        if ($this->option('fresh')) {
            $this->warn('Opsi --fresh diaktifkan. Mengosongkan tabel terkait...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Product::truncate();
            Category::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->info('Tabel products dan categories berhasil dikosongkan.');
        }

        // 1. MIGRASI KATEGORI
        $this->info('Langkah 1: Mengambil Data Kategori (product_cat)...');
        
        $wpCategories = DB::connection('wp_legacy')
            ->table('wp_terms')
            ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
            ->where('wp_term_taxonomy.taxonomy', 'product_cat')
            ->select('wp_terms.term_id', 'wp_terms.name', 'wp_terms.slug', 'wp_term_taxonomy.parent')
            ->get();

        $catCount = 0;
        // Insert Pass 1: Semua Kategori Independen (Parent ID diabai dulu)
        foreach ($wpCategories as $wpCat) {
            Category::updateOrCreate(
                ['id' => $wpCat->term_id], // Pertahankan ID WP asli jika memungkinkan (opsional namun rekomen for mapping)
                [
                    'name' => $wpCat->name,
                    'slug' => urldecode($wpCat->slug),
                    'icon' => 'fas fa-box', // Default
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $catCount++;
        }

        // Insert Pass 2: Relasi Parent-Child (Menge-set Parent ID)
        foreach ($wpCategories as $wpCat) {
            if ($wpCat->parent > 0) {
                Category::where('id', $wpCat->term_id)->update(['parent_id' => $wpCat->parent]);
            }
        }
        $this->info("Berhasil memigrasi {$catCount} Kategori.");

        // 2. MIGRASI PRODUK
        $this->info('Langkah 2: Mengambil Data Produk (post_type = product)...');

        $wpProducts = DB::connection('wp_legacy')
            ->table('wp_posts')
            ->where('post_type', 'product')
            ->whereIn('post_status', ['publish', 'draft'])
            ->select('ID', 'post_title', 'post_name', 'post_content', 'post_status', 'guid')
            ->get();

        $prodCount = 0;
        $bar = $this->output->createProgressBar(count($wpProducts));
        $bar->start();

        foreach ($wpProducts as $wpProd) {
            // A. Ambil Meta Data Produk (Harga & Stok)
            $metaData = DB::connection('wp_legacy')
                ->table('wp_postmeta')
                ->where('post_id', $wpProd->ID)
                ->whereIn('meta_key', ['_regular_price', '_sale_price', '_stock', '_thumbnail_id'])
                ->pluck('meta_value', 'meta_key')
                ->toArray();

            $basePrice = isset($metaData['_regular_price']) && is_numeric($metaData['_regular_price']) ? $metaData['_regular_price'] : 0;
            $promoPrice = isset($metaData['_sale_price']) && is_numeric($metaData['_sale_price']) ? $metaData['_sale_price'] : null;
            $stock = isset($metaData['_stock']) && is_numeric($metaData['_stock']) ? $metaData['_stock'] : 10; // Default stock 10 jika null

            // B. Ambil ID Kategori Pertama dari Product (wp_term_relationships)
            // Woocommerce menghubungkan post_id dengan term_taxonomy_id (yang biasanya identik dengan term_id)
            $termRel = DB::connection('wp_legacy')
                ->table('wp_term_relationships')
                ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                ->where('wp_term_taxonomy.taxonomy', 'product_cat')
                ->where('wp_term_relationships.object_id', $wpProd->ID)
                ->first();

            $categoryId = $termRel ? $termRel->term_id : Category::first()->id ?? null; // Fallback ke term_id relasi atau ID Kategori Acak

            // C. Ambil URL Gambar Fitur (Thumbnail)
            $imageUrl = null;
            if (isset($metaData['_thumbnail_id'])) {
                $attachPost = DB::connection('wp_legacy')
                    ->table('wp_posts')
                    ->where('ID', $metaData['_thumbnail_id'])
                    ->first();
                if ($attachPost) {
                    $imageUrl = $attachPost->guid; // Biasanya memuat URL absolute, ex: http://domain/wp-content/uploads/x.jpg
                }
            }

            // D. Migrasi Data ke Model Product
            Product::updateOrCreate(
                ['wp_post_id' => $wpProd->ID], // Mapping Identifier
                [
                    'category_id' => $categoryId,
                    'name' => $wpProd->post_title,
                    'slug' => urldecode($wpProd->post_name),
                    'description' => $wpProd->post_content,
                    'price_type' => 'rental_flat', // Default semua harga diasumsikan rental flat Harian
                    'price_per_day' => $basePrice,
                    'promo_price' => $promoPrice,
                    'tier_price' => null, // WP tidak punya tier asli
                    'tier_promo_price' => null,
                    'stock_quantity' => $stock,
                    'image' => $imageUrl,
                    'is_active' => $wpProd->post_status === 'publish' ? 1 : 0,
                ]
            );

            $prodCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Berhasil memigrasi {$prodCount} Produk.");
        $this->info("✅ Proses Import Katalog WordPress ke Skema Renta Selesai!");
        
        return Command::SUCCESS;
    }
}
