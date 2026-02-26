<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;
use App\Models\Wishlist;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.header', function ($view) {
            $userId = auth()->id();
            $sessionId = Session::getId();

            // Cart data
            $cart = Cart::with('items')->where(function($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId)->whereNull('user_id');
                }
            })->first();

            $cartCount = $cart ? $cart->items->sum('quantity') : 0;
            $cartTotal = $cart ? $cart->items->sum('subtotal') : 0;

            // Wishlist data
            $wishlistCount = Wishlist::where(function($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId)->whereNull('user_id');
                }
            })->count();

            $view->with(compact('cartCount', 'cartTotal', 'wishlistCount'));
        });
    }
}
