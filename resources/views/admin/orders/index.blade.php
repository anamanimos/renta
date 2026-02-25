@extends('layouts.admin')

@section('title', 'Kelola Pesanan')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<style>
    .product-list-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background-color 0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
    .active-filter-tag { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:6px; font-size:0.85rem; font-weight:600; color:var(--white); }
    .active-filter-tag.bg-blue { background-color:#3b82f6; }
    .active-filter-tag.bg-yellow { background-color:#eab308; color:#fff; }
    .active-filter-tag.bg-green { background-color:#10b981; }
    .active-filter-tag.bg-danger { background-color:#ef4444; }
    .filter-popover { display:none; position:absolute; top:100%; left:0; margin-top:10px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; width:340px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); z-index:100; }
    .filter-popover.show { display:block; }
    .filter-pill-new { padding:4px 10px; border-radius:6px; font-size:0.85rem; cursor:pointer; display:flex; align-items:center; gap:6px; transition:0.2s; }
    .filter-pill-new:hover { opacity:0.8; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Kelola Pesanan</h1>
    <p>Daftar transaksi penyewaan dan pembelian.</p>
</div>

<div class="product-list-card">
    <div class="table-header-actions" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div class="filter-toolbar" style="display:flex; gap:10px; position:relative;">
            <form method="GET" action="{{ route('admin.orders.index') }}" style="display:flex; gap:10px;">
                <select name="status" onchange="this.form.submit()" style="padding:8px 16px; border:1px solid var(--border-color); border-radius:6px; font-family:inherit; font-size:0.9rem; background:var(--white);">
                    <option value="">Semua Status</option>
                    <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="awaiting_verification" {{ request('status') == 'awaiting_verification' ? 'selected' : '' }}>Verifikasi Pembayaran</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Diproses</option>
                    <option value="active_rent" {{ request('status') == 'active_rent' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </form>
            @if(request('status'))
            <a href="{{ route('admin.orders.index') }}" style="background-color:#f8fafc; border:1px solid #e2e8f0; color:#475569; padding:8px 16px; border-radius:6px; font-weight:500; display:inline-flex; align-items:center; gap:8px; text-decoration:none; font-size:0.9rem;">
                <i class="ti ti-refresh"></i> Reset
            </a>
            @endif
        </div>
        <div class="search-control">
            <input type="text" id="customSearch" placeholder="Cari data..." style="background:#f8fafc; border:1px solid #e2e8f0; width:250px; padding:8px 16px; border-radius:6px; outline:none; font-family:inherit;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table" id="ordersTable" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $i => $order)
                <tr>
                    <td>{{ $orders->firstItem() + $i }}</td>
                    <td style="font-weight:600;">#{{ $order->order_number }}</td>
                    <td>
                        <div class="table-user">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'T') }}&background=f0e68c" alt="{{ $order->user->name ?? 'Tamu' }}">
                            <span>{{ $order->user->name ?? 'Tamu' }}</span>
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($order->start_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->end_date)->format('d M Y') }}</td>
                    <td style="font-weight:500;">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $statusMap = [
                                'pending_payment' => ['pending', 'Menunggu Pembayaran'],
                                'awaiting_verification' => ['active', 'Verifikasi Pembayaran'],
                                'processing' => ['active', 'Sedang Diproses'],
                                'active_rent' => ['active', 'Sedang Berjalan'],
                                'completed' => ['completed', 'Selesai'],
                                'cancelled' => ['reject', 'Dibatalkan'],
                            ];
                            $st = $statusMap[$order->status] ?? ['pending', $order->status];
                        @endphp
                        <span class="badge-status {{ $st[0] }}">{{ $st[1] }}</span>
                    </td>
                    <td>
                        <button class="action-btn view" onclick="window.location.href='{{ route('admin.orders.show', $order->id) }}'" title="Detail">
                            <i class="ti ti-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:var(--text-light);">
                        <i class="ti ti-shopping-cart" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Belum ada pesanan masuk.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div style="padding:15px 0; border-top:1px solid var(--border-color); margin-top:15px;">
        {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
