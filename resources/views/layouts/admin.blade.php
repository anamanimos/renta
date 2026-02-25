<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | Renta Enterprise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"
    />
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
  </head>
  <body>
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
      <div class="sidebar-header">
        <img
          src="{{ asset('assets/images/logo-renta.png') }}"
          alt="Renta Logo"
          class="sidebar-logo"
        />
        <button class="close-sidebar" id="closeSidebar">
          <i class="ti ti-x"></i>
        </button>
      </div>
      <ul class="sidebar-menu">
        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <a href="{{ route('admin.dashboard') }}"
            ><i class="ti ti-home"></i> <span>Dashboard</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
          <a href="{{ route('admin.orders.index') }}"
            ><i class="ti ti-shopping-cart"></i> <span>Pesanan</span>
            @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
            <span class="badge">{{ $pendingOrdersCount }}</span>
            @endif
          </a>
        </li>
        <li class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
          <a href="{{ route('admin.products.index') }}"
            ><i class="ti ti-box"></i> <span>Produk</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <a href="{{ route('admin.users.index') }}"
            ><i class="ti ti-users"></i> <span>Pengguna</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
          <a href="{{ route('admin.reports.index') }}"
            ><i class="ti ti-chart-bar"></i> <span>Laporan</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
          <a href="{{ route('admin.coupons.index') }}"
            ><i class="ti ti-ticket"></i> <span>Kupon</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
          <a href="{{ route('admin.pages.index') }}"
            ><i class="ti ti-notebook"></i> <span>Halaman</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
          <a href="{{ route('admin.articles.index') }}"
            ><i class="ti ti-article"></i> <span>Artikel</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.migration.*') ? 'active' : '' }}">
          <a href="{{ route('admin.migration.index') }}"
            ><i class="ti ti-database-import"></i> <span>Impor WP</span></a
          >
        </li>
        <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
          <a href="{{ route('admin.settings.general') }}"
            ><i class="ti ti-settings"></i> <span>Pengaturan</span></a
          >
        </li>
      </ul>
      <div class="sidebar-footer">
        <a href="#" class="logout-btn" id="logoutBtn" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
          <i class="ti ti-logout"></i> <span>Logout</span>
        </a>
        <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none;">@csrf</form>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
      <!-- Top Navbar -->
      <header class="admin-header">
        <div class="header-left">
          <button class="menu-toggle" id="menuToggle">
            <i class="ti ti-menu-2"></i>
          </button>
          @hasSection('header-left-extra')
            @yield('header-left-extra')
          @else
          <div class="search-bar">
            <i class="ti ti-search"></i>
            <input type="text" placeholder="Cari pesanan, produk..." />
          </div>
          @endif
        </div>
        <div class="header-right">
          <!-- Store Mode Switcher -->
          <div class="mode-switcher-container">
            <span class="mode-label mode-rental active">Rental</span>
            <div class="switch mode-toggle-btn">
              <div class="slider-knob"></div>
            </div>
            <span class="mode-label mode-katalog">Katalog</span>
          </div>

          <button class="icon-btn" id="notificationBtn">
            <i class="ti ti-bell"></i>
            <span class="notification-badge">3</span>
          </button>
          <div class="user-profile">
            <img
              src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=D32F2F&color=fff"
              alt="Admin"
            />
            <div class="user-info">
              <span class="user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
              <span class="user-role">Super Admin</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <div class="dashboard-content">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:10px; margin-bottom:20px; border-left:4px solid #4caf50; display:flex; align-items:center; gap:10px;">
            <i class="ti ti-check" style="font-size:1.2rem;"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="background:#ffebee; color:#c62828; padding:15px; border-radius:10px; margin-bottom:20px; border-left:4px solid #ef5350; display:flex; align-items:center; gap:10px;">
            <i class="ti ti-alert-circle" style="font-size:1.2rem;"></i> {{ session('error') }}
        </div>
        @endif

        @yield('content')
      </div>
    </main>

    <!-- Mobile Bottom Navigation (App-like feel) -->
    <nav class="mobile-bottom-nav admin-bottom-nav">
      <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="ti ti-home"></i>
        <span>Home</span>
      </a>
      <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
        <i class="ti ti-shopping-cart"></i>
        <span>Pesanan</span>
        @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
        <span class="nav-badge">{{ $pendingOrdersCount }}</span>
        @endif
      </a>
      <a href="#" class="scan-btn">
        <div class="icon-circle">
          <i class="ti ti-scan"></i>
        </div>
        <span>Scan</span>
      </a>
      <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
        <i class="ti ti-box"></i>
        <span>Produk</span>
      </a>
      <a href="#">
        <i class="ti ti-user"></i>
        <span>Profil</span>
      </a>
    </nav>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="{{ asset('assets/js/admin.js') }}"></script>
    @stack('scripts')
  </body>
</html>
