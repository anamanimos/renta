<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->orderBy('is_main', 'desc')->get();
        return view('profile.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'city_id' => 'required|string|max:100', // Sesuaikan jika ini FK angka di masa depan
            'district_id' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string|max:500',
            'is_main' => 'nullable|boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_main'] = $request->has('is_main') ? true : false;

        // Jika dijadikan utama, hapus flag utama dari alamat lain
        if ($validated['is_main']) {
            auth()->user()->addresses()->update(['is_main' => false]);
        }

        // Jika ini alamat pertama, otomatis jadi utama
        if (auth()->user()->addresses()->count() === 0) {
            $validated['is_main'] = true;
        }

        Address::create($validated);

        return redirect()->back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'city_id' => 'required|string|max:100',
            'district_id' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'full_address' => 'required|string|max:500',
            'is_main' => 'nullable|boolean',
        ]);

        $validated['is_main'] = $request->has('is_main') ? true : false;

        if ($validated['is_main'] && !$address->is_main) {
            auth()->user()->addresses()->update(['is_main' => false]);
        }

        // Jangan biarkan hapus flag status utama jika dia satu-satunya yg tersisa
        if (!$validated['is_main'] && $address->is_main) {
           // Temukan alamat lain untuk dijadikan pengganti utama as default (optional policy)
           // Biarkan saja untuk fleksibilitas (nanti bisa diverifikasi saat checkout)
        }

        $address->update($validated);

        return redirect()->back()->with('success', 'Alamat berhasil diubah.');
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->delete();

        // Jika alamat yang dihapus adalah alamat utama, jadikan alamat tertua lainnya sebagai utama
        if ($address->is_main) {
            $nextMain = auth()->user()->addresses()->first();
            if ($nextMain) {
                $nextMain->update(['is_main' => true]);
            }
        }

        return redirect()->back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function setMain(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        auth()->user()->addresses()->update(['is_main' => false]);
        $address->update(['is_main' => true]);

        return redirect()->back()->with('success', 'Alamat ditetapkan sebagai yang Utama.');
    }
}
