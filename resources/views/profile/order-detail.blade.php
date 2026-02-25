@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number . ' | Renta Enterprise')

@push('styles')
<style>
    .detail-page-wrapper {
        max-width: 900px;
        margin: 0 auto;
        padding-bottom: 60px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-light);
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 25px;
        transition: var(--transition);
        text-decoration: none;
    }

    .back-link:hover {
        color: var(--primary-color);
    }

    .order-header-box {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .order-header-info h2 {
        font-size: 20px;
        margin-bottom: 5px;
        color: var(--text-dark);
    }

    .order-header-info p {
        font-size: 14px;
        color: var(--text-light);
        margin: 0;
    }

    .order-status-badge {
        font-size: 14px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 30px;
        display: inline-block;
        text-transform: uppercase;
    }

    .status-pending_payment { background: #fff3cd; color: #856404; }
    .status-awaiting_verification { background: #ffeeba; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-active_rent { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }

    /* Order Tracking Timeline */
    .order-timeline-box {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 30px 25px;
        margin-bottom: 25px;
    }

    .timeline-container {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-top: 10px;
    }

    .timeline-line {
        position: absolute;
        top: 20px;
        left: 40px;
        right: 40px;
        height: 4px;
        background: #eee;
        z-index: 1;
    }

    .timeline-line-active {
        position: absolute;
        top: 20px;
        left: 40px;
        height: 4px;
        background: var(--primary-color);
        z-index: 2;
        transition: width 0.5s ease;
    }

    .timeline-step {
        position: relative;
        z-index: 3;
        text-align: center;
        width: 80px;
    }

    .timeline-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #eee;
        color: #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin: 0 auto 10px;
        transition: var(--transition);
    }

    .timeline-step.active .timeline-icon {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: #fff;
    }

    .timeline-step.completed .timeline-icon {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .timeline-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-dark);
        line-height: 1.3;
    }

    .timeline-date {
        font-size: 11px;
        color: var(--text-light);
        margin-top: 5px;
        display: block;
    }

    /* Detail Cards */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    .detail-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 20px 25px;
    }

    .detail-card h3 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 10px;
        color: var(--text-dark);
    }

    .detail-content p {
        font-size: 14px;
        color: var(--text-light);
        line-height: 1.6;
        margin-bottom: 5px;
    }

    .detail-content p strong {
        color: var(--text-dark);
        font-weight: 600;
    }

    /* Order Items Table */
    .order-items-box {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 20px 25px;
        margin-bottom: 25px;
    }

    .order-items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .order-items-table th {
        text-align: left;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 13px;
        text-transform: uppercase;
        color: var(--text-light);
    }
    
    .order-items-table td {
        padding: 15px 0;
        border-bottom: 1px solid #f5f5f5;
        vertical-align: middle;
    }

    .order-items-table tr:last-child td {
        border-bottom: none;
    }

    .item-info-flex {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .item-info-flex img {
        width: 60px;
        height: 60px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 5px;
        object-fit: contain;
    }

    .item-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .item-meta {
        font-size: 12px;
        color: var(--text-light);
    }

    /* Summary Box */
    .summary-box {
        background: #fdfdfd;
        border: 1px solid var(--primary-color);
        border-radius: 8px;
        padding: 20px 25px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
        color: var(--text-dark);
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
        font-size: 18px;
        font-weight: 700;
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .order-header-box {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        .detail-grid {
            grid-template-columns: 1fr;
        }
        .timeline-container {
            overflow-x: auto;
            padding-bottom: 10px;
        }
        .timeline-line, .timeline-line-active {
            left: 30px;
            right: 30px;
        }
        .order-items-table th { display: none; }
        .order-items-table tr {
            display: block;
            margin-bottom: 15px;
            background: #fefefe;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
        }
        .order-items-table td {
            display: block;
            padding: 5px 0;
            border: none;
            text-align: right;
        }
        .order-items-table td[data-title]::before {
            content: attr(data-title);
            float: left;
            font-weight: 600;
            color: var(--text-light);
            font-size: 12px;
            text-transform: uppercase;
        }
        .item-info-flex {
            text-align: left;
            float: none;
            margin-bottom: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 30px;">
    <div class="detail-page-wrapper">
        <a href="{{ url('/orders') }}" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Pesanan Saya</a>

        <!-- Header -->
        <div class="order-header-box">
            <div class="order-header-info">
                <h2>Pesanan #{{ $order->order_number }}</h2>
                <p>Dibuat pada {{ $order->created_at->format('d F Y H:i') }} WIB</p>
            </div>
            <div class="order-status-badge status-{{ $order->status }}">
                {{ str_replace('_', ' ', $order->status) }}
            </div>
        </div>

        @php
            // Simple Logic for Timeline Process
            $progressList = [
                'pending_payment' => 0,
                'awaiting_verification' => 25,
                'processing' => 50,
                'active_rent' => 75,
                'completed' => 100,
                'cancelled' => 0
            ];
            $currentProgress = $order->status === 'cancelled' ? 0 : ($progressList[$order->status] ?? 0);
        @endphp

        @if($order->status !== 'cancelled')
        <!-- Timeline -->
        <div class="order-timeline-box">
            <h3 style="font-size: 16px; margin-bottom: 25px; margin-top: 0;">Status Pelacakan</h3>
            <div class="timeline-container">
                <div class="timeline-line"></div>
                <div class="timeline-line-active" style="width: {{ $currentProgress }}%;"></div>
                
                <div class="timeline-step {{ $currentProgress >= 0 ? 'completed' : '' }}">
                    <div class="timeline-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="timeline-label">Dibuat</div>
                    <span class="timeline-date">{{ $order->created_at->format('d/m/y') }}</span>
                </div>
                
                <div class="timeline-step {{ $currentProgress == 0 ? 'active' : ($currentProgress > 0 ? 'completed' : '') }}">
                    <div class="timeline-icon"><i class="fas fa-wallet"></i></div>
                    <div class="timeline-label">Pembayaran</div>
                </div>
                
                <div class="timeline-step {{ $currentProgress == 50 ? 'active' : ($currentProgress > 50 ? 'completed' : '') }}">
                    <div class="timeline-icon"><i class="fas fa-box-open"></i></div>
                    <div class="timeline-label">Diproses</div>
                </div>
                
                <div class="timeline-step {{ $currentProgress == 75 ? 'active' : ($currentProgress > 75 ? 'completed' : '') }}">
                    <div class="timeline-icon"><i class="fas fa-truck-loading"></i></div>
                    <div class="timeline-label">Dikirim / Sewa</div>
                </div>
                
                <div class="timeline-step {{ $currentProgress == 100 ? 'active completed' : '' }}">
                    <div class="timeline-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="timeline-label">Selesai</div>
                </div>
            </div>

            <!-- Payment Action Banner (Visible only when pending_payment) -->
            @if($order->status == 'pending_payment')
            <div style="margin-top: 30px; background: #fdf6f6; border: 1px dashed var(--primary-color); border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h4 style="margin: 0 0 5px; color: var(--text-dark); font-size: 15px;">Selesaikan Pembayaran Anda!</h4>
                    <p style="margin: 0; font-size: 13px; color: var(--text-light);">Pesanan akan dibatalkan otomatis jika melewati batas waktu penyewaan.</p>
                </div>
                <a href="{{ url('/payment/' . $order->id) }}" class="btn-primary" style="background: #28a745; border-color: #28a745; text-decoration: none;">Cara Pembayaran / Upload Bukti</a>
            </div>
            @endif
        </div>
        @else
        <div class="order-timeline-box" style="text-align:center; padding: 20px; border-color: #f5c6cb; background: #f8d7da;">
            <p style="color: #721c24; margin: 0; font-weight: 600;">Pesanan ini telah Dibatalkan.</p>
        </div>
        @endif

        <!-- Info Grid -->
        <div class="detail-grid">
            <div class="detail-card">
                <h3>Alamat Pengiriman</h3>
                <div class="detail-content">
                    @if($order->address)
                        <p><strong>{{ $order->address->recipient_name }} - {{ $order->address->label }}</strong></p>
                        <p>{{ $order->address->phone_number }}</p>
                        <p>{{ $order->address->full_address }}</p>
                        <p>{{ $order->address->district_id }}, {{ $order->address->city_id }} {{ $order->address->postal_code }}</p>
                    @else
                        <p><em>Alamat tidak ditemukan / Ambil di Toko</em></p>
                    @endif
                </div>
            </div>
            
            <div class="detail-card">
                <h3>Informasi Pembayaran</h3>
                <div class="detail-content">
                    <p><strong>Metode Pembayaran:</strong> Transfer Bank Langsung</p>
                    <p><strong>Status:</strong> <span style="font-weight: 600;" class="{{ $order->status == 'pending_payment' ? 'text-danger' : 'text-success' }}">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span></p>
                    
                    @if($order->payment_proof)
                    <div style="margin-top: 10px;">
                        <span style="font-size:12px;color:green;"><i class="fas fa-check-circle"></i> Bukti Upload Diterima</span>
                    </div>
                    @endif
                    
                    @if($order->status == 'pending_payment')
                    <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                        <p style="font-size: 12px; margin-bottom: 5px;">Transfer Pembayaran Ke:</p>
                        <p><strong>BCA: 1234567890 (PT Renta Enterprise)</strong></p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="order-items-box">
            <h3 style="font-size: 16px; margin-bottom: 15px; margin-top: 0; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Daftar Produk yang Disewa</h3>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th width="50%">Produk</th>
                        <th width="15%">Harga</th>
                        <th width="15%" style="text-align: center;">Qty</th>
                        <th width="20%" style="text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td data-title="Produk">
                            <div class="item-info-flex">
                                <img src="{{ asset('assets/images/product-placeholder.png') }}" alt="{{ $item->product->name ?? 'Terhapus' }}">
                                <div>
                                    <div class="item-name">{{ $item->product->name ?? 'Produk Terhapus' }}</div>
                                    <div class="item-meta">Tgl Sewa: {{ \Carbon\Carbon::parse($order->start_date)->format('d M y') }} - {{ \Carbon\Carbon::parse($order->end_date)->format('d M y') }}</div>
                                    <div class="item-meta">({{ $order->total_days }} Hari)</div>
                                </div>
                            </div>
                        </td>
                        <td data-title="Harga">Rp{{ number_format($item->price, 0, ',', '.') }} / hari</td>
                        <td data-title="Qty" style="text-align: center;">{{ $item->quantity }}</td>
                        <td data-title="Total" style="text-align: right; font-weight: 600;">
                            Rp{{ number_format($item->price * $item->quantity * max(1, $order->total_days), 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 100%; max-width: 350px;">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal Sewa</span>
                        <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Biaya Pengiriman</span>
                        <span>Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-total">
                        <span>Total Tagihan</span>
                        <span>Rp{{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
