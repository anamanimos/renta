<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;

class MigrateWordPressData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan sinkronisasi data User dan Produk dari database WordPress lama (RentaEnterprise) ke Laravel.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai sinkronisasi data dari WordPress legacy...");

        $this->migrateUsers();
        $this->migrateProducts();

        $this->info("Selesai! Seluruh data Master berhasil disinkronisasikan.");
    }

    private function migrateUsers()
    {
        $this->info("Mensinkronisasikan Users...");
        $wpUsers = DB::connection('mysql_wp')->table('wp_users')->get();
        $count = 0;

        foreach ($wpUsers as $wpUser) {
            // Coba ambil nomor telepon dari wp_usermeta (WooCommerce standar: billing_phone)
            $phoneMeta = DB::connection('mysql_wp')->table('wp_usermeta')
                            ->where('user_id', $wpUser->ID)
                            ->where('meta_key', 'billing_phone')
                            ->first();

            $phoneNumber = $phoneMeta ? $phoneMeta->meta_value : null;

            User::updateOrCreate(
                ['wp_user_id' => $wpUser->ID], // Acuan pencarian
                [
                    'name' => $wpUser->display_name ?: $wpUser->user_login,
                    'email' => $wpUser->user_email !== '' ? $wpUser->user_email : null,
                    'phone_number' => $phoneNumber,
                    // Karena WP hash password formatnya berbeda, Anda bisa reset password atau biarkan kosong
                    // untuk alur Passwordless (OTP WhatsApp)
                    // 'password' => $wpUser->user_pass, 
                    'role' => 'customer'
                ]
            );
            $count++;
        }

        $this->line("Berhasil mensinkronkan $count User.");
    }

    private function migrateProducts()
    {
        $this->info("Mensinkronisasikan Produk/Alat...");
        // Ambil wp_posts dengan type 'product' (WooCommerce) & status publish
        $wpPosts = DB::connection('mysql_wp')->table('wp_posts')
                        ->where('post_type', 'product')
                        ->where('post_status', 'publish')
                        ->get();
        $count = 0;

        foreach ($wpPosts as $wpPost) {
            // Ambil harga dan stok dari postmeta
            $priceMeta = DB::connection('mysql_wp')->table('wp_postmeta')->where('post_id', $wpPost->ID)->where('meta_key', '_price')->first();
            $stockMeta = DB::connection('mysql_wp')->table('wp_postmeta')->where('post_id', $wpPost->ID)->where('meta_key', '_stock')->first();

            Product::updateOrCreate(
                ['wp_post_id' => $wpPost->ID],
                [
                    'name' => $wpPost->post_title,
                    'slug' => cloneUniqueSlug($wpPost->post_name),
                    'description' => $wpPost->post_content,
                    'price_per_day' => $priceMeta ? (float) $priceMeta->meta_value : 0,
                    'stock_quantity' => $stockMeta && $stockMeta->meta_value ? (int) $stockMeta->meta_value : 1,
                    'is_active' => true
                ]
            );
            $count++;
        }

        $this->line("Berhasil mensinkronkan $count Produk.");
    }
}

// Helper kecil di luar class atau sesuaikan di dalam utk hindari slug ganda
function cloneUniqueSlug($slug) {
    if(Product::where('slug', $slug)->exists()) {
        return $slug . '-' . rand(100, 999);
    }
    return $slug;
}
