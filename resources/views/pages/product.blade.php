@extends('layouts.app')

@section('title', 'Katalog Peralatan Sewa | Renta Enterprise')

@section('content')
<main class="product-page">
    <!-- Breadcrumb & Toolbar -->
    <div class="shop-toolbar bg-gray section-padding" style="padding: 30px 0; background: #fdfdfd; border-bottom: 1px solid #eaeaea; margin-bottom: 30px;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="breadcrumb" style="color: var(--text-light); font-size: 13px;">
                <a href="{{ url('/') }}" style="color: var(--primary-color); text-decoration: none;">Beranda</a> / 
                <span style="font-weight: 600; color: var(--text-dark);">
                    {{ request('category') ? 'Kategori: ' . ucfirst(str_replace('-', ' ', request('category'))) : 'Katalog Produk' }}
                </span>
            </div>
            <div class="shop-controls" style="display: flex; align-items: center; gap: 20px; font-size: 13px; color: var(--text-light);">
                <div class="filter-toggle">
                    <strong>{{ $products->total() }}</strong> Peralatan Ditemukan
                </div>
            </div>
        </div>
    </div>

    <!-- Product Grid Section -->
    <section class="shop-products section-padding" style="padding-bottom: 80px;">
        <div class="container">
            <div class="products-grid">
                @forelse($products as $product)
                <div class="product-card" style="display: flex; flex-direction: column;">
                    <div class="product-image" style="position: relative; padding-top: 100%; overflow: hidden; background:#f5f5f5;">
                        <a href="{{ route('product.show', $product->slug) }}">
                            <img src="{{ !empty($product->image) ? (Str::startsWith($product->image, ['http://', 'https://']) ? $product->image : asset($product->image)) : asset('assets/images/product-1.png') }}" alt="{{ $product->name }}" style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit: contain; padding: 15px;">
                        </a>
                        <div class="brand-logo">
                            <img src="{{ asset('assets/images/logo-small.png') }}" alt="Renta">
                        </div>
                    </div>
                    <div class="product-info" style="padding: 20px; text-align: left; flex: 1; display: flex; flex-direction: column;">
                        <span style="font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ $product->category->name ?? 'Uncategorized' }}</span>
                        <h3 style="font-size: 15px; margin: 8px 0; line-height: 1.4; flex: 1;">
                            <a href="{{ route('product.show', $product->slug) }}" style="color: var(--text-dark); text-decoration: none;">{{ $product->name }}</a>
                        </h3>
                        <div class="price" style="margin-top: 5px; margin-bottom: 15px;">
                            <span class="current-price" style="color: var(--primary-color); font-weight: 700; font-size: 16px;">Rp{{ number_format($product->price_per_day, 0, ',', '.') }} <small style="color: #999; font-weight:400; font-size:12px;">/ Hari</small></span>
                        </div>
                        
                        <div style="margin-top: auto;">
                            <!-- Form Add to Cart Placeholder -->
                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.3s; background: var(--primary-color); color: #fff;" onmouseover="this.style.background='#b02323'" onmouseout="this.style.background='var(--primary-color)'">
                                    <i class="fas fa-shopping-cart"></i> Tambah Sewa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px 20px;">
                    <i class="fas fa-box-open" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
                    <h3 style="color: #666;">Peralatan Tidak Ditemukan</h3>
                    <p style="color: #999;">Belum ada peralatan sewa untuk kategori ini.</p>
                </div>
                @endforelse
            </div>
            
            <!-- Pagination Controls -->
            <div style="margin-top: 40px; display: flex; justify-content: center;">
                {{ $products->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </section>
</main>
@endsection
