@extends('layouts.app')

@section('title', 'Profil Saya | Renta Enterprise')

@section('content')
<div class="container" style="padding-top: 20px;">
    <!-- Breadcrumb -->
    <div class="shop-breadcrumb" style="margin-bottom: 30px;">
        <a href="{{ url('/') }}">Beranda</a> <span class="separator"><i class="fas fa-chevron-right"></i></span> 
        <a href="#">Akun Saya</a> <span class="separator"><i class="fas fa-chevron-right"></i></span> 
        <span>Profil Saya</span>
    </div>

    <div class="account-container">
        <!-- Load Sidebar -->
        @include('profile.sidebar')

        <!-- Main Account Content -->
        <div class="account-content">
            <h1 class="shop-page-title" style="text-align: left; padding-bottom: 10px; border-bottom: none; margin-bottom: 20px;">Profil Saya</h1>
            
            <div class="profile-card">
                <div class="profile-header-img">
                    <div class="profile-avatar-large">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="profile-avatar-actions">
                        <h4 style="margin: 0 0 5px; font-size: 18px;">{{ auth()->user()->name }}</h4>
                    </div>
                </div>

                <!-- Notifikasi / Info Alert jika ada session('success') -->
                @if(session('success'))
                <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">
                    {{ session('success') }}
                </div>
                @endif

                <form action="{{ url('/profile/update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <!-- Secara default dinonaktifkan / read-only -->
                            <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
                            <small style="color: var(--text-light); font-size: 12px; margin-top: 5px; display: block;">Email tidak dapat diubah (Login Identity)</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nomor Telepon / WhatsApp</label>
                            <input type="tel" class="form-control" value="{{ auth()->user()->phone_number }}" disabled>
                            <small style="color: var(--text-light); font-size: 12px; margin-top: 5px; display: block;">Nomor WhatsApp terverifikasi</small>
                        </div>
                        <div class="form-group">
                            <label>Ganti Kata Sandi (Opsional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 15px;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
