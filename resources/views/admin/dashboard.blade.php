@extends('layouts.admin')

@section('title', 'Overview Dashboard')

@section('content')
<div class="page-header">
    <h1>Overview Dashboard</h1>
    <p>Selamat datang kembali, {{ auth()->user()->name ?? 'Admin' }}!</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon income"><i class="ti ti-wallet"></i></div>
        <div class="stat-details">
            <h3>Total Pendapatan</h3>
            <p class="stat-value">Rp {{ number_format($revenue ?? 0, 0, ',', '.') }}</p>
            <span class="stat-change positive"><i class="ti ti-trending-up"></i> Bulan ini</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orders">
            <i class="ti ti-shopping-bag"></i>
        </div>
        <div class="stat-details">
            <h3>Pesanan Baru</h3>
            <p class="stat-value">{{ $newOrders ?? 0 }}</p>
            <span class="stat-change positive"><i class="ti ti-trending-up"></i> Bulan ini</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon active-rentals">
            <i class="ti ti-refresh"></i>
        </div>
        <div class="stat-details">
            <h3>Total Produk</h3>
            <p class="stat-value">{{ $totalProducts ?? 0 }}</p>
            <span class="stat-change text-muted">Katalog tersedia</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon customers">
            <i class="ti ti-users"></i>
        </div>
        <div class="stat-details">
            <h3>Total Pelanggan</h3>
            <p class="stat-value">{{ number_format($totalCustomers ?? 0) }}</p>
            <span class="stat-change positive"><i class="ti ti-trending-up"></i> Pengguna terdaftar</span>
        </div>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="recent-orders-section">
    <div class="section-header">
        <h2>Pesanan Terbaru</h2>
        <a href="{{ route('admin.orders.index') }}" class="view-all">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Tanggal Sewa</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestOrders as $order)
                <tr>
                    <td>#{{ $order->order_number }}</td>
                    <td>
                        <div class="table-user">
                            <img
                              src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'Tamu') }}&background=f0e68c"
                              alt="{{ $order->user->name ?? 'Tamu' }}"
                            />
                            <span>{{ $order->user->name ?? 'Tamu' }}</span>
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($order->start_date)->format('d M Y') }}</td>
                    <td>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
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
                    <td>
                        <button class="action-btn view" onclick="window.location.href='{{ route('admin.orders.show', $order->id) }}'" title="Lihat">
                            <i class="ti ti-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:30px; color:var(--text-light);">Belum ada pesanan terbaru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
