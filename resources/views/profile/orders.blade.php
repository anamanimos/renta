@extends('layouts.app')

@section('title', 'Pesanan Saya | Renta Enterprise')

@push('styles')
<style>
    .order-tabs {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 25px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .order-tabs::-webkit-scrollbar {
        display: none;
    }

    .order-tab {
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-light);
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: var(--transition);
        white-space: nowrap;
        text-decoration: none;
    }

    .order-tab.active, .order-tab:hover {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .order-card {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 20px;
        background: #fff;
    }

    .order-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fbfbfb;
        border-radius: 8px 8px 0 0;
    }

    .order-id-date {
        font-size: 13px;
    }

    .order-id-date strong {
        display: block;
        font-size: 14px;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .order-status {
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 30px;
        text-transform: uppercase;
    }

    .status-pending_payment { background: #fff3cd; color: #856404; }
    .status-awaiting_verification { background: #ffeeba; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-active_rent { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }

    .order-card-body {
        padding: 20px;
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .order-item-img {
        width: 80px;
        height: 80px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 5px;
    }

    .order-item-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .order-item-info {
        flex: 1;
    }

    .order-item-title {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 5px;
    }

    .order-item-meta {
        font-size: 13px;
        color: var(--text-light);
        margin-bottom: 3px;
    }

    .order-card-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-total-price {
        text-align: right;
        font-size: 14px;
        color: var(--text-light);
    }

    .order-total-price strong {
        font-size: 18px;
        color: var(--primary-color);
        display: block;
        margin-top: 2px;
    }

    .order-actions {
        display: flex;
        gap: 10px;
    }

    @media (max-width: 991px) {
        .shop-page-title {
            font-size: 22px;
            margin-bottom: 15px;
        }
        .order-card-header {
            padding: 12px 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-status {
            font-size: 11px;
            padding: 4px 10px;
        }
        .order-card-body {
            padding: 15px;
            gap: 12px;
            flex-direction: row;
            align-items: flex-start;
        }
        .order-item-img {
            width: 65px;
            height: 65px;
        }
        .order-item-title {
            font-size: 14px;
        }
        .order-card-footer {
            padding: 15px;
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
        .order-total-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: left;
        }
        .order-actions {
            width: 100%;
            justify-content: space-between;
            gap: 10px;
        }
        .order-actions a {
            flex: 1;
            text-align: center;
            padding: 10px 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 20px;">
    <!-- Breadcrumb -->
    <div class="shop-breadcrumb" style="margin-bottom: 30px;">
        <a href="{{ url('/') }}">Beranda</a> <span class="separator"><i class="fas fa-chevron-right"></i></span> 
        <a href="{{ url('/profile') }}">Akun Saya</a> <span class="separator"><i class="fas fa-chevron-right"></i></span> 
        <span>Pesanan Saya</span>
    </div>

    <div class="account-container">
        <!-- Load Sidebar -->
        @include('profile.sidebar')

        <!-- Main Account Content -->
        <div class="account-content">
            <h1 class="shop-page-title" style="text-align: left; padding-bottom: 10px; border-bottom: none; margin-bottom: 20px;">Pesanan Saya</h1>
            
            <div class="order-tabs">
                <a href="{{ url('/orders') }}" class="order-tab {{ !request('status') ? 'active' : '' }}">Semua Pesanan</a>
                <a href="{{ url('/orders?status=pending_payment') }}" class="order-tab {{ request('status') == 'pending_payment' ? 'active' : '' }}">Belum Bayar</a>
                <a href="{{ url('/orders?status=processing') }}" class="order-tab {{ in_array(request('status'), ['processing', 'active_rent']) ? 'active' : '' }}">Diproses/Disewa</a>
                <a href="{{ url('/orders?status=completed') }}" class="order-tab {{ request('status') == 'completed' ? 'active' : '' }}">Selesai</a>
                <a href="{{ url('/orders?status=cancelled') }}" class="order-tab {{ request('status') == 'cancelled' ? 'active' : '' }}">Dibatalkan</a>
            </div>

            <div class="order-list">
                @forelse($orders as $order)
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-id-date">
                            <strong>#{{ $order->order_number }}</strong>
                            {{ $order->created_at->format('d F Y') }}
                        </div>
                        <div class="order-status status-{{ $order->status }}">
                            {{ str_replace('_', ' ', $order->status) }}
                        </div>
                    </div>
                    
                    <div class="order-card-body">
                        @php
                            $firstItem = $order->items->first();
                            $itemCount = $order->items->count();
                        @endphp
                        
                        @if($firstItem)
                        <div class="order-item-img">
                            <img src="{{ asset('assets/images/product-placeholder.png') }}" alt="{{ $firstItem->product->name ?? 'Produk' }}">
                        </div>
                        <div class="order-item-info">
                            <div class="order-item-title">{{ $firstItem->product->name ?? 'Produk Dihapus' }}</div>
                            <div class="order-item-meta">{{ $firstItem->quantity }} Barang x Rp{{ number_format($firstItem->price, 0, ',', '.') }}</div>
                            <div class="order-item-meta">Tgl Sewa: {{ \Carbon\Carbon::parse($order->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($order->end_date)->format('d M Y') }}</div>
                            
                            @if($itemCount > 1)
                            <div style="font-size: 12px; color: var(--text-light); margin-top: 5px;">+ {{ $itemCount - 1 }} produk lainnya</div>
                            @endif
                        </div>
                        @else
                        <div class="order-item-info">
                            <div class="order-item-title">Detail produk tidak ditemukan</div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="order-card-footer">
                        <div class="order-actions">
                            <a href="{{ url('/orders/' . $order->id) }}" class="btn-outline" style="padding: 8px 15px; font-size: 12px;">Lihat Detail</a>
                            
                            @if($order->status == 'pending_payment')
                            <a href="{{ url('/payment/' . $order->id) }}" class="btn-primary" style="padding: 8px 15px; font-size: 12px; background: #28a745; border-color: #28a745;">Bayar Sekarang</a>
                            @endif
                        </div>
                        <div class="order-total-price">
                            Total Pesanan
                            <strong>Rp{{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 40px; background: #fafafa; border-radius: 12px; border: 1px dashed #ddd;">
                    <i class="fas fa-box-open" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px; color: #555;">Belum ada pesanan</h3>
                    <p style="color: #777; margin-bottom: 20px;">Anda belum melakukan pemesanan sewa peralatan.</p>
                    <a href="{{ url('/product') }}" class="btn-primary">Mulai Sewa Sekarang</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
