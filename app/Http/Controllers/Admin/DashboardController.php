<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Pesanan baru bulan ini
        $newOrders = Order::whereMonth('created_at', $currentMonth)
                          ->whereYear('created_at', $currentYear)
                          ->count();

        // Pendapatan bulan ini (dari order yg bukan dibatalkan/pending base)
        $revenue = Order::whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear)
                        ->whereNotIn('status', ['cancelled'])
                        ->sum('grand_total');

        // Total katalog produk
        $totalProducts = Product::count();

        // Total pelanggan teregistrasi
        $totalCustomers = User::where('role', 'customer')->count();
        
        // Terakhir order (5 row)
        $latestOrders = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('newOrders', 'revenue', 'totalProducts', 'totalCustomers', 'latestOrders'));
    }
}
