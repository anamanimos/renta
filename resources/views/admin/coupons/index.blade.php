@extends('layouts.admin')

@section('title', 'Kelola Kode Kupon')

@push('styles')
<style>
    .product-list-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background-color 0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
    /* Modal Overlay */
    .modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:2000; opacity:0; visibility:hidden; transition:0.3s; }
    .modal-overlay.show { opacity:1; visibility:visible; }
    .modal-card { background:var(--white); width:90%; max-width:500px; border-radius:12px; padding:24px; transform:translateY(-20px); transition:0.3s; }
    .modal-overlay.show .modal-card { transform:translateY(0); }
    .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .modal-header h3 { font-size:1.25rem; font-weight:600; }
    .modal-close { background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-light); }
    .form-group { margin-bottom:15px; }
    .form-label { display:block; margin-bottom:8px; font-weight:500; }
    .form-control, .form-select { width:100%; padding:10px 12px; border-radius:8px; border:1px solid var(--border-color); outline:none; font-family:inherit; transition:0.2s; }
    .form-control:focus, .form-select:focus { border-color:var(--primary-color) !important; box-shadow:0 0 0 3px rgba(211,47,47,0.1); }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Kelola Kode Kupon</h1>
    <p>Atur promo, diskon, dan batas klaim untuk pelanggan.</p>
</div>

<div class="product-actions-top" style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:20px;">
    <button class="btn-primary" id="btnCreateCoupon">
        <i class="ti ti-plus"></i> Tambah Kupon
    </button>
</div>

<div class="product-list-card">
    <div class="table-header-actions" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div class="filter-toolbar" style="display:flex; gap:10px;">
            <span style="font-size:0.9rem; color:var(--text-light);">Menampilkan <strong>{{ $coupons->count() }}</strong> kupon</span>
        </div>
        <div class="search-control">
            <form method="GET" action="{{ route('admin.coupons.index') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode kupon..." style="background:#f8fafc; border:1px solid #e2e8f0; width:250px; padding:8px 16px; border-radius:6px; outline:none; font-family:inherit;">
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table" id="couponsTable" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Kupon</th>
                    <th>Tipe Diskon</th>
                    <th>Nominal/Persen</th>
                    <th>Batas Klaim</th>
                    <th>Tgl Berakhir</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $i => $coupon)
                <tr>
                    <td>{{ $coupons->firstItem() + $i }}</td>
                    <td><strong style="font-size:1.1rem; color:var(--primary-color);">{{ $coupon->code }}</strong></td>
                    <td>{{ $coupon->discount_type === 'percent' ? 'Persentase' : 'Nominal' }}</td>
                    <td>
                        {{ $coupon->discount_type === 'percent' ? $coupon->discount_value.'%' : 'Rp '.number_format($coupon->discount_value, 0, ',', '.') }}
                    </td>
                    <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?: '∞' }}</td>
                    <td>{{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : 'Tanpa Batas' }}</td>
                    <td>
                        @if(!$coupon->is_active)
                            <span class="badge-status reject">Nonaktif</span>
                        @elseif($coupon->expires_at && $coupon->expires_at->isPast())
                            <span class="badge-status reject">Kedaluwarsa</span>
                        @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                            <span class="badge-status pending">Limit Habis</span>
                        @else
                            <span class="badge-status active">Aktif</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus kupon ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn delete" title="Hapus"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:var(--text-light);">
                        <i class="ti ti-ticket" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Belum ada kupon. Klik "Tambah Kupon" untuk membuat kode promo pertama.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
    <div style="padding:15px 0; border-top:1px solid var(--border-color); margin-top:15px;">
        {{ $coupons->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

<!-- Create Coupon Modal -->
<div class="modal-overlay" id="couponModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Tambah Kupon Baru</h3>
            <button class="modal-close" id="closeModalBtn"><i class="ti ti-x"></i></button>
        </div>
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Kode Kupon *</label>
                <input type="text" name="code" class="form-control" style="text-transform:uppercase" placeholder="Msl: PROMO2026" required>
            </div>
            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1">
                    <label class="form-label">Tipe Diskon *</label>
                    <select name="discount_type" class="form-select" required>
                        <option value="fixed">Nominal (Rp)</option>
                        <option value="percent">Persentase (%)</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1">
                    <label class="form-label">Nilai Diskon *</label>
                    <input type="number" name="discount_value" class="form-control" placeholder="Msl: 50000" required min="1">
                </div>
            </div>
            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1">
                    <label class="form-label">Limit Klaim</label>
                    <input type="number" name="usage_limit" class="form-control" placeholder="Kosongkan = unlimited" min="1">
                </div>
                <div class="form-group" style="flex:1">
                    <label class="form-label">Tanggal Berakhir *</label>
                    <input type="date" name="expires_at" class="form-control" required>
                </div>
            </div>
            <div class="form-group" style="margin-top:20px;">
                <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">
                    Simpan Kupon
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('couponModal');
    const btnOpen = document.getElementById('btnCreateCoupon');
    const btnClose = document.getElementById('closeModalBtn');

    btnOpen.addEventListener('click', () => modal.classList.add('show'));
    btnClose.addEventListener('click', () => modal.classList.remove('show'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('show');
    });
});
</script>
@endpush
