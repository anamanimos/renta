@extends('layouts.app')

@section('title', 'Wishlist Saya | Renta Enterprise')

@section('content')
<main>
    <div class="container" style="padding: 40px 15px; max-width: 1200px; margin: 0 auto;">
        <!-- Breadcrumb -->
        <div class="shop-breadcrumb" style="margin-bottom: 25px; font-size: 14px;">
            <a href="{{ url('/') }}" style="color:var(--primary-color); text-decoration:none;">Beranda</a> <span class="separator" style="margin:0 10px; color:#ccc;"><i class="fas fa-chevron-right"></i></span> 
            <span style="font-weight:600; color:var(--text-dark);">Wishlist Saya</span>
        </div>

        <section class="shop-page-section">
            <h1 class="shop-page-title" style="margin-bottom: 30px; font-size: 28px; color: var(--text-dark);">Wishlist Saya</h1>
            
            <div class="wishlist-container">
                @if(session('success'))
                    <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
                    </div>
                @endif

                @if($wishlists->count() > 0)
                    <!-- Daftar Item Wishlist -->
                    <div style="background: #fff; border-radius: 10px; border: 1px solid #eaeaea; overflow:hidden;">
                        <table class="shop-table" style="width: 100%; border-collapse: collapse;">
                            <thead style="background: #fdfdfd; border-bottom: 2px solid #eee;">
                                <tr style="text-align:left; color:#555; font-size:14px;">
                                    <th style="padding: 20px;">Peralatan</th>
                                    <th style="padding: 20px;">Harga Mulai</th>
                                    <th style="padding: 20px; text-align:center;">Status</th>
                                    <th style="padding: 20px; text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($wishlists as $wishlist)
                                <tr style="border-bottom: 1px solid #f5f5f5; transition:0.2s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='white'">
                                    <td style="padding: 20px; display:flex; align-items:center; gap:15px;">
                                        <div style="width:70px; height:70px; background:#f9f9f9; border-radius:8px; overflow:hidden; border:1px solid #eee;">
                                            <a href="{{ route('product.show', $wishlist->product->slug) }}">
                                                <img src="{{ Str::startsWith($wishlist->product->image, 'http') ? $wishlist->product->image : asset($wishlist->product->image ?? 'assets/images/product-1.png') }}" style="width:100%; height:100%; object-fit:contain; padding:5px;">
                                            </a>
                                        </div>
                                        <div>
                                            <a href="{{ route('product.show', $wishlist->product->slug) }}" style="color:var(--text-dark); text-decoration:none; font-weight:600; font-size:15px;">
                                                {{ $wishlist->product->name }}
                                            </a>
                                            <div style="font-size:12px; color:#888; margin-top:5px;"><i class="fas fa-tag"></i> {{ $wishlist->product->category->name ?? 'Peralatan' }} <span style="font-weight:600; color:var(--primary-color);">({!! ($wishlist->product->price_type === 'sell_once' || $wishlist->product->price_type === 'beli_putus') ? 'Jual Putus' : 'Sewa' !!})</span></div>
                                        </div>
                                    </td>
                                    <td style="padding: 20px; color:#555; font-weight:500;">
                                        @php
                                            $priceBaseUI = $wishlist->product->promo_price ?? $wishlist->product->price_per_day;
                                        @endphp
                                        Rp{{ number_format($priceBaseUI, 0, ',', '.') }}
                                        @if($wishlist->product->has_variants)
                                            <div style="font-size: 12px; color: #888; font-weight: normal;">(Berdasarkan Varian)</div>
                                        @endif
                                    </td>
                                    <td style="padding: 20px; text-align:center;">
                                        @if($wishlist->product->is_active)
                                            <span style="display:inline-block; padding:5px 10px; background:#e8f5e9; color:#2e7d32; border-radius:20px; font-size:12px; font-weight:600;">Tersedia</span>
                                        @else
                                            <span style="display:inline-block; padding:5px 10px; background:#ffebee; color:#c62828; border-radius:20px; font-size:12px; font-weight:600;">Kosong</span>
                                        @endif
                                    </td>
                                    <td style="padding: 20px; text-align:center;">
                                        <div style="display:flex; gap:10px; justify-content:center;">
                                            <a href="{{ route('product.show', $wishlist->product->slug) }}" class="btn-primary" style="padding:8px 15px; border-radius:6px; background:var(--primary-color); color:white; text-decoration:none; font-size:13px; font-weight:600;"><i class="fas fa-eye"></i> Lihat</a>
                                            <form action="{{ route('wishlist.toggle', $wishlist->product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" title="Hapus dari Wishlist" style="background:rgba(211,47,47,0.1); border:none; width:34px; height:34px; border-radius:6px; color:#d32f2f; cursor:pointer; font-size:14px; transition:0.3s;" onmouseover="this.style.background='#d32f2f'; this.style.color='#fff';" onmouseout="this.style.background='rgba(211,47,47,0.1)'; this.style.color='#d32f2f';"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </div>
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
                            <i class="far fa-heart" style="font-size: 35px; color: #aaa;"></i>
                        </div>
                        <h3 style="color: #444; margin-bottom:10px;">Wishlist Anda Masih Kosong</h3>
                        <p style="color: #777; margin-bottom:25px;">Simpan peralatan favorit Anda di sini untuk disewa atau dibeli nanti.</p>
                        <a href="{{ route('products.index') }}" class="btn-primary" style="display:inline-block; padding:12px 30px; border-radius:30px; text-decoration:none; font-weight:600; background:var(--primary-color); color:#fff;"><i class="fas fa-search" style="margin-right:8px;"></i> Jelajahi Katalog</a>
                    </div>
                @endif
            </div>
        </section>
    </div>
</main>
@endsection
