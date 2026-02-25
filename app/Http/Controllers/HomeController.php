<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Ambil 4 produk terbaru untuk Today Hot Deals
        $hotProducts = Product::where('is_active', true)
                                ->orderBy('created_at', 'desc')
                                ->take(4)
                                ->get();

        return view('home', compact('hotProducts'));
    }
}
