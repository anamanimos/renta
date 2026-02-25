<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('products.index')->with('error', 'Keranjang Anda masih kosong.');
        }

        if (!$cart->start_date || !$cart->end_date) {
            return redirect()->route('cart.index')->with('error', 'Tentukan periode tanggal sewa terlebih dahulu!');
        }

        $addresses = $user->addresses;
        return view('pages.checkout', compact('cart', 'addresses'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'notes' => 'nullable|string'
        ]);

        $user = auth()->user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('products.index');
        }

        // Hitung Subtotal dan Final
        $days = max(1, $cart->total_days);
        $subtotal = $cart->subtotal;

        $shippingCost = 150000; // Hardcode biaya pengiriman minimalis
        
        $discountAmount = 0;
        if($cart->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', $cart->coupon_code)->first();
            if($coupon) {
                if($coupon->discount_type === 'percentage') {
                    $discountAmount = $subtotal * ($coupon->discount_value / 100);
                } else {
                    $discountAmount = $coupon->discount_value;
                }
                $discountAmount = min($discountAmount, $subtotal); // Cegah diskon minus
            }
        }

        $grandTotal = $subtotal - $discountAmount + $shippingCost;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'start_date' => $cart->start_date,
                'end_date' => $cart->end_date,
                'total_days' => $days,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'coupon_code' => $cart->coupon_code,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'status' => 'pending_payment',
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->subtotal / $item->quantity  // Snaphot harga total per unit per durasi
                ]);
            }

            // Tambah penggunaan kupon
            if($cart->coupon_code && isset($coupon)) {
                $coupon->increment('used_count');
            }

            // Hapus isi troli pasca sukses memesan
            $cart->delete();
            DB::commit();

            return redirect()->route('orders.show', $order->id)->with('success', 'Hore! Pesanan berhasil dibuat. Langkah selanjutnya adalah proses pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi galat pada sistem pesanan. Detail: ' . $e->getMessage());
        }
    }
}
