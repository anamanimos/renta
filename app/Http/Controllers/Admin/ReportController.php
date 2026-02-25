<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $orders = Order::with('user')
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue = $orders->where('status', 'completed')->sum('grand_total');
        $completedOrders = $orders->where('status', 'completed')->count();
        $avgTransaction = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

        return view('admin.reports.index', compact(
            'orders', 'totalRevenue', 'completedOrders', 'avgTransaction',
            'startDate', 'endDate'
        ));
    }
}
