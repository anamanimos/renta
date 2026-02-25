@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('header-left-extra')
<a href="{{ route('admin.orders.index') }}" class="icon-btn" style="margin-right:15px; color:var(--text-color)">
    <i class="ti ti-arrow-left"></i> Kembali
</a>
@endsection

@push('styles')
<style>
    .detail-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:15px; }
    .detail-title { display:flex; align-items:center; gap:15px; }
    .detail-title h1 { font-size:1.5rem; margin:0; color:var(--text-color); }
    .btn-outline { padding:8px 16px; border:1px solid var(--border-color); background:var(--white); border-radius:6px; color:var(--text-color); text-decoration:none; display:flex; align-items:center; gap:8px; transition:0.2s; font-weight:500; cursor:pointer; font-family:inherit; }
    .btn-outline:hover { background-color:var(--bg-color); border-color:var(--text-light); }
    .detail-actions { display:flex; gap:10px; }
    .btn-success-solid { background-color:var(--success); color:white; border:none; padding:8px 16px; border-radius:6px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:8px; font-family:inherit; }
    .btn-danger-solid { background-color:var(--danger); color:white; border:none; padding:8px 16px; border-radius:6px; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:8px; font-family:inherit; }
    .detail-grid { display:grid; grid-template-columns:2fr 1fr; gap:24px; }
    @media (max-width:992px) { .detail-grid { grid-template-columns:1fr; } }
    .card { background:var(--white); border-radius:12px; padding:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:24px; }
    .card-title { font-size:1.1rem; font-weight:600; margin-bottom:16px; border-bottom:1px solid var(--border-color); padding-bottom:12px; display:flex; align-items:center; justify-content:space-between; }
    .info-row { display:flex; margin-bottom:12px; }
    .info-label { width:150px; color:var(--text-light); font-size:0.9rem; }
    .info-value { flex:1; font-weight:500; color:var(--text-color); font-size:0.95rem; }
    .item-list { width:100%; border-collapse:collapse; }
    .item-list th { text-align:left; padding:12px; background:var(--bg-color); color:var(--text-light); font-weight:600; font-size:0.85rem; }
    .item-list td { padding:16px 12px; border-bottom:1px solid var(--border-color); vertical-align:middle; }
    .item-product { display:flex; align-items:center; gap:12px; }
    .item-img { width:48px; height:48px; border-radius:8px; object-fit:cover; background:#f1f5f9; border:1px solid var(--border-color); }
    .item-name { font-weight:600; color:var(--text-color); display:block; }
    .item-sku { font-size:0.8rem; color:var(--text-light); }
    .summary-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px dashed var(--border-color); }
    .summary-row.total { border-bottom:none; font-size:1.2rem; font-weight:700; color:var(--primary-color); padding-top:16px; }
</style>
@endpush

@section('content')
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
    $totalDays = \Carbon\Carbon::parse($order->start_date)->diffInDays(\Carbon\Carbon::parse($order->end_date));
@endphp

<div class="detail-header">
    <div class="detail-title">
        <h1>Pesanan #{{ $order->order_number }}</h1>
        <span class="badge-status {{ $st[0] }}" style="margin-left:10px;">{{ $st[1] }}</span>
    </div>
    <div class="detail-actions">
        <button class="btn-outline" onclick="window.print()">
            <i class="ti ti-printer"></i> Cetak Invoice
        </button>
        @if(!in_array($order->status, ['completed', 'cancelled']))
        <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" style="display:inline;">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="btn-danger-solid" onclick="return confirm('Yakin ingin membatalkan pesanan ini?');">
                <i class="ti ti-x"></i> Tolak
            </button>
        </form>
        <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" style="display:inline;">
            @csrf @method('PUT')
            @php
                $nextStatus = match($order->status) {
                    'pending_payment' => 'awaiting_verification',
                    'awaiting_verification' => 'processing',
                    'processing' => 'active_rent',
                    'active_rent' => 'completed',
                    default => 'processing'
                };
                $nextLabel = match($order->status) {
                    'pending_payment' => 'Verifikasi',
                    'awaiting_verification' => 'Proses Pesanan',
                    'processing' => 'Mulai Sewa',
                    'active_rent' => 'Selesaikan',
                    default => 'Proses'
                };
            @endphp
            <input type="hidden" name="status" value="{{ $nextStatus }}">
            <button type="submit" class="btn-success-solid">
                <i class="ti ti-check"></i> {{ $nextLabel }}
            </button>
        </form>
        @endif
    </div>
</div>

<div class="detail-grid">
    <!-- Left Column: Items & Rental Info -->
    <div class="left-col">
        <div class="card">
            <h3 class="card-title">Informasi Penyewaan</h3>
            <div class="info-row">
                <span class="info-label">Durasi Sewa</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($order->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($order->end_date)->format('d M Y') }} ({{ $totalDays }} Hari)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat Kirim</span>
                <span class="info-value">{{ $order->shipping_address ?? 'Diambil di Toko' }}</span>
            </div>
            @if($order->notes)
            <div class="info-row">
                <span class="info-label">Catatan Pembeli</span>
                <span class="info-value">"{{ $order->notes }}"</span>
            </div>
            @endif
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <h3 class="card-title" style="margin:24px 24px 16px; border-bottom:none;">Barang yang Disewa</h3>
            <div style="overflow-x:auto;">
                <table class="item-list">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga/Hari</th>
                            <th>Qty</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="item-product">
                                    @if($item->product && $item->product->image)
                                    <img src="{{ Str::startsWith($item->product->image, 'http') ? $item->product->image : asset($item->product->image) }}" class="item-img" alt="{{ $item->product->name }}">
                                    @else
                                    <img src="https://placehold.co/48x48?text=IMG" class="item-img" alt="Product">
                                    @endif
                                    <div>
                                        <span class="item-name">{{ $item->product->name ?? 'Produk dihapus' }}</span>
                                        <span class="item-sku">SKU: {{ $item->product->slug ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</td>
                            <td>{{ $item->quantity }} Unit</td>
                            <td style="text-align:right; font-weight:600;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Customer & Payment -->
    <div class="right-col">
        <div class="card">
            <h3 class="card-title">Pelanggan</h3>
            <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'T') }}&background=f0e68c" style="width:50px; height:50px; border-radius:50%;" alt="{{ $order->user->name ?? 'Tamu' }}">
                <div>
                    <h4 style="margin:0; font-size:1rem;">{{ $order->user->name ?? 'Tamu' }}</h4>
                    <span style="font-size:0.85rem; color:var(--text-light);">Member Sejak {{ $order->user ? $order->user->created_at->format('Y') : '-' }}</span>
                </div>
            </div>
            @if($order->user && $order->user->email)
            <div class="info-row" style="margin-bottom:8px;">
                <i class="ti ti-mail" style="color:var(--text-light); margin-right:8px; display:flex; align-items:center;"></i>
                <span class="info-value">{{ $order->user->email }}</span>
            </div>
            @endif
            @if($order->user && $order->user->phone_number)
            <div class="info-row" style="margin-bottom:8px;">
                <i class="ti ti-brand-whatsapp" style="color:var(--text-light); margin-right:8px; display:flex; align-items:center;"></i>
                <span class="info-value">{{ $order->user->phone_number }}</span>
            </div>
            @endif
        </div>

        <div class="card">
            <h3 class="card-title">Rincian Pembayaran</h3>
            <div class="summary-row">
                <span>Subtotal Barang ({{ $totalDays }} Hari)</span>
                <span>Rp {{ number_format($order->subtotal ?? $order->grand_total, 0, ',', '.') }}</span>
            </div>
            @if($order->shipping_cost)
            <div class="summary-row">
                <span>Ongkos Kirim</span>
                <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="summary-row total">
                <span>Total Tagihan</span>
                <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
            </div>

            @if($order->payment_proof)
            <div style="margin-top:20px; text-align:center;">
                <a href="{{ $order->payment_proof }}" target="_blank" class="btn-outline" style="width:100%; justify-content:center; text-decoration:none;">
                    Lihat Bukti Transfer <i class="ti ti-external-link"></i>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
