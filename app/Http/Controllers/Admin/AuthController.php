<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            if (Auth::user()->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            }
            // Terlanjur login tapi bukan admin
            Auth::logout();
            return back()->with('error', 'Akses ditolak. Anda bukan Administrator.');
        }

        return back()->with('error', 'Kombinasi Email dan Kata Sandi tidak cocok.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }

    public function sendOtp(Request $request, \App\Services\WhatsAppService $waService)
    {
        $request->validate(['phone' => 'required|string|min:9']);
        $phone = $request->phone;
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $user = \App\Models\User::where('phone_number', $phone)->first();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Nomor Anda bukan milik Administrator.'], 403);
        }

        $otp = rand(1000, 9999);
        \Illuminate\Support\Facades\Cache::put('admin_otp_' . $phone, $otp, now()->addMinutes(2));

        $message = "RENTA ENTERPRISE\n\nKode Akses Login Admin Anda adalah: *{$otp}*\n\nJangan berikan kode ini kepada siapapun. Berlaku selama 2 menit.";
        $waService->sendMessage($phone, $message);
        
        return response()->json(['success' => true, 'message' => 'Kode OTP berhasil dikirim ke WhatsApp Anda.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string|size:4',
            'remember' => 'boolean'
        ]);

        $phone = $request->phone;
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $cachedOtp = \Illuminate\Support\Facades\Cache::get('admin_otp_' . $phone);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Kode OTP Admin tidak sah atau sudah usang.'], 400);
        }

        $user = \App\Models\User::where('phone_number', $phone)->first();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Izin login sebagai Admin ditolak.'], 403);
        }

        \Illuminate\Support\Facades\Cache::forget('admin_otp_' . $phone);
        Auth::login($user, $request->remember);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => route('admin.dashboard')
        ]);
    }
}
