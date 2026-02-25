<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi | Renta Enterprise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Left Side: Illustration / Branding -->
        <div class="login-illustration">
            <div class="illustration-overlay"></div>
            <div class="illustration-content">
                <a href="{{ url('/') }}"><img src="{{ asset('assets/images/Logo-putih.png') }}" alt="Renta Enterprise" class="login-logo"></a>
                <h1>Spesialis Sewa Peralatan Event & Multimedia</h1>
                <p>Pulihkan akses akun Anda dengan cepat dan aman.</p>
            </div>
        </div>

        <!-- Right Side: Reset Password Form -->
        <div class="login-form-wrapper">
            <div class="login-form-inner">
                
                <div id="step-forgot-password" class="form-step active">
                    <h2>Lupa Kata Sandi? 🔒</h2>
                    <p class="subtitle">Masukkan nomor WhatsApp yang terdaftar. Kami akan mengirimkan OTP untuk mengatur ulang kata sandi Anda.</p>

                    <form id="formForgotPassword" onsubmit="handleForgotPasswordSubmit(event)">
                        @csrf
                        <div class="input-group">
                            <label for="recoveryPhone">Nomor WhatsApp</label>
                            <input type="text" id="recoveryPhone" class="form-control" placeholder="Contoh: 081234567890" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        </div>
                        
                        <button type="submit" class="btn-primary login-btn" style="margin-top: 25px;">
                            <span>Kirim Kode Reset via WA</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

                    <div class="login-footer">
                        <p>Teringat kata sandi Anda? <a href="{{ route('login') }}">Kembali untuk Masuk</a></p>
                    </div>
                </div>

                <!-- Formulir Input OTP Reset (Tersembunyi Awalnya) -->
                <div id="step-reset-otp" class="form-step" style="display:none;">
                    <h2>Verifikasi Reset 🔑</h2>
                    <p class="subtitle">Kami telah mengirim kode 4 digit via WhatsApp. Masukkan kode tersebut dan buat kata sandi baru.</p>

                    <form id="formResetPassword" onsubmit="handleResetPasswordSubmit(event)">
                        @csrf
                        <input type="hidden" id="resetPhoneValue">
                        <div class="input-group">
                            <label>Kode OTP Baru</label>
                            <input type="text" id="resetOtp" class="form-control" maxlength="4" placeholder="4 Digit Kode" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px; text-align: center; letter-spacing: 12px; font-size: 20px;">
                        </div>
                        <div class="input-group">
                            <label>Kata Sandi Baru</label>
                            <input type="password" id="newPassword" class="form-control" placeholder="Minimal 6 karakter" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>
                        <button type="submit" class="btn-primary login-btn" style="margin-top:25px;">
                            <span>Atur Ulang & Masuk</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p id="loadingText">Memproses...</p>
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

        async function handleForgotPasswordSubmit(e) {
            e.preventDefault();
            const phone = document.getElementById('recoveryPhone').value.trim();
            
            if(phone.length < 9) {
                alert("Mohon masukkan nomor WhatsApp yang benar.");
                return;
            }

            toggleLoading(true, "Mengirim kode OTP Reset via WA...");

            try {
                const response = await fetch('/api/auth/forgot-password-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ phone: phone })
                });

                const result = await response.json();
                toggleLoading(false);

                if (response.ok && result.success) {
                    // Pindah ke tahap ke-2 (Input OTP dan Password Baru)
                    document.getElementById('step-forgot-password').style.display = 'none';
                    document.getElementById('step-reset-otp').style.display = 'block';
                    document.getElementById('resetPhoneValue').value = phone;
                    alert(result.message);
                } else {
                    alert(result.message || 'Terjadi kesalahan sistem.');
                }
            } catch (error) {
                toggleLoading(false);
                alert("Galat koneksi server. Silakan coba lagi.");
            }
        }

        async function handleResetPasswordSubmit(e) {
            e.preventDefault();
            const phone = document.getElementById('resetPhoneValue').value;
            const otpCode = document.getElementById('resetOtp').value.trim();
            const newPassword = document.getElementById('newPassword').value;

            if (otpCode.length !== 4) {
                alert('Kode OTP tidak valid.');
                return;
            }

            if (newPassword.length < 6) {
                alert('Kata sandi harus minimal 6 karakter.');
                return;
            }

            toggleLoading(true, "Memverifikasi OTP dan mengganti sandi...");

            try {
                const response = await fetch('/api/auth/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ phone: phone, otp: otpCode, password: newPassword })
                });

                const result = await response.json();
                toggleLoading(false);

                if (response.ok && result.success) {
                    alert(result.message);
                    window.location.href = result.redirect;
                } else {
                    alert(result.message || 'Gagal mengatur ulang kata sandi.');
                }
            } catch (error) {
                toggleLoading(false);
                alert("Galat komunikasi data.");
            }
        }
    </script>
</body>
</html>
