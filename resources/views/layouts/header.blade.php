<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-left">
            <span><i class="fas fa-map-marker-alt"></i> Jl. Saman H No.33/35, Purwantoro, Kec. Blimbing, Kota Malang, Jawa Timur 65122</span>
        </div>
        <div class="top-bar-right">
            <!-- Social links if any, left blank for now as in screenshot -->
        </div>
    </div>
</div>

<!-- Main Header Area -->
<header class="main-header">
    <div class="container header-container">
        <div class="logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/images/logo-renta.png') }}" alt="Renta Enterprise">
            </a>
        </div>
        <div class="search-box">
            <form action="{{ url('/products') }}" method="GET">
                <input type="text" name="search" placeholder="Search for products" value="{{ request('search') }}">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="header-actions">
            @auth
                <a href="{{ url('/profile') }}" class="auth-link">AKUN SAYA</a>
                <form method="POST" action="{{ url('/logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="auth-link" style="background:none; border:none; cursor:pointer; color:var(--text-color);">LOGOUT</button>
                </form>
            @else
                <a href="{{ url('/login') }}" class="auth-link">LOGIN / REGISTER</a>
            @endauth
            
            <a href="{{ url('/wishlist') }}" class="action-icon wishlist-icon">
                <i class="far fa-heart"></i>
                <span class="badge" id="header-wishlist-count">{{ $wishlistCount ?? 0 }}</span>
            </a>
            <a href="{{ url('/cart') }}" class="action-icon cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="badge" id="header-cart-count">{{ $cartCount ?? 0 }}</span>
                <span class="cart-total" id="header-cart-total">Rp{{ number_format($cartTotal ?? 0, 0, ',', '.') }}</span>
            </a>
        </div>
    </div>
</header>

<!-- Navigation Area -->
<nav class="main-nav">
    <div class="container nav-container">
        <div class="categories-btn">
            <i class="fas fa-bars"></i>
            <span>BROWSE CATEGORIES</span>
            <i class="fas fa-chevron-down"></i>
            
            <!-- Dropdown menu -->
            <ul class="categories-dropdown {{ request()->is('/') ? 'active' : '' }}">
                <li class="has-submenu">
                    <a href="#"><i class="fas fa-volume-up"></i> Sound System <i class="fas fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="#"><i class="fas fa-cubes"></i> Ala Carte</a></li>
                        <li><a href="#"><i class="fas fa-box-open"></i> Package</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#"><i class="fas fa-lightbulb"></i> Lighting / Effect System <i class="fas fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="#"><i class="fas fa-cubes"></i> Ala Carte</a></li>
                        <li><a href="#"><i class="fas fa-box-open"></i> Package</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#"><i class="fas fa-layer-group"></i> Stage+Rigging <i class="fas fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="#"><i class="fas fa-cubes"></i> Ala Carte</a></li>
                        <li><a href="#"><i class="fas fa-box-open"></i> Package</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#"><i class="fas fa-desktop"></i> Multimedia <i class="fas fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="#"><i class="fas fa-cubes"></i> Ala Carte</a></li>
                        <li><a href="#"><i class="fas fa-box-open"></i> Package</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fas fa-video"></i> Live Cam + Photo/Video Package</a></li>
                <li class="has-submenu">
                    <a href="#"><i class="fas fa-wifi"></i> Live Streaming <i class="fas fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="#"><i class="fas fa-cubes"></i> Ala Carte</a></li>
                        <li><a href="#"><i class="fas fa-box-open"></i> Package</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fas fa-camera"></i> Photobooth</a></li>
                <li><a href="#"><i class="fas fa-campground"></i> Tenda & Dekorasi Kain</a></li>
                <li><a href="#"><i class="fas fa-chair"></i> Kursi</a></li>
                <li><a href="#"><i class="fas fa-table"></i> Meja</a></li>
                <li><a href="#"><i class="fas fa-couch"></i> Beanbag</a></li>
                <li><a href="#"><i class="fas fa-store"></i> Booth Partai</a></li>
                <li><a href="#"><i class="fas fa-charging-station"></i> Generator</a></li>
                <li><a href="#"><i class="fas fa-snowflake"></i> Cooling System</a></li>
                <li><a href="#"><i class="fas fa-cloud"></i> Balon</a></li>
                <li><a href="#"><i class="fas fa-sign"></i> Papan Bunga</a></li>
                <li><a href="#"><i class="fas fa-ellipsis-h"></i> Others</a></li>
            </ul>
        </div>
        
        <ul class="nav-links">
            <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">HOME</a></li>
            <li><a href="{{ url('/products') }}" class="{{ request()->is('products') ? 'active' : '' }}">PRODUK</a></li>
            <li><a href="{{ url('/about') }}" class="{{ request()->is('about') ? 'active' : '' }}">TENTANG KAMI</a></li>
            <li><a href="{{ url('/contact') }}" class="{{ request()->is('contact') ? 'active' : '' }}">KONTAK</a></li>
            <li><a href="{{ url('/cara-sewa') }}" class="{{ request()->is('cara-sewa') ? 'active' : '' }}">CARA SEWA</a></li>
            <li><a href="{{ url('/payment/verify') }}" class="highlight">KONFIRMASI PEMBAYARAN</a></li>
        </ul>
    </div>
</nav>
