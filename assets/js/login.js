// ==== Login OTP Mockup Logic ====

const step1 = document.getElementById('step-1-phone');
const step2 = document.getElementById('step-2-otp');
const overlay = document.getElementById('loadingOverlay');
const loadingText = document.getElementById('loadingText');
const displayPhone = document.getElementById('displayPhone');
const whatsappNumber = document.getElementById('whatsappNumber');

// OTP Inputs logic
const otpBoxes = document.querySelectorAll('.otp-box');

// Auto focus next input and handle paste
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

    // Handle paste event
    box.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim();
        
        // Ensure we only process numbers and limit to number of boxes
        const numericData = pastedData.replace(/\D/g, '').substring(0, otpBoxes.length);
        
        if (numericData.length > 0) {
            for (let i = 0; i < numericData.length; i++) {
                if (otpBoxes[i]) {
                    otpBoxes[i].value = numericData[i];
                }
            }
            
            // Focus on the last filled box or proceed to submit if full
            const focusIndex = Math.min(numericData.length, otpBoxes.length - 1);
            otpBoxes[focusIndex].focus();
            
            // Auto submit if 4 digits are pasted
            if (numericData.length === 4) {
               document.getElementById('btnVerifyOtp').click();
            }
        }
    });
});

let countdownInterval;

function startTimer() {
    let timeLeft = 60;
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

function showLoading(text, duration, callback) {
    loadingText.innerText = text;
    overlay.classList.add('active');
    
    setTimeout(() => {
        overlay.classList.remove('active');
        if(callback) callback();
    }, duration);
}

// Handle Step 1 Submit
function handlePhoneSubmit(e) {
    e.preventDefault();
    const phone = whatsappNumber.value;
    
    if(phone.length < 9) {
        alert("Mohon masukkan nomor WhatsApp yang valid.");
        return;
    }

    displayPhone.innerText = "+62 " + phone;
    
    showLoading("Mengirim kode OTP...", 1500, () => {
        step1.classList.remove('active');
        step2.classList.add('active');
        otpBoxes[0].focus();
        startTimer();
    });
}

// Handle Go Back
function backToPhoneStep(e) {
    e.preventDefault();
    step2.classList.remove('active');
    step1.classList.add('active');
    clearInterval(countdownInterval);
    otpBoxes.forEach(box => box.value = '');
}

// Handle Resend
function resendOtp(e) {
    e.preventDefault();
    showLoading("Mengirim ulang kode OTP...", 1000, () => {
        startTimer();
        otpBoxes.forEach(box => box.value = '');
        otpBoxes[0].focus();
        // Mock notification
        setTimeout(() => alert("Kode OTP baru (misal: 1234) telah dikirim ke WhatsApp Anda."), 100);
    });
}

// Handle OTP Submit
function handleOtpSubmit(e) {
    e.preventDefault();
    
    let otp = "";
    otpBoxes.forEach(box => otp += box.value);
    
    if(otp.length < 4) {
        alert("Mohon lengkapi 4 digit kode OTP.");
        return;
    }

    // Mock verification
    showLoading("Memverifikasi...", 1500, () => {
        if(otp === "1234") {
            // Success
            loadingText.innerText = "Login Berhasil!";
            overlay.classList.add('active');
            setTimeout(() => {
                window.location.href = "index.html"; // Redirect to home
            }, 1000);
        } else {
            // Error
            alert("Kode OTP salah. Hint: gunakan 1234 untuk percobaan.");
            otpBoxes.forEach(box => box.value = '');
            otpBoxes[0].focus();
        }
    });
}
