<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    private function getCart()
    {
        $userId = auth()->id();
        $sessionId = Session::getId();

        if ($userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
        } else {
            $cart = Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
        }
        return $cart;
    }

    public function index()
    {
        $cart = $this->getCart()->load('items.product');
        return view('pages.cart', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $cart = $this->getCart();

        $variantId = $request->input('variant_id');

        $cartItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('variant_id', $variantId)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'quantity' => 1
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function update(Request $request, CartItem $item)
    {
        $cart = $this->getCart();
        if ($item->cart_id !== $cart->id) {
            abort(403);
        }

        $request->validate(['quantity' => 'required|integer|min:1']);
        $item->update(['quantity' => $request->quantity]);

        return redirect()->route('cart.index')->with('success', 'Kuantitas berhasil diperbarui!');
    }

    public function remove(CartItem $item)
    {
        $cart = $this->getCart();
        if ($item->cart_id !== $cart->id) {
            abort(403);
        }

        $item->delete();
        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang.');
    }

    public function setDates(Request $request)
    {
        $cart = $this->getCart();
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);
        
        // +1 hari karena min pemakaian sewa dihitung 1 hari penuh meski start/end di hari sama
        $totalDays = $start->diffInDays($end) + 1;

        $cart->update([
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => $totalDays
        ]);

        return redirect()->route('cart.index')->with('success', 'Periode sewa berhasil diatur!');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string']);
        $cart = $this->getCart();
        
        $coupon = \App\Models\Coupon::where('code', $request->coupon_code)
                    ->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>=', now());
                    })->first();

        if (!$coupon) {
            return back()->with('error', 'Kupon tidak valid atau sudah kedaluwarsa.');
        }

        if ($coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit) {
            return back()->with('error', 'Kupon ini sudah mencapai batas kuota penggunaan.');
        }

        $cart->update(['coupon_code' => $coupon->code]);
        return back()->with('success', 'Kupon berhasil diterapkan!');
    }

    public function removeCoupon()
    {
        $cart = $this->getCart();
        $cart->update(['coupon_code' => null]);
        return back()->with('success', 'Kupon berhasil dilepas.');
    }
}
