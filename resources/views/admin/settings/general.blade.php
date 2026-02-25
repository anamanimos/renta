@extends('layouts.admin')

@section('title', 'Pengaturan Toko')

@push('styles')
<style>
    .layout-grid { display:grid; grid-template-columns:250px 1fr; gap:24px; align-items:start; }
    @media (max-width:992px) { .layout-grid { grid-template-columns:1fr; } }
    .settings-nav { background:var(--white); border-radius:12px; padding:10px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); }
    .settings-nav a { display:flex; align-items:center; gap:12px; padding:12px 16px; color:var(--text-color); text-decoration:none; border-radius:8px; font-weight:500; transition:0.2s; }
    .settings-nav a:hover, .settings-nav a.active { background-color:var(--bg-color); color:var(--primary-color); }
    .settings-nav a i { font-size:1.2rem; }
    .card { background:var(--white); border-radius:16px; padding:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:24px; }
    .card-title { font-size:1.1rem; font-weight:600; margin-bottom:16px; border-bottom:1px solid var(--border-color); padding-bottom:12px; }
    .form-group { margin-bottom:20px; }
    .form-label { display:block; margin-bottom:8px; font-weight:500; }
    .form-control { width:100%; padding:10px 12px; border-radius:8px; border:1px solid var(--border-color); outline:none; font-family:inherit; transition:0.2s; }
    .form-control:focus { border-color:var(--primary-color) !important; box-shadow:0 0 0 3px rgba(211,47,47,0.1); }
    .btn-primary { background-color:var(--primary-color); color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; font-family:inherit; }
    /* Toggle Switch */
    .switch { position:relative; display:inline-block; width:50px; height:24px; }
    .switch input { opacity:0; width:0; height:0; }
    .slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#cbd5e1; transition:0.4s; border-radius:24px; }
    .slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background-color:white; transition:0.4s; border-radius:50%; }
    input:checked + .slider { background-color:var(--primary-color); }
    input:checked + .slider:before { transform:translateX(26px); }
    .toggle-row { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--border-color); }
    .toggle-row:last-child { border-bottom:none; }
</style>
@endpush

@section('content')
<div class="page-header" style="margin-bottom:24px;">
    <h1>Pengaturan Toko</h1>
    <p>Konfigurasi website, integrasi, dan operasional.</p>
</div>

<div class="layout-grid">
    <!-- Settings Sidebar Navigation -->
    <div class="settings-nav">
        <a href="{{ route('admin.settings.general') }}" class="active">
            <i class="ti ti-adjustments"></i> Umum
        </a>
        <a href="{{ route('admin.settings.whatsapp') }}">
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
            <div style="background-color:#dcfce7; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">
                <i class="ti ti-check"></i> {{ session('success') }}
            </div>
        @endif
        <form action="{{ route('admin.settings.general.update') }}" method="POST">
            @csrf
            <div class="card">
                <h3 class="card-title">Informasi Dasar Website</h3>
                <div class="form-group">
                    <label class="form-label">Nama Toko *</label>
                    <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $settings['store_name']) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Kontak Utama *</label>
                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings['contact_email']) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Singkat (SEO Meta)</label>
                    <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', $settings['seo_description']) }}</textarea>
                </div>
            </div>

            <div class="card">
                <h3 class="card-title">Logo & Favicon</h3>
                <div style="display:flex; gap:20px; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Logo Website</label>
                        <div style="display:flex; align-items:center; gap:15px; border:1px solid var(--border-color); padding:15px; border-radius:8px;">
                            <img src="{{ asset('assets/images/logo-renta.png') }}" alt="Logo" style="height:40px; background:#f1f5f9; padding:5px; border-radius:4px;" onerror="this.src='https://placehold.co/120x40?text=LOGO'">
                            <input type="file" id="uploadLogo" style="display:none" accept="image/*">
                            <button type="button" class="btn-primary" style="padding:6px 12px; font-size:0.85rem;" onclick="document.getElementById('uploadLogo').click()">
                                Ganti Logo
                            </button>
                        </div>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Favicon (Ikon Tab)</label>
                        <div style="display:flex; align-items:center; gap:15px; border:1px solid var(--border-color); padding:15px; border-radius:8px;">
                            <div style="width:32px; height:32px; background:var(--primary-color); border-radius:4px; display:flex; align-items:center; justify-content:center; color:white; font-weight:700;">R</div>
                            <input type="file" id="uploadFavicon" style="display:none" accept="image/png, image/x-icon">
                            <button type="button" class="btn-primary" style="padding:6px 12px; font-size:0.85rem; background:#64748b;" onclick="document.getElementById('uploadFavicon').click()">
                                Ganti Favicon
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 class="card-title">Fitur Website</h3>
                <div class="toggle-row">
                    <div>
                        <strong style="display:block; margin-bottom:4px;">Mode Pemeliharaan (Maintenance)</strong>
                        <span style="font-size:0.85rem; color:var(--text-light);">Sembunyikan website dari pelanggan saat sedang diupdate.</span>
                    </div>
                    <label class="switch"><input type="checkbox" name="maintenance_mode" value="1" {{ old('maintenance_mode', $settings['maintenance_mode']) == '1' ? 'checked' : '' }}><span class="slider"></span></label>
                </div>
                <div class="toggle-row">
                    <div>
                        <strong style="display:block; margin-bottom:4px;">Registrasi Pengguna Baru</strong>
                        <span style="font-size:0.85rem; color:var(--text-light);">Izinkan pelanggan membuat akun baru.</span>
                    </div>
                    <label class="switch"><input type="checkbox" name="allow_registration" value="1" {{ old('allow_registration', $settings['allow_registration']) == '1' ? 'checked' : '' }}><span class="slider"></span></label>
                </div>
                <div class="toggle-row">
                    <div>
                        <strong style="display:block; margin-bottom:4px;">Auto-Approve Review Produk</strong>
                        <span style="font-size:0.85rem; color:var(--text-light);">Ulasan akan langsung tayang tanpa moderasi.</span>
                    </div>
                    <label class="switch"><input type="checkbox" name="auto_approve_reviews" value="1" {{ old('auto_approve_reviews', $settings['auto_approve_reviews']) == '1' ? 'checked' : '' }}><span class="slider"></span></label>
                </div>
            </div>

            <div style="text-align:right;">
                <button type="submit" class="btn-primary">
                    <i class="ti ti-device-floppy"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showComingSoon(e) {
    e.preventDefault();
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Segera Hadir', text: 'Fitur ini akan tersedia di update selanjutnya.', icon: 'info' });
    } else {
        alert('Fitur ini akan tersedia di update selanjutnya.');
    }
}
</script>
@endpush
