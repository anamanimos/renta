// ==== Login OTP Backend Connected Logic ====

const step1 = document.getElementById('step-1-phone');
const stepNewUser = document.getElementById('step-new-user');
const step2 = document.getElementById('step-2-otp');
const overlay = document.getElementById('loadingOverlay');
const loadingText = document.getElementById('loadingText');
const displayPhone = document.getElementById('displayPhone');
const displayPhoneNew = document.getElementById('displayPhoneNew');
const whatsappNumber = document.getElementById('whatsappNumber');
const fullNameInput = document.getElementById('fullName');
const emailInput = document.getElementById('regEmail');
const passwordInput = document.getElementById('regPassword');

// Global Auth Headers
const apiHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': window.csrfToken
};

// OTP Inputs logic
const otpBoxes = document.querySelectorAll('.otp-box');
otpBoxes.forEach((box, index) => {
    box.addEventListener('input', function() {
        if(this.value.length === 1) {
            if(index < otpBoxes.length - 1) {
                otpBoxes[index + 1].focus();
            }
        }
    });
    box.addEventListener('keydown', function(e) {
        if(e.key === 'Backspace' && this.value.length === 0) {
            if(index > 0) {
                otpBoxes[index - 1].focus();
                otpBoxes[index - 1].value = '';
            }
        }
    });
    box.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim();
        const numericData = pastedData.replace(/\D/g, '').substring(0, otpBoxes.length);
        if (numericData.length > 0) {
            for (let i = 0; i < numericData.length; i++) {
                if (otpBoxes[i]) {
                    otpBoxes[i].value = numericData[i];
                }
            }
            const focusIndex = Math.min(numericData.length, otpBoxes.length - 1);
            otpBoxes[focusIndex].focus();
            if (numericData.length === 4) {
               document.getElementById('btnVerifyOtp').click();
            }
        }
    });
});

let countdownInterval;

function startTimer() {
    let timeLeft = 120; // 2 minutes
    const timerText = document.getElementById('timerText');
    const resendLink = document.getElementById('resendLink');
    const countdownEl = document.getElementById('countdown');
    
    timerText.style.display = 'inline';
    resendLink.style.display = 'none';
    countdownEl.innerText = timeLeft;

    clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        timeLeft--;
        countdownEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            timerText.style.display = 'none';
            resendLink.style.display = 'inline-block';
        }
    }, 1000);
}

function showLoading(text) {
    loadingText.innerText = text;
    overlay.classList.add('active');
}
function hideLoading() {
    overlay.classList.remove('active');
}

// 1. Submit Nomor HP -> Send OTP
async function handlePhoneSubmit(e) {
    e.preventDefault();
    const phone = whatsappNumber.value;
    if(phone.length < 9) {
        alert("Mohon masukkan nomor WhatsApp yang valid.");
        return;
    }

    showLoading("Mengirimkan kode OTP...");
    try {
        const response = await fetch(window.appUrl + '/api/auth/send-otp', {
            method: 'POST',
            headers: apiHeaders,
            body: JSON.stringify({ phone: phone })
        });
        const data = await response.json();
        hideLoading();

        if(response.ok) {
            displayPhone.innerText = "+62 " + (phone.startsWith('0') ? phone.substring(1) : phone);
            step1.classList.remove('active');
            stepNewUser.classList.remove('active');
            step2.classList.add('active');
            otpBoxes[0].focus();
            startTimer();
        } else {
            alert(data.message || "Gagal mengirim OTP.");
        }
    } catch(err) {
        hideLoading();
        alert("Terjadi kesalahan jaringan.");
    }
}

// 2. Submit OTP -> Verify OTP
async function handleOtpSubmit(e) {
    e.preventDefault();
    let otp = "";
    otpBoxes.forEach(box => otp += box.value);
    if(otp.length < 4) {
        alert("Mohon lengkapi 4 digit kode OTP.");
        return;
    }

    showLoading("Memverifikasi OTP...");
    try {
        const response = await fetch(window.appUrl + '/api/auth/verify-otp', {
            method: 'POST',
            headers: apiHeaders,
            body: JSON.stringify({ phone: whatsappNumber.value, otp: otp })
        });
        const data = await response.json();
        
        if(response.ok) {
            if(data.action === 'register_required') {
                hideLoading();
                // Pengguna Baru -> Input Nama/Email/Password
                displayPhoneNew.innerText = displayPhone.innerText;
                step2.classList.remove('active');
                stepNewUser.classList.add('active');
                fullNameInput.focus();
            } else if(data.action === 'login_success') {
                loadingText.innerText = "Login Berhasil!";
                setTimeout(() => {
                    window.location.href = data.redirect || window.appUrl;
                }, 500);
            }
        } else {
            hideLoading();
            alert(data.message || "Kode OTP salah atau kedaluwarsa.");
            otpBoxes.forEach(box => box.value = '');
            otpBoxes[0].focus();
        }
    } catch(err) {
        hideLoading();
        alert("Terjadi kesalahan jaringan.");
    }
}

// 3. Submit New User -> Register Target
async function handleNewUserSubmit(e) {
    e.preventDefault();
    const name = fullNameInput.value.trim();
    const email = emailInput ? emailInput.value.trim() : null;
    const password = passwordInput ? passwordInput.value : null;
    
    if(name.length < 3) {
        alert("Mohon isi nama lengkap dengan benar.");
        return;
    }

    showLoading("Menyimpan rincian dan mendaftarkan akun...");
    try {
        const response = await fetch(window.appUrl + '/api/auth/register', {
            method: 'POST',
            headers: apiHeaders,
            body: JSON.stringify({ name: name, email: email, password: password })
        });
        const data = await response.json();
        
        if(response.ok) {
            loadingText.innerText = "Pendaftaran Berhasil!";
            setTimeout(() => {
                window.location.href = data.redirect || window.appUrl;
            }, 500);
        } else {
            hideLoading();
            let msg = data.message || "Gagal mendaftar.";
            if(data.errors) {
                // simple format validation errors
                msg = Object.values(data.errors).map(e => e.join(', ')).join('\n');
            }
            alert(msg);
        }
    } catch(err) {
        hideLoading();
        alert("Terjadi kesalahan jaringan.");
    }
}

// Dynamic Go Back Handler
function backToStep(e, currentStepId, targetStepId) {
    e.preventDefault();
    document.getElementById(currentStepId).classList.remove('active');
    document.getElementById(targetStepId).classList.add('active');
    
    if(currentStepId === 'step-2-otp') {
        clearInterval(countdownInterval);
        otpBoxes.forEach(box => box.value = '');
    }
}

// Hook backward button on OTP Step
document.getElementById('btnBackFromOtp').addEventListener('click', function(e) {
    backToStep(e, 'step-2-otp', 'step-1-phone');
});

// Resend OTP
async function resendOtp(e) {
    e.preventDefault();
    showLoading("Mengirim ulang kode OTP...");
    try {
        const response = await fetch(window.appUrl + '/api/auth/send-otp', {
            method: 'POST',
            headers: apiHeaders,
            body: JSON.stringify({ phone: whatsappNumber.value })
        });
        hideLoading();
        if(response.ok) {
            startTimer();
            otpBoxes.forEach(box => box.value = '');
            otpBoxes[0].focus();
            alert("Kode OTP baru telah dikirim ke WhatsApp Anda.");
        } else {
            alert("Gagal mengirim ulang OTP.");
        }
    } catch(err) {
        hideLoading();
        alert("Terjadi kesalahan jaringan saat mengirim ulang.");
    }
}
