<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->orders()->with(['items.product']);

        if ($request->has('status') && $request->status) {
            if ($request->status === 'processing') {
                $query->whereIn('status', ['processing', 'active_rent']);
            } else {
                $query->where('status', $request->status);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return view('profile.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'address']);

        // Kita bisa membuat view detail-pesanan.blade.php nantinya. Sementara fallback ke detail yg sama.
        return view('profile.order-detail', compact('order'));
    }

    public function payment(Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->status !== 'pending_payment') {
            abort(403, 'Akses ditolak atau pesanan tidak dalam status Menunggu Pembayaran.');
        }

        return view('profile.payment', compact('order'));
    }

    public function uploadPayment(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->status !== 'pending_payment') {
            abort(403);
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = time() . '_' . $order->order_number;
            
            // Upload ke Cloudinary
            $uploadedFileUrl = cloudinary()->upload($file->getRealPath(), [
                'folder' => 'renta/payments',
                'public_id' => $filename
            ])->getSecurePath();

            $order->update([
                'payment_proof' => $uploadedFileUrl,
                'status' => 'awaiting_verification'
            ]);

            return redirect()->route('orders.show', $order->id)->with('success', 'Bukti pembayaran berhasil diunggah! Menunggu verifikasi admin.');
        }

        return back()->with('error', 'Gagal mengunggah file.');
    }
}
