@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-container">
        <div class="hero-spacer"></div> <!-- Spacer for absolute categories -->
        <div class="hero-content">
            <div class="hero-banner slider-container">
            <div class="slider-wrapper">
                <!-- Slide 1 -->
                <div class="slide active">
                    <a href="#">
                        <img src="{{ asset('assets/images/MainSlider1.jpeg') }}" alt="Live Streaming Promo">
                    </a>
                </div>
                <!-- Slide 2 -->
                <div class="slide">
                    <a href="#">
                        <img src="{{ asset('assets/images/MainSlider2.jpeg') }}" alt="Live Streaming Promo">
                    </a>
                </div>
            </div>
            
            <!-- Slider Controls -->
            <button class="slider-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
            
            <!-- Slider Dots -->
            <div class="slider-dots">
                <span class="dot active" data-slide="0"></span>
            </div>
            </div>
            
            <!-- Why Choose Us -->
            <div class="why-us">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h3>Peralatan Berkualitas</h3>
                <p>Kami menyediakan berbagai peralatan event terkini dan terawat dengan baik dari brand terpercaya.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Layanan Profesional</h3>
                <p>Didukung oleh tim teknisi yang handal dan berpengalaman menangani berbagai event skala besar.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <h3>Pengiriman Cepat</h3>
                <p>Armada logistik kami siap mengantarkan kebutuhan event Anda tepat waktu dan aman.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h3>Harga Kompetitif</h3>
                <p>Solusi penyewaan terbaik dengan paket harga yang transparan dan dapat disesuaikan budget.</p>
            </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Today Hot Deals (Statis Sementara, Nanti Dinamis dari DB) -->
<section class="hot-deals section-padding bg-gray">
    <div class="container">
        <h2 class="section-title">TODAY HOT DEALS</h2>
        
        <div class="products-grid">
            @if(isset($hotProducts) && $hotProducts->count() > 0)
                @foreach($hotProducts as $k => $product)
                <div class="product-card">
                    <div class="product-image">
                        <img src="{{ asset('assets/images/product-'.($k%4+1).'.png') }}" alt="{{ $product->name }}">
                        <div class="brand-logo">
                            <img src="{{ asset('assets/images/logo-small.png') }}" alt="Renta">
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>{{ $product->name }}</h3>
                        <div class="price">
                            <span class="current-price">Rp{{ number_format($product->price_per_day, 0, ',', '.') }} Unit Price</span>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <!-- Product 1 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="{{ asset('assets/images/product-1.png') }}" alt="Stage">
                        <div class="brand-logo">
                            <img src="{{ asset('assets/images/logo-small.png') }}" alt="Renta">
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Stage (t: 40-60cm) + Karpet</h3>
                        <div class="price">
                            <span class="current-price">Rp45.000 / Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Product 2 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="{{ asset('assets/images/product-2.png') }}" alt="AC 3 PK">
                        <div class="brand-logo">
                            <img src="{{ asset('assets/images/logo-small.png') }}" alt="Renta">
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>AC 3 PK</h3>
                        <div class="price">
                            <span class="current-price">Rp450.000 / Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Product 3 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="{{ asset('assets/images/product-3.png') }}" alt="AC 5 PK">
                        <div class="brand-logo">
                            <img src="{{ asset('assets/images/logo-small.png') }}" alt="Renta">
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>AC 5 PK</h3>
                        <div class="price">
                            <span class="current-price">Rp750.000 / Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Product 4 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="{{ asset('assets/images/product-4.png') }}" alt="Camera">
                        <div class="brand-logo">
                            <img src="{{ asset('assets/images/logo-small.png') }}" alt="Renta">
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Additional Camera - HD - Sony 755 + Operator</h3>
                        <div class="price">
                            <span class="current-price">Rp2.500.000 / Hari</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Langkah Sewa or Promo -->
<section class="langkah-sewa section-padding">
    <div class="container">
        <div class="langkah-banner slider-container" style="border-radius: 8px; overflow: hidden; position: relative;">
            <div class="slider-wrapper">
                <!-- Slide 1 -->
                <div class="slide active">
                    <img src="{{ asset('assets/images/Langkah-Order-1.jpg') }}" alt="Langkah Order 1" style="width: 100%; display: block;">
                </div>
                <!-- Slide 2 -->
                <div class="slide">
                    <img src="{{ asset('assets/images/Langkah-Order-2.jpg') }}" alt="Langkah Order 2" style="width: 100%; display: block;">
                </div>
                <!-- Slide 3 -->
                <div class="slide">
                    <img src="{{ asset('assets/images/Langkah-Order-3.jpg') }}" alt="Langkah Order 3" style="width: 100%; display: block;">
                </div>
                <!-- Slide 4 -->
                <div class="slide">
                    <img src="{{ asset('assets/images/Langkah-Order-4.jpg') }}" alt="Langkah Order 4" style="width: 100%; display: block;">
                </div>
            </div>
            
            <!-- Slider Controls -->
            <button class="slider-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
            
            <!-- Slider Dots -->
            <div class="slider-dots">
                <span class="dot active" data-slide="0"></span>
            </div>
        </div>
    </div>
</section>

<!-- Latest Project -->
<section class="latest-project section-padding">
    <div class="container">
        <h2 class="section-title left-align border-bottom">LATEST PROJECT</h2>
        <div class="project-grid">
            <div class="project-item"><img src="{{ asset('assets/images/project-1.png') }}" alt="Project 1"></div>
            <div class="project-item"><img src="{{ asset('assets/images/project-2.png') }}" alt="Project 2"></div>
            <div class="project-item"><img src="{{ asset('assets/images/project-3.png') }}" alt="Project 3"></div>
        </div>
    </div>
</section>

<!-- Our Clients -->
<section class="our-clients section-padding">
    <div class="container">
        <h2 class="section-title border-bottom">OUR CLIENTS</h2>
        <div class="clients-grid">
            @for($i=1; $i<=32; $i++)
                <div class="client-item"><img src="{{ asset('assets/images/logo-small.png') }}" alt="Client {{ $i }}"></div>
            @endfor
        </div>
    </div>
</section>
@endsection
