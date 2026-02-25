<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');

Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products.index');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [App\Http\Controllers\CartController::class, 'add'])->name('add');
    Route::put('/update/{item}', [App\Http\Controllers\CartController::class, 'update'])->name('update');
    Route::delete('/remove/{item}', [App\Http\Controllers\CartController::class, 'remove'])->name('remove');
    Route::post('/dates', [App\Http\Controllers\CartController::class, 'setDates'])->name('dates');
    Route::post('/coupon', [App\Http\Controllers\CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/coupon', [App\Http\Controllers\CartController::class, 'removeCoupon'])->name('coupon.remove');
});

Route::prefix('wp-admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/authenticate', [App\Http\Controllers\Admin\AuthController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
    
    // API Otentikasi Lanjutan Admin
    Route::post('/api/auth/send-otp', [App\Http\Controllers\Admin\AuthController::class, 'sendOtp']);
    Route::post('/api/auth/verify-otp', [App\Http\Controllers\Admin\AuthController::class, 'verifyOtp']);
    
    // Rute yang butuh status Admin
    Route::middleware(['auth', 'is_admin'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
        Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('orders', App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show', 'update']);

        // Pengguna
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Laporan
        Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

        // Kupon
        Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class)->except(['create', 'show', 'edit']);

        // Halaman
        Route::resource('pages', App\Http\Controllers\Admin\PageController::class)->except(['show']);

        // Artikel
        Route::resource('articles', App\Http\Controllers\Admin\ArticleController::class)->except(['show']);

        // Pengaturan
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'general'])->name('settings.general');
        Route::post('/settings/general', [App\Http\Controllers\Admin\SettingController::class, 'updateGeneral'])->name('settings.general.update');

        Route::get('/settings/payment', [App\Http\Controllers\Admin\SettingController::class, 'payment'])->name('settings.payment');
        Route::post('/settings/payment', [App\Http\Controllers\Admin\SettingController::class, 'storePayment'])->name('settings.payment.store');
        Route::delete('/settings/payment/{index}', [App\Http\Controllers\Admin\SettingController::class, 'destroyPayment'])->name('settings.payment.destroy');

        Route::get('/settings/whatsapp', [App\Http\Controllers\Admin\SettingController::class, 'whatsapp'])->name('settings.whatsapp');
        Route::post('/settings/whatsapp', [App\Http\Controllers\Admin\SettingController::class, 'updateWhatsapp'])->name('settings.whatsapp.update');

        Route::get('/settings/logs', [App\Http\Controllers\Admin\SettingController::class, 'logs'])->name('settings.logs');
    });
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/logout', function (Illuminate\Http\Request $request) {
    Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth');

// Rute Pengguna Terautentikasi
Route::middleware('auth')->group(function () {
    // Checkout & Orders
    Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    
    // Profil & Riwayat Pesanan
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [ProfileController::class, 'update']);
    
    // Address Book
    Route::resource('addresses', AddressController::class)->except(['show', 'create', 'edit']);
    Route::put('/addresses/{address}/set-main', [AddressController::class, 'setMain']);
    
    // Order History
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    
    // Payment
    Route::get('/payment/{order}', [OrderController::class, 'payment'])->name('payment.show');
    Route::post('/payment/{order}/upload', [OrderController::class, 'uploadPayment'])->name('payment.upload');
});

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request')->middleware('guest');

// Autentikasi OTP & Login Routes
Route::post('/api/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/api/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/api/auth/register', [AuthController::class, 'registerWithOtp']);
Route::post('/api/auth/login-password', [AuthController::class, 'loginWithPassword']);
Route::post('/api/auth/forgot-password-otp', [AuthController::class, 'sendResetOtp']);
Route::post('/api/auth/reset-password', [AuthController::class, 'resetPassword']);
