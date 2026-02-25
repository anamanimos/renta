@extends('layouts.admin')

@section('title', 'Pembayaran | Admin Renta Enterprise')

@section('header', 'Pengaturan Pembayaran')
@section('description', 'Kelola rekening bank manual dan status integrasi payment gateway.')

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
        display: flex;
        justify-content: space-between;
        align-items: center;
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

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .bank-list { display: flex; flex-direction: column; gap: 15px; }
    .bank-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--border-color);
        padding: 15px 20px;
        border-radius: 12px;
        background: #f8fafc;
    }
    .bank-info strong { display: block; font-size: 1.1rem; }
    .bank-info span { display: block; color: var(--text-light); font-size: 0.9rem; margin-top: 4px; }

    .btn-outline-danger {
        background: transparent;
        border: 1px solid var(--danger);
        color: var(--danger);
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: 0.2s;
    }
    .btn-outline-danger:hover { background: var(--danger); color: white; }
</style>
@endpush

@section('content')
<div class="layout-grid">
    <!-- Settings Sidebar -->
    <div class="settings-nav">
        <a href="{{ route('admin.settings.general') }}">
            <i class="ti ti-adjustments"></i> Umum
        </a>
        <a href="{{ route('admin.settings.whatsapp') }}">
            <i class="ti ti-brand-whatsapp"></i> Integrasi WhatsApp
        </a>
        <a href="{{ route('admin.settings.payment') }}" class="active">
            <i class="ti ti-credit-card"></i> Pembayaran
        </a>
        <a href="{{ route('admin.settings.logs') }}">
            <i class="ti ti-history"></i> Log Aktivitas
        </a>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        
        <!-- Alerts -->
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <h3 class="card-title">
                Rekening Bank Transfer Manual
                <button class="btn-primary" style="font-size: 0.85rem; padding: 6px 12px" onclick="addBankForm()">
                    <i class="ti ti-plus"></i> Tambah Rekening
                </button>
            </h3>

            <!-- Form Template Bank (Tersembunyi) -->
            <form id="addBankForm" action="{{ route('admin.settings.payment.store') }}" method="POST" style="display:none; background:#f8fafc; border:1px solid #ddd; padding:15px; border-radius:8px; margin-bottom:20px;">
                @csrf
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div>
                        <label class="form-label">Nama Bank (Misal: BCA)</label>
                        <input type="text" name="bank_name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Nomor Rekening</label>
                        <input type="text" name="account_number" class="form-control" required>
                    </div>
                </div>
                <div style="margin-top:15px;">
                    <label class="form-label">Atas Nama (A/N)</label>
                    <input type="text" name="account_name" class="form-control" required>
                </div>
                <div style="margin-top:15px; text-align:right;">
                    <button type="button" class="btn-primary" style="background:var(--text-light)" onclick="cancelAddBank()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Bank</button>
                </div>
            </form>

            <div class="bank-list">
                @forelse($banks as $bank)
                <div class="bank-item">
                    <div class="bank-info">
                        <strong>{{ $bank['bank_name'] }}</strong>
                        <span>No. Rekening: {{ $bank['account_number'] }}</span>
                        <span>A/N: {{ $bank['account_name'] }}</span>
                    </div>
                    <div>
                        <form action="{{ route('admin.settings.payment.destroy', $loop->index) }}" method="POST" onsubmit="return confirm('Hapus rekening bank ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-outline-danger" title="Hapus">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p style="color:var(--text-light); text-align:center;">Belum ada rekening transfer manual yang ditambahkan.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Payment Gateway (Otomatis) - Segera Hadir</h3>
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; padding: 15px; border-radius: 8px;">
                <i class="ti ti-info-circle" style="font-size: 1.5rem; float:left; margin-right:15px;"></i>
                <p style="margin: 0;">Integrasi Otomatis via API Payment Gateway (Misal Midtrans/Xendit) sedang dalam antrean pembaruan sistem berikutnya. Sementara itu, sistem penerimaan bukti pembayaran Manual via Upload berjalan dengan sempurna.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function addBankForm() {
        document.getElementById('addBankForm').style.display = 'block';
    }
    function cancelAddBank() {
        document.getElementById('addBankForm').style.display = 'none';
    }
</script>
@endpush
