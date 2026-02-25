<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Renta Enterprise</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    
    <script>
        // Global variables for JS
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        window.appUrl = "{{ url('/') }}";
    </script>
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Left Side: Illustration / Branding -->
        <div class="login-illustration">
            <div class="illustration-overlay"></div>
            <div class="illustration-content">
                <a href="{{ url('/') }}"><img src="{{ asset('assets/images/Logo-putih.png') }}" alt="Renta Enterprise" class="login-logo"></a>
                <h1>Spesialis Sewa Peralatan Event & Multimedia</h1>
                <p>Masuk untuk mengelola pesanan, melihat riwayat transaksi, dan mendapatkan penawaran eksklusif.</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-form-wrapper">
            <div class="login-form-inner">
                
                <!-- Step 1: Input WhatsApp Number -->
                <div id="step-1-phone" class="form-step active">
                    <h2>Selamat Datang! 👋</h2>
                    <p class="subtitle">Masuk atau Daftar dengan cepat menggunakan nomor WhatsApp Anda. Tanpa perlu kata sandi.</p>

                    <form id="formPhone" onsubmit="handlePhoneSubmit(event)">
                        <div class="input-group">
                            <label for="whatsappNumber">Nomor WhatsApp</label>
                            <div class="phone-input-wrapper">
                                <span class="phone-prefix">+62</span>
                                <input type="tel" id="whatsappNumber" placeholder="812-3456-7890" required autocomplete="off" pattern="[0-9]{9,13}">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-primary login-btn" id="btnSendOtp">
                            <span>Lanjutkan</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="login-footer">
                        <p>Dengan melanjutkan, Anda menyetujui <a href="#">Syarat & Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> kami.</p>
                        <p style="margin-top: 10px;"><a href="{{ url('/forgot-password') }}">Lupa Kata Sandi?</a></p>
                        <a href="{{ url('/') }}" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
                    </div>
                </div>

                <!-- Step 1.5: Lengkapi Data (Untuk Pengguna Baru) -->
                <div id="step-new-user" class="form-step">
                    <a href="#" class="back-to-phone" onclick="backToStep(event, 'step-new-user', 'step-1-phone')"><i class="fas fa-arrow-left"></i> Ganti Nomor</a>
                    <h2>Pengguna Baru? Lengkapi Profil Anda 👤</h2>
                    <p class="subtitle">Sepertinya nomor <strong id="displayPhoneNew"></strong> belum terdaftar. Silakan masukkan nama lengkap Anda untuk melanjutkan.</p>

                    <form id="formNewUser" onsubmit="handleNewUserSubmit(event)">
                        <div class="input-group" style="margin-bottom: 15px;">
                            <label for="fullName">Nama Lengkap</label>
                            <input type="text" id="fullName" class="form-control" placeholder="Masukkan nama sesuai KTP" required autocomplete="name" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        </div>
                        
                        <div class="input-group" style="margin-bottom: 15px;">
                            <label for="regEmail">Email Aktif</label>
                            <input type="email" id="regEmail" class="form-control" placeholder="nama@email.com" required autocomplete="email" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        </div>

                        <div class="input-group" style="margin-bottom: 25px;">
                            <label for="regPassword">Kata Sandi Baru</label>
                            <input type="password" id="regPassword" class="form-control" placeholder="Minimal 6 karakter" required minlength="6" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        </div>
                        
                        <button type="submit" class="btn-primary login-btn">
                            <span>Kirim Kode OTP</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>

                <!-- Step 2: Input OTP -->
                <div id="step-2-otp" class="form-step">
                    <a href="#" class="back-to-phone" id="btnBackFromOtp"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <h2>Verifikasi Kode OTP 🔒</h2>
                    <p class="subtitle">Kami telah mengirimkan kode 4-digit ke WhatsApp <strong id="displayPhone"></strong>.</p>

                    <form id="formOtp" onsubmit="handleOtpSubmit(event)">
                        <div class="otp-input-group">
                            <input type="text" class="otp-box" maxlength="1" required autocomplete="off" autofocus>
                            <input type="text" class="otp-box" maxlength="1" required autocomplete="off">
                            <input type="text" class="otp-box" maxlength="1" required autocomplete="off">
                            <input type="text" class="otp-box" maxlength="1" required autocomplete="off">
                        </div>

                        <button type="submit" class="btn-primary login-btn" id="btnVerifyOtp">
                            <span>Verifikasi & Masuk</span>
                        </button>
                    </form>

                    <div class="resend-wrapper">
                        <p>Belum menerima kode? <span id="timerText">Tunggu <strong id="countdown">60</strong> detik</span><a href="#" id="resendLink" onclick="resendOtp(event)" style="display: none;">Kirim Ulang</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p id="loadingText">Memproses...</p>
    </div>

    <!-- Gunakan asset agar memanggil dari public/assets/js/login.js -->
    <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
