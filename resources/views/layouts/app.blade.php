<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sewa Peralatan Event & Multimedia | Renta Enterprise')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    @stack('styles')
</head>
<body>
    
    @include('layouts.header')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    @include('layouts.footer')

    <!-- WhatsApp Floating Button -->
    <a href="#" class="float-wa">
        <div class="wa-tooltip">Butuh bantuan? Hubungi kami sekarang</div>
        <div class="wa-icon-wrapper">
            <i class="fab fa-whatsapp"></i>
        </div>
    </a>

    <a href="#" class="scroll-top">
        <i class="fas fa-chevron-up"></i>
    </a>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ url('/products') }}" class="{{ request()->is('products') ? 'active' : '' }}">
            <i class="fas fa-box"></i>
            <span>Produk</span>
        </a>
        <a href="{{ url('/orders') }}" class="pesanan-btn" style="position: relative;">
            <div class="icon-circle">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <span>Pesanan Saya</span>
        </a>
        <a href="{{ url('/categories') }}" class="{{ request()->is('categories') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>Kategori</span>
        </a>
        @auth
        <a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Akun</span>
        </a>
        @else
        <a href="{{ url('/login') }}" class="{{ request()->is('login') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Login</span>
        </a>
        @endauth
    </nav>

    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
