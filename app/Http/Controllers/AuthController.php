<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    private function transferGuestCart($user, $oldSessionId)
    {
        $guestCart = \App\Models\Cart::with('items')->where('session_id', $oldSessionId)->whereNull('user_id')->first();
        if ($guestCart) {
            $userCart = \App\Models\Cart::where('user_id', $user->id)->first();
            if ($userCart) {
                foreach ($guestCart->items as $item) {
                    $existing = $userCart->items()->where('product_id', $item->product_id)->first();
                    if ($existing) {
                        $existing->increment('quantity', $item->quantity);
                    } else {
                        $item->update(['cart_id' => $userCart->id]);
                    }
                }
                if (!$userCart->start_date && $guestCart->start_date) {
                    $userCart->update([
                        'start_date' => $guestCart->start_date,
                        'end_date' => $guestCart->end_date,
                        'total_days' => $guestCart->total_days
                    ]);
                }
                $guestCart->delete();
            } else {
                $guestCart->update(['user_id' => $user->id]);
            }
        }
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:9',
        ]);

        $phone = $request->phone;
        // Normalisasi nomor telepon ke +62 jika perlu (Sesuai kebutuhan Gateway)
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Cek apakah user ada?
        $user = User::where('phone_number', $phone)->first();
        $isRegistered = $user ? true : false;

        // Generate OTP (4 Digit random)
        $otp = rand(1000, 9999);

        // Simpan OTP sementara di Cache (berlaku 2 Menit)
        Cache::put('otp_' . $phone, $otp, now()->addMinutes(2));

        // Kirim via WA (Aktifkan baris di bawah pada Production)
        $message = "Kode OTP Renta Enterprise Anda adalah: *{$otp}*\n\nBerlaku selama 2 menit. JANGAN BERIKAN kode ini kepada siapapun.";
        $this->waService->sendMessage($phone, $message);
        
        // Return success response dengan status user
        return response()->json([
            'success' => true,
            'is_registered' => $isRegistered,
            'message' => 'OTP telah dikirim.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:9',
            'otp'   => 'required|string|size:4'
        ]);

        $phone = $request->phone;
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $cachedOtp = Cache::get('otp_' . $phone);

        // Jika OTP kedaluwarsa atau salah
        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Kode OTP tidak valid atau telah kedaluwarsa.'], 400);
        }

        // Clear Cache OTP
        Cache::forget('otp_' . $phone);

        $user = User::where('phone_number', $phone)->first();

        // Jika user tidak ada (User Baru) -> Lempar Flag Register
        if (!$user) {
            // Karena OTP valid, simpan penanda ini untuk registrasi nanti
            session(['verified_phone' => $phone]);
            return response()->json([
                'success' => true,
                'action' => 'register_required',
                'message' => 'Lengkapi data Anda'
            ]);
        }

        // Jika user ada -> Login
        $oldSessionId = Session::getId();
        Auth::login($user);
        $this->transferGuestCart($user, $oldSessionId);
        return response()->json([
            'success' => true,
            'action' => 'login_success',
            'redirect' => url('/profile') // Atau home sesuai rute
        ]);
    }

    public function registerWithOtp(Request $request)
    {
        // Validasi Nomor yg sudah terverifikasi sebelumnya
        $verifiedPhone = session('verified_phone');
        if (!$verifiedPhone) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. OTP belum diverifikasi.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone_number' => $verifiedPhone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer'
        ]);

        // Bersihkan session penanda
        session()->forget('verified_phone');

        // Login Otomatis
        $oldSessionId = Session::getId();
        Auth::login($user);
        $this->transferGuestCart($user, $oldSessionId);

        return response()->json([
            'success' => true,
            'redirect' => url('/profile')
        ]);
    }

    // Fungsi tambahan: Login via Email/Password (Fallback jika WA error)
    public function loginWithPassword(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $oldSessionId = Session::getId();
            $request->session()->regenerate();
            $this->transferGuestCart(Auth::user(), $oldSessionId);
            return response()->json([
                'success' => true,
                'redirect' => url('/profile')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kombinasi email dan password salah.'
        ], 401);
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:9',
        ]);

        $phone = $request->phone;
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $user = User::where('phone_number', $phone)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Nomor WhatsApp tidak terdaftar di sistem.'], 404);
        }

        $otp = rand(1000, 9999);
        Cache::put('reset_otp_' . $phone, $otp, now()->addMinutes(5));

        $message = "Kode OTP Reset Kata Sandi Anda adalah: *{$otp}*\n\nBerlaku selama 5 menit. Jika merasa tidak meminta perubahan sandi, abaikan pesan ini.";
        $this->waService->sendMessage($phone, $message);
        
        return response()->json([
            'success' => true,
            'message' => 'Kode OTP Reset telah dikirim melalui WhatsApp.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string|size:4',
            'password' => 'required|string|min:6'
        ]);

        $phone = $request->phone;
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $cachedOtp = Cache::get('reset_otp_' . $phone);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Kode OTP tidak sah atau kedaluwarsa.'], 400);
        }

        $user = User::where('phone_number', $phone)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Pengguna tak ditemukan.'], 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        Cache::forget('reset_otp_' . $phone);

        // Login Otomatis Pasca Sukses Reset
        $oldSessionId = Session::getId();
        Auth::login($user);
        $this->transferGuestCart($user, $oldSessionId);

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diatur ulang!',
            'redirect' => url('/profile')
        ]);
    }
}
