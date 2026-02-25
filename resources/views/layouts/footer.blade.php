<!-- Footer -->
<footer class="main-footer">
    <div class="container footer-container">
        <div class="footer-widget brand-info">
            <img src="{{ asset('assets/images/Logo-putih.png') }}" alt="Renta" class="footer-logo">
            <p>Spesialis sewa peralatan event dan multimedia lengkap di Malang dan sekitarnya. Solusi profesional untuk mensukseskan berbagai acara Anda.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        
        <div class="footer-widget link-helper">
            <h3>Link Helper</h3>
            <ul>
                <li><a href="{{ url('/cara-sewa') }}"><i class="fas fa-chevron-right"></i> Cara Sewa</a></li>
                <li><a href="{{ url('/payment/verify') }}"><i class="fas fa-chevron-right"></i> Konfirmasi Pembayaran</a></li>
                <li><a href="{{ url('/privacy-policy') }}"><i class="fas fa-chevron-right"></i> Kebijakan Privasi</a></li>
                <li><a href="{{ url('/terms-conditions') }}"><i class="fas fa-chevron-right"></i> Syarat & Ketentuan</a></li>
                <li><a href="{{ url('/sitemap') }}"><i class="fas fa-chevron-right"></i> Sitemap</a></li>
            </ul>
        </div>
        
        <div class="footer-widget services-list">
            <h3>Layanan Kami</h3>
            <ul>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Sound System</a></li>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Lighting & Effect</a></li>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Stage & Rigging</a></li>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Multimedia LED</a></li>
                <li><a href="#"><i class="fas fa-chevron-right"></i> Vendor Event</a></li>
            </ul>
        </div>

        <div class="footer-widget">
            <h3>Hubungi Kami</h3>
            <ul class="contact-info">
                <li>
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Alamat Kantor</strong>
                        Jl. Saman H No.33/35, Purwantoro, Kec. Blimbing, Kota Malang 65122
                    </div>
                </li>
                <li>
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <strong>Telepon / WA</strong>
                        0812-3456-7890
                    </div>
                </li>
            </ul>
            <div class="payment-methods">
                <h3>Metode Pembayaran</h3>
                <div class="payment-icons">
                    <img src="{{ asset('assets/images/bca.png') }}" alt="BCA">
                    <img src="{{ asset('assets/images/bni.png') }}" alt="BNI">
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Copyright © <span>Renta Enterprise</span> {{ date('Y') }}. All Rights Reserved.</p>
    </div>
</footer>
