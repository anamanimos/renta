@extends('layouts.admin')

@section('title', 'Integrasi WhatsApp | Admin Renta Enterprise')

@section('header', 'Integrasi WhatsApp')
@section('description', 'Konfigurasi notifikasi SMS/WhatsApp otomatis via API Gateway.')

@push('styles')
<style>
    .layout-grid {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .layout-grid { grid-template-columns: 1fr; }
    }

    .settings-nav {
        background: var(--white);
        border-radius: 12px;
        padding: 10px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .settings-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: 0.2s;
    }
    .settings-nav a:hover,
    .settings-nav a.active {
        background-color: var(--bg-color);
        color: var(--primary-color);
    }
    .settings-nav a i { font-size: 1.2rem; }

    .card {
        background: var(--white);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 16px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 12px;
    }

    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 500; }
    .form-control, .form-select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        outline: none;
        font-family: inherit;
        transition: 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
    }

    .btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
    .btn-success { background-color: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }

    .alert-box {
        background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a;
        padding: 15px; border-radius: 8px; margin-bottom: 20px;
        display: flex; align-items: start; gap: 10px; font-size: 0.9rem;
    }

    .status-indicator {
        display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
        border-radius: 20px; font-size: 0.85rem; font-weight: 600;
        background: {{ $settings['wa_enabled'] ? '#d1fae5' : '#fee2e2' }};
        color: {{ $settings['wa_enabled'] ? '#059669' : '#dc2626' }};
        margin-left: 10px;
    }
</style>
@endpush

@section('content')
<div class="layout-grid">
    <!-- Settings Sidebar -->
    <div class="settings-nav">
        <a href="{{ route('admin.settings.general') }}">
            <i class="ti ti-adjustments"></i> Umum
        </a>
        <a href="{{ route('admin.settings.whatsapp') }}" class="active">
            <i class="ti ti-brand-whatsapp"></i> Integrasi WhatsApp
        </a>
        <a href="{{ route('admin.settings.payment') }}">
            <i class="ti ti-credit-card"></i> Pembayaran
        </a>
        <a href="{{ route('admin.settings.logs') }}">
            <i class="ti ti-history"></i> Log Aktivitas
        </a>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <div class="alert-box">
            <i class="ti ti-info-circle" style="font-size: 1.5rem"></i>
            <div>
                <strong>Info Layanan Terintegrasi:</strong>
                <p style="margin: 5px 0 0">
                    Sistem Renta telah terhubung dengan server pusat WhatsApp berbasis `aldinokemal/go-whatsapp-web-multidevice` untuk pengiriman OTP. Pada bagian ini, Anda dapat mengelola format pesan dan token API jika menggunakan layanan Gateway Pihak Ketiga Tambahan.
                </p>
            </div>
        </div>

        <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST">
            @csrf
            <div class="card">
                <h3 class="card-title">
                    Koneksi API / Master Gateway
                    <span class="status-indicator">
                        <i class="ti ti-{{ $settings['wa_enabled'] ? 'check' : 'x' }}"></i> 
                        {{ $settings['wa_enabled'] ? 'Terhubung (Enabled)' : 'Nonaktif (Disabled)' }}
                    </span>
                </h3>

                <div class="form-group" style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="wa_enabled" id="wa_enabled" value="1" {{ $settings['wa_enabled'] ? 'checked' : '' }} style="width:18px; height:18px;">
                    <label for="wa_enabled" class="form-label" style="margin:0;">Aktifkan Pengiriman Pesan Peringatan via WA (Di Atas Pengaturan Bawaan)</label>
                </div>

                <div class="form-group">
                    <label class="form-label">Endpoint WA Gateway Tambahan (Opsional)</label>
                    <input type="url" name="wa_endpoint" class="form-control" value="{{ $settings['wa_endpoint'] }}" placeholder="https://api.fonnte.com/send">
                </div>
                
                <div class="form-group">
                    <label class="form-label">API Token / Key</label>
                    <input type="text" name="wa_token" class="form-control" value="{{ $settings['wa_token'] }}" placeholder="xxxxxxxxxxxxxxxxxxxx">
                </div>
            </div>

            <div class="card">
                <h3 class="card-title">Template Pesan Otomatis (Notifikasi)</h3>

                <div class="form-group">
                    <label class="form-label">Pesan: Pesanan Baru Dibuat</label>
                    <textarea name="wa_template_new_order" class="form-control" rows="4">{{ $settings['wa_template_new_order'] }}</textarea>
                    <small style="color: var(--text-light)">Variabel: {customer_name}, {order_id}, {total_amount}</small>
                </div>

                <div class="form-group" style="margin-top: 30px">
                    <label class="form-label">Pesan: Pembayaran Berhasil (Diproses)</label>
                    <textarea name="wa_template_payment_success" class="form-control" rows="4">{{ $settings['wa_template_payment_success'] }}</textarea>
                    <small style="color: var(--text-light)">Variabel: {customer_name}, {order_id}, {pickup_date}</small>
                </div>
            </div>

            <div style="text-align: right">
                <button type="submit" class="btn-success">
                    <i class="ti ti-device-floppy"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
