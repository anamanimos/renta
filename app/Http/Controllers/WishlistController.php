<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class WishlistController extends Controller
{
    private function getSessionId()
    {
        return Session::getId();
    }

    public function index()
    {
        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        $wishlists = Wishlist::with('product.category')
            ->where(function($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId)->whereNull('user_id');
                }
            })
            ->latest()
            ->get();

        return view('pages.wishlist', compact('wishlists'));
    }

    public function toggle(Request $request, Product $product)
    {
        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        $query = Wishlist::where('product_id', $product->id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId)->whereNull('user_id');
        }

        $wishlist = $query->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Produk dihapus dari wishlist!';
            $status = 'removed';
        } else {
            Wishlist::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
            ]);
            $message = 'Produk ditambahkan ke wishlist!';
            $status = 'added';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $message,
                'wishlist_count' => $this->getWishlistCount()
            ]);
        }

        return back()->with('success', $message);
    }

    private function getWishlistCount()
    {
        $userId = auth()->id();
        $sessionId = $this->getSessionId();

        return Wishlist::where(function($q) use ($userId, $sessionId) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('session_id', $sessionId)->whereNull('user_id');
            }
        })->count();
    }
}
