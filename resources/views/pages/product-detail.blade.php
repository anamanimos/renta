@extends('layouts.app')

@section('title', $product->name . ' | Renta Enterprise')

@section('content')
<main class="product-detail-page bg-gray section-padding" style="padding: 40px 0; background: #fdfdfd; min-height: 80vh;">
    <!-- Breadcrumb -->
    <div class="container" style="margin-bottom: 20px;">
        <div class="breadcrumb" style="color: var(--text-light); font-size: 13px;">
            <a href="{{ url('/') }}" style="color: var(--primary-color); text-decoration: none;">Beranda</a> / 
            <a href="{{ route('products.index', ['category' => $product->category->slug ?? '']) }}" style="color: var(--primary-color); text-decoration: none;">{{ $product->category->name ?? 'Katalog' }}</a> / 
            <span style="font-weight: 600; color: var(--text-dark);">{{ $product->name }}</span>
        </div>
    </div>

    <div class="container">
        <div class="product-detail-card" style="background: #fff; border-radius: 12px; border: 1px solid #eaeaea; overflow: hidden; display: flex; flex-wrap: wrap; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <!-- Gambar Produk -->
            <div class="product-gallery" style="flex: 1; min-width: 350px; background: #f9f9f9; padding: 0; position: relative; border-right: 1px solid #eaeaea;">
                <img src="{{ !empty($product->image) ? (Str::startsWith($product->image, ['http://', 'https://']) ? $product->image : asset($product->image)) : asset('assets/images/product-1.png') }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                
            </div>

            <!-- Detail & Pemesanan -->
            <div class="product-info-panel" style="flex: 1; min-width: 350px; padding: 40px;">
                <span class="category-label" style="display: inline-block; padding: 4px 10px; background: #fee2e2; color: var(--primary-color); border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 15px;">
                    {{ $product->category->name ?? 'Tanpa Kategori' }}
                </span>
                
                <h1 style="font-size: 26px; line-height: 1.3; color: var(--text-dark); margin-bottom: 10px;">{{ $product->name }}</h1>
                
                <div class="price-section" style="margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #eaeaea;">
                    <div id="dynamicPriceDisplay" style="display: flex; align-items: baseline; gap: 10px;">
                        <span class="currency" style="font-size: 18px; font-weight: 600; color: var(--primary-color);">Rp</span>
                        <span id="priceValue" style="font-size: 32px; font-weight: 800; color: var(--primary-color);">
                            {{ number_format($product->price_per_day, 0, ',', '.') }}
                        </span>
                        <span id="priceUnit" style="font-size: 14px; color: #888; font-weight: 500;">
                            {{ $product->price_type == 'sell_once' ? '/ Unit' : '/ Hari' }}
                        </span>
                    </div>

                    @if($product->price_type == 'rental_tiered')
                    <div id="tierPriceContainer" style="margin-top: 8px; font-size: 13px; color: #059669; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                        <i class="ti ti-discount-check" style="font-size: 16px;"></i> 
                        Hari selanjutnya: Rp <span id="tierPriceValue">{{ number_format($product->tier_price, 0, ',', '.') }}</span> / Hari
                    </div>
                    @endif
                </div>

                <div class="description-section" style="margin-bottom: 30px; font-size: 14px; line-height: 1.6; color: #555;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; text-transform: uppercase;">Deskripsi Produk</h4>
                    <div>
                        {!! $product->description ?: '<p style="font-style:italic; color:#999;">Tidak ada gambaran spesifik mengenai produk ini.</p>' !!}
                    </div>
                </div>

                <form action="{{ route('cart.add', $product->id) }}" method="POST" id="addToCartForm">
                    @csrf
                    
                    @if($product->variants && $product->variants->count() > 1)
                    <div class="variant-selection" style="margin-bottom: 25px;">
                        <label for="variantSelect" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-dark); margin-bottom: 8px;">Pilih Ukuran / Varian</label>
                        <select id="variantSelect" name="variant_id" style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; color: var(--text-dark); background: #fcfcfc; appearance: none; cursor: pointer; transition: all 0.3s;" onchange="updateVariantDetails(this)">
                            @foreach($product->variants as $index => $variant)
                                <option value="{{ $variant->id }}" 
                                    data-price="{{ $variant->price_per_day }}" 
                                    data-tier="{{ $variant->tier_price }}" 
                                    data-stock="{{ $variant->stock_quantity }}"
                                    {{ $index == 0 ? 'selected' : '' }}>
                                    {{ $variant->name }}
                                </option>
                            @endforeach
                        </select>
                        <div style="position: relative; top: -33px; right: 15px; text-align: right; pointer-events: none; color: #999;"><i class="ti ti-chevron-down"></i></div>
                    </div>
                    @elseif($product->variants && $product->variants->count() == 1)
                    @php $singleVariant = $product->variants->first(); @endphp
                    <input type="hidden" name="variant_id" value="{{ $singleVariant->id }}">
                    <div id="singleVariantData" 
                         data-price="{{ $singleVariant->price_per_day }}" 
                         data-tier="{{ $singleVariant->tier_price }}" 
                         data-stock="{{ $singleVariant->stock_quantity }}"
                         style="display: none;"></div>
                    @endif

                    <div class="stock-info" style="margin-bottom: 25px; font-size: 13px; color: #666;">
                        <i class="ti ti-box" style="margin-right: 5px;"></i> Ketersediaan: <strong id="stockDisplay" style="color: var(--text-dark);">Tersedia</strong>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <button type="submit" id="btnSubmitCart" class="btn-primary" style="flex: 1; padding: 14px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; letter-spacing: 0.5px; cursor: pointer; transition: 0.3s; background: var(--primary-color); color: #fff; display: flex; justify-content: center; align-items: center; gap: 8px;">
                            <i class="ti ti-shopping-cart-plus" style="font-size: 20px;"></i> Tambah Sewa Ke Keranjang
                        </button>
                        
                        @php
                            $inWishlist = false;
                            if(auth()->check()) {
                                $inWishlist = \App\Models\Wishlist::where('product_id', $product->id)->where('user_id', auth()->id())->exists();
                            } else {
                                $inWishlist = \App\Models\Wishlist::where('product_id', $product->id)->where('session_id', \Illuminate\Support\Facades\Session::getId())->whereNull('user_id')->exists();
                            }
                        @endphp
                        
                        <button type="button" id="btnWishlistToggle" class="btn-wishlist {{ $inWishlist ? 'active' : '' }}" onclick="toggleWishlist({{ $product->id }})" style="width: 50px; padding: 0; border: 1px solid {{ $inWishlist ? 'var(--primary-color)' : '#ddd' }}; border-radius: 8px; background: {{ $inWishlist ? 'rgba(211,47,47,0.05)' : '#fff' }}; color: {{ $inWishlist ? 'var(--primary-color)' : '#888' }}; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; font-size: 20px;">
                            <i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        @if($relatedProducts && $relatedProducts->count() > 0)
        <!-- Related Products -->
        <div style="margin-top: 50px;">
            <h3 style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 20px;">Mungin Anda Juga Butuh</h3>
            <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
                @foreach($relatedProducts as $related)
                <div class="product-card" style="display: flex; flex-direction: column; background: #fff; border: 1px solid #eaeaea; border-radius: 8px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;">
                    <div class="product-image" style="position: relative; padding-top: 100%; overflow: hidden; background:#f9f9f9;">
                        <img src="{{ $related->image && Str::startsWith($related->image, 'http') ? $related->image : asset($related->image ?? 'assets/images/product-1.png') }}" alt="{{ $related->name }}" style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit: contain; padding: 15px; transition: transform 0.3s;">
                    </div>
                    <div class="product-info" style="padding: 15px; text-align: left; flex: 1; display: flex; flex-direction: column;">
                        <h3 style="font-size: 14px; margin: 0 0 10px 0; line-height: 1.4; flex: 1;">
                            <a href="{{ route('product.show', $related->slug) }}" style="color: var(--text-dark); text-decoration: none;">{{ $related->name }}</a>
                        </h3>
                        <div class="price" style="margin-top: auto;">
                            <span style="color: var(--primary-color); font-weight: 700; font-size: 15px;">Rp{{ number_format($related->price_per_day, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .product-card:hover .product-image img { transform: scale(1.05); }
    select:focus { outline: none; border-color: var(--primary-color) !important; box-shadow: 0 0 0 3px rgba(211,47,47,0.1); }
    #btnSubmitCart:hover { background: #b02323 !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(211,47,47,0.3); }
    #btnSubmitCart:disabled { background: #ccc !important; cursor: not-allowed; transform: none; box-shadow: none; }
    .btn-wishlist:hover { border-color: var(--primary-color) !important; color: var(--primary-color) !important; background: rgba(211,47,47,0.05) !important; transform: translateY(-2px); }
</style>
<script>
    // Inisialisasi format mata uang
    const formatRp = (angka) => {
        return new Intl.NumberFormat('id-ID').format(angka);
    };

    function updateVariantDetails(selectElement) {
        const option = selectElement.options[selectElement.selectedIndex];
        
        // Harga Base
        const newPrice = option.dataset.price;
        const priceValueEl = document.getElementById('priceValue');
        if (priceValueEl && newPrice) {
            priceValueEl.style.opacity = '0.5';
            setTimeout(() => {
                priceValueEl.innerText = formatRp(newPrice);
                priceValueEl.style.opacity = '1';
            }, 150);
        }

        // Harga Tier (ika ada)
        const newTier = option.dataset.tier;
        const tierPriceValueEl = document.getElementById('tierPriceValue');
        const tierContainerEl = document.getElementById('tierPriceContainer');
        
        if (tierContainerEl) {
            if (newTier && newTier > 0) {
                tierPriceValueEl.innerText = formatRp(newTier);
                tierContainerEl.style.display = 'flex';
            } else {
                tierContainerEl.style.display = 'none';
            }
        }

        // Ketersediaan Stok
        const newStock = parseInt(option.dataset.stock);
        const stockDisplayEl = document.getElementById('stockDisplay');
        const btnSubmit = document.getElementById('btnSubmitCart');
        
        if (newStock > 0) {
            stockDisplayEl.innerText = `Tersedia (${newStock} Unit)`;
            stockDisplayEl.style.color = '#059669';
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="ti ti-shopping-cart-plus" style="font-size: 20px;"></i> Tambah Sewa Ke Keranjang';
        } else {
            stockDisplayEl.innerText = 'Habis / Kosong';
            stockDisplayEl.style.color = 'var(--danger)';
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="ti ti-alert-triangle" style="font-size: 20px;"></i> Stok Varian Ini Kosong';
        }
    }

    // Picu pembaruan JS pertama kali jika ada varian
    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.getElementById('variantSelect');
        if (sel) {
            updateVariantDetails(sel);
            // Tambahkan CSS smooth transition ke teks harga
            document.getElementById('priceValue').style.transition = 'opacity 0.2s ease-in-out';
        } else {
            const singleData = document.getElementById('singleVariantData');
            if (singleData) {
                updateVariantDetails({ options: [singleData], selectedIndex: 0 });
                document.getElementById('priceValue').style.transition = 'opacity 0.2s ease-in-out';
            }
        }
    });

    // AJAX Form Submission untuk Keranjang
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnSubmitCart');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner" style="font-size: 20px; animation: spin 1s linear infinite;"></i> Menambahkan...';
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: 'Lihat Keranjang',
                        cancelButtonText: 'Lanjut Belanja',
                        confirmButtonColor: 'var(--primary-color)',
                        cancelButtonColor: '#6c757d',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/cart';
                        }
                    });

                    // Update badge keranjang di header jika ada
                    const cartBadges = document.querySelectorAll('.cart-icon .badge');
                    cartBadges.forEach(badge => {
                        badge.innerText = data.cart_count;
                        // Tambahkan efek denyut/pop up singkat pada badge
                        badge.style.transform = 'scale(1.3)';
                        setTimeout(() => badge.style.transform = 'scale(1)', 300);
                        badge.style.transition = 'transform 0.3s ease-in-out';
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menambahkan ke keranjang. Silakan coba lagi.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }

    // Fungsi Toggle Wishlist via AJAX
    window.toggleWishlist = function(productId) {
        const btn = document.getElementById('btnWishlistToggle');
        const icon = btn.querySelector('i');
        
        // Animasi loading sederhana
        icon.className = 'fas fa-spinner fa-spin';
        
        fetch(`/wishlist/toggle/${productId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update Badge di Header
                const wishlistBadges = document.querySelectorAll('.wishlist-icon .badge');
                wishlistBadges.forEach(badge => {
                    badge.innerText = data.wishlist_count;
                    badge.style.transform = 'scale(1.3)';
                    setTimeout(() => badge.style.transform = 'scale(1)', 300);
                    badge.style.transition = 'transform 0.3s ease-in-out';
                });

                // Update styling tombol
                if(data.status === 'added') {
                    btn.classList.add('active');
                    btn.style.borderColor = 'var(--primary-color)';
                    btn.style.background = 'rgba(211,47,47,0.05)';
                    btn.style.color = 'var(--primary-color)';
                    icon.className = 'fas fa-heart';
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Disimpan ke Wishlist',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    btn.classList.remove('active');
                    btn.style.borderColor = '#ddd';
                    btn.style.background = '#fff';
                    btn.style.color = '#888';
                    icon.className = 'far fa-heart';
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Dihapus dari Wishlist',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error toggling wishlist:', error);
            icon.className = btn.classList.contains('active') ? 'fas fa-heart' : 'far fa-heart'; // Reset icon
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Gagal memperbarui wishlist',
                showConfirmButton: false,
                timer: 2000
            });
        });
    };
</script>
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
