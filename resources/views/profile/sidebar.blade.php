<!-- Sidebar Account -->
<aside class="account-sidebar">
    <div class="account-user-info">
        <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
        <div class="user-details">
            <h4>{{ auth()->user()->name }}</h4>
            <p>{{ auth()->user()->email ?? auth()->user()->phone_number }}</p>
        </div>
    </div>
    <ul class="account-menu">
        <li><a href="{{ url('/profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}"><i class="far fa-user"></i> Profil Saya</a></li>
        <li><a href="{{ url('/orders') }}" class="{{ request()->is('orders*') ? 'active' : '' }}"><i class="fas fa-shopping-bag"></i> Pesanan Saya</a></li>
        <li><a href="{{ url('/addresses') }}" class="{{ request()->is('addresses*') ? 'active' : '' }}"><i class="far fa-map"></i> Buku Alamat</a></li>
        <li>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <a href="#" onclick="event.preventDefault(); this.closest('form').submit();" style="color:var(--text-dark); display:flex; align-items:center; padding:14px 25px;"><i class="fas fa-sign-out-alt" style="margin-right:10px;"></i> Keluar</a>
            </form>
        </li>
    </ul>
</aside>

<!-- Account Styles -->
@push('styles')
<style>
    .account-container {
        display: flex;
        gap: 30px;
        align-items: flex-start;
        margin-bottom: 50px;
    }
    .account-content {
        flex: 1;
    }
    .profile-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 35px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: var(--transition);
    }
    .profile-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }
    .profile-header-img {
        display: flex;
        align-items: center;
        gap: 25px;
        margin-bottom: 35px;
        padding-bottom: 30px;
        border-bottom: 1px solid #f0f0f0;
    }
    .profile-avatar-large {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), #f76b6b);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(211, 47, 47, 0.2);
        border: 3px solid #fff;
    }
    .account-menu {
        list-style: none;
        padding: 10px 0;
        margin: 0;
    }
    .account-menu li a {
        display: flex;
        align-items: center;
        padding: 14px 25px;
        color: var(--text-dark);
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 14px;
        border-left: 3px solid transparent;
        text-decoration: none;
    }
    .account-menu li a i {
        width: 24px;
        font-size: 16px;
        color: var(--text-light);
        transition: var(--transition);
        margin-right: 10px;
    }
    .account-menu li a:hover, .account-menu li a.active {
        background: #fff8f8;
        color: var(--primary-color);
        border-left-color: var(--primary-color);
    }
    .account-menu li a:hover i, .account-menu li a.active i {
        color: var(--primary-color);
    }
    .form-row {
        display: flex;
        gap: 25px;
        margin-bottom: 25px;
    }
    .form-group {
        flex: 1;
    }
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }
    .form-control {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
        background-color: #fbfbfb;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
    }
    .form-control:disabled {
        background: #f0f0f0;
        color: #777;
        cursor: not-allowed;
        border-color: #e5e5e5;
    }
    @media (max-width: 991px) {
        .account-container {
            flex-direction: column;
            margin-bottom: 30px;
            width: 100%;
            overflow: hidden;
        }
        .account-content {
            width: 100%;
            min-width: 0px;
        }
        .account-sidebar {
            display: none;
        }
        .form-row {
            flex-direction: column;
            gap: 20px;
        }
    }
</style>
@endpush
