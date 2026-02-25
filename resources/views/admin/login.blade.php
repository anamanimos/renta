<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Renta Enterprise</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    
    <style>
        /* Gaya Tambahan Spesifik Admin */
        .login-illustration.admin-bg {
            background-image: url('https://images.unsplash.com/photo-1542744173-8e7e53415bb0?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
        .illustration-overlay.admin-overlay {
            background: linear-gradient(135deg, rgba(211,47,47,0.85) 0%, rgba(176,35,35,0.95) 100%);
        }
        
        .login-tabs { display: flex; margin-bottom: 25px; border-bottom: 2px solid #eee; }
        .login-tab { flex: 1; text-align: center; padding: 12px 0; cursor: pointer; color: #888; font-weight: 500; transition: 0.3s; font-size:14px; }
        .login-tab.active { color: var(--primary-color); border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; }
        .login-tab:hover:not(.active) { color: #333; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .remember-me { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; margin-bottom:15px; cursor: pointer;}
        .remember-me input { width:16px; height:16px; cursor: pointer; accent-color: var(--primary-color);}
        
        .admin-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Left Side: Illustration / Branding Admin -->
        <div class="login-illustration admin-bg">
            <div class="illustration-overlay admin-overlay"></div>
            <div class="illustration-content">
                <a href="{{ url('/') }}"><img src="{{ asset('assets/images/Logo-putih.png') }}" alt="Renta Enterprise" class="login-logo"></a>
                <br>
                <div class="admin-badge">PORTAL ADMINISTRATOR</div>
                <h1>Kendalikan Seluruh Operasional Sewa</h1>
                <p>Otentikasi khusus manajemen. Kelola persediaan, pantau pesanan harian, dan mutasi keuangan secara terpadu.</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-form-wrapper">
            <div class="login-form-inner">
                
                <div class="form-step active">
                    <h2>RENTA ADMIN 💼</h2>
                    <p class="subtitle" style="margin-bottom: 5px;">Silakan otentikasi diri Anda untuk masuk ke Dasbor Manajerial.</p>
                    
                    @if(session('error'))
                        <div style="background:#ffebee; color:#c62828; padding:12px; border-radius:6px; margin: 15px 0 20px; font-size:13px; border:1px solid #ffcdd2;">
                            <i class="fas fa-exclamation-circle" style="margin-right: 5px;"></i> {!! session('error') !!}
                        </div>
                    @endif

                    <div class="login-tabs" style="margin-top: 25px;">
                        <div class="login-tab active" onclick="switchTab('password')"><i class="fas fa-lock" style="margin-right:5px;"></i> Kata Sandi</div>
                        <div class="login-tab" onclick="switchTab('otp')"><i class="fab fa-whatsapp" style="margin-right:5px;"></i> Kode OTP WA</div>
                    </div>

                    <!-- Form 1: Password Login -->
                    <div id="tab-password" class="tab-content active">
                        <form action="{{ route('admin.authenticate') }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <label>Alamat Email Admin</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;" required>
                            </div>
                            <div class="input-group">
                                <label>Kata Sandi</label>
                                <input type="password" name="password" class="form-control" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px;" required>
                            </div>
                            <label class="remember-me">
                                <input type="checkbox" name="remember"> Ingat Sesi Saya
                            </label>

                            <button type="submit" class="btn-primary login-btn" style="margin-top: 15px;">
                                <span>Masuk Dashboard</span>
                                <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Form 2: OTP Login -->
                    <div id="tab-otp" class="tab-content">
                        <!-- Form Request OTP -->
                        <form id="formRequestOtp" onsubmit="handleRequestOtp(event)">
                            <div class="input-group">
                                <label>Nomor WhatsApp Admin</label>
                                <input type="text" id="adminPhone" class="form-control" placeholder="Contoh: 081234567890" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;" required>
                            </div>
                            
                            <label class="remember-me">
                                <input type="checkbox" id="rememberOtp"> Ingat Sesi Saya
                            </label>

                            <button type="submit" class="btn-primary login-btn" id="btnRequestOtp" style="margin-top: 15px;">
                                <span class="btn-text">Kirim OTP ke WA</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>

                        <!-- Form Verify OTP -->
                        <form id="formVerifyOtp" style="display:none;" onsubmit="handleVerifyOtp(event)">
                            <input type="hidden" id="verifiedPhone">
                            <input type="hidden" id="verifiedRemember">
                            <div class="input-group">
                                <label>Isi Kode OTP</label>
                                <input type="text" id="adminOtpCode" class="form-control" maxlength="4" placeholder="4 Digit" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; text-align:center; font-size:20px; letter-spacing:15px; font-weight: 600;" required>
                            </div>

                            <button type="submit" class="btn-primary login-btn" id="btnVerifyOtp" style="margin-top: 20px;">
                                <span class="btn-text">Verifikasi & Masuk</span>
                                <i class="fas fa-check-circle"></i>
                            </button>
                            
                            <div style="text-align:center; margin-top:15px;">
                                <a href="#" onclick="cancelOtp(event)" style="color:var(--text-light); text-decoration: underline; font-size: 13px;">Ganti Nomor Admin</a>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p id="loadingText">Mengirim otentikasi...</p>
    </div>

    <script>
        const overlay = document.getElementById('loadingOverlay');
        const loadingText = document.getElementById('loadingText');

        function toggleLoading(active, text = 'Memproses...') {
            if (active) {
                loadingText.innerText = text;
                overlay.classList.add('active');
            } else {
                overlay.classList.remove('active');
            }
        }

        function switchTab(tabId) {
            document.querySelectorAll('.login-tab').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            
            event.currentTarget.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        }

        function cancelOtp(e) {
            if(e) e.preventDefault();
            document.getElementById('formVerifyOtp').style.display = 'none';
            document.getElementById('formRequestOtp').style.display = 'block';
            document.getElementById('adminOtpCode').value = '';
        }

        async function handleRequestOtp(e) {
            e.preventDefault();
            const phone = document.getElementById('adminPhone').value.trim();
            const remember = document.getElementById('rememberOtp').checked;
            
            toggleLoading(true, "Mengecek Izin Admin & Mengirim OTP...");

            try {
                const response = await fetch('/api/admin/auth/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ phone: phone })
                });

                const result = await response.json();
                toggleLoading(false);
                
                if (response.ok && result.success) {
                    document.getElementById('formRequestOtp').style.display = 'none';
                    document.getElementById('formVerifyOtp').style.display = 'block';
                    document.getElementById('verifiedPhone').value = phone;
                    document.getElementById('verifiedRemember').value = remember ? '1' : '0';
                    alert(result.message);
                } else {
                    alert(result.message || 'Akses ditolak / Gagal meminta OTP.');
                }
            } catch (err) {
                toggleLoading(false);
                alert("Kesalahan jaringan.");
            }
        }

        async function handleVerifyOtp(e) {
            e.preventDefault();
            const phone = document.getElementById('verifiedPhone').value;
            const otpCode = document.getElementById('adminOtpCode').value.trim();
            const remember = document.getElementById('verifiedRemember').value === '1';

            toggleLoading(true, "Memvalidasi OTP Admin...");

            try {
                const response = await fetch('/api/admin/auth/verify-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ phone: phone, otp: otpCode, remember: remember })
                });

                const result = await response.json();
                toggleLoading(false);

                if (response.ok && result.success) {
                    window.location.href = result.redirect;
                } else {
                    alert(result.message || 'OTP bermasalah.');
                }
            } catch (error) {
                toggleLoading(false);
                alert("Kesalahan jaringan.");
            }
        }
    </script>
</body>
</html>
