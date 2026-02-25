@extends('layouts.app')

@section('title', 'Checkout | Renta Enterprise')

@section('content')
<main>
    <div class="container" style="padding: 40px 15px; max-width: 1200px; margin: 0 auto;">
        <!-- Breadcrumb -->
        <div class="shop-breadcrumb" style="margin-bottom: 25px; font-size: 14px;">
            <a href="{{ url('/') }}" style="color:var(--primary-color); text-decoration:none;">Beranda</a> <span class="separator" style="margin:0 10px; color:#ccc;"><i class="fas fa-chevron-right"></i></span> 
            <a href="{{ route('cart.index') }}" style="color:var(--primary-color); text-decoration:none;">Keranjang Belanja</a> <span class="separator" style="margin:0 10px; color:#ccc;"><i class="fas fa-chevron-right"></i></span> 
            <span style="font-weight:600; color:var(--text-dark);">Checkout Pembayaran</span>
        </div>

        <section class="shop-page-section">
            <h1 class="shop-page-title" style="margin-bottom: 30px; font-size: 28px; color: var(--text-dark);">Penyelesaian Pesanan</h1>
            
            <form action="{{ route('checkout.process') }}" method="POST">
                @csrf
                <div class="checkout-container" style="display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start;">
                    
                    <!-- Address Selection -->
                    <div class="checkout-form-area" style="flex: 1; min-width: 60%;">
                        @if($errors->any())
                            <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                                <ul style="margin:0; padding-left:20px;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div style="background: #fff; padding: 30px; border-radius: 10px; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px;">
                            <h3 class="billing-title" style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 2px dashed #eee; padding-bottom: 15px;"><i class="fas fa-map-marked-alt" style="color:var(--primary-color); margin-right:10px;"></i> Alamat Pengiriman & Instalasi</h3>
                            
                            @if(count($addresses) > 0)
                                <div class="address-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">
                                    @foreach($addresses as $address)
                                    <label class="address-card" style="position: relative; display: block; border: 2px solid {{ $address->is_main ? 'var(--primary-color)' : '#eee' }}; border-radius: 8px; padding: 15px; cursor: pointer; transition: 0.2s;">
                                        <input type="radio" name="address_id" value="{{ $address->id }}" {{ $address->is_main ? 'checked' : '' }} required style="position: absolute; top: 20px; right: 20px; accent-color: var(--primary-color);">
                                        <div class="address-card-inner">
                                            <div class="address-header" style="margin-bottom: 10px;">
                                                <h4 style="margin: 0; font-size: 15px; color: var(--text-dark); display:flex; align-items:center; gap:10px;">{{ $address->label }} @if($address->is_main)<span style="background: rgba(211,47,47,0.1); color: var(--primary-color); padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;">Utama</span>@endif</h4>
                                            </div>
                                            <div class="address-body" style="font-size: 13px; color: #666; line-height: 1.5;">
                                                <p style="margin:0 0 5px;"><strong>{{ $address->recipient_name }}</strong> ({{ $address->phone_number }})</p>
                                                <p style="margin:0;">{{ $address->full_address }}</p>
                                                <p style="margin:0;">{{ $address->city_id }} - {{ $address->postal_code }}</p>
                                            </div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            @else
                                <div style="background: #fff8e1; color: #f57f17; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #ffecb3;">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 30px; margin-bottom: 15px;"></i>
                                    <p style="margin: 0 0 15px; font-weight: 500;">Anda belum mendaftarkan buku alamat.</p>
                                    <a href="{{ route('addresses.index') }}" class="btn-primary" style="display: inline-block; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 13px;">Kelola Buku Alamat</a>
                                </div>
                            @endif
                        </div>

                        <!-- Catatan Pesanan -->
                        <div style="background: #fff; padding: 30px; border-radius: 10px; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                            <h3 class="billing-title" style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 2px dashed #eee; padding-bottom: 15px;"><i class="fas fa-clipboard-list" style="color:var(--primary-color); margin-right:10px;"></i> Catatan Pesanan Khusus (Opsional)</h3>
                            <textarea name="notes" class="form-control" placeholder="Contoh: Titip di pos satpam, atau tolong siapkan kabel sambungan extra..." style="width: 100%; min-height: 120px; padding: 15px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; font-size: 14px; outline: none; transition: 0.3s; box-sizing:border-box;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='#ddd'"></textarea>
                        </div>
                    </div>
                    
                    <!-- Sidebar Order Summary -->
                    <div class="checkout-sidebar" style="width: 380px;">
                        <div style="background: #fff; border: 1px solid #eaeaea; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); position:sticky; top:100px;">
                            
                            <!-- Daftar Item Mini -->
                            <div style="padding: 25px 25px 15px;">
                                <h3 class="cart-totals-title" style="margin-top:0; font-size:18px; color:var(--text-dark); margin-bottom: 20px;">Rincian Pesanan</h3>
                                @php
                                    $subtotalCart = $cart->subtotal;
                                    $days = max(1, $cart->total_days);
                                @endphp
                                @foreach($cart->items as $item)
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #f0f0f0;">
                                        <div style="flex: 1; padding-right: 15px;">
                                            <div style="font-weight: 600; font-size: 14px; color: var(--text-dark);">
                                                {{ $item->product->name }} <span style="color:var(--primary-color);">x {{ $item->quantity }}</span>
                                                <div style="font-size:11px; color:#555; display:inline-block; margin-left:5px; padding:2px 6px; background:#eee; border-radius:10px;">
                                                    {!! $item->product->price_type === 'sell_once' ? 'Jual Putus' : ($item->product->price_type === 'rental_tiered' ? 'Sewa Tiered' : 'Sewa Flat') !!}
                                                </div>
                                            </div>
                                            @if($item->product->price_type !== 'sell_once')
                                            <div style="font-size: 11px; color: #888; margin-top: 3px;">Periode: {{ Carbon\Carbon::parse($cart->start_date)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($cart->end_date)->format('d/m/Y') }}</div>
                                            @endif
                                        </div>
                                        <div style="font-weight: 600; font-size: 14px; color: #555;">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Kalkulasi Harga -->
                            <div style="padding: 15px 25px 25px; background: #fafafa; border-radius: 0 0 10px 10px;">
                                @php
                                    $shippingCost = 150000; // Hardcode untuk kemudahan testing MVP
                                    $discountAmount = 0;
                                    if($cart->coupon_code) {
                                        $coupon = \App\Models\Coupon::where('code', $cart->coupon_code)->first();
                                        if($coupon) {
                                            if($coupon->discount_type === 'percentage') {
                                                $discountAmount = $subtotalCart * ($coupon->discount_value / 100);
                                            } else {
                                                $discountAmount = $coupon->discount_value;
                                            }
                                            $discountAmount = min($discountAmount, $subtotalCart);
                                        }
                                    }
                                    $grandTotal = $subtotalCart - $discountAmount + $shippingCost;
                                @endphp

                                <div style="display:flex; justify-content:space-between; margin-bottom:12px; color:#666; font-size:14px;">
                                    <span>Subtotal ({{ $days }} Hari)</span>
                                    <span style="font-weight:600;">Rp{{ number_format($subtotalCart, 0, ',', '.') }}</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#666; font-size:14px;">
                                    <span>Biaya Pengiriman & Setup</span>
                                    <span style="font-weight:600;">Rp{{ number_format($shippingCost, 0, ',', '.') }}</span>
                                </div>
                                @if($discountAmount > 0)
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#16a34a; font-size:14px;">
                                    <span>Diskon Kupon ({{ $cart->coupon_code }})</span>
                                    <span style="font-weight:600;">-Rp{{ number_format($discountAmount, 0, ',', '.') }}</span>
                                </div>
                                @endif

                                <div style="display:flex; justify-content:space-between; margin-top:20px; padding-top:20px; border-top:2px solid #eaeaea; font-weight:700; font-size:20px; color:var(--text-dark);">
                                    <span>Total Tagihan</span>
                                    <span style="color:var(--primary-color);">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
                                </div>
                                
                                <button type="submit" class="btn-primary" style="width:100%; padding:15px; border:none; border-radius:8px; margin-top:25px; font-size:15px; font-weight:600; cursor:pointer; background:var(--primary-color); color:#fff; transition:0.3s; box-shadow:0 6px 15px rgba(211,47,47,0.25);" {{ count($addresses) == 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>Konfirmasi Pesanan</button>
                                
                                <p style="font-size: 11px; color: #888; margin-top: 15px; text-align: center; line-height: 1.5;">
                                    <i class="fas fa-lock" style="color: #4CAF50;"></i> Transaksi dilindungi enkripsi SSL 256-bit.<br>Anda sepakat terhadap <a>Syarat & Ketentuan</a> kami.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>
@endsection
