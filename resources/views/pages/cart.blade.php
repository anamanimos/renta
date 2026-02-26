@extends('layouts.app')

@section('title', 'Keranjang Belanja | Renta Enterprise')

@section('content')
<main>
    <div class="container" style="padding: 40px 15px; max-width: 1200px; margin: 0 auto;">
        <!-- Breadcrumb -->
        <div class="shop-breadcrumb" style="margin-bottom: 25px; font-size: 14px;">
            <a href="{{ url('/') }}" style="color:var(--primary-color); text-decoration:none;">Beranda</a> <span class="separator" style="margin:0 10px; color:#ccc;"><i class="fas fa-chevron-right"></i></span> 
            <span style="font-weight:600; color:var(--text-dark);">Troli Penyewaan</span>
        </div>

        <section class="shop-page-section">
            <h1 class="shop-page-title" style="margin-bottom: 30px; font-size: 28px; color: var(--text-dark);">Keranjang Troli</h1>
            
            <div class="cart-container" style="display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start;">
                
                <div class="cart-content" style="flex: 1; min-width: 60%;">
                    @if(session('success'))
                        <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($cart->items->count() > 0)
                        <!-- Set Tanggal Sewa -->
                        <div style="background: #fff; padding: 25px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.02)">
                            <h4 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; color:var(--text-dark);"><i class="far fa-calendar-alt" style="color:var(--primary-color); margin-right:8px;"></i> Tentukan Periode Sewa Anda</h4>
                            <form action="{{ route('cart.dates') }}" method="POST" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                                @csrf
                                <div style="flex: 1; min-width: 200px;">
                                    <label style="display:block; margin-bottom:8px; font-size:13px; color:#666; font-weight:500;">Tanggal Diambil / Mulai</label>
                                    <input type="date" name="start_date" value="{{ $cart->start_date ? \Carbon\Carbon::parse($cart->start_date)->format('Y-m-d') : '' }}" required style="width:100%; padding:12px 15px; border:1px solid #ddd; border-radius:6px; font-family:inherit; outline:none; transition:0.3s;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='#ddd'">
                                </div>
                                <div style="flex: 1; min-width: 200px;">
                                    <label style="display:block; margin-bottom:8px; font-size:13px; color:#666; font-weight:500;">Tanggal Dikembalikan / Selesai</label>
                                    <input type="date" name="end_date" value="{{ $cart->end_date ? \Carbon\Carbon::parse($cart->end_date)->format('Y-m-d') : '' }}" required style="width:100%; padding:12px 15px; border:1px solid #ddd; border-radius:6px; font-family:inherit; outline:none; transition:0.3s;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='#ddd'">
                                </div>
                                <div>
                                    <button type="submit" class="btn-primary" style="padding: 12px 25px; border:none; border-radius:6px; font-weight:600; cursor:pointer; background:var(--primary-color); color:#fff; transition:0.3s;"><i class="fas fa-save" style="margin-right:5px;"></i> Simpan Periode</button>
                                </div>
                            </form>
                            @if($cart->start_date && $cart->end_date)
                            <div style="margin-top: 20px; padding: 12px 15px; background: rgba(46, 125, 50, 0.05); color: #2e7d32; border-left: 4px solid #2e7d32; font-size: 14px; border-radius:0 6px 6px 0;">
                                <strong>Terekam:</strong> {{ Carbon\Carbon::parse($cart->start_date)->translatedFormat('d M Y') }} sampai {{ Carbon\Carbon::parse($cart->end_date)->translatedFormat('d M Y') }} (Total Durasi: <strong>{{ $cart->total_days }} Hari</strong> Pemakaian)
                            </div>
                            @endif
                        </div>

                        <!-- Daftar Item Keranjang -->
                        <div style="background: #fff; border-radius: 10px; border: 1px solid #eaeaea; overflow:hidden;">
                            <table class="shop-table" style="width: 100%; border-collapse: collapse;">
                                <thead style="background: #fdfdfd; border-bottom: 2px solid #eee;">
                                    <tr style="text-align:left; color:#555; font-size:14px;">
                                        <th style="padding: 20px;">Peralatan</th>
                                        <th style="padding: 20px;">Tarif / Hari</th>
                                        <th style="padding: 20px;">Kuantitas</th>
                                        <th style="padding: 20px;">Subtotal ({{ max(1, $cart->total_days) }} Hari)</th>
                                        <th style="padding: 20px; text-align:center;"><i class="fas fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $subtotalCart = $cart->subtotal;
                                    @endphp
                                    @foreach($cart->items as $item)
                                    <tr style="border-bottom: 1px solid #f5f5f5; transition:0.2s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
                                        <td style="padding: 20px; display:flex; align-items:center; gap:15px;">
                                            <div style="width:70px; height:70px; background:#f9f9f9; border-radius:8px; overflow:hidden; border:1px solid #eee;">
                                                <img src="{{ Str::startsWith($item->product->image, 'http') ? $item->product->image : asset($item->product->image ?? 'assets/images/product-1.png') }}" style="width:100%; height:100%; object-fit:contain; padding:5px;">
                                            </div>
                                            <div>
                                                <a href="{{ route('product.show', $item->product->slug) }}" style="color:var(--text-dark); text-decoration:none; font-weight:600; font-size:15px;">
                                                    {{ $item->product->name }}
                                                    @if($item->variant)
                                                        <span style="color:#666; font-weight:normal; font-size:13px;"> - {{ $item->variant->name }}</span>
                                                    @endif
                                                </a>
                                                @php $uiPriceType = $item->variant ? $item->variant->price_type : $item->product->price_type; @endphp
                                                <div style="font-size:12px; color:#888; margin-top:5px;"><i class="fas fa-tag"></i> {{ $item->product->category->name ?? 'Peralatan' }} <span style="font-weight:600; color:var(--primary-color);">({!! ($uiPriceType === 'sell_once' || $uiPriceType === 'beli_putus') ? 'Jual Putus' : (($uiPriceType === 'rental_tiered' || $uiPriceType === 'custom_pricing') ? 'Sewa Tiered' : 'Sewa Flat') !!})</span></div>
                                            </div>
                                        </td>
                                        <td style="padding: 20px; color:#555; font-weight:500;">
                                            @php
                                                $priceBaseUI = $item->variant ? $item->variant->price_per_day : ($item->product->promo_price ?? $item->product->price_per_day);
                                            @endphp
                                            @if($uiPriceType === 'sell_once' || $uiPriceType === 'beli_putus')
                                                Rp{{ number_format($priceBaseUI, 0, ',', '.') }} <small>(Beli)</small>
                                            @elseif($uiPriceType === 'rental_tiered' || $uiPriceType === 'custom_pricing')
                                                Rp{{ number_format($priceBaseUI, 0, ',', '.') }} <small>(Hari ke-1)</small>
                                            @else
                                                Rp{{ number_format($priceBaseUI, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td style="padding: 20px;">
                                            <form action="{{ route('cart.update', $item->id) }}" method="POST" style="display:inline-flex; align-items:center; background:#f9f9f9; padding:5px; border-radius:6px; border:1px solid #eee;">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" style="width:45px; height:35px; border:none; background:transparent; text-align:center; font-weight:600; outline:none; font-family:inherit;">
                                                <button type="submit" title="Perbarui Kuantitas" style="background:#fff; border:1px solid #ddd; width:35px; height:35px; border-radius:4px; color:var(--primary-color); cursor:pointer; font-size:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05);"><i class="fas fa-sync-alt"></i></button>
                                            </form>
                                        </td>
                                        <td style="padding: 20px; font-size:16px; color:var(--text-dark);"><strong>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                                        <td style="padding: 20px; text-align:center;">
                                            <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Item" style="background:rgba(211,47,47,0.1); border:none; width:36px; height:36px; border-radius:50%; color:#d32f2f; cursor:pointer; font-size:14px; transition:0.3s;" onmouseover="this.style.background='#d32f2f'; this.style.color='#fff';" onmouseout="this.style.background='rgba(211,47,47,0.1)'; this.style.color='#d32f2f';"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- State Kosong -->
                        <div style="text-align:center; padding: 100px 20px; background:#fff; border-radius:10px; border:1px dashed #ddd; box-shadow:0 4px 15px rgba(0,0,0,0.02);">
                            <div style="width:80px; height:80px; background:rgba(0,0,0,0.03); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                                <i class="fas fa-shopping-basket" style="font-size: 35px; color: #aaa;"></i>
                            </div>
                            <h3 style="color: #444; margin-bottom:10px;">Troli Anda Masih Kosong</h3>
                            <p style="color: #777; margin-bottom:25px;">Mari penuhi keranjang dengan peralatan terbaik untuk event luar biasa Anda.</p>
                            <a href="{{ route('products.index') }}" class="btn-primary" style="display:inline-block; padding:12px 30px; border-radius:30px; text-decoration:none; font-weight:600; background:var(--primary-color); color:#fff;"><i class="fas fa-search" style="margin-right:8px;"></i> Jelajahi Katalog</a>
                        </div>
                    @endif
                </div>
                
                @if($cart->items->count() > 0)
                <div class="cart-sidebar" style="width: 340px; background: #fff; border: 1px solid #eaeaea; border-radius: 10px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); position:sticky; top:100px;">
                    <h3 style="margin-top:0; border-bottom:2px dashed #eee; padding-bottom:15px; font-size:18px; color:var(--text-dark);">Ringkasan Belanja</h3>
                    
                    <div style="display:flex; justify-content:space-between; margin:20px 0 15px; color:#555; font-size:14px;">
                        <span>Durasi Sewa Termasuk</span>
                        <span style="font-weight:600; color:var(--text-dark);">{{ $cart->total_days }} Hari</span>
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#555; font-size:14px;">
                        <span>Total Nilai Alat</span>
                        <span style="font-weight:600; color:var(--text-dark);">Rp{{ number_format($subtotalCart, 0, ',', '.') }}</span>
                    </div>

                    @php
                        $discountAmount = 0;
                        if($cart->coupon_code) {
                            $coupon = \App\Models\Coupon::where('code', $cart->coupon_code)->first();
                            if($coupon) {
                                if($coupon->discount_type === 'percentage') {
                                    $discountAmount = $subtotalCart * ($coupon->discount_value / 100);
                                } else {
                                    $discountAmount = $coupon->discount_value;
                                }
                                $discountAmount = min($discountAmount, $subtotalCart); // Cegah diskon minus
                            }
                        }
                        $grandTotal = $subtotalCart - $discountAmount;
                    @endphp
                    
                    @if($cart->coupon_code && $discountAmount > 0)
                    <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#16a34a; font-size:14px;">
                        <span style="display:flex; align-items:center;">
                            <i class="ti ti-ticket" style="margin-right:5px;"></i> Kupon ({{ $cart->coupon_code }})
                            <form action="{{ route('cart.coupon.remove') }}" method="POST" style="margin-left:8px; display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none; border:none; color:#ef4444; font-size:12px; cursor:pointer;" title="Hapus Kupon"><i class="ti ti-x"></i></button>
                            </form>
                        </span>
                        <span style="font-weight:600;">-Rp{{ number_format($discountAmount, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div style="display:flex; justify-content:space-between; margin-bottom:20px; color:#555; font-size:14px;">
                        <span>Biaya Pengiriman</span>
                        <span style="font-size:12px; color:var(--primary-color); background:rgba(211,47,47,0.08); padding:2px 8px; border-radius:4px; font-weight:600;">Dihitung saat checkout</span>
                    </div>
                    
                    <!-- Kotak Kupon -->
                    @if(!$cart->coupon_code)
                    <div style="margin-bottom:20px;">
                        <form action="{{ route('cart.coupon.apply') }}" method="POST" style="display:flex; gap:10px;">
                            @csrf
                            <input type="text" name="coupon_code" placeholder="Punya kode promo?" style="flex:1; padding:10px 12px; border:1px solid #ddd; border-radius:6px; font-family:inherit; outline:none; font-size:13px;" required>
                            <button type="submit" style="background:#f1f5f9; color:#475569; border:none; padding:0 15px; border-radius:6px; font-weight:600; cursor:pointer; font-size:13px; transition:0.2s;">Terapkan</button>
                        </form>
                        @error('coupon_code')
                            <small style="color:#ef4444; display:block; margin-top:5px;">{{ $message }}</small>
                        @enderror
                        @if(session('error'))
                            <small style="color:#ef4444; display:block; margin-top:5px;">{{ session('error') }}</small>
                        @endif
                    </div>
                    @endif

                    <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">
                    
                    <div style="display:flex; justify-content:space-between; font-weight:700; font-size:20px; color:var(--text-dark);">
                        <span>Total Estimasi</span>
                        <span style="color:var(--primary-color);">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                    
                    @if(!$cart->start_date || !$cart->end_date)
                    <div style="margin-top: 25px; padding: 15px; background: #fff8e1; color: #f57f17; border-radius: 8px; border:1px solid #ffecb3; font-size: 13px; line-height:1.5;">
                        <i class="fas fa-info-circle" style="font-size:16px; margin-bottom:8px; display:block;"></i> 
                        Silakan tentukan <strong>Periode Sewa</strong> (Tanggal Ambil & Kembali) pada form di sebelah kiri untuk melanjutkan proses penyewaan.
                    </div>
                    @endif
                    
                    <!-- Form Checkout Lanjutan -->
                    <form action="#" method="GET">
                        <button type="submit" class="btn-primary" style="display:block; text-align:center; width:100%; padding:15px; border:none; border-radius:8px; margin-top:25px; font-size:15px; font-weight:600; cursor:pointer; background:var(--primary-color); color:#fff; transition:0.3s; box-shadow:0 4px 10px rgba(211,47,47,0.2);" {{ (!$cart->start_date || !$cart->end_date) ? 'disabled' : '' }} onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                            <i class="fas fa-lock" style="margin-right:8px; font-size:13px;"></i> Ke Pembayaran Aman (Checkout)
                        </button>
                    </form>
                    <div style="text-align:center; margin-top:15px; font-size:12px; color:#888;">
                        <i class="fas fa-shield-alt"></i> Transaksi Anda terenkripsi dan aman.
                    </div>
                </div>
                @endif
            </div>
        </section>
    </div>
</main>
@endsection
