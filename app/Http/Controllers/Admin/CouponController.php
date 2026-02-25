<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }
        $coupons = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'required|date',
        ]);

        Coupon::create($validated);
        return back()->with('success', 'Kupon berhasil ditambahkan.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'required|date',
            'is_active' => 'boolean'
        ]);

        $coupon->update($validated);
        return back()->with('success', 'Kupon berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Kupon berhasil dihapus.');
    }
}
