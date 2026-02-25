@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

@push('styles')
<style>
    .product-list-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .product-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px; }
    .filter-group { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
    .form-control { border:1px solid var(--border-color); padding:8px 12px; border-radius:8px; font-family:inherit; outline:none; transition:0.2s; }
    .form-control:focus { border-color:var(--primary-color) !important; box-shadow:0 0 0 3px rgba(211,47,47,0.1); }
    .stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:30px; }
    .stat-card { background:var(--white); padding:24px; border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); display:flex; align-items:center; gap:20px; }
    .stat-icon { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; }
    .stat-icon.income { background-color:#ecfdf5; color:var(--success); }
    .stat-icon.orders { background-color:#eff6ff; color:#3b82f6; }
    .stat-icon.avgsale { background-color:#fefce8; color:#f59e0b; }
    .stat-details h3 { font-size:0.9rem; color:var(--text-light); margin-bottom:5px; font-weight:500; }
    .stat-value { font-size:1.4rem; font-weight:700; color:var(--text-color); margin-bottom:0; }
    @media (max-width:992px) { .stats-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="page-header" style="margin-bottom:20px;">
    <h1>Laporan Penjualan</h1>
    <p>Rekapitulasi transaksi sewa dan penjualan produk.</p>
</div>

<!-- Date Range Filter -->
<div class="product-actions" style="background:var(--white); padding:20px; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:30px;">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="filter-group">
        <label style="font-weight:500; font-size:0.9rem;">Rentang Tanggal:</label>
        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
        <span style="color:var(--text-light)">-</span>
        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
        <button type="submit" class="btn-primary">
            <i class="ti ti-search"></i> Terapkan Filter
        </button>
    </form>
</div>

<!-- Quick Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon income"><i class="ti ti-cash"></i></div>
        <div class="stat-details">
            <h3>Total Pendapatan (Periode)</h3>
            <p class="stat-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orders"><i class="ti ti-checkups"></i></div>
        <div class="stat-details">
            <h3>Pesanan Sukses</h3>
            <p class="stat-value">{{ $completedOrders }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon avgsale"><i class="ti ti-receipt"></i></div>
        <div class="stat-details">
            <h3>Rata-rata Transaksi</h3>
            <p class="stat-value">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</p>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="product-list-card">
    <div class="table-header-actions" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div class="filter-toolbar" style="display:flex; gap:10px;">
            <span style="font-size:0.9rem; color:var(--text-light);">Menampilkan <strong>{{ $orders->count() }}</strong> transaksi</span>
        </div>
        <div class="search-control">
            <input type="text" id="customSearch" placeholder="Cari laporan..." style="background:#f8fafc; border:1px solid #e2e8f0; width:250px; padding:8px 16px; border-radius:6px; outline:none; font-family:inherit;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table" id="reportTable" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Pesanan</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Total Tagihan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $i => $order)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}" style="color:var(--primary-color); font-weight:600; text-decoration:none;">#{{ $order->order_number }}</a>
                    </td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td>{{ $order->user->name ?? 'Tamu' }}</td>
                    <td style="font-weight:500;">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $statusMap = [
                                'pending_payment' => ['pending', 'Menunggu'],
                                'awaiting_verification' => ['active', 'Verifikasi'],
                                'processing' => ['active', 'Diproses'],
                                'active_rent' => ['active', 'Berjalan'],
                                'completed' => ['completed', 'Selesai'],
                                'cancelled' => ['reject', 'Dibatalkan'],
                            ];
                            $st = $statusMap[$order->status] ?? ['pending', $order->status];
                        @endphp
                        <span class="badge-status {{ $st[0] }}">{{ $st[1] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--text-light);">
                        <i class="ti ti-chart-bar" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Tidak ada transaksi dalam rentang tanggal ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($orders->count() > 0)
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align:right;">Total Pemasukan (Selesai):</th>
                    <th colspan="2" style="color:var(--primary-color); font-size:1.1rem;">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
