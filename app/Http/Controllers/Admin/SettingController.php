<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function general()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'Renta Enterprise'),
            'contact_email' => Setting::get('contact_email', 'support@renta.com'),
            'seo_description' => Setting::get('seo_description', 'Layanan penyewaan alat musik dan sound system profesional terbaik di kota Anda.'),
            'maintenance_mode' => Setting::get('maintenance_mode', '0'),
            'allow_registration' => Setting::get('allow_registration', '1'),
            'auto_approve_reviews' => Setting::get('auto_approve_reviews', '0'),
        ];
        
        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'seo_description' => 'nullable|string',
        ]);

        Setting::set('store_name', $validated['store_name']);
        Setting::set('contact_email', $validated['contact_email']);
        Setting::set('seo_description', $validated['seo_description'] ?? '');
        
        Setting::set('maintenance_mode', $request->has('maintenance_mode') ? '1' : '0');
        Setting::set('allow_registration', $request->has('allow_registration') ? '1' : '0');
        Setting::set('auto_approve_reviews', $request->has('auto_approve_reviews') ? '1' : '0');

        return back()->with('success', 'Pengaturan umum berhasil diperbarui.');
    }

    public function payment()
    {
        $banksJson = Setting::get('bank_accounts', '[]');
        $banks = json_decode($banksJson, true) ?: [];
        
        return view('admin.settings.payment', compact('banks'));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $banksJson = Setting::get('bank_accounts', '[]');
        $banks = json_decode($banksJson, true) ?: [];
        
        $banks[] = [
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
        ];

        Setting::set('bank_accounts', json_encode($banks));
        
        return back()->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    public function destroyPayment($index)
    {
        $banksJson = Setting::get('bank_accounts', '[]');
        $banks = json_decode($banksJson, true) ?: [];
        
        if (isset($banks[$index])) {
            array_splice($banks, $index, 1);
            Setting::set('bank_accounts', json_encode($banks));
            return back()->with('success', 'Rekening bank berhasil dihapus.');
        }

        return back()->with('error', 'Rekening tidak ditemukan.');
    }

    public function whatsapp()
    {
        $settings = [
            'wa_enabled' => Setting::get('wa_enabled', '0'),
            'wa_endpoint' => Setting::get('wa_endpoint', ''),
            'wa_token' => Setting::get('wa_token', ''),
            'wa_template_new_order' => Setting::get('wa_template_new_order', "Halo {customer_name},\nPesanan rental Anda dengan nomor {order_id} berhasil dibuat.\nTotal Tagihan: Rp{total_amount}\n\nMohon segera lakukan pembayaran. Terima kasih!\n- Renta Enterprise"),
            'wa_template_payment_success' => Setting::get('wa_template_payment_success', "Halo {customer_name},\nPembayaran untuk pesanan {order_id} telah kami terima.\nPesanan sedang kami siapkan untuk tanggal ambil {pickup_date}.\n\nTerima kasih atas kepercayaannya!\n- Renta Enterprise"),
        ];

        return view('admin.settings.whatsapp', compact('settings'));
    }

    public function updateWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'wa_endpoint' => 'nullable|url',
            'wa_token' => 'nullable|string',
            'wa_template_new_order' => 'required|string',
            'wa_template_payment_success' => 'required|string',
        ]);

        Setting::set('wa_enabled', $request->has('wa_enabled') ? '1' : '0');
        Setting::set('wa_endpoint', $validated['wa_endpoint'] ?? '');
        Setting::set('wa_token', $validated['wa_token'] ?? '');
        Setting::set('wa_template_new_order', $validated['wa_template_new_order']);
        Setting::set('wa_template_payment_success', $validated['wa_template_payment_success']);

        return back()->with('success', 'Pengaturan integrasi WhatsApp berhasil diperbarui.');
    }

    public function logs()
    {
        return view('admin.settings.logs');
    }
}
